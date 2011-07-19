<?php
/**
 * @package   Installer Bundle Framework - RocketTheme
 * @version   1.9 October 21, 2010
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Installer uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 */
defined('_JEXEC') or die('Restricted access');
require_once(dirname(__FILE__).DS.'lib'.DS.'RokInstaller.php');

global $mainframe;
global $installer;
global $err_status;
global $keep_installer;

$installer = $this;

$err_status = false;

$db =& JFactory::getDBO();

$name_tag = $this->manifest->getElementByPath('name');
$bundle_name = $name_tag->data();
$bundle_name = preg_replace("/ /","", $bundle_name);

$cogs = array();

$cogs_tag = $this->manifest->getElementByPath('cogs');
$keep_installer = ( $cogs_tag->attributes('keepinstaller') != null) ?(strtolower($cogs_tag->attributes('keepinstaller')) == 'true') ? true : false : true;


/**
	Get the XML tag and convert the sub tags to an array for processing
**/
if (is_a($cogs_tag, 'JSimpleXMLElement') && count($cogs_tag->children())) {
    $cogs_sub_tags = $cogs_tag->children();
	reset($cogs_sub_tags);
	while (list($key, $value) = each($cogs_sub_tags)) {
		$cog =& $cogs_sub_tags[$key]; 
		$cogs[] = array( 
						'name' => $cog->attributes('name'),
						'group' => $cog->attributes('group'), 
						'title' => $cog->data(),
						'type' => strtolower($cog->name()), 
						'folder' => $this->parent->getPath('source').DS.$cog->attributes('folder'), 
						'installer' => new RokInstaller(), 
						'status' => false, 
						'published' => ( $cog->attributes('published') != null) ?(strtolower($cog->attributes('published')) == 'true') ? true : false : false,
						'core' => ( $cog->attributes('core') != null) ? (strtolower($cog->attributes('core')) == 'true') ? true : false : false,
						'enabled' =>  ($cog->attributes('enabled') != null) ?(strtolower($cog->attributes('enabled')) == 'true') ? true : false : false,
						'access' => ( $cog->attributes('access') != null) ? $cog->attributes('access') : 0,
                        'client' => ($cog->attributes('client') != null) ? $cog->attributes('client') : 0,
                        'position' => ($cog->attributes('position') != null) ? $cog->attributes('position') : 'left',
                        'moduletitle' => $cog->attributes('title'),
                        'showtitle' => ($cog->attributes('showtitle') != null) ?(strtolower($cog->attributes('showtitle')) == 'true') ? true : false : true,
                        'ordering' => ($cog->attributes('ordering') != null) ? $cog->attributes('ordering') : 0,
                        'params' => $cog->attributes('params')
						);
	}
}

/**
	Run the installer for each sub component
**/
if (!empty($cogs)) { 
	for ($i = 0; $i < count($cogs); $i++) {
		$cog =& $cogs[$i];
		if (
            // installer run successfully
            $cog['installer']->install($cog['folder'])
            // and is an upgrade or is not an upgrade and the adjust settings runs successfully
            && ($cog['installer']->upgrade || (!$cog['installer']->upgrade && _adjust_settings($db, $cog)))
        )
        {
		    $cog['status'] = true;
		} else {
			$err_status = true;
			break;
		}
	}
}

//update the bundle component to not enabled.
$query = 'UPDATE #__components set enabled = 0 where name ='. $db->Quote($bundle_name);
// query extension id and client id
$db->setQuery($query);
$db->query();

/**
	Rollback on error
**/
if ($err_status) {
	$this->parent->abort(JText::_('Component').' '.JText::_('Install').': '.JText::_('Error'), 'component');
	for ($i = 0; $i < count($cogs); $i++) { 
		if ($cogs[$i]['status']) {
			$cogs[$i]['installer']->abort(JText::_($cogs[$i]['type']).' '.JText::_('Install').': '.JText::_('Error'), $cogs[$i]['type']);
			$cogs[$i]['status'] = false;
		}
	}
}
 
function _adjust_settings(&$db, &$cog) {
	if ($cog['type'] == 'plugin' || $cog['type'] == 'module' || $cog['type'] == 'component'){ 
		switch ($cog['type']) {
			case 'plugin':
				$query = 'SELECT * FROM #__plugins WHERE element='.$db->Quote($cog['name']) . ' and folder=' . $db->Quote($cog['group']); 
				break;
			case 'module':
				$query = 'SELECT * FROM #__modules WHERE module='.$db->Quote($cog['name']);
				break;
			case 'component':
				$query = 'SELECT * FROM #__components WHERE name='.$db->Quote($cog['title']);
				break;
		}
				
		// query extension id and client id
		$db->setQuery($query);
		$db_cog = $db->loadObject();

        $params = ($cog['params'])?', params = ' . $db->Quote($cog['params']) : '';
        $ordering = ($cog['ordering'])?', ordering = ' . $cog['ordering'] : '';
        $core =  ($cog['core'])?1:0;
        switch ($cog['type']) {
			case 'plugin':
                $client = ($cog['client'])?', client_id = ' . $cog['client'] : '';
				$published  =  ($cog['published'])?1:0;
				$query = 'UPDATE #__plugins set published=' . $published . ', iscore = ' . $core . ', access = ' . $cog['access'] . $client. $params . $ordering . ' where id = ' . $db_cog->id;
				break;
			case 'module':
				$published  =  ($cog['published'])? 'true':'false';
                $client = ($cog['client'])?', client_id = ' . $cog['client'] : '';
                $position = ($cog['position'])?', position = ' . $db->Quote($cog['position']) : '';
                $title = ($cog['moduletitle'])?', title = ' . $db->Quote($cog['title']) : '';
                $showtitle = ($cog['showtitle'])?', showtitle = ' . (($cog['showtitle'])?1:0) : '';
				$query = 'UPDATE #__modules set published = ' . $published . ', iscore = ' . $core . ', access = ' . $cog['access'] . $client. $position. $title. $showtitle . $ordering . $params .' where id = ' . $db_cog->id;
				break;
			case 'component':
				$enabled  =  ($cog['enabled'])?1:0;
				$query = 'UPDATE #__components set enabled = ' . $enabled . ', iscore = ' . $core . $params . $ordering.' where id = ' . $db_cog->id;
				break;
		}
		// query extension id and client id
		$db->setQuery($query);
		if ($db->query() == false)
			return false;
	}
	return true;
}

function com_install() {
    jimport('joomla.filesystem.file');
    global $err_status, $installer;
    global $keep_installer;
    
    if (!$keep_installer) {
        $installer->parent->abort('','component');
        $installer->parent->_paths['extension_administrator'] = dirname(dirname($installer->parent->_paths['manifest']));
    }
	return !$err_status;
}

?>
<h3><?php echo JText::_('Bundled Installations'); ?></h3>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title"><?php echo JText::_('Unit'); ?></th>
			<th width="60%"><?php echo JText::_('Status'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
	</tfoot>
	<tbody>
		<?php 
			reset($cogs);
			while (list($key, $value) = each($cogs)):
				$cog =&$cogs[$key]; 
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="key"><?php echo $cog['title']; ?> (<?php echo JText::_($cog['type']); ?>)</td>
				<td>
					<?php $style = $cog['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
					<span style="<?php echo $style; ?>"><?php echo $cog['status'] ? JText::_('Installed') : JText::_('NOT Installed'); ?></span>
				</td>
			</tr>
		<?php endwhile; ?>
	</tbody>
</table>
