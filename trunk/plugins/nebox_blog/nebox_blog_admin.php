<?php
global $main;

include (dirname(__FILE__).'/lang/'.$_SESSION['language'].'.php');

$nbb_url = os_href_link(FILENAME_PLUGINS_PAGE, 'page=nebox_blog_admin');

// Статусы
$status = array();
if($_GET['action'] == 'editcategories')
{
	$status[] = array('id' => 0, 'text' => NBB_UPDATE_SAVE);
}
else
{
	$status[] = array('id' => 0, 'text' => NBB_TABLE_FOOTER_STATUS_0);
}
$status[] = array('id' => 1, 'text' => NBB_TABLE_FOOTER_STATUS_1);
$status[] = array('id' => 2, 'text' => NBB_TABLE_FOOTER_STATUS_2);
$status[] = array('id' => 3, 'text' => NBB_TABLE_FOOTER_STATUS_3);

// Edit
$listedit = array();
$listedit[] = array('id' => 0, 'text' => NBB_TABLE_HEADING_ACTION_EDIT_0);
$listedit[] = array('id' => 1, 'text' => NBB_TABLE_HEADING_ACTION_EDIT);

// CATEGORIES
$select_categories = array();

$categories_query = os_db_query("SELECT id, title, description FROM ".DB_NEBOX_BLOG_CATEGORY." ORDER BY position ASC"); 

$select_categories[] = array('id' => 0, 'text' => NBB_TABLE_FOOTER_STATUS_0); 

