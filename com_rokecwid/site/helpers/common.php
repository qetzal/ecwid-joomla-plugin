<?php
/**
 * Joomla! 1.5 component ecwid
 *
 * @version $Id: common.php 2010-01-30 07:51:07 svn $
 * @author Andy Miller
 * @package Joomla
 * @subpackage rokecwid
 * @license GNU/GPL
 *
 * ECWID.com e-commerce wrapper
 *
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