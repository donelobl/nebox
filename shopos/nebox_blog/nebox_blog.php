<?php
/*
Plugin Name: NeBox Blog
Plugin URI: http://www.nebox.ru/
Description: Плагин блога
Version: 1.1
Author: NeBox (Посетить блог)
Author URI: http://www.nebox.ru/
Plugin Group: Плагины NeBox.ru
*/

define("NBBV",' v1.1 beta');
define("NBB_PLUGIN",plugdir().'/');

/* Simple Setting
---------------------------------------------------*/

// Постов на странице
define("NBBS_NUMBER_OF_POSTS", 5);

// Шаблон блога
define("NBBS_THEME", "default");

// ЧПУ ссылки
define("NBBS_SEF", true);

/*---------------------------------------------------*/

if (NBBS_SEF == true)
{
	define("NEBOX_BLOG_URL", "blog.html");
}
else
{
	define("NEBOX_BLOG_URL", "index.php?page=blog");
}

define("DB_NEBOX_BLOG_CATEGORY", DB_PREFIX."nebox_blog_category");
define("DB_NEBOX_BLOG_POSTS", DB_PREFIX."nebox_blog_posts");
define("DB_NEBOX_BLOG_COMMENTS", DB_PREFIX."nebox_blog_comments");
define("DB_NEBOX_BLOG_WELCOME", DB_PREFIX."nebox_blog_welcome");

add_action('page', 'blog');
add_filter('title', 'blog_title');
add_action('head', 'nebox_blog_head');
add_action('page_admin','nebox_blog_admin');
add_action('head_admin','nebox_blog_admin_head');
add_action('box','nebox_blog_box');
add_action('box','nebox_blog_box_new');

