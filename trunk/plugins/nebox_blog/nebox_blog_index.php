<?php
include (NBB_PLUGIN.'/lang/site_'.$_SESSION['language'].'.php');

$breadcrumb->add(NBB_TITLE, os_href_link(NEBOX_BLOG_URL, '', 'NONSSL'));

if ((isset($_GET['b_cat']) && is_numeric($_GET['b_cat']))) {

	$posts_query = "
		SELECT
			p.id AS post_id,
			p.categories_id,
			p.name AS post_name,
			p.short_description AS post_short_description,
			p.status,
			p.position,
			p.date_added AS post_date,
			c.id AS cat_id,
			c.title AS cat_title
		FROM 
			".DB_NEBOX_BLOG_POSTS." p, 
			".DB_NEBOX_BLOG_CATEGORY." c
		WHERE 
			p.status = 1 AND
			c.id = ".(int)$_GET['b_cat']." AND
			p.categories_id = ".(int)$_GET['b_cat']."
		ORDER BY
			p.position DESC
	";
	$breadcrumb->add($posts['cat_title'], NEBOX_BLOG_URL.'&b_cat='.$_GET['b_cat']);
	$osTemplate->assign('navtrail', $breadcrumb->trail(' &raquo; '));

	$module_content = array();
	/*
	$split = new splitPageResults($posts_query, $_GET['page'], NBBS_NUMBER_OF_POSTS, 'b_cat');
	$query = os_db_query($split->sql_query);
	if (($split->number_of_rows > 0))
	{
		$osTemplate->assign('NAVIGATION_BAR', TEXT_RESULT_PAGE.' '.$split->display_links(MAX_DISPLAY_PAGE_LINKS, os_get_all_get_params(array ('page', 'info', 'x', 'y'))));
		$osTemplate->assign('NAVIGATION_BAR_PAGES', $split->display_count(TEXT_DISPLAY_NUMBER_OF_LATEST_NEWS));
	}
	*/
	$query = os_db_query($posts_query);
	while ($posts = os_db_fetch_array($query,true))
	{

		//$com_query = os_db_query("SELECT * FROM ".DB_NEBOX_BLOG_COMMETS." WHERE post_id = ".$posts['post_id']."");
		//$com_count = os_db_num_rows($com_query);
	
		$module_content[]=array
		(
			'POST_ID'  => $posts['post_id'],
			'POST_NAME'  => $posts['post_name'],
			'POST_SHORT_DESC'  => $posts['post_short_description'],
			'POST_DATE'  => $posts['post_date'],
			'CAT_ID' => $posts['cat_id'],
			'CAT_TITLE' => $posts['cat_title'],
			'COM_COUNT' => $com_count
		);
	}
}
elseif ((isset($_GET['b_post']) && is_numeric($_GET['b_post'])))
{
	$posts_query = os_db_query("
		SELECT
			p.id AS post_id,
			p.categories_id AS post_cat_id,
			p.name AS post_name, 
			p.description AS post_description,  
			p.status, 
			p.date_added AS post_date,
			c.id AS cat_id,
			c.title AS cat_title
		FROM 
			".DB_NEBOX_BLOG_POSTS." p, 
			".DB_NEBOX_BLOG_CATEGORY." c 
		WHERE 
			p.status = 1 AND
			p.id = ".$_GET['b_post']." AND
			p.categories_id = c.id
		LIMIT 1
	");
	$posts = os_db_fetch_array($posts_query);

	// Хлебные крошки
	$breadcrumb->add($posts['post_name'], NEBOX_BLOG_URL.'&b_post='.$_GET['b_post']);
	$osTemplate->assign('navtrail', $breadcrumb->trail(' &raquo; '));

	$osTemplate->assign('POST_ID', $posts['post_id']);
	$osTemplate->assign('POST_NAME', $posts['post_name']);
	$osTemplate->assign('POST_DESC', $posts['post_description']);
	$osTemplate->assign('POST_DATE', $posts['post_date']);
	$osTemplate->assign('CAT_ID', $posts['cat_id']);
	$osTemplate->assign('CAT_TITLE', $posts['cat_title']);

	if (!empty($_GET['b_post'])) {

		// Настройки
		$show_form		=	'true';		// Показывать форму добавления комментариев
		$com_limit		=	'100';		// Количество комментариев в списке
		$com_sort		=	'1';		// Сортировка комментариев: 1 - новые внизу, 0 - новые вверху

		$s_cid = $_SESSION['customer_id'];
		$s_aid = $_SESSION['customers_status']['customers_status_id'];

		$comment_sorting = $com_sort == 1 ? "ASC" : "DESC";

		$com_query = os_db_query("SELECT * FROM ".DB_NEBOX_BLOG_COMMETS." WHERE post_id = ".$_GET['b_post']." ORDER BY date_added ".$comment_sorting." LIMIT ".$com_limit."");

		// Получаем имя юзера по сессии, чтобы заполнить в форме поле name
		if (isset($s_cid))
			$current_user = $_SESSION['customer_first_name'];

		while ($com = os_db_fetch_array($com_query)) {

			// Смотрим, кто у нас оставил коммент - гость или покупатель
			if ($com['user_id'] == '1') {
				if ($s_aid == 0) {
					// Если зашли как админ, то будем видеть ссылку на страницу пользователя в админке.
					// В противном случае показыем просто текст
					$user_group = "<a href=\"/admin/customers.php?cID=".$s_cid."&action=edit\" title=\"Страница пользователя\" target=\"_blank\">Покупатель</a>";
				} else {
					$user_group = "Покупатель";
				}
			} else {
				$user_group = "Гость";
			}

			// Ссылка на удаление коммента. Видна только админку.
			if ($s_aid == 0)
				$del_link = "<span class=\"delete_button\"><a href=\"#\" id=\"".$com['id']."\" class=\"delete_comm\">X</a></span>";

			// Для возможности чередовать стили комментариев (может кому будет нужно)
			// bg1 и bg2 это CSS стили, которым можно присвоить разные цвета фона и т.д..
			// В шаблоне доступна метка {$com_data.COM_COLOR} которую можно использовать
			// таким образом <li class="{$com_data.COM_COLOR}">тут тело коммента</li>
			$bg = $bg == 'bg1' ? 'bg2':'bg1';
			$com_content[] = array (
				'COM_ID'		=> $com['id'],
				'COM_USER'		=> $com['name'],
				'COM_TEXT'		=> $com['text'],
				'COM_DATA'		=> $com['date_added'],
				'COM_COLOR'		=> $bg,
				'COM_USER_S'	=> $user_group,
				'COM_DELETE'	=> $del_link,
				'COM_STATUS'	=> $com['status'],
			);
		}
		$com_count = os_db_num_rows($com_query);

		$osTemplate->assign('COM_COUNT', $com_count);
		$osTemplate->assign('COM_CON', $com_content);
		$osTemplate->assign('COM_S_USER', $current_user);
		$osTemplate->assign('COM_SHOW_FORM', $show_form);

	}
	$osTemplate->assign('ONE', true);
}
else
{
	$welcome_query = os_db_query("SELECT description FROM ".DB_NEBOX_BLOG_WELCOME." WHERE id = 1"); // ;)
	$welcome = os_db_fetch_array($welcome_query);

	$osTemplate->assign('WELCOME_TEXT', $welcome['description']);
}

$osTemplate->assign('language', $_SESSION['language']);
$osTemplate->caching = 0;
$osTemplate->assign('module_content', $module_content);
$main_content = $osTemplate->fetch( dirname(__FILE__).'/themes/'.NBBS_THEME.'/blog.html');
$osTemplate->assign('main_content', $main_content);

$osTemplate->assign('language', $_SESSION['language']);
$osTemplate->caching = 0;
$template = (file_exists(_THEMES_C.FILENAME_ARTICLES.'.html') ? CURRENT_TEMPLATE.'/'.FILENAME_ARTICLES.'.html' : CURRENT_TEMPLATE.'/index.html');
$osTemplate->load_filter('output', 'trimhitespace');
$osTemplate->display($template);



/*INFOCENTER_MODUL_ON == 'false' && */
/*
if (isset($_GET['blog']))
{
	if (isset($_GET['blog']) && is_numeric($_GET['blog']))
	{
		$start_query = os_db_query("SELECT titel, description FROM ".DB_NEBOX_BLOG_CATEGORY." WHERE id = '".(int)$_GET['blog']."'");
		$categorie_name = os_db_fetch_array($start_query);

		$osTemplate->assign('CATNAME', $categorie_name['titel']);
		$osTemplate->assign('TEXT', $categorie_name['description']);

		if (!isset($_GET['item']))
		{
			$item = 0;
			$items_query = os_db_query("SELECT id, title FROM ".DB_NEBOX_BLOG_POSTS." WHERE status = 1 AND categories_id = '".(int)$_GET['blog']."' ORDER BY position ASC");  

			while ($items = os_db_fetch_array($items_query))
			{
				$categorie_array[$item] = array
				(
					'ITEM_ID'     => $items['id'],
					'ITEM_TITLE'  => $items['title'],
					'ITEM_LINK'   => os_href_link(FILENAME_INFOCENTER.'?blog='.(int)$_GET['blog'].'&item='.$items['id'])
				);
				$item++;  
			}
			if ($item > 0)
			{
				$osTemplate->assign('FLAGCATLEVEL2', 'flagcatlevel2');
			}
			$osTemplate->assign('ITEMS1', $categorie_array);
		}
	}
	elseif (!is_numeric($_GET['blog']))
	{
		$osTemplate->assign('FLAGINFOSTART', 'startseite');
		
		$start_query = os_db_query("SELECT description FROM ".DB_NEBOX_BLOG_WELCOME." WHERE id = 1");
		$start = os_db_fetch_array($start_query);
		$osTemplate->assign('TEXT', $start['description']);
	}

	if ((isset($_GET['blog']) && is_numeric($_GET['blog'])) && (isset($_GET['item'])&& is_numeric($_GET['item'])))
	{
		$select_item_query = os_db_query("SELECT title, name, description FROM ".DB_NEBOX_BLOG_POSTS." WHERE status = 1 AND id = '".(int)$_GET['item']."' AND categories_id = '".(int)$_GET['blog']."'");
		$select_item = os_db_fetch_array($select_item_query);  

		$osTemplate->assign('NAME', $select_item['name']);
		$osTemplate->assign('TITEL', $select_item['title']);
		$osTemplate->assign('TEXT', $select_item['description']);
	}
 
}
elseif (!isset($_GET['blog']))
{
	$osTemplate->assign('TEXT', 'Oops.');
	$osTemplate->assign('language', $_SESSION['language']);
}
*/


?>