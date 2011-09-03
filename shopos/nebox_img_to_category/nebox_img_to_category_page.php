<?php
function randGen($iLength = 10)
{
	if ($iLength > 20)
	{
		$iLength = 20;
	}
	return substr(md5(uniqid(mt_rand(), true)), 0, $iLength);
}

global $main, $db, $messageStack;

$p_url = os_href_link(FILENAME_PLUGINS_PAGE, 'page=nebox_img_to_category_page');
$p_http = _HTTP;

$dir_img = dir_path('catalog').'media/nitc_img/';

if(!is_dir($dir_img))
{
	@mkdir($dir_img, 0777);

	if(is_dir($dir_img))
		$messageStack->add_session('Директория <b>media/nitc_img</b> успешно создана!', 'success');
	else
		$messageStack->add_session('Проблема при создании директории <b>media/nitc_img</b>! Создайте ее вручную', 'error');
}

if ($_GET['action'] == 'add')
{
	$link = mysql_real_escape_string($_POST['link']);
	$title = mysql_real_escape_string($_POST['title']);
	$text = $_POST['text'];
	$category = (int)$_POST['category'];

	$img = $_FILES['img']['tmp_name'];
	$filename = $_FILES['img']['name'];
	$img_name = randGen('555');

	if (!empty($img))
	{
		$extentions = array( "gif","jpg","jpeg","png");
		$size = filesize($_FILES['img']['tmp_name']);
		$type = strtolower(substr($filename, 1+strrpos($filename,".")));
		$new_name = $img_name.'.'.$type;

		if($size > 1024*3*1024)
		{
			$messageStack->add_session('Размер файла превышает три мегабайта. Уменьшите размер вашего файла или загрузите другой!', 'error');
		}
		elseif(!in_array($type,$extentions))
		{
			$messageStack->add_session('Файл имеет недопустимое расширение. Допустимыми являются форматы изображений *.gif, *.jpg, *.jpeg, *.png.!', 'error');
		}
		else
		{
			if (move_uploaded_file($img, $dir_img.$new_name))
			{
				chmod($dir_img.$new_name,0777);
				$messageStack->add_session('Картинка успешно загружена!', 'success');
			}
			else
				$messageStack->add_session('Ошибка загрузки картинки!', 'error');
		}
	}

	$db->query("insert ".TABLE_NBITC." SET img = '$new_name', link = '$link', title = '$title', text = '$text', category = '$category'");
	os_redirect($p_url);
}

if ($_GET['delete'])
{
	$query = $db->query("SELECT * FROM ".TABLE_NBITC." where id = '".(int)$_GET['delete']."'");
	$row = mysql_fetch_assoc($query);
	
	@unlink($dir_img.$row['img']);

	$db->query("delete FROM ".TABLE_NBITC." where id ='".(int)$_GET['delete']."'");
	os_redirect($p_url);
}

