<?php
/**
 * @version   1.3 July 15, 2011
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


/**
 * ecwid Common Helper
 */
class RokEcwidCommon  {

	function displayECWIDScript() {
	
		// Get Component parameters
		$eparams = JComponentHelper::getParams( 'com_rokecwid' );
		
		$body = JResponse::getBody();
		
		//var_dump ($body);
		
		$ecwid_script = "app.ecwid.com/script.js";
		$protocol = (isset($_SERVER['https']) && $_SERVER['https'] == 'on') ? 'https://' : 'http://';
		
		if (!defined('ECWID_SCRIPT')) {
			//echo '<script type="text/javascript" src="'. $protocol.$ecwid_script.'?'. $eparams->get('storeID', 1003). '"></script>'."\n";
			define('ECWID_SCRIPT',1);
		
		}
		
	
	}	
	
	
}
?>
