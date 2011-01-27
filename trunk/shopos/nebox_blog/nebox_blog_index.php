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
			p.position, p.id DESC
	";
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

	$navtrail_cat_query = os_db_query("SELECT id, title FROM ".DB_NEBOX_BLOG_CATEGORY." WHERE id = '".(int)$_GET['b_cat']."'"); 
	$navtrail_cat = os_db_fetch_array($navtrail_cat_query);

	if (NBBS_SEF == true)
	{
		$cat_url = _HTTP.'blog/cat/'.$navtrail_cat['id'].'.html';
	}
	else
	{
		$cat_url = _HTTP.'index.php?page=blog&b_cat='.$navtrail_cat['id'];
	}

	$breadcrumb->add($navtrail_cat['title'], $cat_url);
	$osTemplate->assign('navtrail', $breadcrumb->trail(' &raquo; '));

	$module_content = array();
	while ($posts = os_db_fetch_array($query,true))
	{

		if (NBBS_SEF == true)
		{
			$post_url = _HTTP.'blog/post/'.$posts['post_id'].'.html';
			$cat_url = _HTTP.'blog/cat/'.$posts['cat_id'].'.html';
		}
		else
		{
			$post_url = _HTTP.'index.php?page=blog&b_post='.$posts['post_id'];
			$cat_url = _HTTP.'index.php?page=blog&b_cat='.$posts['cat_id'];
		}
	
		$module_content[]=array
		(
			'POST_NAME'  => $posts['post_name'],
			'POST_SHORT_DESC'  => $posts['post_short_description'],
			'POST_DATE'  => $posts['post_date'],
			'CAT_TITLE' => $posts['cat_title'],
			'POST_URL' => $post_url,
			'CAT_URL' => $cat_url
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
	
	if (NBBS_SEF == true)
	{
		$post_url = _HTTP.'blog/post/'.$posts['post_id'].'.html';
		$cat_url = _HTTP.'blog/cat/'.$posts['cat_id'].'.html';
	}
	else
	{
		$post_url = _HTTP.'index.php?page=blog&b_post='.$posts['post_id'];
		$cat_url = _HTTP.'index.php?page=blog&b_cat='.$posts['cat_id'];
	}

	$breadcrumb->add($posts['post_name'], $post_url);
	$osTemplate->assign('navtrail', $breadcrumb->trail(' &raquo; '));

	$osTemplate->assign('POST_ID', $posts['post_id']);
	$osTemplate->assign('POST_NAME', $posts['post_name']);
	$osTemplate->assign('POST_DESC', $posts['post_description']);
	$osTemplate->assign('POST_DATE', $posts['post_date']);
	$osTemplate->assign('CAT_TITLE', $posts['cat_title']);
	$osTemplate->assign('POST_URL', $post_url);
	$osTemplate->assign('CAT_URL', $cat_url);

	if (!empty($_GET['b_post'])) {

		// Настройки
		// Показывать форму добавления комментариев
		$show_form		=	'true';
		// Количество комментариев в списке
		$com_limit		=	100;
		// Сортировка комментариев: 1 - новые внизу, 0 - новые вверху
		$com_sort		=	1;

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
?>