while($categories = os_db_fetch_array($categories_query))
{
	$select_categories[] = array('id' => $categories['id'], 'text' => $categories['title'], 'description' => $categories['description']);
}
	
	
switch($_GET['action'])
{
	case 'comments_list':
		if($_GET['action'] == 'comments_list')
		{
			if ($_GET['delete_comment'])
			{
				$delete = os_db_query("DELETE FROM ".DB_NEBOX_BLOG_COMMETS." WHERE id = '".(int)$_GET['delete_comment']."'");
				os_redirect($nbb_url.'&action=comments_list');
			}
			if ($_GET['delete_comment_all']=='true')
			{
				os_db_query("truncate ".DB_NEBOX_BLOG_COMMETS."");
				os_redirect($nbb_url.'&action=comments_list');
			}
		}
	break;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	case 'set':
	$max_elements = count($_POST['status']);
  
	// OFFLINE
	if ($_POST['set_status'] == 1)
	{
		$update_array = array('status' => '0'); 

		for ($i = 0; $i < $max_elements; $i++)
		{
			// POSTS OVERVIEW?
			if ((int)$_GET['cat'])
			{
				os_db_perform(DB_NEBOX_BLOG_POSTS, $update_array, 'update', "id = '".$_POST['status'][$i]."'");
			}
			else
			{
				os_db_perform(DB_NEBOX_BLOG_CATEGORY, $update_array, 'update', "id = '".$_POST['status'][$i]."'");
				os_db_perform(DB_NEBOX_BLOG_POSTS, $update_array, 'update', "categories_id = '".$_POST['status'][$i]."'");
			}
		}

	// ONLINE
	}
	elseif ($_POST['set_status'] == 2)
	{
		$update_array = array('status' => '1');

		for($i = 0; $i < $max_elements; $i++)
		{
			// POSTS OVERVIEW?
			if ((int)$_GET['cat'])
			{
				os_db_perform(DB_NEBOX_BLOG_POSTS, $update_array, 'update', "id = '".$_POST['status'][$i]."'");
			}
			else
			{
				os_db_perform(DB_NEBOX_BLOG_CATEGORY, $update_array, 'update', "id = '".$_POST['status'][$i]."'");
				os_db_perform(DB_NEBOX_BLOG_POSTS, $update_array, 'update', "categories_id = '".$_POST['status'][$i]."'");
			}
		}

	// DEL ALL
	}
	elseif ($_POST['set_status'] == 3)
	{
		for ($i = 0; $i < $max_elements; $i++)
		{
			// POSTS OVERVIEW?
			if ((int)$_GET['cat'])
			{
				os_db_query("DELETE FROM ".DB_NEBOX_BLOG_POSTS." where id = '".$_POST['status'][$i]."'");
			}
			else
			{
				os_db_query("DELETE FROM ".DB_NEBOX_BLOG_CATEGORY." where id = '".$_POST['status'][$i]."'");
				os_db_query("DELETE FROM ".DB_NEBOX_BLOG_POSTS." where categories_id = '".$_POST['status'][$i]."'");
			}
		}
	}

	// STAY IN FAQ CATEGORIE
	if ((int)$_GET['cat'])
	{
		os_redirect($nbb_url.'&action=showposts&cat='.(int)$_GET['cat']);

	// STAY IN CATEGORIES-OVERVIEW  
	}
	else
	{
		os_redirect($nbb_url);
	}
    break;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    case 'insertcategorie':
      
	// nдchste ID ermitteln
	if ($_POST['categorie_id'] == '')
	{
		$next_query = os_db_query("SELECT MAX(id) AS ID FROM ".DB_NEBOX_BLOG_CATEGORY);
		$next_id = os_db_fetch_array($next_query);

		$new_categorie_array = array
		(
			'title' 	  => os_db_prepare_input($_POST['title']),
			'position' 	  => os_db_prepare_input($_POST['position']),
			'description' => os_db_prepare_input($_POST['description'])
		);
		os_db_perform(DB_NEBOX_BLOG_CATEGORY, $new_categorie_array);    

	// UPDATE
	}
	elseif ($_POST['categorie_id'] != '')
	{
		$categorieID = $_POST['categorie_id'];

		$update_categorie_array = array
		(
			'title'         => os_db_prepare_input($_POST['title']),
			'position'      => os_db_prepare_input($_POST['position']),
			'description'   => os_db_prepare_input($_POST['description'])
		);
		os_db_perform(DB_NEBOX_BLOG_CATEGORY, $update_categorie_array, 'update', "id = '" . $categorieID . "'");
	}
	os_redirect($nbb_url);
	break;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	case 'updatestartsite':

	// INSERT
	if ($_POST['id'] != 1)
	{
		$new_start_array = array
		(
			'id' => 1,
			'description' => os_db_prepare_input($_POST['description'])
		);
		os_db_perform(DB_NEBOX_BLOG_WELCOME, $new_start_array);  

	// UPDATE	
	}
	else
	{
		os_db_query("UPDATE ".DB_NEBOX_BLOG_WELCOME." SET description = '".os_db_prepare_input($_POST['description'])."' WHERE id = 1");
	}
	os_redirect($nbb_url);
	break;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    case 'insertpost':

	if ($_POST['post_id'] == '')
	{
		$next_query = os_db_query("SELECT MAX(id) AS ID FROM ".DB_NEBOX_BLOG_POSTS);
		$next_id = os_db_fetch_array($next_query);

		$new_post_array = array
		(
			'categories_id'		=> os_db_prepare_input($_POST['categories_id']),
			'title'				=> os_db_prepare_input($_POST['title']),
			'name'				=> os_db_prepare_input($_POST['name']),
			'short_description'	=> os_db_prepare_input($_POST['short_description']),
			'description'		=> os_db_prepare_input($_POST['description']),
			'status'			=> os_db_prepare_input($_POST['status']),
			'position'			=> os_db_prepare_input($_POST['position']),
			'date_added'		=> os_db_prepare_input(nov()),
			'm_title'			=> os_db_prepare_input($_POST['post_m_title']),
			'm_desc'			=> os_db_prepare_input($_POST['post_m_desc']),
			'm_keywords'		=> os_db_prepare_input($_POST['post_m_keywords'])
		);

		os_db_perform(DB_NEBOX_BLOG_POSTS, $new_post_array);
	// UPDATE  
	}
	elseif ($_POST['post_id'] != '')
	{
		$update_post_array = array
		(
			'categories_id'		=> os_db_prepare_input($_POST['categories_id']),
			'title'				=> os_db_prepare_input($_POST['title']),
			'name'				=> os_db_prepare_input($_POST['name']),
			'short_description'	=> os_db_prepare_input($_POST['short_description']),
			'description'		=> os_db_prepare_input($_POST['description']),
			'status'			=> os_db_prepare_input($_POST['status']),
			'position'			=> os_db_prepare_input($_POST['position']),
			'date_added'		=> os_db_prepare_input(nov()),
			'm_title'			=> os_db_prepare_input($_POST['post_m_title']),
			'm_desc'			=> os_db_prepare_input($_POST['post_m_desc']),
			'm_keywords'		=> os_db_prepare_input($_POST['post_m_keywords'])
		);
		os_db_perform(DB_NEBOX_BLOG_POSTS, $update_post_array, 'update', "id = '".$_POST['post_id']."' ");
	}
	os_redirect($nbb_url.'&action=showposts&cat='.$_POST['categories_id']);
	break;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	default:

	$cat_query = os_db_query("SELECT * FROM ".DB_NEBOX_BLOG_CATEGORY." ORDER BY position ASC");

	if ($_GET['action'] == 'showposts')
	{
		$categorie_query = os_db_query("SELECT title FROM ".DB_NEBOX_BLOG_CATEGORY." WHERE id = '".(int)$_GET['cat']."'"); 
		$categorie = os_db_fetch_array($categorie_query);
 
		$posts_query = os_db_query("SELECT fi.id, fi.title, fi.date_added, fi.position AS position, fi.status AS status, fi.categories_id AS categories_id FROM ".DB_NEBOX_BLOG_POSTS." fi WHERE fi.categories_id = '".(int)$_GET['cat']."' ORDER BY fi.position ASC"); 
	}

}

