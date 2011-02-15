<?php
/*
Plugin Name: Падающий снег
Plugin URI: http://nebox.ru/cms/shopos/novogodnij-plagin-padayushhij-sneg-dlya-shopos/
Version: 1.0
Description: Плагин добавляет на все страницы вашего сайта падающий снег
Author: NeBox (Посетить блог)
Author URI: http://nebox.ru/
Plugin Group: Плагины от NeBox
*/

add_action('head', 'snowstorm_head');

function snowstorm_head () {
	_e('
		<script src="'.plugurl().'snowstorm.js"></script>
		<script type="text/javascript">
			snowStorm.snowColor = \'#fff\';
			snowStorm.sflakesMax = 64;
			snowStorm.sflakesMaxActive = 64;
			snowStorm.svMaxX = 1;
			snowStorm.svMaxY = 3;
			snowStorm.snowStick = false;
			snowStorm.followMouse = false;
		</script>
	');
}

?>