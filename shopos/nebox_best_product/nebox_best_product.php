<?php
/*
Plugin Name: Товар дня
Plugin URI: http://nebox.ru/cms/shopos/plagin-tovar-dnya-dlya-shopos/
Version: 1.0
Description: Плагин "Товар дня" в вашем магазине.<br /><br />В файл <font color="red">index.html</font> вашего шаблона необходимо вставить в нужное место <font color="red">{$box_BEST_PRODUCT}</font>.
Author: NeBox (посетить блог)
Author URI: http://nebox.ru/
Plugin Group: Плагины NeBox.ru
*/

add_action('page_admin', 'nebox_best_product_page');
add_action('box', 'nebox_best_product_box');

define("TABLE_BEST_PRODUCT", DB_PREFIX . "best_product");

function nebox_best_product_page() {
	include (dirname(__FILE__).'/nebox_best_product_page.php');
}
if (get_option('best_product_show')=='true') {

	function nebox_best_product_box() {

		global $osTemplate;
		global $osPrice;
		global $PHP_SELF;
		
		$best_product_title = get_option('best_product_box_title');
		$best_product_show_img = get_option('best_product_show_img');
		$best_product_show_descr = get_option('best_product_show_descr');
		$best_product_show_cat = get_option('best_product_show_cat');
		$best_product_show_price = get_option('best_product_show_price');
		$best_product_show_buy = get_option('best_product_show_buy');

		$box = new osTemplate;

		$best_products_query = os_db_query("select distinct p.products_id, pd.products_name, pd.products_short_description, p.products_image, p.products_price, c.categories_id, cd.categories_name from ".TABLE_PRODUCTS." p, os_products_description pd, ".TABLE_PRODUCTS_TO_CATEGORIES." p2c, ".TABLE_CATEGORIES." c , " . TABLE_BEST_PRODUCT . " sp, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_status = 1 and p.products_id = p2c.products_id and pd.products_id = p.products_id and p.products_id = sp.product_id and pd.language_id = '".$_SESSION['languages_id']."' and c.categories_id = p2c.categories_id and c.categories_status = 1 and cd.categories_id = p2c.categories_id");
		$best_products = os_db_fetch_array($best_products_query,true);

		$best_products_price = $osPrice->Format($best_products['products_price'], true);

		$product_link = os_href_link(FILENAME_PRODUCT_INFO, os_product_link($best_products['products_id'], $best_products['products_name']));

		$buy_now = os_href_link(basename($PHP_SELF), 'action=buy_now&BUYproducts_id='.$best_products['products_id'].'&'.os_get_all_get_params(array ('action')), 'NONSSL');

		if (get_option('best_product_img')=='images_thumbnail') {
			$product_img_size = 'images_thumbnail';
		} elseif (get_option('best_product_img')=='images_info') {
			$product_img_size = 'images_info';
		} elseif (get_option('best_product_img')=='images_popup') {
			$product_img_size = 'images_popup';
		}

		if ($best_products['products_image']) {
			$best_products['products_image'] = http_path($product_img_size) . $best_products['products_image'];
		} else {
			$best_products['products_image'] = http_path('images') . 'product_images/noimage.gif';
		}

		$box->assign('TITLE', $best_products['products_name']);
		$box->assign('CAT_TITLE', $best_products['categories_name']);
		$box->assign('LINK', $product_link);
		$box->assign('CAT_LINK',os_href_link(FILENAME_DEFAULT, os_category_link($best_products['categories_id'],$best_products['categories_name'])));
		$box->assign('IMG', $best_products['products_image']);
		$box->assign('PRICE', $best_products_price);
		$box->assign('DESCRIPTION', $best_products['products_short_description']);
		$box->assign('BUY_NOW', $buy_now);

		$box->assign('best_product_title', $best_product_title);
		$box->assign('best_product_show_img', $best_product_show_img);
		$box->assign('best_product_show_descr', $best_product_show_descr);
		$box->assign('best_product_show_cat', $best_product_show_cat);
		$box->assign('best_product_show_price', $best_product_show_price);
		$box->assign('best_product_show_buy', $best_product_show_buy);
		
		$box->template_dir = plugdir();

		if (!CacheCheck()) {
			$box->caching = 0;
			$_box_value = $box->fetch('nebox_box_best_product.html');
		} else {
			$box->caching = 1;
			$box->cache_lifetime = CACHE_LIFETIME;
			$box->cache_modified_check = CACHE_CHECK;
			$cache_id = $_SESSION['language'];
			$_box_value = $box->fetch('nebox_box_best_product.html',$cache_id);
		}

		$osTemplate->assign('box_BEST_PRODUCT', $_box_value);
	}

}

function nebox_best_product_install() {

	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'best_product;');
	os_db_query ("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."best_product` (
		`product_id` VARCHAR( 11 ) DEFAULT '0' NOT NULL
	) ENGINE=MyISAM	DEFAULT CHARSET=utf8;");

	os_db_query ("INSERT INTO `".DB_PREFIX."best_product` ( `product_id` ) VALUES ('1')");

	add_option('nebox_best_product_button', '', 'readonly');
	add_option('best_product_show', 'true', 'radio', "array('true', 'false')");
	add_option('best_product_img', 'images_thumbnail', 'radio', "array('images_thumbnail', 'images_info', 'images_popup')");
	add_option('best_product_box_title', 'Товар дня!', 'input');
	add_option('best_product_show_img', 'true', 'radio', "array('true', 'false')");
	add_option('best_product_show_descr', 'true', 'radio', "array('true', 'false')");
	add_option('best_product_show_cat', 'true', 'radio', "array('true', 'false')");
	add_option('best_product_show_price', 'true', 'radio', "array('true', 'false')");
	add_option('best_product_show_buy', 'true', 'radio', "array('true', 'false')");

}

function nebox_best_product_button_readonly() {
	_e('<center>'.add_button('page', 'nebox_best_product_page', 'Выбор товара' ).'</center>');
}

function nebox_best_product_delete(){
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'best_product');
}
?>