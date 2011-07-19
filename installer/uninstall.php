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

$err_status = false;
$db =& JFactory::getDBO();



$cogs = array();


$cogs_tag =& $this->manifest->getElementByPath('cogs');

/**
	Get the XML tag and convert the sub tags to an array for processing
**/
if (is_a($cogs_tag, 'JSimpleXMLElement') && count($cogs_tag->children())) {
    $cogs_sub_tags = $cogs_tag->children();
	reset($cogs_sub_tags);
	while (list($key, $value) = each($cogs_sub_tags)) {
		$cog =& $cogs_sub_tags[$key]; 
		
		switch ($cog->name()) {
			case 'plugin':
				$query = 'SELECT * FROM #__plugins WHERE element='.$db->Quote($cog->attributes('name')) . ' and folder=' . $db->Quote($cog->attributes('group'));
				break;
			case 'module':
				$query = 'SELECT * FROM #__modules WHERE module='.$db->Quote($cog->attributes('name'));
				break;
			case 'component':
				$query = 'SELECT * FROM #__components WHERE name='.$db->Quote($cog->data());
				break;
			case 'template':
				$query = 'SELECT * from #__templates_menu where template=' . $db->Quote($cog->attributes('name'));
				break;
		}
		
		// query extension id and client id
		$db->setQuery($query);
		$db_cog = $db->loadObject();
		
		$cogs[] = array( 
						'name' => $cog->data(), 
						'type' => strtolower($cog->name()), 
						'id' => isset($db_cog->id) ? $db_cog->id : $cog->attributes('name'),
						'client_id' => isset($db_cog->client_id) ? $db_cog->client_id : 0,						
						'installer' => new RokInstaller(),
						'uninstall' => ( $cog->attributes('uninstall') != null) ? (strtolower($cog->attributes('uninstall')) == 'true') ? true : false : false, 
						'status' => false,
						);
	}
}

// uninstall additional extensions
for ($i = 0; $i < count($cogs); $i++) {
	$cog =& $cogs[$i];
	if($cog['uninstall']) { 
		if (_reset_core($db, $cog) && $cog['installer']->uninstall($cog['type'], $cog['id'], $cog['client_id'])) {
			$cog['status'] = true;
		}
		else {
			$err_status = true;
		}
	}
}

 
function _reset_core(&$db, &$cog) {
	if ($cog['type'] == 'plugin' || $cog['type'] == 'module' || $cog['type'] == 'component'){ 
		switch ($cog['type']) {
			case 'plugin':
				$query = 'UPDATE #__plugins set iscore = 0 WHERE id = ' . $cog['id'];
				break;
			case 'module':
				$query = 'UPDATE #__modules set iscore = 0 WHERE id = ' . $cog['id'];
				break;
			case 'component':
				$query = 'UPDATE #__components set iscore = 0 WHERE id = ' . $cog['id'];
				break;
		}
		
		$db->setQuery($query);
		if ($db->query() == false)
			return false;
	}
	return true;
}

function com_uninstall() {
	global $err_status;
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
				if ($cog['uninstall']):
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="key"><?php echo $cog['name']; ?> (<?php echo JText::_($cog['type']); ?>)</td>
				<td>
					<?php $style = $cog['status'] ? 'font-weight: bold; color: green;' : 'font-weight: bold; color: red;'; ?>
					<span style="<?php echo $style; ?>"><?php echo $cog['status'] ? JText::_('Uninstalled successfully') : JText::_('Uninstall FAILED'); ?></span>
				</td>
			</tr>
		<?php 	endif;
			endwhile; ?>
	</tbody>
</table>