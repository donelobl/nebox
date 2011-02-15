<?php
/*
Plugin Name: Проверка файлов
Plugin URI: http://nebox.ru/cms/shopos/plagin-proverka-fajlov-dlya-shopos/
Version: 1.0
Description: Плагин сохраняет хеш суммы файлов в базе данных, что дает возможность при необходимости сравнить их с файловой структурой для выявления отличий.
Author: NeBox (посетить блог)
Author URI: http://nebox.ru/
Plugin Group: Плагины от NeBox
*/

add_action('page_admin', 'file_chk_page');

function file_chk_page() {

	define("TABLE_FILECHK", DB_PREFIX . "filechk_");

	define('FILE_CHK_PAGE_HEADER','Проверка файловой структуры');
	define('FILECHK_BUTTON_SET_NEW','Обновить');
	define('FILECHK_BUTTON_CONTROL','Смотреть');
	define('FILE_CHK_SET_NEW_OK','Записи в базе успешно обновлены');
	define('TH_STATUS','Статус');
	define('TH_DATA','Дата');
	define('TH_FILE','Файл');
	define('TH_HTML_FILE','HTML файлы');
	define('TH_PHP_FILE','PHP файлы');
	define('TH_JS_FILE','JS файлы');
	define('TH_CSS_FILE','CSS файлы');
	define('TH_EMPTY','Нет записей');
	define('STATUS_DELETE','УДАЛЕН');
	define('STATUS_CHANGE','ИЗМЕНЕН');
	define('STATUS_OK','OK');
	define('MAKE_SELECTION','Как пользоваться?<br />1 - Нажимаем кнопку Обновить у PHP или HTML. После нажатия произойдет запись хеша файлов, и путей к файлам в базу.<br />2 - Нажимаем кнопку Смотреть и на странице появятся все файлый выбранного расширения.<br />3 - После этого раз в несколько дней следите за файлами. Если файл изменится, то статус его смениться с OK на ИЗМЕНЕН.<br /><br />Не забывайте после своих изменениях в файлах делать обновления!');

	include (dirname(__FILE__).'/file_chk_page.php');

}

function file_chk_install() {

	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'filechk_php;');
	os_db_query ("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."filechk_php` (
		`id` int(11) NOT NULL auto_increment,
		`filepath` text,
		`hash` varchar(32) default NULL,
		`date` datetime DEFAULT '0000-00-00 00:00:00',
		KEY `id` (`id`)
	) ENGINE=MyISAM	DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;");

	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'filechk_html;');
	os_db_query ("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."filechk_html` (
		`id` int(11) NOT NULL auto_increment,
		`filepath` text,
		`hash` varchar(32) default NULL,
		`date` datetime DEFAULT '0000-00-00 00:00:00',
		KEY `id` (`id`)
	) ENGINE=MyISAM	DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;");
	
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'filechk_css;');
	os_db_query ("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."filechk_css` (
		`id` int(11) NOT NULL auto_increment,
		`filepath` text,
		`hash` varchar(32) default NULL,
		`date` datetime DEFAULT '0000-00-00 00:00:00',
		KEY `id` (`id`)
	) ENGINE=MyISAM	DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;");
	
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'filechk_js;');
	os_db_query ("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."filechk_js` (
		`id` int(11) NOT NULL auto_increment,
		`filepath` text,
		`hash` varchar(32) default NULL,
		`date` datetime DEFAULT '0000-00-00 00:00:00',
		KEY `id` (`id`)
	) ENGINE=MyISAM	DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;");

	add_option('file_chk_button', '', 'readonly');

}

function file_chk_button_readonly() {
	 _e('<center>'.add_button('page', 'file_chk_page', 'Настройки' ).'</center>');
}

function file_chk_delete(){
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'filechk_php');
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'filechk_html');
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'filechk_js');
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'filechk_css');
}
?>