function nebox_blog_head() {
	_e('<link rel="stylesheet" type="text/css" href="'.plugurl().'themes/'.NBBS_THEME.'/css/blog_style.css" />');
	//_e('<script src="'.plugurl().'js/jquery.js" type="text/javascript"></script>');
	_e('<script src="'.plugurl().'js/comm.js" type="text/javascript"></script>');
	_e('<script type="text/javascript" >
	function addComment(){
		form = document.getElementById("com");

		post_id = form.post_id.value;
		text = form.comtext.value;
		name = form.comname.value;

		erdiv = document.getElementById("error");
		erdiv.innerHTML = "Ждите..."

		JsHttpRequest.query(
			"'.plugurl().'ajax/comment.add.php",{
				"post_id": post_id,
				"comtext": text,
				"comname": name
			},
			function (result, errors){
				if (result.err==\'no\'){
					erdiv.innerHTML = "";

					td = document.createElement("div");
					td.innerHTML = "<div class=\"comment-added\"><h3>Спасибо "+result.name+", ваш комментарий добавлен</h3><div class=\"comment-added-content\">"+result.text+"</div></div>";

					comtb = document.getElementById("comtab");
					comtb.appendChild(td);
					form.comtext.value = "";
				} else {
					erdiv.innerHTML = result.log;
				}
			},
			true
		)
	}
$(document).ready(function() {
	$(\'.delete_comm\').live("click",function(){
		var ID = $(this).attr("id");
		var dataString = \'id=\'+ ID;
		if (confirm("Вы уверены, что хотите удалить комментарий?")){
			$.ajax({
				type:		"POST",
				url:		"'.plugurl().'ajax/comment.del.php",
				data:		dataString,
				cache:		false,
				success:	function(html){
					$(".comm"+ID).slideUp(\'slow\', function() {$(this).remove();});
				}
			});
		}
		return false;
	});
});
	</script>');
}
function nebox_blog_admin()
{
	function nebox_blog_admin_head()
	{ 
		// Empty
	}
	include (dirname(__FILE__).'/nebox_blog_admin.php');
}
function nebox_blog_install()
{
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'nebox_blog_category;');
	os_db_query ("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."nebox_blog_category` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`title` varchar(255) NOT NULL DEFAULT '',
		`description` text NOT NULL,
		`status` int(1) NOT NULL default '0',
		`position` int(11) NOT NULL default '0',
		`m_title` text NOT NULL,
		`m_desc` text NOT NULL,
		`m_keywords` text NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM	DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;");
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'nebox_blog_posts;');
	os_db_query ("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."nebox_blog_posts` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`categories_id` int(11) NOT NULL default '0',
		`title` varchar(255) NOT NULL DEFAULT '',
		`name` varchar(200) NOT NULL default '',
		`short_description` text NOT NULL,
		`description` text NOT NULL,
		`status` int(1) NOT NULL default '0',
		`position` int(11) NOT NULL default '0',
		`date_added` datetime NOT NULL default '0000-00-00 00:00:00',
		`m_title` text NOT NULL,
		`m_desc` text NOT NULL,
		`m_keywords` text NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM	DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;");
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'nebox_blog_comments;');
	os_db_query ("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."nebox_blog_comments` (
		`id` int(11) NOT NULL auto_increment,
		`post_id` int(11) NOT NULL,
		`name` varchar(100) NOT NULL default '',
		`text` text NOT NULL,
		`date_added` datetime NOT NULL default '0000-00-00 00:00:00',
		`status` tinyint(1) NOT NULL default '0',
		`user_id` int(11) NOT NULL default '0',
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM	DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;");
	os_db_query ('DROP TABLE IF EXISTS '.DB_PREFIX.'nebox_blog_welcome;');
	os_db_query ("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."nebox_blog_welcome` (
		`id` int(1) NOT NULL default '0',
		`description` text NOT NULL,
		`m_title` text NOT NULL,
		`m_desc` text NOT NULL,
		`m_keywords` text NOT NULL,
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM	DEFAULT CHARSET=utf8 ;");

	add_option('nebox_blog_button', '', 'readonly');
	add_option('blog_url', '', 'readonly');
}

function blog()
{
	function blog_title($value)
	{
		$value = 'Блог - '.$value;
		return $value;
	}
	global $osTemplate;
	global $breadcrumb;

	require(dir_path('includes') . 'header.php');
	include(dirname(__FILE__).'/nebox_blog_index.php');
	require(dir_path('includes') . 'bottom.php');
}
function blog_url_readonly()
{
	_e('
		<center>
			<br /><a href="'.http_path('catalog').NEBOX_BLOG_URL.'" target="_blank"><strong>Ссылка на ваш блог</a></strong><br /><br />
		</center>
	');
}

function nebox_blog_button_readonly()
{
	 _e('<center>'.add_button('page', 'nebox_blog_admin', 'Управление блогом' ).'</center>');
}

function nebox_blog_box()
{
	global $osTemplate;

	$box = new osTemplate;

	$blog_box_query = os_db_query("SELECT * FROM ".DB_NEBOX_BLOG_CATEGORY." WHERE status = 1 ORDER BY position ASC");

	while($blog_box = os_db_fetch_array($blog_box_query, true)) {

		if ($blog_box['id'] == $_GET['b_cat'])
		{
			$blog_cat_url_current = " current";
		}
		else
		{
			$blog_cat_url_current = "";
		}

		if (NBBS_SEF == true)
		{
			$blog_cat_url = _HTTP.'blog/cat/'.$blog_box['id'].'.html';
		}
		else
		{
			$blog_cat_url = _HTTP.'index.php?page=blog&b_cat='.$blog_box['id'];
		}

		$box_content[] = array
		(
			'TITLE' => $blog_box['title'],
			'DESCR' => $blog_box['description'],
			'CAT_URL' => $blog_cat_url,
			'CURRENT' => $blog_cat_url_current
		);
		$box->assign('box_content', $box_content);
	}

	$box->template_dir = plugdir();

	if (!CacheCheck())
	{
		$box->caching = 0;
		$_box_value = $box->fetch('themes/'.NBBS_THEME.'/blog_box.html');
	}
	else
	{
		$box->caching = 1;
		$box->cache_lifetime = CACHE_LIFETIME;
		$box->cache_modified_check = CACHE_CHECK;
		$cache_id = $_SESSION['language'];
		$_box_value = $box->fetch('themes/'.NBBS_THEME.'/blog_box.html',$cache_id);
	}
	$osTemplate->assign('NEBOX_BLOG_BOX', $_box_value);
}

function nebox_blog_box_new()
{
	global $osTemplate;

	$box = new osTemplate;

	$blog_box_query = os_db_query("SELECT id,title FROM ".DB_NEBOX_BLOG_POSTS." WHERE status = 1 ORDER BY id DESC LIMIT 10");

	while($blog_box = os_db_fetch_array($blog_box_query, true)) {
	
		if (NBBS_SEF == true)
		{
			$blog_post_url = _HTTP.'blog/post/'.$blog_box['id'].'.html';
		}
		else
		{
			$blog_post_url = _HTTP.'index.php?page=blog&b_post='.$blog_box['id'];
		}

		$box_content[] = array
		(
			'TITLE' => $blog_box['title'],
			'POST_URL' => $blog_post_url
		);
		$box->assign('box_content', $box_content);
	}

	$box->template_dir = plugdir();

	if (!CacheCheck())
	{
		$box->caching = 0;
		$_box_value = $box->fetch('themes/'.NBBS_THEME.'/blog_box_new.html');
	}
	else
	{
		$box->caching = 1;
		$box->cache_lifetime = CACHE_LIFETIME;
		$box->cache_modified_check = CACHE_CHECK;
		$cache_id = $_SESSION['language'];
		$_box_value = $box->fetch('themes/'.NBBS_THEME.'/blog_box_new.html',$cache_id);
	}
	$osTemplate->assign('NEBOX_BLOG_BOX_NEW', $_box_value);
}
?>