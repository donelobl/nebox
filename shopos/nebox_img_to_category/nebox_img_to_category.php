<?php
/*
Plugin Name: Картинки для категорий
Plugin URI: http://nebox.ru/
Version: 1.0
Description: Плагин "Картинки для категорий" выводит блок на страницах категории к которой он привязан.<br /><br />В файл <font color="red">index.html</font> вашего шаблона необходимо вставить в нужное место <font color="red">{$box_NBITC}</font>.
Author: NeBox (посетить блог)
Author URI: http://nebox.ru/

*/

add_action('page_admin', 'nebox_img_to_category_page');
add_action('box', 'nebox_img_to_category_box');
add_action('admin_menu', 'nebox_img_to_category_admin_menu');

define("TABLE_NBITC", DB_PREFIX . "nebox_img_to_category");

function nebox_img_to_category_admin_menu()
{
	add_plug_menu('Картинки для категорий', 'plugins_page.php?page=nebox_img_to_category_page');
}

function nebox_img_to_category_page()
{
	include (dirname(__FILE__).'/nebox_img_to_category_page.php');
}

function nebox_img_to_category_box()
{
	global $osTemplate, $db, $cPath;
	$box = new osTemplate;

	$nbttc_query = $db->query("SELECT * FROM ".TABLE_NBITC." WHERE category = ".(int)$cPath."");
	$nbttc = os_db_fetch_array($nbttc_query,true);

	$imgs = _HTTP.'media/nitc_img/'.$nbttc['img'];

	$box->assign('img', $imgs);
	$box->assign('link', $nbttc['link']);
	$box->assign('title', $nbttc['title']);
	$box->assign('text', $nbttc['text']);
	$box->assign('cPath', $cPath);

	$box->template_dir = plugdir();

	if (!CacheCheck()) {
		$box->caching = 0;
		$_box_value = $box->fetch('nebox_img_to_category.html');
	} else {
		$box->caching = 1;
		$box->cache_lifetime = CACHE_LIFETIME;
		$box->cache_modified_check = CACHE_CHECK;
		$cache_id = $_SESSION['language'];
		$_box_value = $box->fetch('nebox_img_to_category.html',$cache_id);
	}

	$osTemplate->assign('box_NBITC', $_box_value);
}

function nebox_img_to_category_install()
{
	global $db;

	$db->query('DROP TABLE IF EXISTS '.DB_PREFIX.'nebox_img_to_category;');
	$db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."nebox_img_to_category` (
		`id` int(11) NOT NULL AUTO_INCREMENT ,
		`img` VARCHAR(255) DEFAULT '',
		`link` VARCHAR(255) DEFAULT '',
		`title` VARCHAR(255) DEFAULT '',
		`text` text,
		`category` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`, `category`)
	) ENGINE=MyISAM	DEFAULT CHARSET=utf8;");

	add_option('nebox_img_to_category_button', '', 'readonly');

}

function nebox_img_to_category_button_readonly() {
	_e('<center>'.add_button('page', 'nebox_img_to_category_page', 'Управление' ).'</center>');
}

function nebox_img_to_category_delete()
{
	global $db;
	$db->query('DROP TABLE IF EXISTS '.DB_PREFIX.'nebox_img_to_category');
}
?>