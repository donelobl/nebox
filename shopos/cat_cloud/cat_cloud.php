<?php
/*
Plugin Name: Облако категорий
Plugin URI: http://nebox.ru/cms/shopos/plagin-oblako-kategorij-dlya-shopos/
Description: Плагин выводит облако категори<br /><br />Установить в файле index.html вашего шаблона метку <font color="red">{$box_CAT_CLOUD}</font><br /><br />
Version: 1.3
Author: NeBox (посетить блог)
Author URI: http://www.nebox.ru/
*/

add_action('box', 'cat_cloud_box');

function cat_cloud_box() {

	// Показывать подкатегории в облаке?
	if (get_option('show_sub_cat')=='true') {
		$show_sub_cat = 1;
	} else {
		$show_sub_cat = '';
	}

	// Сортировка
	if (get_option('sort_cat')=='desc') {
		$sort_cat = 'cd.categories_name DESC';
	} elseif (get_option('sort_cat')=='asc') {
		$sort_cat = 'cd.categories_name ASC';
	} elseif (get_option('sort_cat')=='desc2') {
		$sort_cat = 'c.categories_id DESC';
	} elseif (get_option('sort_cat')=='asc2') {
		$sort_cat = 'c.categories_id ASC';
	} elseif (get_option('sort_cat')=='rand') {
		$sort_cat = 'RAND()';
	}

	// Разделитель
	if (get_option('sep')=='sep_sp') {
		$sep = " ";
	} else {
		$sep = "<br />";
	}
	
	$cat_title = get_option('cat_box_title');

	global $osTemplate;

	$box = new osTemplate;

	$query = "SELECT c.categories_id, cd.categories_name, cd.categories_heading_title, count(*) AS c FROM ".TABLE_CATEGORIES." AS c, ".TABLE_CATEGORIES_DESCRIPTION." AS cd, ".TABLE_PRODUCTS_TO_CATEGORIES." AS p2c WHERE c.categories_id = cd.categories_id ".(empty($show_sub_cat) ? 'AND c.parent_id = ("0")':'')." AND ".$group_check." c.categories_status = '1' AND p2c.categories_id=c.categories_id AND cd.language_id = '" .(int) $_SESSION['languages_id']. "' GROUP BY c.categories_id ORDER BY ".$sort_cat." LIMIT " . get_option('max_display_cats');

	$query = osDBquery($query);
	
	$max_display_chr = get_option('max_display_chr');
	$cat_font_size = false;
	if ( strtolower(get_option('cat_font_size')) == 'true') {
		$min_font_size = get_option('min_font_size');
		$max_font_size = get_option('max_font_size');
		$cat_font_size = true;
	}

	while ($cat = os_db_fetch_array($query, true)) {

		$cat_link = os_category_link($cat['categories_id'],$cat['categories_name']);
		
		$cat_name_tmp = $cat['categories_name'];
		if ( strlen($cat_name_tmp) > $max_display_chr ) {
			$cat_name = mb_substr($cat_name_tmp,0, $max_display_chr);
		} else {
			$cat_name = $cat_name_tmp;
		}
		if ($cat_name != $cat_name_tmp) $cat_name = $cat_name.'&hellip;';

		if (get_option('cat_font_size')=='true') {
			$font_size = mt_rand(get_option('min_font_size'),get_option('max_font_size'));
		} else {
			$font_size = $cat['c'];
		}

		$box_content[] = array (
			'SIZE' => $font_size,
			'LINK' => os_href_link(FILENAME_DEFAULT, $cat_link),
			'TITLE' => $cat['categories_heading_title'],
			'NAME' => $cat_name,
			'SEP' => $sep
		);
		$box->assign('box_content', $box_content);
		$box->assign('cat_title', $cat_title);
	}

	$box->template_dir = plugdir();

	$box->caching = 0;
	$_box_value = $box->fetch('box_cat_cloud.html');

	$osTemplate->assign('box_CAT_CLOUD', $_box_value);
}

function cat_cloud_install() {
	add_option('sort_cat', 'rand', 'radio', "array('desc', 'asc', 'desc2', 'asc2', 'rand')");
	add_option('sep', 'sep_sp', 'radio', "array('sep_sp', 'sep_br')");
    add_option('max_display_cats', '100', 'input');
	add_option('cat_font_size', 'true', 'radio', "array('true', 'false')");
	add_option('min_font_size', '9', 'input');
	add_option('max_font_size', '26', 'input');
	add_option('max_display_chr', '50', 'input');
	add_option('show_sub_cat', 'false', 'radio', "array('true', 'false')");
	add_option('cat_box_title', 'Облако категорий', 'input');
}
?>