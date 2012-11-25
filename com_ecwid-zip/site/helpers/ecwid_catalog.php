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

function show_ecwid($params) {
	$store_id = $params['store_id'];
	
	if (empty($store_id)) {
	  $store_id = '1003'; //demo mode
	}
		
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

	$ecwid_default_category_id = $params['ecwid_default_category_id'];

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

    if ($api_enabled) {

        if (isset($_GET['_escaped_fragment_'])) {
            $fragment = $_GET['_escaped_fragment_'];
            if (preg_match('!/~/(product|category)/.*id=([\d+]*)!', $fragment, $matches)) {
                $type = $matches[1];
                $id = $matches[2];

                if ($api_enabled && $type && $id) {
                    $ajaxIndexingContent = show_ecwid_catalog($store_id, $type, $id);
                } 
            }
        } else {
            $noscript = show_ecwid_catalog($store_id, 'category', $ecwid_default_category_id);
        }

        if ($ajaxIndexingContent) {
            return $ajaxIndexingContent;
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
	
	$integration_code = <<<EOT
<div id="$ecwid_element_id"></div>
<div>
<script type="text/javascript"> xProductBrowser("categoriesPerRow=$ecwid_pb_categoriesperrow","views=grid($ecwid_pb_productspercolumn_grid,$ecwid_pb_productsperrow_grid) list($ecwid_pb_productsperpage_list) table($ecwid_pb_productsperpage_table)","categoryView=$ecwid_pb_defaultview","searchView=$ecwid_pb_searchview","style="$ecwid_default_category_str);</script>
</div>
<noscript>$noscript</noscript>
EOT;

	return $integration_code;
}

function show_ecwid_catalog($ecwid_store_id, $type, $id) {
	$ecwid_store_id = intval($ecwid_store_id);
	$api = new EcwidProductApi($ecwid_store_id);

    if ($type == 'product') {
        $ecwid_product_id = $id;
    } elseif ($type == 'category') {
        $ecwid_category_id = $id;
    } else {
        $ecwid_category_id = isset($_GET['ecwid_category_id']) ? intval($_GET['ecwid_category_id']) : false;
        $ecwid_product_id = isset($_GET['ecwid_product_id']) ? intval($_GET['ecwid_product_id']) : false;
    }

	if (!empty($ecwid_product_id)) {
		$params = array(
			array("alias" => "p", "action" => "product", "params" => array("id" => $ecwid_product_id)),
			array("alias" => "pf", "action" => "profile")
		);

		$batch_result = $api->get_batch_request($params);
		$product = $batch_result["p"];
		$profile = $batch_result["pf"];

    } else {
		if (empty($ecwid_category_id)) {
			$ecwid_category_id = 0;
		}
		$params = array(
			array("alias" => "c", "action" => "categories", "params" => array("parent" => $ecwid_category_id)),
			array("alias" => "p", "action" => "products", "params" => array("category" => $ecwid_category_id)),
			array("alias" => "pf", "action" => "profile")
		);

		$batch_result = $api->get_batch_request($params);
		$categories = $batch_result["c"];
		$products = $batch_result["p"];
		$profile = $batch_result["pf"];
	}

    $html = '';
	if (isset($product) && is_array($product)) {
        $name = htmlentities($product['name'], ENT_COMPAT, 'UTF-8');
        $imageCode = '';
        if ($product['thumbnailUrl']) {
            $sku = htmlentities($product['sku'], ENT_COMPAT, 'UTF-8');
            $imageCode = <<<EOT
<div class="ecwid_catalog_product_image photo"><img src="$product[thumbnailUrl]" alt="$sku $name" /></div>        
EOT;
        }

        $html = <<<EOT
<div itemscope itemtype="http://schema.org/Product">
    $imageCode
    <div itemprop="name" class="ecwid_catalog_product_name fn">$name</div>
    <div itemscope itemprop="offers" itemtype="http://schema.org/Offer" class="ecwid_catalog_product_price price">
        Price: <span itemprop="price">$product[price]</span>&nbsp;<span itemprop="priceCurrency">$profile[currency]</span>
        <meta itemprop="availability" content="InStock" /> 
    </div>
    <div itemprop="description" class="ecwid_catalog_product_description description">$product[description]</div>
</div>
EOT;
        $document = JFactory::getDocument();
        $document->setTitle($document->getTitle() . ' | ' . $product["name"]);
        $description = explode('<br>', wordwrap(strip_tags($product["description"]), 150, "<br>"));
        $document->setDescription($description[0]);

	} else {
		if (is_array($categories)) {
			foreach ($categories as $category) {
                $category_url = ecwid_internal_construct_url($category["url"]);
				$category_name = $category["name"];
				$html .= "<div class='ecwid_catalog_category_name'><a href='" . htmlspecialchars($category_url) . "'>" . $category_name . "</a><br /></div>";
			}
		}

		if (is_array($products)) {
			foreach ($products as $product) {
                $product_url = ecwid_internal_construct_url($product["url"]);
				$product_name = $product["name"];
				$product_price = $product["price"] . "&nbsp;" . $profile["currency"];
				$html .= "<div>";
				$html .= "<span class='ecwid_product_name'><a href='" . htmlspecialchars($product_url) . "'>" . $product_name . "</a></span>";
				$html .= "&nbsp;&nbsp;<span class='ecwid_product_price'>" . $product_price . "</span>";
				$html .= "</div>";
			}
		}
	}

	return $html;
}

function ecwid_is_api_enabled($ecwid_store_id) {
	$ecwid_store_id = intval($ecwid_store_id);
	$api = new EcwidProductApi($ecwid_store_id);
  return $api->is_api_enabled();
}

function ecwid_zerolen() {
  foreach (func_get_args() as $arg) {
    if (strlen($arg) == 0) return true;
  }
  return false;
}

function ecwid_get_request_uri() {
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

    if (isset($_SERVER['PATH_INFO']) && !ecwid_zerolen($_SERVER['PATH_INFO'])) {
        if ($_SERVER['PATH_INFO'] == $_SERVER['PHP_SELF']) {
            $request_uri = $_SERVER['PHP_SELF'];
        } else {
            $request_uri = $_SERVER['PHP_SELF'] . $_SERVER['PATH_INFO'];
        }
    } else {
        $request_uri = $_SERVER['PHP_SELF'];
    }
    # Append query string
    if (isset($_SERVER['argv']) && isset($_SERVER['argv'][0]) && !ecwid_zerolen($_SERVER['argv'][0])) {
        $request_uri .= '?' . $_SERVER['argv'][0];
    } else if (isset($_SERVER['QUERY_STRING']) && !ecwid_zerolen($_SERVER['QUERY_STRING'])) {
        $request_uri .= '?' . $_SERVER['QUERY_STRING'];
    }
    }
    return $request_uri;
}

function ecwid_internal_construct_url($url_with_anchor, $additional_get_params = array()) {
  $request_uri  = parse_url(ecwid_get_request_uri());
  $base_url = $request_uri['path'];

	// extract anchor
	$url_fragments = parse_url($url_with_anchor);
	$anchor = $url_fragments["fragment"];

	// get params
	$get_params = $_GET;
    unset($get_params['_escaped_fragment_']);
	$get_params = array_merge($get_params, $additional_get_params);

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

	if ($anchor != "") {
		$base_url .= "#" . $anchor;
	}

	return $base_url;
}
