<?php
/*
Plugin Name: Расширенная карта сайта
Plugin URI: http://nebox.ru/cms/shopos/plagin-rasshirennaya-karta-sajta-dlya-shopos/
Version: 1.1
Description: Плагин "Расширенная карта сайта" выводит дерево категорий и подкатегорий всех уровней с товарами. Выводит информационные страниы и новости.
Author: NeBox (Посетить блог)
Author URI: http://nebox.ru/
Plugin Group: Плагины от NeBox
*/

add_action('page', 'sitemap_all');
add_filter('title', 'sitemap_all_title');
add_action('head', 'sitemap_all_head');

function sitemap_all () {

	// Добавляем свой title
	function sitemap_all_title($value) {
		$value = 'Карта сайта - '.$value;
		return $value;
	}

	// Подключаем CSS файл
	function sitemap_all_head() {
	   _e('<link rel="stylesheet" href="'.plugurl().'css/sitemap_all.css" />');
	}

	global $osTemplate;
	global $breadcrumb;

	// Хедер
	require(dir_path('includes') . 'header.php');

	// Хлебные крошки
	$breadcrumb->add('Карта сайта', 'index.php?page=sitemap_all');
	$osTemplate->assign('navtrail', $breadcrumb->trail(' &raquo; '));

	// Основной файл плагина
	include(dirname(__FILE__).'/index.php');

	// Подвал
	require(dir_path('includes') . 'bottom.php');

}

// Ссылка на карту насай
function sitemap_all_url_readonly() {
	_e('
		<center>
			<br /><a href="'.http_path('catalog').'index.php?page=sitemap_all'.'" target="_blank"><strong>Ссылка на карту сайта</a></strong><br /><br />
		</center>
	');
}

// Установка
function sitemap_all_install() {
	add_option('sitemap_all_url', '', 'readonly');
}

?>