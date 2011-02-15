<?php
/*
Plugin Name: Опросы
Plugin URI: http://www.templatica.ru/
Version: 1.1
Description: Плагин дает возможность создавать опросы в магазине
Author: NeBox
Author URI: http://www.templatica.ru/
Plugin Group: Плагины от NeBox
*/

add_action('page_admin', 'poll_manager_page');
add_action('box', 'poll_manager_box');
add_action('head_admin', 'poll_manager_head');

// БД
define("TABLE_POLL", DB_PREFIX."poll");
define("TABLE_POLL_DESCRIPTION", DB_PREFIX."poll_description");
define("TABLE_POLL_ITEMS", DB_PREFIX."poll_items");
define("TABLE_POLL_ITEMS_DESCRIPTION", DB_PREFIX."poll_items_description");

// Страница плагина
function poll_manager_page() {

	// Текст
	define('POLL_HEADING_TITLE','Менеджер опросов');
	define('TABLE_HEADING_SETTINGS_NAVI_OVERVIEW','Главная');
	define('TABLE_HEADING_SETTINGS_NEW','Новый опрос');
	define('POLL_ID','ID');
	define('POLL_NAME','Опрос');
	define('POLL_HITS','Голосов');
	define('POLL_DATE_START','Начало');
	define('POLL_DATE_END','Окончание');
	define('POLL_STATUS','Действие');
	define('POLL_EDIT','Редактировать');
	define('POLL_ONLINE','Включен');
	define('POLL_OFFLINE','Выключен');
	define('POLL_DELETE','Удалить');
	define('ITEM_ID','ID');
	define('ITEM_NAME','Вопрос');
	define('ITEM_HITS','Голосов');
	define('ITEM_POSITION','Позиция');
	define('ITEM_COLOR','Цвет фона');
	define('TABLE_HEADING_EDITING','Ответы');
	define('TABLE_HEADING_EDITING_NEW','Добавить варинт');
	define('TEXT_STATUS_DELETE','Удалить');
	define('TEXT_UPDATE','Обновить');
	define('TEXT_SAVE','Сохранить');
	define('NO_POLL','Опросов не найдено ..');
	define('POLL_CUSTOMERS_GROUPS','<b>Какой группе(ам) будем показывать опрос?</b><br />Зажмине CTRL и мышкой укажите несколько.');

	// Подключаем нужные файлы в хедер только на странице плагина
	function poll_manager_head() { 
		_e ('<link rel="stylesheet" type="text/css" href="'.plugurl().'css/style.css">');
		_e ('<script language="javascript" src="'.plugurl().'js/jquery-1.4.2.min.js"></script>');
		_e ('<script language="javascript" src="'.plugurl().'js/iColorPicker.js"></script>');
	}

	include (dirname(__FILE__).'/poll_manager_page.php');
}

// Бокс
function poll_manager_box() {

	$poll_manager_title = get_option('poll_manager_box_text_title');

	$_title = '';
	$_content = '';

	global $osTemplate;

	$box = new osTemplate;

	include( plugdir().'poll_manager_box.php' );

	$box->template_dir = plugdir();

	$box->assign('poll_manager_title', $poll_manager_title);

	if (!CacheCheck()) {
		$box->caching = 0;
		$_box_value = $box->fetch('box_poll_manager.html');
	} else {
		$box->caching = 1;
		$box->cache_lifetime = CACHE_LIFETIME;
		$box->cache_modified_check = CACHE_CHECK;
		$cache_id = $_SESSION['language'];
		$_box_value = $box->fetch('box_poll_manager.html',$cache_id);
	}

	$osTemplate->assign('box_poll_manager', $_box_value);
}

// Устанавливаем плагин
function poll_manager_install() {

	// Создаем таблицы в БД
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'poll;');
	os_db_query ("CREATE TABLE `".DB_PREFIX."poll` (
		id int(11) NOT NULL auto_increment,
		ip varchar(200) NOT NULL default '',
		`status` int(1) NOT NULL default '0',
		`start` datetime NOT NULL default '0000-00-00 00:00:00',
		`end` datetime NOT NULL default '0000-00-00 00:00:00',
		customers_groups varchar(100) NOT NULL default '',
		PRIMARY KEY  (id)
	) TYPE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'poll_description;');
	os_db_query ("CREATE TABLE `".DB_PREFIX."poll_description` (
		id int(11) NOT NULL auto_increment,
		language_id int(11) NOT NULL default '1',
		title varchar(200) NOT NULL default '',
		PRIMARY KEY  (id,language_id)
	) TYPE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'poll_items;');
	os_db_query ("CREATE TABLE `".DB_PREFIX."poll_items` (
		item_id int(11) NOT NULL auto_increment,
		poll_id int(11) default NULL,
		color varchar(7) default NULL,
		hits int(11) NOT NULL default '0',
		position int(11) NOT NULL default '0',
		PRIMARY KEY  (item_id)
	) TYPE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'poll_items_description;');
	os_db_query ("CREATE TABLE `".DB_PREFIX."poll_items_description` (
		item_id int(11) NOT NULL auto_increment,
		language_id int(11) NOT NULL default '1',
		poll_id int(11) NOT NULL default '0',
		title varchar(150) NOT NULL default '',
		PRIMARY KEY  (item_id,language_id),
		KEY title (title)
	) TYPE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

	// Кнопочка и титл бокса
	add_option('poll_manager_button', '', 'readonly');
	add_option('poll_manager_box_text_title', 'Опрос магазина', 'input');
}

// Добавляем кнопку на страницу управления боксами
function poll_manager_button_readonly() {
	 _e('<center>'.add_button('page', 'poll_manager_page', 'Управление опросами' ).'</center>');
}

// Удаляем таблицу из БД
function poll_manager_delete(){
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'poll');
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'poll_description');
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'poll_items');
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'poll_items_description');
}

?>