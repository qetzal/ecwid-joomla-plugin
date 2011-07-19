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
class RokEcwidControllerDefault extends RokEcwidController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		$default['default_task'] = 'edit';
		parent::__construct( $default );

		$this->registerTask( 'apply', 'save' );
	}

	/**
	 * Show the configuration edit form
	 * @param string The URL option
	 */
	function edit()
	{
		global $mainframe;
		$component = 'com_rokecwid';

		// load the component's language file
		$lang = & JFactory::getLanguage();
		$lang->load( $component );

		JRequest::setVar('view', 'default');
		JRequest::setVar('component', $component);
		parent::display();
	}

	/**
	 * Save the configuration
	 */
	function save()
	{
		$component = JRequest::getVar( 'component' );

		$table =& JTable::getInstance('component');
		if (!$table->loadByOption( $component ))
		{
			JError::raiseWarning( 500, 'Not a valid component' );
			return false;
		}
		
	
		
		$post = JRequest::get( 'post' );
		$post['option'] = $component;
		$table->bind( $post );

		// pre-save checks
		if (!$table->check()) {
			JError::raiseWarning( 500, $table->getError() );
			return false;
		}

		// save the changes
		if (!$table->store()) {
			JError::raiseWarning( 500, $table->getError() );
			return false;
		}

		//$this->setRedirect( 'index.php?option=com_config', $msg );
		$msg = "Configuration has been saved";
		$this->setRedirect('index.php?option=com_rokecwid&controller=default', $msg);
	}

	/**
	 * Cancel operation
	 */
	function cancel()
	{
		$this->setRedirect('index.php?option=com_rokecwid&controller=default');
	}
}