<?php
/*
Plugin Name: Категории на главной
Plugin URI: http://www.templatica.ru/
Version: 1.0
Description: Плагин выводит список категорий на главную страницу.<br /><br />В файл <font color="red">index.html</font> вашего шаблона необходимо вставить в нужное место <font color="red">{$box_CATEGORIES_LIST}</font>.
Author: NeBox
Author URI: http://www.templatica.ru/
Plugin Group: Плагины от NeBox
*/

add_action('box', 'category_list_box');
	
function category_list_box() {

	$category_list_title = get_option('category_list_box_text_title');

	$_title = '';
	$_content = '';

	global $osTemplate;

	$box = new osTemplate;

	$categories_query = "SELECT c.categories_id, c.categories_image, cd.categories_name, cd.categories_description FROM ".TABLE_CATEGORIES." AS c, ".TABLE_CATEGORIES_DESCRIPTION." AS cd WHERE c.categories_id = cd.categories_id AND c.parent_id = '0' AND ".$group_check." c.categories_status = '1' AND cd.language_id = '" .(int) $_SESSION['languages_id']. "' ORDER BY c.sort_order ASC";
	$categories_query = osDBquery($categories_query);

	while($categories = os_db_fetch_array($categories_query, true)) {
		$categories_image = 'images/categories/' . $categories['categories_image'];
		if (!is_file($categories_image)) $categories_image = '';
		$category_link = os_category_link($categories['categories_id'],$categories['categories_name']);
		$box_content[] = array (
			'CATEGORY_NAME' => $categories['categories_name'],
			'CATEGORY_IMAGE' => $categories_image,
			'CATEGORY_LINK' => os_href_link(FILENAME_DEFAULT,  $category_link),
			'CATEGORY_DESCRIPTION' => $categories['categories_description']
		);
		$box->assign('box_content', $box_content);
	}

	$box->template_dir = plugdir();

	$box->assign('category_list_title', $category_list_title);

	if (!CacheCheck()) {
		$box->caching = 0;
		$_box_value = $box->fetch('box_category_list.html');
	} else {
		$box->caching = 1;
		$box->cache_lifetime = CACHE_LIFETIME;
		$box->cache_modified_check = CACHE_CHECK;
		$cache_id = $_SESSION['language'];
		$_box_value = $box->fetch('box_category_list.html',$cache_id);
	}

	$osTemplate->assign('box_CATEGORIES_LIST', $_box_value);

}

function category_list_install() {
	add_option('category_list_box_text_title', 'Категории товаров', 'input');
}

?>