$main->head();
$main->top_menu();
?>
<style>
.wrapper-content {width:100%;background:#f4fdff;margin:0;padding:0;}
.wrap-con {padding:0 10px 10px 10px;}
.clear {clear:both;}

.wrap-con h1 {font-size:1.4em;margin:0;padding:5px 0 0 5px;}
.wrap-con h2 {font-size:1.2em;}

.blog-table {padding:5px;font-size:0.9em;}
.blog-table .blog-table-left {background:#ffffff;width:200px;padding:5px;}
.blog-table .blog-table-right {background:#ffffff;padding:5px;}

.blog-table .table1 {background:#ffffff;width:50%;padding:5px;}
.blog-table .table2 {background:#ffffff;width:30%;padding:5px;}
.blog-table .table3 {background:#ffffff;width:15%;padding:5px;}
.blog-table .table4 {background:#ffffff;width:5%;padding:5px;}

.blog-table .tableh1 {background:#bbe6ef;width:50%;padding:5px;}
.blog-table .tableh2 {background:#bbe6ef;width:30%;padding:5px;}
.blog-table .tableh3 {background:#bbe6ef;width:15%;padding:5px;}
.blog-table .tableh4 {background:#bbe6ef;width:5%;padding:5px;}

.button-submit {padding:5px 10px 5px 10px;font-size:0.8em;cursor:pointer;margin:5px 0 5px 0;background:#ffffff;border:1px solid #b6cddb;}
.button-submit:hover {background:#b6cddb;}

a.add-new-prod-to-cat {display:block;padding:5px;text-align:center;margin:0 0 0 0;background:#ffffff;}

.no_cb {text-align:center;padding:20px;}
</style>
<div class="wrapper-content">
	<div class="wrap-con">
		<h1>Картинки для категорий</h1>
		<form method="post" action="<?php echo $p_url, '&action=add'; ?>" enctype="multipart/form-data">
		<table class="blog-table" width="100%" border="0" cellpadding="0" cellspacing="2">
			<tr>
				<td class="blog-table-left">Текст (Код)<br /><font color="red">Если заполняете это поле, то не нужно загружать картинку или указывать Название!</font></td>
				<td class="blog-table-right">
					<textarea class="round" name="text" id="text" style="height:150px;width:95%;"></textarea>
				</td>
			</tr>
			<tr>
				<td class="blog-table-left">Название</td>
				<td class="blog-table-right">
					<input type="text" name="title" id="title" value="" style="width:95%;" />
					<br />Название будет отображаться в виде заголовка. Не более 255 символов
				</td>
			</tr>
			<tr>
				<td class="blog-table-left"><b>Картинка</b></td>
				<td class="blog-table-right"><input type="file" name="img" id="img" size="40" /><br />Картинка по размеру уже должна быть подогнана как нужно. Плагин не режет ее, а просто загружает</td>
			</tr>
			<tr>
				<td class="blog-table-left">Ссылка</td>
				<td class="blog-table-right">
					<input type="text" name="link" id="link" value="" style="width:95%;" />
					<br />C HTTP:// если на другой сайт. Если на свой, то <font color="red">/shop_content.php?coID=20</font>. Не более 255 символов
				</td>
			</tr>
			<tr>
				<td class="blog-table-left">Категория<br /><small><font color="red"><b>Обязательно!</b></font></small></td>
				<td class="blog-table-right">
					<select name="category" size="15">
					<?php
						$nbttc_cat_query =  $db->query("
							SELECT 
								c.categories_id, c.categories_image, cd.categories_name, cd.categories_description 
							FROM 
								".TABLE_CATEGORIES." AS c, ".TABLE_CATEGORIES_DESCRIPTION." AS cd 
							WHERE 
								c.categories_id = cd.categories_id AND 
								c.categories_status = '1' AND 
								cd.language_id = '" .(int) $_SESSION['languages_id']. "' 
							ORDER BY 
								c.sort_order ASC
						");
						while ($nbttc_cat = os_db_fetch_array($nbttc_cat_query)) {
					?>
						<option value="<?php echo $nbttc_cat['categories_id'];?>"><?php echo $nbttc_cat['categories_name'];?></option>
					<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="blog-table-left"></td>
				<td class="blog-table-right"><input class="button-submit" type="submit" name="add" value="Сохранить" /></td>
			</tr>
		</table>
		</form>
	</div>
		<table class="blog-table" width="100%" border="0" cellpadding="0" cellspacing="2">
			<tr>
				<td class="tableh1">Название и Текст</td>
				<td class="tableh2">Картинка</td>
				<td class="tableh2">Ссылка</td>
				<td class="tableh2">Категория</td>
				<td class="tableh4">Действие</td>
			</tr>
<?php
	$nbttc_query =  $db->query("SELECT * FROM ".TABLE_NBITC." ORDER BY id DESC");
	
	if (os_db_num_rows($nbttc_query) != 0) {
	while ($nbttc = os_db_fetch_array($nbttc_query))
	{
		if (!empty($nbttc['img']))
			$imgs = '<img src="'.$p_http.'media/nitc_img/'.$nbttc['img'].'" alt="'.$nbttc['title'].'" />';
		else
			$imgs = 'картинки нет';

		$nbttc_cats_query =  $db->query("SELECT categories_id, categories_name FROM ".TABLE_CATEGORIES_DESCRIPTION." WHERE categories_id = ".(int)$nbttc['category']."");
		$nbttc_cats = os_db_fetch_array($nbttc_cats_query);
?>
			<tr>
				<td class="table1"><b><?php echo $nbttc['title'];?></b><br /><small><?php echo $nbttc['text'];?></small></td>
				<td class="table2"><?php echo $imgs; ?></td>
				<td class="table2"><?php echo $nbttc['link'];?></td>
				<td class="table2"><?php echo $nbttc_cats['categories_name'];?></td>
				<td class="table4"><a href="<?php echo $p_url,'&delete='.$nbttc['id']; ?>" onclick="return confirm('Подтверждаете удаление?');">Удалить</a></td>
			</tr>
<?php }
} else {
	echo "<tr class=\"cb-tableh1\"><td colspan=\"5\"><div class=\"no_cb\">Пока ничего нет!</div></td></tr>";
}
?>
		</table>
</div>
<?php $main->bottom(); ?>