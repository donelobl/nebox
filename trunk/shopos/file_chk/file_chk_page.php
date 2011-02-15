<?php
//Copyright (c) 2007 Self-Commerce
	global $main;

	function do_filechk() {
		$sql = 'TRUNCATE TABLE '. TABLE_FILECHK . $_GET['type'];	
		os_db_query ($sql);
		recursive_filechk (DIR_FS_DOCUMENT_ROOT, '', $_GET['type']);
	}

	function recursive_filechk ($dir, $prefix = '', $extension) {
		$directory = @opendir($dir);

		while ($file = @readdir($directory)) {
			if (!in_array($file, array('.', '..'))) {

				$is_dir = (@is_dir($dir .'/'. $file)) ? true : false;

				$temp_path  = '';
				$temp_path  = $dir . '/' . (($is_dir) ? strtoupper($file) : $file);
				$temp_path  = str_replace('//', '/', $temp_path);
				$files = $file;

				$extension  = str_replace('.', '', $extension);

				if ( preg_match("/^.*?\." . $extension . "$/", $temp_path) && !preg_match('/cache\\//m', $temp_path) ) {
					$filehash = @filesize($temp_path) . '-' . count(@file($temp_path));
					$filehash = md5($filehash);
					
					$date_update = date('Y-m-d H:i:s');

					$sql = 'INSERT INTO '. TABLE_FILECHK . $_GET['type'] . " (`filepath`, `hash`, `date`) VALUES ('$temp_path', '$filehash', '$date_update')";
					if (!($result = os_db_query($sql))) {
						echo 'fehler';
					}
				}

				if ($is_dir){
					recursive_filechk($dir .'/'. $file, $dir .'/', $extension);
				}
			}
		}
		@closedir($directory);
	}

	// Считаем файлы в таблицах (Может можно как-то сократить?)
	$count_html_sql = os_db_query("SELECT id FROM os_filechk_html");
	$count_html = os_db_num_rows($count_html_sql);
	if (os_db_num_rows($count_html_sql) == 0) $count_html = TH_EMPTY;

	$count_php_sql = os_db_query("SELECT id FROM os_filechk_php");
	$count_php = os_db_num_rows($count_php_sql);
	if (os_db_num_rows($count_php_sql) == 0) $count_php = TH_EMPTY;

	$count_js_sql = os_db_query("SELECT id FROM os_filechk_js");
	$count_js = os_db_num_rows($count_js_sql);
	if (os_db_num_rows($count_js_sql) == 0) $count_js = TH_EMPTY;

	$count_css_sql = os_db_query("SELECT id FROM os_filechk_css");
	$count_css = os_db_num_rows($count_css_sql);
	if (os_db_num_rows($count_css_sql) == 0) $count_css = TH_EMPTY;

	$main->head();
	$main->top_menu();

