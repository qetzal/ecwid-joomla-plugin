<?php defined('_JEXEC') or die('Restricted access'); 

// Get Component parameters

if (!defined('ECWID_SCRIPT')) {define('ECWID_SCRIPT',1);}

$params = JComponentHelper::getParams( 'com_rokecwid' );
include_once (JPATH_SITE.DS.'components'.DS.'com_rokecwid'.DS.'helpers'.DS.'ecwid_catalog.php');

list($column_grid, $row_grid) = explode(",", $params->get( 'grid' ));

$options = array (
		'store_id'                          => $params->get('storeID', 1003),
		'list_of_views'                     => array('list','grid','table'),
		'ecwid_pb_categoriesperrow'         => $params->get( 'categoriesPerRow' ),
		'ecwid_pb_productspercolumn_grid'   => trim($column_grid),
		'ecwid_pb_productsperrow_grid'      => trim($row_grid),
		'ecwid_pb_productsperpage_list'     => $params->get( 'list' ),
		'ecwid_pb_productsperpage_table'    => $params->get( 'table' ),
		'ecwid_pb_defaultview'              => $params->get( 'categoryView' ),
		'ecwid_pb_searchview'               => $params->get( 'searchView' ),
		'ecwid_mobile_catalog_link'         => '',
		'ecwid_default_category_id'         => $params->get( 'defaultCategory' ),
		'ecwid_is_secure_page'              => '',
		'ecwid_enable_html_mode'			=> ($params->get('enableHTMLMode',0) == 1),
		'ecwid_show_seo_catalog'            => ($params->get("enableInlineSeoCatalog", 0) == 1));
?>
<div id="ecwid_jwrapper">
<?php
echo show_ecwid($options);
?>
</div>
