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
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');
jimport('joomla.application.component.helper');

/**
 * HTML View class for the Rokdownloads component
 *
 * @static
 * @package		Joomla
 * @subpackage	RokDownloads
 * @since 1.0
 */

class RokEcwidViewDefault extends RokEcwidAdminView
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		global $mainframe, $option;
		$document = & JFactory::getDocument();
		// Set toolbar items for the page
		JToolBarHelper::title(   JText::_( 'Ecwid Configuration' ), 'rokecwid.png' );
		JToolBarHelper::save();
		JToolBarHelper::cancel('cancel','Reset');

		$uri	=& JFactory::getURI();
		// Get the page/component configuration
		$model = $this->getModel();
		$params = &$model->getParams();
		$component = JRequest::getVar('component');

		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('Ecwid Edit Configuration') );

		$this->assignRef('groups',$params->getGroups());
		$this->assignRef('params', $params);
		$this->assignRef('component', $component);
		$this->assignRef('request_url',	$uri->toString());
		parent::display($tpl);
	}

}
?>
