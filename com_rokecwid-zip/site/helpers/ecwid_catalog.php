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

include_once "ecwid_product_api.php";
include_once "EcwidCatalog.php";

function show_ecwid($params) {
	$store_id = $params['store_id'];
	
	if (empty($store_id)) {
	  $store_id = '1003'; //demo mode
	}
	
    $c = new EcwidCatalog($store_id, RokEcwidController::buildEcwidUrl());
    	
	$list_of_views = $params['list_of_views'];

    if (is_array($list_of_views)) {
    	foreach ($list_of_views as $k=>$v) {
    		if (!in_array($v, array('list','grid','table'))) unset($list_of_views[$k]);
	}
    }
	
	if ((!is_array($list_of_views)) || empty($list_of_views)) {
		$list_of_views = array('list','grid','table');
	}

	$ecwid_pb_categoriesperrow = $params['ecwid_pb_categoriesperrow'];
	if (empty($ecwid_pb_categoriesperrow)) {
		$ecwid_pb_categoriesperrow = 3;
	}
	$ecwid_pb_productspercolumn_grid = $params['ecwid_pb_productspercolumn_grid'];
	if (empty($ecwid_pb_productspercolumn_grid)) {
		$ecwid_pb_productspercolumn_grid = 3;
	}
	$ecwid_pb_productsperrow_grid = $params['ecwid_pb_productsperrow_grid'];
	if (empty($ecwid_pb_productsperrow_grid)) {
		$ecwid_pb_productsperrow_grid = 3;
	}
	$ecwid_pb_productsperpage_list = $params['ecwid_pb_productsperpage_list'];
	if (empty($ecwid_pb_productsperpage_list)) {
		$ecwid_pb_productsperpage_list = 10;
	}
	$ecwid_pb_productsperpage_table = $params['ecwid_pb_productsperpage_table'];
	if (empty($ecwid_pb_productsperpage_table)) {
		$ecwid_pb_productsperpage_table = 20;
	}
	$ecwid_pb_defaultview = $params['ecwid_pb_defaultview'];
	if (empty($ecwid_pb_defaultview) || !in_array($ecwid_pb_defaultview, $list_of_views)) {
		$ecwid_pb_defaultview = 'grid';
	}
	$ecwid_pb_searchview = $params['ecwid_pb_searchview'];
	if (empty($ecwid_pb_searchview) || !in_array($ecwid_pb_searchview, $list_of_views)) {
		$ecwid_pb_searchview = 'list';
	}

	$ecwid_com = "app.ecwid.com";

	$ecwid_default_category_id = intval($params['ecwid_default_category_id']);

 	$ecwid_mobile_catalog_link = $params['ecwid_mobile_catalog_link'];
	if (empty($ecwid_mobile_catalog_link)) {
		$ecwid_mobile_catalog_link = "//$ecwid_com/jsp/$store_id/catalog";
	}

    $ajaxIndexingContent = '';
    $noscript = '';

    $cache = JFactory::getCache();
    $cache->setCaching(1);
    $cache->setLifeTime(360);
    $api_enabled = $cache->call('ecwid_is_api_enabled', $store_id);

    $integration_code = '';

    if ($api_enabled) {

        if (isset($_GET['_escaped_fragment_'])) {
            $fragment = $_GET['_escaped_fragment_'];
            if (preg_match('!/~/(product|category)/.*id=([\d+]*)!', $fragment, $matches)) {
                $type = $matches[1];
                $id = $matches[2];

                if ($api_enabled && $type && $id) {
                    if ($type == 'product') {
                        $ajaxIndexingContent = $c->get_product($id);

                        $api = new EcwidProductApi($store_id);
                        $product = $api->get_product($id);
                        $document = JFactory::getDocument();
                        $document->setTitle($product['name'] . ' | ' . $document->getTitle());

                        $description = $product['description'];
                        $description = strip_tags($description);
                        $description = html_entity_decode($description);
                        $description = trim($description, " \t\xA0\n\r");// Space, tab, non-breaking space, newline, carriage return
                        $description = mb_substr($description, 0, 160);
                        $document->setDescription($description);

                        $integration_code = '<script type="text/javascript"> if (!document.location.hash) document.location.hash = "!/~/product/id='. intval($id) .'";</script>';

                    } elseif ($type == 'category') {
                        $ajaxIndexingContent = $c->get_category($id);
                        $ecwid_default_category_id = $id;
                    }
                } 
            } else {
                $ajaxIndexingContent = $c->get_category($ecwid_default_category_id);
            }
        } else {
            $doc = JFactory::getDocument();
            $doc->addCustomTag('<meta name="fragment" content="!" />');
        }

        if ($ajaxIndexingContent) {
            $noscript = $ajaxIndexingContent;
        }
	}
	
    if (empty($noscript)) {
        $noscript = "Your browser does not support JavaScript.<a href=\"{$ecwid_mobile_catalog_link}\">HTML version of this store</a>";
	}


	if (empty($ecwid_default_category_id)) {
		$ecwid_default_category_str = '';
	} else {
		$ecwid_default_category_str = ',"defaultCategoryId='. $ecwid_default_category_id .'"';
	}

	$ecwid_is_secure_page = $params['ecwid_is_secure_page'];
	if (empty ($ecwid_is_secure_page)) {
		$ecwid_is_secure_page = false;
	}

	$protocol = "http";
	if ($ecwid_is_secure_page) {
		$protocol = "https";
	}

    $ecwid_element_id = "ecwid-inline-catalog";
    if (!empty($params['ecwid_element_id'])) {
        $ecwid_element_id = $params['ecwid_element_id'];
    }

	$integration_code .= <<<EOT
<div id="$ecwid_element_id">$noscript</div>
<div>
<script type="text/javascript"> xProductBrowser("categoriesPerRow=$ecwid_pb_categoriesperrow","views=grid($ecwid_pb_productspercolumn_grid,$ecwid_pb_productsperrow_grid) list($ecwid_pb_productsperpage_list) table($ecwid_pb_productsperpage_table)","categoryView=$ecwid_pb_defaultview","searchView=$ecwid_pb_searchview","style="$ecwid_default_category_str,"id=$ecwid_element_id");</script>
</div>
EOT;

	return $integration_code;
}

function ecwid_is_api_enabled($ecwid_store_id) {
	$ecwid_store_id = intval($ecwid_store_id);
	$api = new EcwidProductApi($ecwid_store_id);
  return $api->is_api_enabled();
}
