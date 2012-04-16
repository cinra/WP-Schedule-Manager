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
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-datepicker',  FILE_URL . 'jquery.ui.datepicker.js', array('jquery','jquery-ui-core') );add_action('save_post', array(&$wpsm, 'set'), 10);

add_action('admin_menu', 'wpsm_add_sidemenu');
function wpsm_add_sidemenu() {
	add_menu_page('schedule', 'schedule', 7, 'wpsm', 'admin_schedule_list');
 	add_submenu_page('wpsm', 'schedule','編集', 7, 'edit', 'admin_schedule_list');
}

function admin_schedule_list() {
	
	global $wpsm;
	$post = get_posts();
	
	$options = "";
	foreach ($post as $p) {
		$options .= '<option value="'.$p->ID.'">'.$p->post_title.'</option>';
	}
	$date_all=$_GET['date'];
	$date_a=explode('-',$date_all);
	
	print_r($date_a);
	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br></div>';
	echo "<h2>スケジュール</h2>";
	$next=date('Y-m-d', mktime(0, 0, 0, $date_a[1]+1, $date_a[2], $date_a[0]));
	$back=date('Y-m-d', mktime(0, 0, 0, $date_a[1]-1, $date_a[2], $date_a[0]));

     echo '<div class="subsubsub"><a href="admin.php?page=wpsm&date='.$back.'">前の月</a> | <a href="admin.php?page=wpsm&date='.$next.'">次の月</div>';
	echo <<<EOF
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
EOF;
	$wpsmdata = $wpsm->get(array(
	 	
	 	'date'=> '',
	 	'term_by' => 'year',
	 	'term'	=> 4
	 	
	 ));
	 
	 print_r($wpsdata);
	
	#if (!empty($wpsdata)) {
	foreach ($wpsmdata as $d) {
	$pp = get_post( $d->post_id);
	$is_link = $d->is_link;
	if($is_link){$check="有";}else{$check = "無";}
	
	print_r($check);
	echo	'<tr class="mainraw">';
	echo    '<td>'.$pp->post_title.'</td>';
	echo    '<td>'.$d->date.'</td>';		
	echo	'<td>'.$d->time.'</td>';
	echo	'<td>'.$check.'</td>';
	echo	'<td>'.$d->url.'</td>';
	echo	'</tr>';
	}
	#}
	echo <<<EOF
	</tbody>
</table>
</form>

EOF;
	echo '</div>';
}





function wpsm_init_adminmenu() {
	add_meta_box('schedule_manager', __('schedule'), 'wpsm_output_metabox', 'post', 'normal');
}
add_action('admin_init', 'wpsm_init_adminmenu');

function wpsm_output_metabox() {

	global $wpsm, $post;
	
	//exit($post->ID);
	
	$dat = $wpsm->get(array('post_id' => $post->ID));
	if (empty($dat)) $dat = array('nodata');
	
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
$('#sc-data').datepicker({ dateFormat: 'yy-mm-dd' });

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
			wspm_description="wspm_description[" + count + "]";
			wpsm_yoyaku="wpsm_yoyaku[" + count + "]";
			
			wpsm_URL="wpsm_url[" + count + "]";
			//$("."+wpsm).children(".day").children("input").attr("name",wpsm_day);
			$("."+wpsm).children(".day").children("input").attr({name:wpsm_day,value:""});
			$("."+wpsm).children(".time").children("input").attr({name:wpsm_time,value:""});
			$("."+wpsm).children(".description").children("input").attr({name:wspm_description,value:""});
			$("."+wpsm).children(".yoyaku").children("input").attr({name:wpsm_yoyaku,value:0});
			$("."+wpsm).children(".URL").children("input").attr({name:wpsm_URL,value:""});
			$(".wpsm_maenas").css("display","inline");
			
			wpsmaf=wpsm;
			wpsm="wpsm_daybox";
		});
		/*$('.wpsm_maenas').live('click', function() {
			//console.log($(this).parent().parent());
			$(this).parent().parent().css("display","none");
			$(this).parent().parent().children(".day").children("input").attr("value","delete");
			$(this).parent().parent().children(".time").children("input").attr("value","delete");
			$(this).parent().parent().children(".description").children("input").attr("value","delete");
			$(this).parent().parent().children(".yoyaku").children("input").attr("value","0");
			$(this).parent().parent().children(".URL").children("input").attr("value","delete");
						
			alcount--;
			console.log(alcount);
			
			if(1>alcount){
				$(".wpsm_maenas").css("display","none");
			}
		});*/
	});
})(jQuery);
</script>
EOF;

	echo '<div class="wpsm_daybox0">';
	
	foreach($dat as $d):
	print_r($d);?>
	<p class="day">
		<label>日付</label>
		<input type="text" name="wpsm_day[0]" size="50" tabindex="1"  id="sc-data" autocomplete="off"<?php if(isset($d->date)):?> value="<?php echo $d->date?>"<?php endif;?> />
	</p>
	<p class="time">
		<label>時間</label>
		<input type="text" name="wpsm_time[0]" size="50" tabindex="1" id="sc-time" autocomplete="off"<?php if(isset($d->time)):?> value="<?php echo $d->time?>"<?php endif;?> />
	</p>
	<p class="description">
		<label>概要</label>
		<input type="text" name="wpsm_description[0]" size="50" tabindex="1" id="sc-description" autocomplete="off"<?php if(isset($d->description)):?> value="<?php echo $d->description?>"<?php endif;?> />
	</p>
	
	<p class="yoyaku">
		<label>予約可</label>
		<input type="checkbox" name="wpsm_yoyaku[0]"<?php if(isset($d->yoyaku)):?> value="<?php echo $d->yoyaku?>"<?php endif;?> />
	</p>
	<p class="URL">
		<label>URL</label>
		<input type="text" name="wpsm_url[0]" size="100" tabindex="1" id="sc-URL" autocomplete="off"<?php if(isset($d->url)):?> value="<?php echo $d->url?>"<?php endif;?> />
	</p>
	<?php endforeach;
	
	echo <<<EOF
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