<?php

global $main;
$languages = os_get_languages();
	
// Статусы
$poll_status = array();
$poll_status[] = array('id' => 0, 'text' => '---');
$poll_status[] = array('id' => 1, 'text' => POLL_OFFLINE);
$poll_status[] = array('id' => 2, 'text' => POLL_ONLINE);
$poll_status[] = array('id' => '&nbsp;', 'text' => '-----------------');
$poll_status[] = array('id' => 3, 'text' => POLL_DELETE);
$poll_status[] = array('id' => '&nbsp;', 'text' => '-----------------');
$poll_status[] = array('id' => 4, 'text' => POLL_EDIT);
  
$customers_groups_query = os_db_query("SELECT customers_status_id, customers_status_name FROM ".TABLE_CUSTOMERS_STATUS." WHERE language_id = '".$_SESSION['languages_id']."' ORDER BY customers_status_id ASC");  

switch ($_GET['action']) {

	// Статус
	case 'status':

		if (is_numeric($_POST['newstatus']) && $_POST['newstatus'] == '1' || $_POST['newstatus'] == '2') {

			if($_POST['newstatus'] == '1') $_POST['newstatus'] = 0;
			if($_POST['newstatus'] == '2') $_POST['newstatus'] = 1;

			os_db_query("UPDATE ".TABLE_POLL." SET status = '".(int)$_POST['newstatus']."' WHERE id = '".(int)$_POST['poll_id']."'");

		// Статус - Удаление опроса
		} elseif (is_numeric($_POST['newstatus']) && $_POST['newstatus'] == '3') {

			os_db_query("DELETE FROM ".TABLE_POLL." WHERE id = '".(int)$_POST['poll_id']."'");
			os_db_query("DELETE FROM ".TABLE_POLL_DESCRIPTION." WHERE id = '".(int)$_POST['poll_id']."'");
			os_db_query("DELETE FROM ".TABLE_POLL_ITEMS." WHERE poll_id = '".(int)$_POST['poll_id']."'");
			os_db_query("DELETE FROM ".TABLE_POLL_ITEMS_DESCRIPTION." WHERE poll_id = '".(int)$_POST['poll_id']."'");

		// Статус - Редактирование
		} elseif (is_numeric($_POST['newstatus']) && $_POST['newstatus'] == '4') {

			// Редиректим на страницу редактирования
			@os_redirect(os_href_link(FILENAME_PLUGINS_PAGE,'page=poll_manager_page&poll='.(int)$_POST['poll_id'].'&action=new_poll'));

		}
		// Редиректим на главную
		@os_redirect(os_href_link(FILENAME_PLUGINS_PAGE, 'page=poll_manager_page'));
		
		break;

	//
	case 'update':

		$lang = sizeof($languages);
		$ids = count($_POST['item_id']);
		$delete_ids = count($_POST['delete']);
		
		// Удаляем\Обновляем
		if (isset($_POST['delete'])) {

			for ($a = 0; $a < $delete_ids; $a++) {

				$delete_array = $_POST['delete'][$a];

				os_db_query("DELETE FROM ".TABLE_POLL_ITEMS." WHERE item_id = '".$delete_array."'");
				os_db_query("DELETE FROM ".TABLE_POLL_ITEMS_DESCRIPTION." WHERE item_id = '".$delete_array."'");

			}

		// Обновляем
		} elseif (empty($_POST['delete'])) {

			for ($b = 0; $b < $ids; $b++) {

				$item_id  = $_POST['item_id'][$b];
				$color    = $_POST['color'.$item_id];
				$position = $_POST['pos'.$item_id];

				for ($i = 0; $i < $lang; $i++) {

					$languages_id = $languages[$i]['id'];
					$title = os_db_prepare_input($_POST['title'][$languages_id][$item_id]);

					os_db_query("UPDATE ".TABLE_POLL_ITEMS_DESCRIPTION." SET title = '".$title."' WHERE item_id = '".$item_id."' AND language_id = '".$languages_id."'");

				}
				os_db_query("UPDATE ".TABLE_POLL_ITEMS." SET color = '".$color."', position = '".$position."' WHERE item_id = '".$item_id."'");
			}
			@os_redirect(os_href_link(FILENAME_PLUGINS_PAGE, 'page=poll_manager_page&poll='.(int)$_GET['poll'].'&action=items'));
		}
		
		break;

	// Добавляем опрос
	case 'new_item':

		$lang = sizeof($languages);

		$count_id = os_db_query("SELECT MAX(item_id) AS id FROM ".TABLE_POLL_ITEMS_DESCRIPTION);
		$id = os_db_fetch_array($count_id);

		$next_id = $id['id'];
		$poll_id  = (int)$_GET['poll'];

		// aller erste antwort?
		if ($next_id != 0 ? $next_id +=1 : $next_id = 1);

		// DESCRIPTION
		for ($i = 0; $i < $lang; $i++) {

			$languages_id = $languages[$i]['id'];

			if ($_POST['title'][$languages_id] != '') {
				$items_array = array (
					'item_id' => $next_id,
					'language_id' => $languages_id,
					'poll_id' => $poll_id,
					'title' => os_db_prepare_input($_POST['title'][$languages_id])
				);
				os_db_perform(TABLE_POLL_ITEMS_DESCRIPTION, $items_array);
			}

		}

		$position = (int)$_POST['position'];
		$color    = $_POST['color100'];
        
		$item_array = array (
			'item_id' => $next_id,
			'poll_id' => $poll_id,
			'color' => $color,
			'position' => $position
		);
		os_db_perform(TABLE_POLL_ITEMS, $item_array);
		@os_redirect(os_href_link(FILENAME_PLUGINS_PAGE,'page=poll_manager_page&poll='.(int)$_GET['poll'].'&action=items'));
		break;

    // 
    case 'insert_new_poll':

		$lang = sizeof($languages);

		$start_time = $_POST['start_year'].'-'.$_POST['start_month'].'-'.$_POST['start_day'].' '.$_POST['start_hour'].':'.$_POST['start_minute'].':'.$_POST['start_second'];
		$end_time   = $_POST['end_year'].'-'.$_POST['end_month'].'-'.$_POST['end_day'].' '.$_POST['end_hour'].':'.$_POST['end_minute'].':'.$_POST['end_second'];

		// UPDATE
		if (isset($_POST['poll_id']) && $_POST['poll_id'] != '') {

			// DESCRIPTION
			for ($i = 0; $i < $lang; $i++) {

				$languages_id = $languages[$i]['id'];
				$title = os_db_prepare_input($_POST['title'][$languages_id]);

				os_db_query("UPDATE ".TABLE_POLL_DESCRIPTION." SET title = '".$title."' WHERE id = '".(int)$_POST['poll_id']."' AND language_id = '".$languages_id."'");

			}

			// Gruppen
			$groups_count = count($_POST['customers_groups']);
			$groups_list = '';
          
			for ($g = 0; $g < $groups_count; $g++) {

				$groups_list .= $_POST['customers_groups'][$g].($g+1 != $groups_count ? ',' : '');

			}

			os_db_query("UPDATE ".TABLE_POLL." SET start = '".$start_time."', end = '".$end_time."', customers_groups = '".$groups_list."' WHERE id = '".(int)$_POST['poll_id']."'");

		//NEW  INSERT
		} else {

			// identifikation der antwort bestimmen und um 1 erhцhen
			$count_id = os_db_query("SELECT MAX(id) AS id FROM ".TABLE_POLL);
			$id = os_db_fetch_array($count_id);

			$next_id = $id['id'];

			// aller erste antwort?
			if ($next_id != 0 ? $next_id +=1 : $next_id = 1);

			// DESCRIPTION
			for ($i = 0; $i < $lang; $i++) {

				$languages_id = $languages[$i]['id'];

				if ($_POST['title'][$languages_id] != '') {

					$poll_array = array (
						'id' => $next_id,
						'language_id' => $languages_id,
						'title' => os_db_prepare_input($_POST['title'][$languages_id])
					);
					os_db_perform(TABLE_POLL_DESCRIPTION, $poll_array);
				}

			}

			// Gruppen
			$groups_count = count($_POST['customers_groups']);
			$groups_list = '';

			for ($g = 0; $g < $groups_count; $g++) {

				$groups_list .= $_POST['customers_groups'][$g].($g+1 != $groups_count ? ',' : '');

			}

			// DATA
			$poll_array = array (
				'id' => $next_id,
				'start' => $start_time,
				'end' => $end_time,
				'customers_groups' => $groups_list
			);
			os_db_perform(TABLE_POLL, $poll_array);
			@os_redirect(os_href_link(FILENAME_PLUGINS_PAGE,'page=poll_manager_page&poll='.(int)$_GET['poll'].'&action=items'));
		}

		@os_redirect(os_href_link(FILENAME_PLUGINS_PAGE, 'page=poll_manager_page'));
		break;

	default: // alle anzeigen

		$poll_query = os_db_query("SELECT p.id, p.status, p.start, p.end, p.customers_groups, pd.title FROM ".TABLE_POLL." p, ".TABLE_POLL_DESCRIPTION." pd WHERE p.id = pd.id AND pd.language_id = '".$_SESSION['languages_id']."' ORDER BY p.id DESC");
		$count_poll = os_db_num_rows($poll_query);

}

	$main->head();
	$main->top_menu();
