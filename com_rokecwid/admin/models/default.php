<?php
/**
 * @version   1.2 April 10, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Based on work by
 * @author Rick Blalock
 * @package Joomla
 * @subpackage ecwid
 * @license GNU/GPL
 *
 * ECWID.com e-commerce wrapper
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );
jimport('joomla.filesystem.file');

/**
 * @package		Joomla
 * @subpackage	Config
 */
class RokEcwidModelDefault extends JModel
{
	
	/**
	 * Get the params for the configuration variables
	 */
	function &getParams($component="com_rokecwid")
	{
		static $instance;

		if ($instance == null)
		{
			$table =& JTable::getInstance('component');
			$table->loadByOption( $component );

			// work out file path
			$option	= preg_replace( '#\W#', '', $table->option );
			$path	= JPATH_ADMINISTRATOR.DS.'components'.DS.$option.DS.'ecwid-config.xml';

			if (file_exists($path)){
				$instance = new JParameter($table->params, $path);
			} else {
				$instance = new JParameter($table->params);
			}
						
		}
		return $instance;
	}
	
	function render($self, $name = 'params', $group = '_default'){
		if (!isset($self->_xml[$group])) {
			return false;
		}

		$params = $self->getParams($name, $group);
		$html = array ();
		$html[] = '<div id="rokecwid-paramslist">';

		if ($description = $self->_xml[$group]->attributes('description')) {
			// add the params description to the display
			$desc	= JText::_($description);
			$html[]	= '<div id="rokecwid-params-description>'.$desc.'</div>';
		}
		
		$i = 0;
		foreach ($params as $param){
			$class = '';
			
			if (!$i){
				$html[] = '<div class="left-column column">';
				$class = ' first';
			}
			if ($i == round(count($params) / 2) - 1){
				$class = ' last';
			}
			if ($i == round(count($params) / 2)){
				$html[] = '</div><div class="right-column column">';
				$class = ' first';
			}
			if ($i >= count($params) - 1){
				$class = ' last';
			}
			
			$html[] = '<div class="rokecwid-row'.$class.'">';

			if ($param[0]) {
				$html[] = '<div class="label"><span>'.$param[0].'</span></div>';
				$html[] = '<div class="value">'.$param[1].'</div>';
			} else {
				$html[] = '<div class="value">'.$param[1].'</div>';
			}

			$html[] = '</div>';
			
			if ($i >= count($params) - 1) $html[] = '</div>';
			$i++;
		}

		if (count($params) < 1) {
			$html[] = "<div class='notice'>".JText::_('There are no Parameters for this item')."</div>";
		}

		$html[] = '</div>';

		return implode("\n", $html);
	}
	
	function loadTemplate($tmpl = false){
		if (!$tmpl) return false;
		
		$template = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rokecwid'.DS.'templates'.DS.$tmpl;
		if (JFile::exists($template.'.php')) return file_get_contents($template . '.php');
		else if (JFile::exists($template.'.html')) return file_get_contents($template . '.html');
		else return false;
	}
	
}
?>
