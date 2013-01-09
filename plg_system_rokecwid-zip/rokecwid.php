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

jimport('joomla.plugin.plugin');

class plgSystemRokEcwid extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgSystemEcwid(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

    /**
     * onBeforeRender handler
     *
     * Adds the prefetch metas
     *
     * @access  public
     * @return null
     */
    function onBeforeRender()
    {
        $doc = JFactory::getDocument();

        if ($doc->getType() == 'html') {
            $doc->addCustomTag('<link rel="dns-prefetch" href="//images-cdn.ecwid.com/">');
            $doc->addCustomTag('<link rel="dns-prefetch" href="//images.ecwid.com/">');
            $doc->addCustomTag('<link rel="dns-prefetch" href="//app.ecwid.com/">');
        }
    }

	/**
	 * onAfterInitialise handler
	 *
	 * Adds the mtupgrade folder to the list of directories to search for JHTML helpers.
	 *
	 * @access	public
	 * @return null
	 */
	function onAfterRender()
	{
		
		//print_r ($body);
		
		if (defined('ECWID_SCRIPT')) {
			$app = &JFactory::getApplication('site');
			$eparams = $app->getParams('com_rokecwid');
			$body = JResponse::getBody();
						
			$ecwid_script = "app.ecwid.com/script.js";
			$protocol = (isset($_SERVER['https']) && $_SERVER['https'] == 'on') ? 'https://' : 'http://';
			$escript = '<script type="text/javascript" src="'. $protocol.$ecwid_script.'?'. $eparams->get('storeID', 1003). '"></script>';
			
			// split up the body after the body tag			
			$matches = preg_split('/(<body.*?>)/i', $body, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE); 

			
			/* assemble the HTML output back with the iframe code in it */
			$body = $matches[0] . $matches[1] . $escript . $matches[2];
			
			JResponse::setBody($body);
			
		
		}
	}
}
