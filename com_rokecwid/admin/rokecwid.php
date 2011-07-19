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

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller
require_once JPATH_COMPONENT . DS . 'controller.php';
require_once JPATH_COMPONENT.DS.'views'.DS.'view.html.php';

// Require specific controller if requested
if ($controller = JRequest::getCmd('controller', 'default')){
	require_once JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
}

// Create the controller
$classname	= 'RokEcwidController'.$controller;
$controller	= new $classname();

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
?>