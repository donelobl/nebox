<?
include ($_SERVER['DOCUMENT_ROOT'].'/includes/top.php');

header("Expires: Mon, 23 May 1995 02:00:00 GTM");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GTM");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

require_once(dir_path('class').'JsHttpRequest.php');

$JsHttpRequest = new JsHttpRequest('');

$post_id = (int)$_REQUEST['post_id'];
$comtext = $_REQUEST['comtext'];
$comname = $_REQUEST['comname'];

$error = "no";

if (!$comname || strlen($comname)>40 || strlen($comname)<3) {
	$log .= "<p>Введите свое имя от 3 до 35 символов!</p>";
	$error = "yes";
}

if (!$comtext) {
	$log .= "<p>Необходимо указать текст записи!</p>";
	$error = "yes";
}
    
if(strlen($comtext)>1010) {
	$log .= "<p>Слишком длинный текст. Разрешено 1000 символов!</p>";
	$error = "yes";
}

if (isset($_SESSION['customer_id'])) {
	$current_user = $_SESSION['customer_id'];
	$comment_status = "1";
} else {
	$current_user = "0";
	$comment_status = "0";
}

$comname = trim(strip_tags($comname));
$comtext = str_replace("\n","<br />\n",$comtext);

if($error == "no") {
	os_db_query("INSERT INTO ".DB_NEBOX_BLOG_COMMETS." VALUES ('','$post_id','$comname','$comtext',now(),'$comment_status','$current_user')");

	$_RESULT['err'] = 'no';

	$_RESULT['post_id'] = $post_id;
	$_RESULT['text'] = $comtext;
	$_RESULT['name'] = $comname;

} else {
	$_RESULT['err'] = 'yes';
	$log = "<div class=\"comments-errors tleft\"><h3>Ошибка</h3>".$log."</div>";
	$_RESULT['log'] = $log;
}  
?>