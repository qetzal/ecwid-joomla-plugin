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

jimport('joomla.application.component.controller');

/**
 * ecwid Component Controller
 */
class RokEcwidController extends JController {
	function display($cachable = false, $urlparams = false) {

        if (isset($_GET['ecwid_product_id']) || isset($_GET['ecwid_category_id'])) {
            $ecwid_page = RokEcwidController::buildEcwidUrl();
            $ecwid_page .= '#!/~/';

            if (isset($_GET['ecwid_product_id'])) {
                $redirect = $ecwid_page . 'product/id=' . $_GET['ecwid_product_id'];
            } elseif (isset($_GET['ecwid_category_id'])) {
                $redirect = $ecwid_page . 'category/id=' . $_GET['ecwid_category_id'];
            }

            $app = JFactory::getApplication();
            $app->redirect($redirect, '', 'message', 301);
        }

        // Make sure we have a default view
        if( !JRequest::getVar( 'view' )) {
		    JRequest::setVar('view', 'ecwid' );
        }
		parent::display();
	}

    static function getRequestUri() {
        static $request_uri = null;

        if (is_null($request_uri)) {
            if (isset($_SERVER['REQUEST_URI'])) {
                $request_uri = $_SERVER['REQUEST_URI'];
                return $request_uri;
            }
            if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
                $request_uri = $_SERVER['HTTP_X_ORIGINAL_URL'];
                return $request_uri;
            } else if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
                $request_uri = $_SERVER['HTTP_X_REWRITE_URL'];
                return $request_uri;
            }

            if (isset($_SERVER['PATH_INFO']) && strlen($_SERVER['PATH_INFO'])) {
                if ($_SERVER['PATH_INFO'] == $_SERVER['PHP_SELF']) {
                    $request_uri = $_SERVER['PHP_SELF'];
                } else {
                    $request_uri = $_SERVER['PHP_SELF'] . $_SERVER['PATH_INFO'];
                }
            } else {
                $request_uri = $_SERVER['PHP_SELF'];
            }
            # Append query string
            if (isset($_SERVER['argv']) && isset($_SERVER['argv'][0]) && strlen($_SERVER['argv'][0])) {
                $request_uri .= '?' . $_SERVER['argv'][0];
            } else if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING'])) {
                $request_uri .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        return $request_uri;
    }
 

    static function buildEcwidUrl($url_with_anchor = '', $additional_get_params = array()) {
        $request_uri  = parse_url(RokEcwidController::getRequestUri());
        $base_url = $request_uri['path'];

        // extract anchor
        $url_fragments = parse_url($url_with_anchor);
        $anchor = @$url_fragments["fragment"];

        // get params
        $get_params = $_GET;
        $get_params = array_merge($get_params, $additional_get_params);
        $real_get_params = array();
        parse_str($_SERVER['QUERY_STRING'], $real_get_params);
        foreach ($get_params as $name => $value) {
            if (!array_key_exists($name, $real_get_params)) {
                unset($get_params[$name]);
            }   
        }   
        unset($get_params['_escaped_fragment_'], $get_params['ecwid_product_id'], $get_params['ecwid_category_id']);
        
        // add GET parameters
        if (count($get_params) > 0) {
            $base_url .= "?";
            $is_first = true;
            foreach ($get_params as $key => $value) {
                if (!$is_first) {
                    $base_url .= "&";
                }   
                $base_url .= $key . "=" . $value;
                $is_first = false;
            }   
        }   

        // extract anchor
        $url_fragments = parse_url($url_with_anchor);
        $anchor = @$url_fragments["fragment"];

        if ($anchor != "") {
            $base_url .= "#" . $anchor;
        }

        return $base_url;
    }

}
?>