?>
<style>
.wrapper-content {width:100%;background:#f4fdff;}
.wrap-con {padding:0 10px 10px 10px;}
.clear {clear:both;}

.make-selection {margin:10px;padding:10px;color:#000000;background:#ffffff;}
.chk-ok {margin:10px;padding:10px;color:#ffffff;background:#589b43;text-align:center;}
.wrap-con h1 {font-size:1.4em;}

a.file_chk_menu {padding:4px 8px 4px 8px;color:#456e90;background:#c5def2;}
a.file_chk_menu:hover {text-decoration:none;color:#ffffff;background:#456e90;}

.blog-table {padding:5px;font-size:0.9em;background:#ffffff;border-bottom:1px solid #b6cddb;}
.blog-table.blog-table-left {width:35%;}
.blog-table.blog-table-right {border-left:1px solid #dcebf5;}
.blog-table-note {font-size:0.8em;color:#9ea5b1;}

.blog-table-head td {padding:5px;font-size:0.9em;background:#c7e0f0;font-weight: bold;}
.blog-table-list {padding:5px;font-size:0.9em;background:#ffffff;border-bottom:1px solid #b6cddb;}
.blr {border-right:1px solid #b6cddb;}
</style>

<div class="wrapper-content">
	<div class="wrap-con">
		<h1><?php echo FILE_CHK_PAGE_HEADER; ?></h1>

		<table border="0" cellspacing="0" cellpadding="0" width="100%">
			<tr class="blog-table-head">
				<td align="center"><?php echo TH_HTML_FILE; ?> (<?php echo $count_html; ?>)</td>
				<td align="center"><?php echo TH_PHP_FILE; ?> (<?php echo $count_php; ?>)</td>
				<td align="center"><?php echo TH_JS_FILE; ?> (<?php echo $count_js; ?>)</td>
				<td align="center"><?php echo TH_CSS_FILE; ?> (<?php echo $count_css; ?>)</td>
			</tr>
			<tr>
				<td width="25%" align="center" class="blog-table-list blr col-1">
					<?php echo '
					<a class="file_chk_menu" href="'. os_href_link(FILENAME_PLUGINS_PAGE, 'page=file_chk_page&action=control&type=html', 'NONSSL').'">'.FILECHK_BUTTON_CONTROL.'</a> 
					<a class="file_chk_menu" href="'. os_href_link(FILENAME_PLUGINS_PAGE, 'page=file_chk_page&action=set_new&type=html', 'NONSSL').'">'.FILECHK_BUTTON_SET_NEW.'</a>
					'; ?>
				</td>
				<td width="25%" align="center" class="blog-table-list blr col-2">
					<?php echo '
					<a class="file_chk_menu" href="'. os_href_link(FILENAME_PLUGINS_PAGE, 'page=file_chk_page&action=control&type=php', 'NONSSL').'">'.FILECHK_BUTTON_CONTROL.'</a></li>
					<a class="file_chk_menu" href="'. os_href_link(FILENAME_PLUGINS_PAGE, 'page=file_chk_page&action=set_new&type=php', 'NONSSL').'">'.FILECHK_BUTTON_SET_NEW.'</a></li>
					'; ?>
				</td>
				<td width="25%" align="center" class="blog-table-list blr col-2">
					<?php echo '
					<a class="file_chk_menu" href="'. os_href_link(FILENAME_PLUGINS_PAGE, 'page=file_chk_page&action=control&type=js', 'NONSSL').'">'.FILECHK_BUTTON_CONTROL.'</a></li>
					<a class="file_chk_menu" href="'. os_href_link(FILENAME_PLUGINS_PAGE, 'page=file_chk_page&action=set_new&type=js', 'NONSSL').'">'.FILECHK_BUTTON_SET_NEW.'</a></li>
					'; ?>
				</td>
				<td width="25%" align="center" class="blog-table-list col-2">
					<?php echo '
					<a class="file_chk_menu" href="'. os_href_link(FILENAME_PLUGINS_PAGE, 'page=file_chk_page&action=control&type=css', 'NONSSL').'">'.FILECHK_BUTTON_CONTROL.'</a></li>
					<a class="file_chk_menu" href="'. os_href_link(FILENAME_PLUGINS_PAGE, 'page=file_chk_page&action=set_new&type=css', 'NONSSL').'">'.FILECHK_BUTTON_SET_NEW.'</a></li>
					'; ?>
				</td>
			</tr>
		</table>


<?php
	echo '<br />';

	if ($_GET['action']) {

		switch ($_GET['action']) {
			case 'set_new':
				do_filechk();
				echo '<div class="chk-ok">'.FILE_CHK_SET_NEW_OK.'</div>';
			break;
			
			//case 'set_change':

				
			//break;

			case 'control':
?>

<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr class="blog-table-head">
		<td align="center">ID</td>
		<td align="center"><?php echo TH_STATUS; ?></td>
		<td align="center"><?php echo TH_DATA; ?></td>
		<td><?php echo TH_FILE; ?></td>
	</tr>

<?php
				$sql = 'SELECT id, filepath, hash, date FROM '. TABLE_FILECHK . $_GET['type'];	 
				$check_query = osDBquery($sql);

				while ($row = os_db_fetch_array($check_query, true)) {
					$current_hash 	= '';

					$current_hash = @filesize($row['filepath']) . '-' . count(@file($row['filepath']));

					if ( $current_hash == '-1' ) {
						$filestatus = STATUS_DELETE ;
						$color = '#0300FF';
					} elseif ( md5($current_hash) != $row['hash']) {
						$filestatus = STATUS_CHANGE ;
						$color = '#FF1200';
					} else {
						$filestatus = STATUS_OK ;
						$color = '#269F00';
					}

					$path_cleaned = str_replace(DIR_FS_DOCUMENT_ROOT, '', $row['filepath']);

					echo '

	<tr>
		<td class="blog-table-list blr col-1" align="center">'.$row['id'].'</td>
		<td class="blog-table-list blr col-1" align="center"><font color="'.$color.'">'.$filestatus.'</font></td>
		<td class="blog-table-list blr col-1" align="center">'.$row['date'].'</td>
		<td class="blog-table-list col-1">'.$path_cleaned.'</td>
					';
				}
				break;
?>
	</tr>
</table>

<?php                  
		}
	} else {
		echo '<div class="make-selection">'.MAKE_SELECTION.'</div>';
	}
?>

	</div>
</div>

<?php $main->bottom(); ?>