$main->head();
$main->top_menu();
?>
<script type="text/javascript">
<!--
function checkboxes(wert){
	var my = document.leiste;
	var len = my.length;
	
	for (var i = 0; i < len; i++) {
		var e = my.elements[i];
		if (e.name == "status[]") {
			e.checked = wert;
		}
	}
}
//-->
</script>
<style>
.wrapper-content {width:100%;background:#f4fdff;}
.wrap-con {padding:0 10px 0 10px;}
.clear {clear:both;}

.msg-success {padding:10px;color:#ffffff;background:#589b43;}

.wrap-con h1 {float:left;font-size:1.2em;}
.right-top-menu {float:right;font-size:0.9em;}
.right-top-menu li {list-style:none;display:inline;}
.right-top-menu li a {padding:4px 8px 4px 8px;color:#456e90;background:#c5def2;}
.right-top-menu li a:hover {text-decoration:none;color:#ffffff;background:#456e90;}

.blog-button {padding:3px 10px 3px 10px;font-size:1em;cursor:pointer;margin:10px 0 10px 0;}
a.blog-del {color:red;text-align:center;}
a.blog-del-big {text-align:center;margin:10px 0 10px 0;display:block;width:200px;padding:4px 8px 4px 8px;background:#c5def2;color:#456e90;}
a:hover.blog-del-big {background:red;color:#ffffff;text-decoration:none;}

.blog-table {padding:5px;font-size:0.9em;background:#ffffff;border-bottom:1px solid #b6cddb;}
.blog-table.blog-table-left {width:35%;}
.blog-table.blog-table-right {border-left:1px solid #dcebf5;}
.blog-table-note {font-size:0.8em;color:#9ea5b1;}

.blog-table-head td {padding:5px;font-size:0.9em;background:#c7e0f0;font-weight: bold;}
.blog-table-list {padding:5px;font-size:0.9em;background:#ffffff;border-bottom:1px solid #b6cddb;}
.blr {border-right:1px solid #b6cddb;}

.mod-copy {display:none;font-size:0.7em;text-align:right;padding:10px;}
.mod-copy a {font-size:1em;}
</style>

<div class="wrapper-content">
	<div class="wrap-con">
		<h1><?php echo NBB_HEADING_TITLE.' ('.NBB_CONTENT_NOTE; ?>)</h1>
		<ul class="right-top-menu">
			<li><a href="<?php echo $nbb_url.'">'.NBB_TABLE_HEADING_NAVIGATION_OVERVIEW; ?></a></li>
			<li><a href="<?php echo $nbb_url.'&action=newcategorie">'.NBB_TABLE_HEADING_NAVIGATION_NEWCATEGORIE; ?></a></li>
			<li><a href="<?php echo $nbb_url.'&action=newpost">'.NBB_TABLE_HEADING_NAVIGATION_NEWPOST; ?></a></li>
			<li><a href="<?php echo $nbb_url.'&action=comments_list'; ?>">Комментарии</a></li>
			<li><a href="<?php echo $nbb_url.'&action=startsite">'.NBB_TABLE_HEADING_NAVIGATION_STARTSITE; ?></a></li>
		</ul>
		<div class="clear"></div>

<?php 
  if($_GET['action'] == 'newcategorie' || $_GET['action'] == 'editcategories'){
    echo os_draw_form('categorie', FILENAME_PLUGINS_PAGE, 'page=nebox_blog_admin&action=insertcategorie', 'post', '');
    
?>
<table width="100%" border="0" cellpadding="5" cellspacing="1" align="center" style="border-bottom:1px solid #dddddd;">
<?php 
	$edit_categorie_query = os_db_query("SELECT * FROM ".DB_NEBOX_BLOG_CATEGORY." WHERE id = '".(int)$_GET['cat']."'");
	$edit_categorie = os_db_fetch_array($edit_categorie_query);    
?>
  <tr>
    <td class="uni_content" height="20">&nbsp;</td>
  </tr>
  <tr>
	<td>
		<table border="0" cellpadding="5" cellspacing="0">
			<tr>
				<td width=""><?php echo NBB_TABLE_HEADING_NEWCATEGORIE_NAME;?></td>
				<td><?php echo os_draw_input_field('title', $edit_categorie['title'],'size="55"');?></td>
				<td><?php echo NBB_TABLE_HEADING_NEWCATEGORIE_POSITION;?></td>
				<td><?php echo os_draw_input_field('position', $edit_categorie['position'],'size="4"');?></td>
			</tr>
		</table>
	</td>
  </tr>
  <tr>
    <td valign="top" colspan="1">
    
<table width="100%" border="0" cellpadding="3" cellspacing="1" align="center" style="border:1px solid #dddddd;">  
  <tr>
    <td class="uni_content"><?php echo os_draw_textarea_field('description','','20','10',$edit_categorie['description'], 'style="width:99%;"'); ?></td>
  </tr>
</table>

		</td>
  </tr> 

  <tr>
    <td class="uni_content" height="20">&nbsp;</td>
  </tr>
  <tr>
    <td align="right"><?php echo os_draw_hidden_field('categorie_id', $edit_categorie['id']) . '<input type="submit" class="uni_button" value="Speichern" onClick="return confirm(\''.NBB_UPDATE_ENTRY.'\')">';?></td>
  </tr> 
  <tr>
    <td class="uni_content" height="20">&nbsp;</td>
  </tr>
</table>

</form>  
<?php    
  }
?>
<br>

<?php
// STARTSEITE

	if($_GET['action'] == 'startsite'){
		echo os_draw_form('start', FILENAME_PLUGINS_PAGE, 'page=nebox_blog_admin&action=updatestartsite', 'post', '');

?>

<table width="100%" border="0" cellpadding="5" cellspacing="1" align="center" style="border-bottom:1px solid #dddddd;">
<?php

  	// EDIT POST
	$start_query = os_db_query("SELECT id, description AS text FROM ".DB_NEBOX_BLOG_WELCOME." WHERE id = 1"); 

	$start = os_db_fetch_array($start_query);
?>
  <tr>
    <td valign="top" colspan="2">
    
<table width="100%" border="0" cellpadding="3" cellspacing="1" align="center" style="border:1px solid #dddddd;"> 
  <tr>
    <td class="uni_content"><?php echo os_draw_textarea_field('description','','20','10',$start['text'], 'style="width:99%;"'); ?></td>
  </tr>
</table>

		</td>
  </tr> 

  <tr>
    <td class="uni_content" height="20">&nbsp;</td>
  </tr>  
	<tr>
		<td class="uni_content">
<?php echo os_draw_hidden_field('id',$start['id']) . '<input type="submit" class="button" onClick="this.blur();" value="'.NBB_BUTTON_SAVE.'"/>'; ?>
<a class="button" onClick="this.blur();" href="<?php echo $nbb_url; ?>"><?php echo NBB_BUTTON_BACK; ?></a></td>
	</tr> 
</table>

</form>
<br />
<!-- news bearbeiten ende -->

<!--
###########################################################################################################
	КОММЕНТАРИИ
###########################################################################################################
-->
		<?php 
			} elseif ($_GET['action'] == 'comments_list'){
		?>
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr class="blog-table-head">
					<td>#</td>
					<td>Комментарий</td>
					<td>Дата</td>
					<td>Автор</td>
					<td>Запись</td>
					<td>Действие</td>
				</tr>
		<?php
			//$comments_query_raw = "select * from blog_comment order by date DESC";
			$com_query = os_db_query("
				SELECT 
					c.id AS com_id,
					c.text,
					c.date_added,
					c.user_id,
					p.id,
					p.name 
				FROM 
					".DB_NEBOX_BLOG_COMMETS." AS c,
					".DB_NEBOX_BLOG_POSTS." AS p
				WHERE 
					c.post_id = p.id 
				ORDER BY 
					c.id DESC
			");

			while ($com = os_db_fetch_array($com_query)) {
		?>
				<tr>
					<td width="3%" class="blog-table-list blr"><?php echo $com['com_id'] ; ?></td>
					<td width="47%" class="blog-table-list blr"><?php echo $com['text'] ; ?></td>
					<td width="10%" class="blog-table-list blr"><?php echo $com['date_added']; ?></td>
					<td width="15%" class="blog-table-list blr"><?php echo $com['user_id']; ?></td>
					<td width="15%" class="blog-table-list blr"><?php echo $com['name']; ?></td>
					<td width="10%" class="blog-table-list">
						<a class="blog-del" href="<?php echo $nbb_url.'&action=comments_list&delete_comment='.$com['com_id'];?>" onclick="return confirm('Вы действительно хотите удалить комментарий?');">Удалить</a>
					</td>
				</tr>
		<?php } ?>
				<tr>
					<td colspan="6">
						<a class="blog-del-big" href="<?php echo $nbb_url.'&action=comments_list&delete_comment_all=true';?>" onclick="return confirm('Вы действительно хотите удалить все комментарии?');">Удалить все комментарии</a>
					</td>
				</tr> 
			</table>
<?php 
}
elseif ($_GET['action'] == 'editpost' || $_GET['action'] == 'newpost')
{
	echo os_draw_form('post', FILENAME_PLUGINS_PAGE, 'page=nebox_blog_admin&action=insertpost', 'post', '');
?>
<!--
###########################################################################################################
	ПОСТ
###########################################################################################################
-->
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
	$post_query = os_db_query("
		SELECT 
			p.id,
			p.categories_id AS categories_id,
			p.title AS post_title,
			p.name AS post_name,
			p.short_description AS post_short_description,
			p.description AS post_description,
			p.status AS post_status,
			p.position AS post_position,
			p.m_title AS post_m_title,
			p.m_desc AS post_m_desc,
			p.m_keywords AS post_m_keywords,
			c.title AS categorie_name
		FROM 
			".DB_NEBOX_BLOG_POSTS." p, 
			".DB_NEBOX_BLOG_CATEGORY." c 
		WHERE 
			p.id = '".(int)$_GET['post']."' AND 
			categories_id = c.id
	");

	$post = os_db_fetch_array($post_query);
?>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWPOST_NAME;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_input_field('name', $post['post_name'],'size="70"');?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWPOST_TITLE;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_input_field('title', $post['post_title'],'size="70"');?></td>
	</tr>
	<tr>
		<td colspan="2" class="blog-table blog-table-left"><?php echo os_draw_textarea_field('short_description','','10','5',$post['post_short_description'], 'style="width:99%;"'); ?></td>
	</tr>
	<tr>
		<td colspan="2" class="blog-table blog-table-left"><?php echo os_draw_textarea_field('description','','20','10',$post['post_description'], 'style="width:99%;"'); ?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_TITLE;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_pull_down_menu('categories_id', $select_categories, $post['categories_id'], '');?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWPOST_M_TITLE;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_input_field('m_title', $post['post_m_title'],'size="70"');?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWPOST_M_DESC;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_textarea_field('m_desc','','5','2',$post['post_m_desc'], 'style="width:99%;"'); ?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWPOST_M_KEYWORDS;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_input_field('m_keywords', $post['post_m_keywords'],'size="70"');?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWCATEGORIE_POSITION;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_input_field('position', $post['post_position'],'size="4"');?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left">Статус</td>
		<td class="blog-table blog-table-right">
			<select class="round" name="status" id="status">
				<option value="1" <?php if ($post['post_status'] == '1') {echo "selected";} ?>>Включено</option>
				<option value="0" <?php if ($post['post_status'] == '0') {echo "selected";} ?>>Выключено</option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="blog-table blog-table-left">
<?php
			echo os_draw_hidden_field('post_id',$post['id']).'<input type="submit" class="button" onClick="this.blur();" value="'.NBB_BUTTON_SAVE.'"/>'; ?>
			<a onClick="this.blur();" href="<?php echo $nbb_url; ?>"><?php echo NBB_BUTTON_BACK; ?></a>
		</td>
	</tr>
</table>
</form>

<?php 
}
elseif ($_GET['action'] == 'showposts')
{
	echo os_draw_form('leiste', FILENAME_PLUGINS_PAGE, 'page=nebox_blog_admin&action=set&cat='.(int)$_GET['cat'], 'post','');
?>
<!--
###########################################################################################################
	ЗАПИСИ В КАТЕГОРИИ
###########################################################################################################
-->
<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr class="blog-table-head">
		<td><input type="checkbox" onClick="javascript:checkboxes(this.checked);"></td>
		<td><?php echo $categorie['title'];?></td>
		<td><?php echo NBB_TABLE_HEADING_NEWCATEGORIE_DATE_ADDED;?></td>
		<td><?php echo NBB_TABLE_HEADING_NEWCATEGORIE_POSITION;?></td>
		<td>Статус</td>
		<td><?php echo NBB_TABLE_HEADING_ACTION;?></td>
	</tr>
<?php
$i = 0;
while ($posts = os_db_fetch_array($posts_query))
{
?>
	<tr>
		<td class="blog-table-list blr"><?php echo os_draw_selection_field('status[]', 'checkbox', $posts['id']);?></td>
		<td class="blog-table-list blr"><?php echo $posts['title'];?></td>
		<td class="blog-table-list blr"><?php echo $posts['date_added'];?></td>
		<td class="blog-table-list blr"><?php echo $posts['position'];?></td>
		<td class="blog-table-list blr"><?php echo $posts['status']; ?></td>
		<td class="blog-table-list"><a href="<?php echo $nbb_url.'&action=editpost&post='.$posts['id'].'">'.NBB_TABLE_HEADING_ACTION_EDIT;?></a></td>
	</tr> 
<?php
	$i++;
	}
?>	
</table>
<br>

<!-- LEISTE -->
<table width="100%" border="0" cellspacing="1" cellpadding="3">
	<tr>
		<td width="70" align="center"><?php echo NBB_TABLE_FOOTER_STATUS;?>
		<td width="70" align="center"><?php echo os_draw_pull_down_menu('set_status', $status, '', 'style="width:100px;"');?></td>
		<td width="50" align="center"><?php echo '<input type="submit" class="uni_button" value="GO!" onClick="return confirm(\''.NBB_UPDATE_ENTRY.'\')">';?></td>
		<td>&nbsp;</td>
	</tr>
</table>	
</form>

<?php
}
else
{
?>
<!--
###########################################################################################################
	КАТЕГОРИИ
###########################################################################################################
-->
<?php echo os_draw_form('leiste', FILENAME_PLUGINS_PAGE, 'page=nebox_blog_admin&action=set', 'post','');?>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr class="blog-table-head">
		<td><input type="checkbox" onClick="javascript:checkboxes(this.checked);"></td>
		<td><?php echo NBB_TABLE_HEADING_TITLE;?></td>
		<td><?php echo NBB_TABLE_HEADING_INFOCENTER_TOPIC;?></td>
		<td><?php echo NBB_TABLE_HEADING_NEWCATEGORIE_POSITION;?></td>
		<td><?php echo NBB_TABLE_HEADING_ACTION;?></td>
	</tr>
  <tr>
<?php
while ($cat = os_db_fetch_array($cat_query))
{
	$post_count_query = os_db_query("SELECT COUNT(id) AS count FROM ".DB_NEBOX_BLOG_POSTS." WHERE categories_id = '".$cat['id']."'"); 
	$post_count = os_db_fetch_array($post_count_query);    
?>
	<tr>
		<td class="blog-table-list blr"><?php echo os_draw_selection_field('status[]', 'checkbox', $cat['id']);?></td>
		<td class="blog-table-list blr"><?php echo $cat['title']; ?></td>
		<td class="blog-table-list blr"><?php echo $post_count['count']; ?></td>
		<td class="blog-table-list blr"><?php echo $cat['position']; ?></td>
		<td class="blog-table-list">
			<a href="<?php echo $nbb_url.'&action=showposts&cat='.$cat['id'].'">'.ICON_FOLDER;?></a>
			<a href="<?php echo $nbb_url.'&action=editcategories&cat='.$cat['id'].'">'.NBB_TABLE_HEADING_ACTION_EDIT;?></a>
		</td>
	</tr> 
<?php
}
?>
</table>

<!-- LEISTE -->
<table width="100%" border="0" cellspacing="0" cellpadding="3">
  <tr>
		<td width="70" align="center"><?php echo NBB_TABLE_FOOTER_STATUS;?>
		<td width="70" align="center"><?php echo os_draw_pull_down_menu('set_status', $status, '', 'style="width:100px;"');?></td>
		<td width="50" align="center"><?php echo '<input type="submit" class="uni_button" value="GO!" onClick="return confirm(\''.NBB_UPDATE_ENTRY.'\')">';?></td>
		<td>&nbsp;</td>
	</tr>
</table>	
</form>
<?php
}
?>



		<div class="mod-copy">Адаптация под ShopOS - <a href="http://templatica.ru" target="_blank">NeBox</a></div>
	</div>
</div>

<?php $main->bottom(); ?>