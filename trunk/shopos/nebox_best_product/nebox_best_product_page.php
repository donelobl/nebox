<?php
	global $main;

	include (dirname(__FILE__).'/lang/'.$_SESSION['language'].'.php');

	$best_product_exist =  os_db_query("select sp.product_id, pd.products_id, pd.products_name from " . TABLE_BEST_PRODUCT . " sp, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.language_id = '" . $_SESSION['languages_id'] . "'");
	$star_exist = os_db_fetch_array($best_product_exist);
	if(os_get_products_name($star_exist['product_id'])==''){
		os_db_query("update " . TABLE_BEST_PRODUCT . " set product_id = '" . $star_exist['products_id'] . "' where product_id = '" . $star_exist['product_id'] . "' limit 1");
	}

	$star_actual_query =  os_db_query("select sp.product_id, pd.products_name from " . TABLE_BEST_PRODUCT . " sp, " . TABLE_PRODUCTS_DESCRIPTION . " pd where sp.product_id = pd.products_id and pd.language_id = '" . $_SESSION['languages_id'] . "' order by pd.products_name");
	$star_actual = os_db_fetch_array($star_actual_query);
	$star_array[] = array(
		'id' => $star_actual['products_id'],
		'text' => $star_actual['products_name']
	);

	if ($_POST['submit']) {
		if (empty($_POST['products_id'])) {
			$products_id = $_POST[products_id2];
		} else {
			$products_id = $_POST[products_id];
		}
		
		os_db_query("update " . TABLE_BEST_PRODUCT . " set product_id = '$products_id' where product_id = '" . $star_actual['product_id'] . "' limit 1");
		
		$star_actual['product_id'] = $products_id;
		$star_actual['products_name'] = os_get_products_name($products_id);
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

.button-submit {padding:5px 10px 5px 10px;font-size:0.8em;cursor:pointer;margin:5px 0 5px 0;background:#ffffff;border:1px solid #b6cddb;}
.button-submit:hover {background:#b6cddb;}
</style>
<div class="wrapper-content">
	<div class="wrap-con">
		<h1><?php echo SP_HEADING_TITLE; ?></h1>
		<form method="post" action="<?php echo $PHP_SELF?>">
		<table class="blog-table" width="100%" border="0" cellpadding="0" cellspacing="2">
			<tr>
				<td class="blog-table-left"><?php echo SP_TEXT_ACTUAL_BEST_PRODUCT;?></td>
				<td class="blog-table-right"><h2><?php echo $star_actual['products_name']; ?></h2></td>
			</tr>
			<tr>
				<td class="blog-table-left"><?php echo SP_TEXT_SELECT_BEST_PRODUCT; ?></td>
				<td class="blog-table-right">
					<select name="products_id" size="15">
					<?php
						$star_query =  os_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and pd.language_id = '" . $_SESSION['languages_id'] . "' order by pd.products_name");
						while ($star = os_db_fetch_array($star_query)) {
					?>
						<option value="<?php echo $star['products_id'];?>"><?php echo $star['products_name'];?></option>
					<?php } ?>
					</select>
				</td>
			</tr>
			<!-- Добавляем товар по ID -->
			<tr>
				<td class="blog-table-left"><?php echo SP_TEXT_SELECT_BEST_PRODUCT_ID; ?></td>
				<td class="blog-table-right"><input type="text" name="products_id2" value="" /><br /><?php echo SP_TEXT_SELECT_BEST_PRODUCT_ID_D; ?></td>
			</tr>
			<tr>
				<td class="blog-table-left"></td>
				<td class="blog-table-right"><input class="button-submit" type="submit" name="submit" value="<?php echo SP_IMAGE_CONFIRM;?>" /></td>
			</tr>
		</table>
		</form>
	</div>
</div>
<?php $main->bottom(); ?>