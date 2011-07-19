<?php
defined('JPATH_BASE') or die();

jimport('joomla.application.component.view');

class RokEcwidAdminView extends JView {
	function __construct($config = array()){
		global $mainframe, $option;
		
		parent::__construct($config);
		
		$document =& JFactory::getDocument();
		$document->addStyleSheet('components/'.$option.'/assets/rokecwid.css');
		$document->addScript('components/'.$option.'/assets/rokecwid.js');
	}
}