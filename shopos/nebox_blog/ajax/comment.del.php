<?php
include ($_SERVER['DOCUMENT_ROOT'].'/includes/top.php');

if ($_SESSION['customers_status']['customers_status_id'] == 0) {
	if($_POST['id']) {
		$id		= $_POST['id'];
		$id		= mysql_escape_String($id);
		$sql	= os_db_query("DELETE FROM ".DB_NEBOX_BLOG_COMMENTS." WHERE id = '$id'");
	}
}
?>