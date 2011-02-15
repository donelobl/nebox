<?php
/*
Plugin Name: Пользователи OnLine
Plugin URI: http://www.templatica.ru/
Version: 1.0
Description: Плагин выводит в блок сколько на сайте гостей и зарегистрированных пользователей.<br /><br />В файл <font color="red">index.html</font> вашего шаблона необходимо вставить в нужное место <font color="red">{$box_whois_online}</font>.<br /><br /><b>Для работы плагина необходимо активировать функцию <font color="red">"Кто сейчас в магазине"</font>(Настройки>Разное)</b>
Author: NeBox
Author URI: http://www.templatica.ru/
Plugin Group: Плагины от NeBox
*/

add_action('box', 'whois_online_box');

function whois_online_box() {

	$_title = '';
	$_content = '';

	global $osTemplate;

	$box = new osTemplate;

	// Тянем гостей
	$whois_guest = os_db_query('select COUNT(*) as online_count from os_whos_online where customer_id = 0');
		$whois_guest_query = os_db_fetch_array($whois_guest);
		$guests = $whois_guest_query['online_count'];

	// Тянем авторизированных
	$whois_customers = os_db_query('select COUNT(*) as online_count from os_whos_online where customer_id <> 0');
		$whois_customers_query = os_db_fetch_array($whois_customers);
		$customers = $whois_customers_query['online_count'];

	// Отдаем теги в файл шаблона - гости и авторизированные юзеры
	$box->assign('guests', $guests);
	$box->assign('customers', $customers);

	// Определяем, что папкой шаблона является папка плагина?
	$box->template_dir = plugdir();

	$_box_value = $box->fetch('box_whois_online.html');

	$osTemplate->assign('box_whois_online', $_box_value);
}
?>