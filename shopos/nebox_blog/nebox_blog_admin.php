<?php
global $main;

include (dirname(__FILE__).'/lang/'.$_SESSION['language'].'.php');

$nbb_url = os_href_link(FILENAME_PLUGINS_PAGE, 'page=nebox_blog_admin');

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

$listedit = array();
$listedit[] = array('id' => 0, 'text' => NBB_TABLE_HEADING_ACTION_EDIT_0);
$listedit[] = array('id' => 1, 'text' => NBB_TABLE_HEADING_ACTION_EDIT);

$select_categories = array();

$categories_query = os_db_query("SELECT id, title, description FROM ".DB_NEBOX_BLOG_CATEGORY." ORDER BY position ASC"); 

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
				$delete = os_db_query("DELETE FROM ".DB_NEBOX_BLOG_COMMENTS." WHERE id = '".(int)$_GET['delete_comment']."'");
				os_redirect($nbb_url.'&action=comments_list');
			}
			elseif ($_GET['delete_comment_all']=='true')
			{
				os_db_query("truncate ".DB_NEBOX_BLOG_COMMENTS."");
				os_redirect($nbb_url.'&action=comments_list');
			}
			elseif ($_GET['status_on'])
			{
				os_db_query("UPDATE ".DB_NEBOX_BLOG_COMMENTS." SET status = 1 WHERE id = '".(int)$_GET['status_on']."'");
				os_redirect($nbb_url.'&action=comments_list');
			}
			elseif ($_GET['status_off'])
			{
				os_db_query("UPDATE ".DB_NEBOX_BLOG_COMMENTS." SET status = 0 WHERE id = '".(int)$_GET['status_off']."'");
				os_redirect($nbb_url.'&action=comments_list');
			}
		}
	break;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	case 'set':
	$max_elements = count($_POST['status']);

	if ($_POST['set_status'] == 1)
	{
		$update_array = array('status' => '0'); 

		for ($i = 0; $i < $max_elements; $i++)
		{
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

	}
	elseif ($_POST['set_status'] == 2)
	{
		$update_array = array('status' => '1');

		for($i = 0; $i < $max_elements; $i++)
		{
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
	}
	elseif ($_POST['set_status'] == 3)
	{
		for ($i = 0; $i < $max_elements; $i++)
		{
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
	if ((int)$_GET['cat'])
	{
		os_redirect($nbb_url.'&action=showposts&cat='.(int)$_GET['cat']);
	}
	else
	{
		os_redirect($nbb_url);
	}
    break;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    case 'insertcategorie':

	if ($_POST['cat_id'] == '')
	{
		$title			= $_POST['title'];
		$description	= $_POST['description'];
		$status			= $_POST['status'];
		$position		= $_POST['position'];
		$m_title		= $_POST['m_title'];
		$m_desc			= $_POST['m_desc'];
		$m_keywords		= $_POST['m_keywords'];

		os_db_query("INSERT INTO ".DB_NEBOX_BLOG_CATEGORY." (title, description, status, position, m_title, m_desc, m_keywords) VALUES ('{$title}','{$description}','{$status}','{$position}','{$m_title}','{$m_desc}', '{$m_keywords}')");
	}
	elseif ($_POST['cat_id'] != '')
	{
		$categorieID = $_POST['cat_id'];

		$update_categorie_array = array
		(
			'title'			=> os_db_prepare_input($_POST['title']),
			'description'	=> os_db_prepare_input($_POST['description']),
			'status'		=> os_db_prepare_input($_POST['status']),
			'position'		=> os_db_prepare_input($_POST['position']),
			'm_title'		=> os_db_prepare_input($_POST['m_title']),
			'm_desc'		=> os_db_prepare_input($_POST['m_desc']),
			'm_keywords'	=> os_db_prepare_input($_POST['m_keywords'])
		);
		os_db_perform(DB_NEBOX_BLOG_CATEGORY, $update_categorie_array, 'update', "id = '".$categorieID."'");
	}
	os_redirect($nbb_url);
	break;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	case 'updatewelcome':

	if ($_POST['id'] != 1)
	{
		$new_welcome_array = array
		(
			'id'			=> 1,
			'description'	=> os_db_prepare_input($_POST['description']),
			'm_title'		=> os_db_prepare_input($_POST['m_title']),
			'm_desc'		=> os_db_prepare_input($_POST['m_desc']),
			'm_keywords'	=> os_db_prepare_input($_POST['m_keywords'])
		);
		os_db_perform(DB_NEBOX_BLOG_WELCOME, $new_welcome_array);
	}
	else
	{
		$update_welcome_array = array
		(
			'id'			=> 1,
			'description'	=> os_db_prepare_input($_POST['description']),
			'm_title'		=> os_db_prepare_input($_POST['m_title']),
			'm_desc'		=> os_db_prepare_input($_POST['m_desc']),
			'm_keywords'	=> os_db_prepare_input($_POST['m_keywords'])
		);
		os_db_perform(DB_NEBOX_BLOG_WELCOME, $update_welcome_array, 'update', "id = 1");
	}
	os_redirect($nbb_url.'&action=startsite');
	break;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    case 'insertpost':

	if ($_POST['post_id'] == '')
	{
		$categories_id		= $_POST['categories_id'];
		$title				= $_POST['title'];
		$name				= $_POST['name'];
		$short_description	= $_POST['short_description'];
		$description		= $_POST['description'];
		$status				= $_POST['status'];
		$position			= $_POST['position'];
		$m_title			= $_POST['m_title'];
		$m_desc				= $_POST['m_desc'];
		$m_keywords			= $_POST['m_keywords'];

		os_db_query("INSERT INTO ".DB_NEBOX_BLOG_POSTS." (categories_id, title, name, short_description, description, status, position, date_added, m_title,m_desc, m_keywords) VALUES ('{$categories_id}','{$title}','{$name}','{$short_description}','{$description}','{$status}', '{$position}', now(),'{$m_title}','{$m_desc}','{$m_keywords}')");
	}
	elseif ($_POST['post_id'] != '')
	{
		$categories_id		= $_POST['categories_id'];
		$title				= $_POST['title'];
		$name				= $_POST['name'];
		$short_description	= $_POST['short_description'];
		$description		= $_POST['description'];
		$status				= $_POST['status'];
		$position			= $_POST['position'];
		$m_title			= $_POST['m_title'];
		$m_desc				= $_POST['m_desc'];
		$m_keywords			= $_POST['m_keywords'];
		os_db_query("UPDATE ".DB_NEBOX_BLOG_POSTS." SET categories_id = '$categories_id', title = '$title', name = '$name', short_description = '$short_description', description = '$description', status = '$status', position = '$position', date_added = now(), m_title = '$m_title',m_desc = '$m_desc', m_keywords = '$m_keywords' WHERE id = ".$_POST['post_id']."");
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
<div class="wrapper-content">
	<div class="wrap-con">
		<h1><?php echo NBB_HEADING_TITLE.' ('.NBB_CONTENT_NOTE; ?>)</h1>
		<ul class="right-top-menu">
			<li><a href="<?php echo $nbb_url.'">'.NBB_TABLE_HEADING_NAVIGATION_OVERVIEW; ?></a></li>
			<li><a href="<?php echo $nbb_url.'&action=newcategorie">'.NBB_TABLE_HEADING_NAVIGATION_NEWCATEGORIE; ?></a></li>
			<li><a href="<?php echo $nbb_url.'&action=newpost">'.NBB_TABLE_HEADING_NAVIGATION_NEWPOST; ?></a></li>
			<li><a href="<?php echo $nbb_url.'&action=comments_list">'.NBB_TABLE_HEADING_NAVIGATION_COMMENTS; ?></a></li>
			<li><a href="<?php echo $nbb_url.'&action=startsite">'.NBB_TABLE_HEADING_NAVIGATION_STARTSITE; ?></a></li>
		</ul>
		<div class="clear"></div>

<?php 
  if($_GET['action'] == 'newcategorie' || $_GET['action'] == 'editcategories'){
    echo os_draw_form('categorie', FILENAME_PLUGINS_PAGE, 'page=nebox_blog_admin&action=insertcategorie', 'post', '');
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
	$cat_edit_query = os_db_query("SELECT * FROM ".DB_NEBOX_BLOG_CATEGORY." WHERE id = '".(int)$_GET['cat']."'");
	$cat_edit = os_db_fetch_array($cat_edit_query); 
?>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWCATEGORIE_NAME;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_input_field('title', $cat_edit['title'],'size="70"');?></td>
	</tr>
	<tr>
		<td colspan="2" class="blog-table blog-table-left"><?php echo os_draw_textarea_field('description','','20','10',$cat_edit['description'], 'style="width:99%;"'); ?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWPOST_M_TITLE;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_input_field('m_title', $cat_edit['m_title'],'size="70"');?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWPOST_M_DESC;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_textarea_field('m_desc','','5','2',$cat_edit['m_desc'], 'style="width:99%;"'); ?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWPOST_M_KEYWORDS;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_input_field('m_keywords', $cat_edit['m_keywords'],'size="70"');?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWCATEGORIE_POSITION;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_input_field('position', $cat_edit['position'],'size="4"');?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left">Статус</td>
		<td class="blog-table blog-table-right">
			<select class="round" name="status" id="status">
				<option value="1" <?php if ($cat_edit['status'] == '1') {echo "selected";} ?>><?php echo NBB_TABLE_FOOTER_STATUS_2 ?></option>
				<option value="0" <?php if ($cat_edit['status'] == '0') {echo "selected";} ?>><?php echo NBB_TABLE_FOOTER_STATUS_1 ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="blog-table blog-table-left">
			<?php echo os_draw_hidden_field('cat_id', $cat_edit['id']) . '<input type="submit" class="uni_button" value="'.NBB_UPDATE_SAVE.'" />';?>
		</td>
	</tr>
</table>
</form>  
<?php    
}
	if($_GET['action'] == 'startsite')
	{
		echo os_draw_form('start', FILENAME_PLUGINS_PAGE, 'page=nebox_blog_admin&action=updatewelcome', 'post', '');

		$start_query = os_db_query("SELECT * FROM ".DB_NEBOX_BLOG_WELCOME." WHERE id = 1"); 
		$start = os_db_fetch_array($start_query);
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td colspan="2" class="blog-table blog-table-left"><?php echo os_draw_textarea_field('description','','20','10',$start['description'], 'style="width:99%;"'); ?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWPOST_M_TITLE;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_input_field('m_title', $start['m_title'],'size="70"');?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWPOST_M_DESC;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_textarea_field('m_desc','','5','2',$start['m_desc'], 'style="width:99%;"'); ?></td>
	</tr>
	<tr>
		<td class="blog-table blog-table-left"><?php echo NBB_TABLE_HEADING_NEWPOST_M_KEYWORDS;?></td>
		<td class="blog-table blog-table-right"><?php echo os_draw_input_field('m_keywords', $start['m_keywords'],'size="70"');?></td>
	</tr>
	<tr>
		<td colspan="2" class="blog-table blog-table-left">
			<?php echo os_draw_hidden_field('id',$start['id']) . '<input type="submit" class="button" onClick="this.blur();" value="'.NBB_BUTTON_SAVE.'"/>'; ?>
			<a onClick="this.blur();" href="<?php echo $nbb_url; ?>"><?php echo NBB_BUTTON_BACK; ?></a>
		</td>
	</tr>
</table>
</form>
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
					<td><?php echo NBB_COMM_COMMENTS;?></td>
					<td><?php echo NBB_COMM_DATA;?></td>
					<td><?php echo NBB_COMM_AUTHOR;?></td>
					<td><?php echo NBB_COMM_TEXT;?></td>
					<td><?php echo NBB_COMM_STATUS;?></td>
					<td><?php echo NBB_COMM_ACTION;?></td>
				</tr>
		<?php
			$com_query = os_db_query("
				SELECT 
					c.id AS com_id,
					c.name AS user_name,
					c.text,
					c.date_added,
					c.status AS com_status,
					p.id,
					p.name 
				FROM 
					".DB_NEBOX_BLOG_COMMENTS." AS c,
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
					<td width="15%" class="blog-table-list blr"><?php echo $com['user_name']; ?></td>
					<td width="15%" class="blog-table-list blr"><?php echo $com['name']; ?></td>
					<td width="15%" class="blog-table-list blr">
					<?php
						if ($com['com_status'] == 1)
						{
							echo "<a class=\"blog-del\" href=\"".$nbb_url."&action=comments_list&status_off=".$com['com_id']."\">".NBB_COMM_ACTION_HIDE."</a>";
						}
						else
						{
							echo "<a class=\"blog-del\" href=\"".$nbb_url."&action=comments_list&status_on=".$com['com_id']."\">".NBB_COMM_ACTION_SHOW."</a>";
						}
					?>
					</td>
					<td width="10%" class="blog-table-list">
						<a class="blog-del" href="<?php echo $nbb_url.'&action=comments_list&delete_comment='.$com['com_id'];?>" onclick="return confirm('<?php echo NBB_COMM_ACTION_DEL_CONFIRM; ?>');"><?php echo NBB_COMM_ACTION_DEL; ?></a>
					</td>
				</tr>
		<?php } ?>
				<tr>
					<td colspan="6">
						<a class="blog-del-big" href="<?php echo $nbb_url.'&action=comments_list&delete_comment_all=true';?>" onclick="return confirm('<?php echo NBB_COMM_ACTION_DEL_ALL_CONFIRM; ?>');"><?php echo NBB_COMM_ACTION_DEL_ALL; ?></a>
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
				<option value="1" <?php if ($post['post_status'] == '1') {echo "selected";} ?>><?php echo NBB_TABLE_FOOTER_STATUS_2 ?></option>
				<option value="0" <?php if ($post['post_status'] == '0') {echo "selected";} ?>><?php echo NBB_TABLE_FOOTER_STATUS_1 ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="blog-table blog-table-left">
			<?php echo os_draw_hidden_field('post_id',$post['id']).'<input type="submit" class="button" onClick="this.blur();" value="'.NBB_BUTTON_SAVE.'"/>'; ?>
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
		<div class="mod-copy">
			<?php echo NBB_HEADING_TITLE.NBBV; ?><br />
			В случае всяких проблем - ICQ: 501760, E-mail: templatica.ru@gmail.com<br />
			Плагин реализовал <a href="http://www.shopos.ru/forum/index.php?action=profile;u=2176" target="_blank" title="Мой профиль на форуме">NeBox</a><br />
			Сайты: <a href="http://nebox.ru" target="_blank" title="Мой блог NeBox.ru">Мой блог</a> и <a href="http://templatica.ru" target="_blank" title="Моя студия Templatica.ru">Моя студия</a>
		</div>
		</div>
	</div>
</div>
<?php $main->bottom(); ?>