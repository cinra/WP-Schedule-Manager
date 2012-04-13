<?php
/*
Plugin Name: WP Schedule Manager
Plugin URI: https://github.com/cinra/WP-Schedule-Manager
Description: a Schedule Manager for wordpress
Version: 0.1
Author: cinra inc,
Author URI: http://www.cinra.co.jp/
License: not-yet
*/

include(dirname(__FILE__).'/core.php');

add_action('save_post', array(&$wpsm, 'set'), 10);

add_action('admin_menu', 'wpsm_add_sidemenu');
function wpsm_add_sidemenu() {
	add_menu_page('schedule', 'スケジュール', 7, 'wpsm', 'admin_schedule_list');
 	add_submenu_page('wpsm', 'schedule','編集', 7, 'edit', 'admin_schedule_list');
}

function admin_schedule_list() {
	
	$post = get_posts();
	
	$options = "";
	foreach ($post as $p) {
		$options .= '<option value="'.$p->ID.'">'.$p->post_title.'</option>';
	}
	
	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br></div>';
	echo "<h2>スケジュール</h2>";
	echo <<<EOF
<div class="subsubsub">前の月 | 次の月</div>

<table class="widefat" style="margin-bottom: 1em;">
	<tr>
		<td>
			<select>
EOF;
	echo $options;
	echo <<<EOF
			</select>
		</td>
	</tr>
</table>

<form>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col" colspan="25">YYYY.MM.DD</th>
		</tr>
		<tr>
			<th scope="col" colspan="1">タイトル</th>
			<th scope="col" colspan="1">日にち</th>
			<th scope="col" colspan="1">時間</th>
			<th scope="col" colspan="1">受付の有無</th>
			<th scope="col" colspan="1">受付URL</th>
		</tr>
	</thead>
	<tbody>
		<tr class="mainraw">
			<td>骸骨君物語</td>
			<td>12.01.01</td>
			<td>16:20</td>
			<td>有</td>
			<td>http://www.yahoo.com</td>
		</tr>
		<tr class="mainraw">
			<td>肋骨君物語</td>
			<td>12.03.01</td>
			<td>11:20</td>
			<td>有</td>
			<td>http://www.yahoo.com</td>
		</tr>
		<tr class="mainraw">
			<td>足骨君物語</td>
			<td>12.02.01</td>
			<td>16:20</td>
			<td>有</td>
			<td>http://www.yahoo.com</td>
		</tr>
		<tr class="mainraw">
			<td>鎖骨君物語</td>
			<td>12.05.01</td>
			<td>16:20</td>
			<td>有</td>
			<td>http://www.yahoo.com</td>
		</tr>
		<tr class="mainraw">
			<td>背骨君物語</td>
			<td>12.01.11</td>
			<td>16:25</td>
			<td>有</td>
			<td>http://www.yahoo.com</td>
		</tr>
		<tr class="mainraw">
			<td>肋骨君物語</td>
			<td>11.01.01</td>
			<td>06:20</td>
			<td>有</td>
			<td>http://www.yahoo.com</td>
		</tr>
	</tbody>
</table>
</form>

EOF;
	echo '</div>';
}





function wpsm_init_adminmenu() {
	add_meta_box('schedule_manager', __('schedule'), 'wpsm_output_metabox', 'post', 'normal');
}
add_action('admin_menu', 'wpsm_init_adminmenu');

function wpsm_output_metabox() {
	echo <<<EOF

<style type="text/css">
a.wpsm_plus {
	background:#333;
}
a.wpsm_plus:hover {
	cursor:pointer;
}
a.wpsm_maenas {
	background:#333;
	display:none;
}
a.wpsm_maenas:hover {
	cursor:pointer;
}
</style>

<script type="text/javascript">
(function($) {


	$(function() {
	 var count=0;
	 var alcount=0;
	 var wpsm="wpsm_daybox";
	 var wpsmaf="wpsm_daybox0";
		$('.wpsm_plus').live('click', function() {
			var html =$('.wpsm_daybox0').html();
			count++;
			alcount++;
			wpsm=wpsm+count;
			//console.log(html);
			var wpsmadd="<div class=" + wpsm + "></div>";
			wpsmaf="."+ wpsmaf;
			$(wpsmaf).after(wpsmadd);
			$("."+wpsm).append(html);
			//console.log($("."+wpsm).children());
			wpsm_day="wpsm_day[" + count + "]";
			wpsm_time="wpsm_time[" + count + "]";
			wpsm_yoyaku="wpsm_yoyaku[" + count + "]";
			wpsm_URL="wpsm_url[" + count + "]";
			//$("."+wpsm).children(".day").children("input").attr("name",wpsm_day);
			$("."+wpsm).children(".day").children("input").attr({name:wpsm_day,value:""});

			$("."+wpsm).children(".time").children("input").attr({name:wpsm_time,value:""});
			$("."+wpsm).children(".yoyaku").children("input").attr({name:wpsm_yoyaku,value:0});
			$("."+wpsm).children(".URL").children("input").attr({name:wpsm_URL,value:""});
			$(".wpsm_maenas").css("display","inline");
			
			
			
			//
			wpsmaf=wpsm;
			wpsm="wpsm_daybox";
						

		});
		$('.wpsm_maenas').live('click', function() {
			console.log($(this).parent().parent());
			$(this).parent().parent().css("display","none");
			$(this).parent().parent().children(".day").children("input").attr("value","delete");
			$(this).parent().parent().children(".time").children("input").attr("value","delete");
			$(this).parent().parent().children(".yoyaku").children("input").attr("value","0");
			$(this).parent().parent().children(".URL").children("input").attr("value","delete");
						
			alcount--;
			console.log(alcount);
			
			if(1>alcount){
				$(".wpsm_maenas").css("display","none");
			}
			
			
		});
	});
})(jQuery);
</script>

<div class="wpsm_daybox0">
	<p class="day">
	<label >日付</label>
	<input type="text" name="wpsm_day[0]" size="50" tabindex="1" value="" id="sc-data" autocomplete="off">
	</p>
	<p class="time">
	<label >時間</label>
	<input type="text" name="wpsm_time[0]" size="50" tabindex="1" value="" id="sc-time" autocomplete="off">
	</p>
	<p class="yoyaku">
	<label >予約可</label>
	<input type="checkbox" name="wpsm_yoyaku[0]" value="0">
	</p>
	<p class="URL">
	<label >URL</label>
	<input type="text" name="wpsm_url[0]" size="100" tabindex="1" value="" id="sc-URL" autocomplete="off">
	<p><a class="wpsm_plus">+</a></p>
	<p><a class="wpsm_maenas">-</a></p>
</div>




EOF;
}



if(defined('WP_PLUGIN_URL')) {
	
	$wpsm = new wp_schedule_manager();
	
	/*global $wpmp;

	if(file_exists(dirname(__FILE__) . '/ext/' . get_locale() . '/class.php')) {
		require_once(dirname(__FILE__) . '/ext/' . get_locale() . '/class.php');
		$wpmp = new multibyte_patch_ext();
	}
	elseif(file_exists(dirname(__FILE__) . '/ext/default/class.php')) {
		require_once(dirname(__FILE__) . '/ext/default/class.php');
		$wpmp = new multibyte_patch_ext();
	}
	else
		$wpmp = new multibyte_patch();*/
}

?>