?>



<div class="wrapper-content br6">
	<div class="wrap-con">
		<h1><?php echo POLL_HEADING_TITLE; ?></h1>
		<ul class="right-top-menu">
			<li><a class="br3" href="<?php echo os_href_link(FILENAME_PLUGINS_PAGE).'?page=poll_manager_page">'. TABLE_HEADING_SETTINGS_NAVI_OVERVIEW;?></a></li>
			<li><a class="br3" href="<?php echo os_href_link(FILENAME_PLUGINS_PAGE,'page=poll_manager_page&action=new_poll').'">'. TABLE_HEADING_SETTINGS_NEW;?></a></li>
		</ul>
		<div class="clear"></div>









<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td width="100%" valign="top">

<?php
	// Пункты ответа?
	if ($_GET['action'] == 'items') {
		// LOAD QUESTION
		$poll_question_query = os_db_query("SELECT title FROM ".TABLE_POLL_DESCRIPTION." WHERE id = '".(int)$_GET['poll']."' AND language_id = '".(int)$_SESSION['languages_id']."'");
		$poll_question = os_db_fetch_array($poll_question_query);	
    
		// Editieren
		$items_id_array = array();
		$count_languages = sizeof($languages);
?>
<h3 class="poll-name"><?php echo $poll_question['title'];?></h3>
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr class="blog-table-head">
					<td width="5%">Язык</td>
					<td width="53%"><?php echo TABLE_HEADING_EDITING;?></td>
					<td width="10%"><?php echo POLL_HITS; ?></td>
					<td width="12%"><?php echo ITEM_COLOR; ?></td>
					<td width="5%"><?php echo ITEM_POSITION; ?></td>
					<td align="center" width="5%"><?php echo TEXT_STATUS_DELETE;?></td>
				</tr>
<?php
		echo os_draw_form('items', FILENAME_PLUGINS_PAGE, 'page=poll_manager_page&poll='.(int)$_GET['poll'].'&action=update','post','');
?>

<?php

	$poll_items_query = os_db_query("SELECT i.item_id, i.color, i.position, id.title FROM ".TABLE_POLL_ITEMS." i, ".TABLE_POLL_ITEMS_DESCRIPTION." id WHERE i.poll_id = '".(int)$_GET['poll']."' AND i.item_id = id.item_id AND id.language_id = '".(int)$_SESSION['languages_id']."' ORDER BY i.position ASC");	

	$count_items_query = os_db_query("SELECT item_id FROM ".TABLE_POLL_ITEMS." WHERE poll_id = '".(int)$_GET['poll']."' ORDER BY position ASC");

	while ($count_items = os_db_fetch_array($count_items_query)) {
		$items_id_array[] = array('id' => $count_items['item_id']);
	}
	$count_array = count($items_id_array);

	for ($a = 0; $a < $count_array; $a++) {

		for($i = 0; $i < $count_languages; $i++) {

			$show_items_query = os_db_query("SELECT i.item_id, i.color, i.position, i.hits, id.title, id.language_id FROM ".TABLE_POLL_ITEMS." i, ".TABLE_POLL_ITEMS_DESCRIPTION." id WHERE i.poll_id = '".(int)$_GET['poll']."' AND i.item_id = id.item_id AND id.language_id = '".(int)$languages[$i]['id']."' AND id.item_id = '".$items_id_array[$a]['id']."'");
			$change_items = os_db_fetch_array($show_items_query);
			$color = $color == 'col-2' ? 'col-1':'col-2';
?>
				<tr  class="blog-table-list <?php echo $color; ?>">
					<td class="blr" width="5%">
<?php
	echo os_draw_hidden_field('item_id[]',$change_items['item_id']);
	echo $languages[$i]['code'].':';
?>
					</td>
					<td class="blr" width="53%"><?php echo os_draw_input_field('title['.$languages[$i]['id'].']['.$items_id_array[$a]['id'].']', $change_items['title'],'class="poll_input" size="80"');?></td>
<?php
	if($i+1 == $count_languages){
?>

					<td class="blr" width="10%"><?php echo os_draw_input_field('hits'.$items_id_array[$a]['id'], $change_items['hits'], 'style="text-align:center;" class="poll_input" size="3"');?></td>
					<td class="blr" width="12%"><?php echo os_draw_input_field('color'.$items_id_array[$a]['id'], $change_items['color'], 'id="mycolor'.$items_id_array[$a]['id'].'" class="poll_input iColorPicker" size="8"');?></td>
					<td class="blr" width="5%"><?php echo os_draw_input_field('pos'.$items_id_array[$a]['id'], $change_items['position'], 'class="poll_input" size="2"');?></td>
					<td align="center" width="5%"><?php echo os_draw_selection_field('delete[]', 'checkbox', $items_id_array[$a]['id']);?></td>
<?php } ?>
				</tr>
<?php
	if($change_items['language_id'] == $count_languages){
?>
				<tr>
					<td class="header_empty" colspan="6"></td>
				</tr>	
<?php }}} ?>
				<tr>
					<td colspan="6"><?php echo '<input type="submit" class="button-submit br3" value="'.TEXT_UPDATE.'">';?></td>
				</tr>
			</table>
		</form>

		<h3 style="margin:15px 0 0 0;" class="poll-name"><?php echo TABLE_HEADING_EDITING_NEW;?></h3>
<?php
	echo os_draw_form('item', FILENAME_PLUGINS_PAGE, 'page=poll_manager_page&poll='.(int)$_GET['poll'].'&action=new_item','post','');
?>
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
<?php
	for ($i = 0; $i < $count_languages; $i ++) {
?>	
			<tr class="blog-table-list col-2">
				<td><?php echo $languages[$i]['code'].':';?></td>
				<td><?php echo os_draw_input_field('title['.$languages[$i]['id'].']','','class="poll_input" size="70"');?></td>
			</tr>
<?php } ?>
			<tr bgcolor="#eeeeee">
				<td colspan="2">
					<table>
						<tr>
							<td><?php echo ITEM_POSITION; ?></td>
							<td><?php echo os_draw_input_field('position', '', 'class="poll_input" size="2"');?></td>
							<td><?php echo ITEM_COLOR; ?></td>
							<td><?php echo os_draw_input_field('color100', '', 'id="mycolor100" class="poll_input iColorPicker" size="8"');?></td>		
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="header_empty" colspan="3"></td>
			</tr>
			<tr>
				<td colspan="2"><?php echo '<input type="submit" class="button-submit br3" value="'.TEXT_SAVE.'">';?></td>
			</tr>
		</table>
		</form>	
		
<?php
	} elseif ($_GET['action'] == 'new_poll') {
		$count_languages = sizeof($languages);
?>		
		<!-- NEW -->
		<fieldset>
			<legend class="poll_heading_fieldset"><b><?php echo TABLE_HEADING_SETTINGS_NEW;?></b></legend>
<?php
	echo os_draw_form('item', FILENAME_PLUGINS_PAGE, 'page=poll_manager_page&poll='.(int)$_GET['poll'].'&action=insert_new_poll','post','');
?>

			<table border="0" cellspacing="1" cellpadding="2" width="100%" align="center">
<?php
	for ($i = 0; $i < $count_languages; $i ++) {
  
		if (is_numeric($_GET['poll'])) {
			$update_query = os_db_query("SELECT p.id, p.start, p.end, p.customers_groups, pd.title FROM ".TABLE_POLL." p, ".TABLE_POLL_DESCRIPTION." pd WHERE p.id = '".(int)$_GET['poll']."' AND p.id = pd.id AND pd.language_id = '".(int)$languages[$i]['id']."'");
			$update = os_db_fetch_array($update_query);
		}
?>	
				<tr bgcolor="#e2e2e2">
					<td class="poll_heading" align="center" width="40"><?php echo $languages[$i]['code'].':';?></td>
					<td><?php	echo os_draw_input_field('title['.$languages[$i]['id'].']',$update['title'],'class="poll_input" size="70"');?></td>
				</tr>
<?php
	}
?>	
				<tr bgcolor="#eeeeee">
					<td colspan="2"><br>
						<table>    
							<tr>
								<td><?php echo POLL_CUSTOMERS_GROUPS;?></td>
							</tr>
							<tr>
								<td>
									<select name="customers_groups[]" size="5" multiple="multiple" class="poll_input">
						<?php
							$g = 0;
							$teilen = explode(',', $update['customers_groups']);
						  
							while ($groups = os_db_fetch_array($customers_groups_query)) {
								$selected = '';
								if (in_array($groups['customers_status_id'], $teilen)) $selected = 'selected';
								echo '<option value="'.$groups['customers_status_id'].'" '.$selected.'>'.$groups['customers_status_name'].'</option>';
								$g++;
							}
						?>
									</select>
								</td>
							</tr>  
						</table> 
						<br />

						<table border="0">
							<tr>
								<td><?php echo POLL_DATE_START;?></td>
								<td>
<?php 
	$days = array();
	$months = array();
	$years = array();
  
	$hours = array();
	$minutes = array();
	$seconds = array();
    
	$days[] = array('id' => 0, 'text' => '00');
	for ($d = 1; $d < 32; $d++) {
		($d < 10 ? $d = '0'.$d : '');  
		$days[] = array('id' => $d, 'text' => $d);
	}

	$months[] = array('id' => 0, 'text' => '00');
	for ($d = 1; $d < 13; $d++) {
		($d < 10 ? $d = '0'.$d : '');  
		$months[] = array('id' => $d, 'text' => $d);
	}

	$years[] = array('id' => 0, 'text' => '0000');
	for ($d = 2006; $d < 2011; $d++) {
		$years[] = array('id' => $d, 'text' => $d);
	} 
  
	$hours[] = array('id' => 0, 'text' => '00:');
	for ($d = 1; $d < 25; $d++) {
		($d < 10 ? $d = '0'.$d : '');
		$hours[] = array('id' => $d, 'text' => $d);
	}

	$minutes[] = array('id' => 0, 'text' => '00:');
	for ($d = 1; $d < 60; $d++) {
		($d < 10 ? $d = '0'.$d : '');
		$minutes[] = array('id' => $d, 'text' => $d);
	}

	$seconds[] = array('id' => 0, 'text' => '00');
	for ($d = 1; $d < 60; $d++) {
		($d < 10 ? $d = '0'.$d : '');  
		$seconds[] = array('id' => $d, 'text' => $d);
	} 

	$date_day         = date("d");
	$date_month       = date("m");
	$date_year        = date("Y");
	$date_hour        = '';
	$date_minute      = '';
	$date_second      = '';
	$date_day_end     = '';
	$date_month_end   = '';
	$date_year_end    = '';
	$date_hour_end    = '';
	$date_minute_end  = '';
	$date_second_end  = '';
  
	if (is_numeric($_GET['poll'])) {
		$date_day         = substr($update['start'],8,2);
		$date_month       = substr($update['start'],5,2);
		$date_year        = substr($update['start'],0,4);
		$date_hour        = substr($update['start'],-8);
		$date_minute      = substr($update['start'],-5);
		$date_second      = substr($update['start'],-2);
		$date_day_end     = substr($update['end'],8,2);
		$date_month_end   = substr($update['end'],5,2);
		$date_year_end    = substr($update['end'],0,4);
		$date_hour_end    = substr($update['end'],-8);
		$date_minute_end  = substr($update['end'],-5);
		$date_second_end  = substr($update['end'],-2);
	}
  
	echo os_draw_hidden_field('poll_id',(int)$_GET['poll']);
	echo os_draw_pull_down_menu('start_day', $days, $date_day, 'class="poll_select"');
	echo os_draw_pull_down_menu('start_month', $months, $date_month, 'class="poll_select"');
	echo os_draw_pull_down_menu('start_year', $years, $date_year, 'class="poll_select"');
	echo '&nbsp;-&nbsp;';
	echo os_draw_pull_down_menu('start_hour', $hours, $date_hour, 'class="poll_select"');
	echo os_draw_pull_down_menu('start_minute', $minutes, $date_minute, 'class="poll_select"');
	echo os_draw_pull_down_menu('start_second', $seconds, $date_second, 'class="poll_select"');

?>
								</td>
							</tr>
							<tr>
								<td><?php echo POLL_DATE_END;?></td>
								<td>
<?php
	echo os_draw_pull_down_menu('end_day', $days, $date_day_end, 'class="poll_select"');
	echo os_draw_pull_down_menu('end_month', $months, $date_month_end, 'class="poll_select"');
	echo os_draw_pull_down_menu('end_year', $years, $date_year_end, 'class="poll_select"');
	echo '&nbsp;-&nbsp;';
	echo os_draw_pull_down_menu('end_hour', $hours, $date_hour_end, 'class="poll_select"');
	echo os_draw_pull_down_menu('end_minute', $minutes, $date_minute_end, 'class="poll_select"');
	echo os_draw_pull_down_menu('end_second', $seconds, $date_second_end, 'class="poll_select"');
?>    
								</td>		
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="header_empty" colspan="3"></td>
				</tr>
				<tr>
					<td colspan="2"><?php echo '<input type="submit" style="border:1px solid #999999;" value="'.TEXT_SAVE.'">';?></td>
				</tr>
			</table>
			</form>
		</fieldset>

<?php } else { ?>

		<table width="100%" border="0" cellspacing="1" cellpadding="3" align="center" style="border-bottom:1px solid #cccccc;">
			<tr>
				<td class="header_poll" width="5%" align="center"><?php echo POLL_ID;?></td>
				<td class="header_poll" width="45%"><?php echo POLL_NAME; ?></td>
				<td class="header_poll" width="5%" align="center"><?php echo POLL_HITS; ?></td>
				<td class="header_poll" width="15%" align="center"><?php echo POLL_DATE_START; ?></td>
				<td class="header_poll" width="15%" align="center"><?php echo POLL_DATE_END; ?></td>
				<td class="header_poll" width="15%" align="center"><?php echo POLL_STATUS; ?></td>
			</tr>	
<?php
	if ($count_poll > 0) {
		$i = 0;
		while ($poll = os_db_fetch_array($poll_query)) {
			$poll_hits_query = os_db_query("SELECT SUM(hits) AS item_hits FROM ".TABLE_POLL_ITEMS." WHERE poll_id = '".$poll['id']."'");
			$poll_hits = os_db_fetch_array($poll_hits_query);

			$item_query = os_db_query("SELECT item_id FROM ".TABLE_POLL_ITEMS." WHERE poll_id = '".$poll['id']."'");
			$item = os_db_fetch_array($item_query);      
?>
			<tr bgcolor="<?php echo ($i%2?'#eeeeee':'#f1f1f1');?>">
				<td class="content_poll" align="center"><?php echo $poll['id'];?></td>
				<td class="content_poll">
<?php 
	echo '<a href="' . os_href_link(FILENAME_PLUGINS_PAGE, os_get_all_get_params(array('oID', 'action')) . 'poll=' . $poll['id'] . '&action=items') . '">'.$poll['title'].'</a><br>- ';

	$g = 0;
	$teilen = explode(',', $poll['customers_groups']);   
	$count_g = count($teilen);
  
	for ($g = 0; $g < $count_g; $g++) {
		$customers_group_query = os_db_query("SELECT customers_status_name FROM ".TABLE_CUSTOMERS_STATUS." WHERE customers_status_id = '".$teilen[$g]."' AND language_id = '".$_SESSION['languages_id']."' ORDER BY customers_status_id ASC"); 
		$groups = os_db_fetch_array($customers_group_query);
		echo '<font color="#cc0000">'.$groups['customers_status_name'].',</font>';
	}

?>
				</td>
				<td class="content_poll" align="center"><?php echo $poll_hits['item_hits'];?></td>
				<td class="content_poll" align="center"><?php echo os_datetime_short($poll['start']);?></td>
				<td class="content_poll" align="center"><?php echo os_datetime_short($poll['end']);?></td>
				<td class="content_poll" align="center">
<?php 
	echo os_draw_form('poll_status', FILENAME_PLUGINS_PAGE, 'page=poll_manager_page&action=status', 'post','');
	echo os_draw_hidden_field('poll_id',$poll['id']);
	echo os_draw_hidden_field('item_id',$item['item_id']);
  
	if ($poll['status'] == 0) {
		$poll['status'] = 1;
	} elseif($poll['status'] == 1) {
		$poll['status'] = 2;
	}
  
	echo os_draw_pull_down_menu('newstatus', $poll_status, $poll['status'], 'onchange="this.form.submit();"');
	echo '</form>';
?>
				</td>		
			</tr>
<?php
		$i++;
		}
} else {
?>
			<tr>
				<td colspan="6" class="content_poll"><?php echo NO_POLL;?></td>
			</tr>
<?php } ?>
		</table>
		<br />
<?php } ?>

	</div>
</div>

<?php $main->bottom(); ?>