<?php
/*
Plugin Name: WP Schedule Manager
Plugin URI: http://cinra.github.com/WP-Schedule-Manager
Description: a Schedule Manager for wordpress
Version: 0.1
Author: CINRA Inc,
Author URI: http://www.cinra.co.jp/
License: not-yet
*/

include(dirname(__FILE__).'/class.php');

if (is_admin()) {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-datepicker', FILE_URL.'jquery.ui.datepicker.js', array('jquery','jquery-ui-core'));
}

add_action('edit_post', array(&$wpsm, 'set'), 10);//投稿記事またはページが更新・編集された場合（コメント含む）
add_action('save_post', array(&$wpsm, 'set'), 10);//インポート、記事・ページ編集フォーム、XMLRPC、メール投稿で記事・ページが作成・更新された場合
add_action('publish_post', array(&$wpsm, 'set'), 10);//公開記事が編集された場合
add_action('transition_post_status', array(&$wpsm, 'set'), 10);//記事が公開に変更された場合

add_action('admin_menu', 'wpsm_add_sidemenu');
function wpsm_add_sidemenu() {
	add_menu_page('schedule', 'schedule', 7, 'wpsm', 'admin_schedule_list');
 	add_submenu_page('wpsm', 'schedule','編集', 7, 'edit', 'admin_schedule_list');
}

function admin_schedule_list() {
	
	global $wpsm;
	echo '<div class="wrap">';
	
	if ($_GET['post_id']) {//個別エントリ編集
		$wpsm->get(array());
	} elseif ($_GET['date_id']) {//スケジュール編集
		if (isset($_POST['wpsm_date_id'])) {
			/*foreach ($_POST['wpsm_date_id'] as $k => $v) {
				$wpsm->set(array('date_id' => $_POST['wpsm_date_id'][$k]));
			}*/
			$wpsm->set();
		}
		$dat = $wpsm->get(array(
			'include'	=> explode(',', $_GET['date_id'])
		));
?>

<form method="POST">

<?php //ここから、全て外部関数に置き換え?>
<?php
	if (!empty($dat)) :
	foreach($dat as $d):
?>

<input type="hidden" name="wpsm_date_id[0]"<?php if(isset($d->ID)):?> value="<?php echo $d->ID?>"<?php endif;?> />
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
<?php //ここまで、外部関数に置き換え?>

<input type="submit" name="publish" id="publish" class="button-primary" value="<?php _e('公開')?>" tabindex="5" accesskey="p">
</form>

<?php
	endforeach;
	endif;
} else {
	$post = get_posts();
	
	$options = "";
	foreach ($post as $p) {
		$options .= '<option value="'.$p->ID.'">'.$p->post_title.'</option>';
	}
	
	// 日付を取得
	$now		= date('Y-m-01');
	$datestr	= ($_GET['date']) ? date('Y-m-d', strtotime($_GET['date'])) : $now;
	$date_a		= explode('-', $datestr);
	
	echo '<div id="icon-options-general" class="icon32"></div>';
	echo "<h2>スケジュール</h2>";
	
	$next = date('Y-m-d', mktime(0, 0, 0, $date_a[1]+1, $date_a[2], $date_a[0]));
	$prev = date('Y-m-d', mktime(0, 0, 0, $date_a[1]-1, $date_a[2], $date_a[0]));
	
	get_monthly_pager($now, $next, $prev);
	get_list_table($wpsm->get(array(
	 	'date'		=> $datestr,
	 	'term_by'	=> 'month',
	 	'term'		=> 1
	)), $datestr);
	get_monthly_pager($now, $next, $prev);
}
	echo '</div>';
}

function get_list_table($dat = array(), $datestr = "") {
if (empty($datestr)) $datestr = $_GET['date'];
?>

<form>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col" colspan="25"><?php echo $datestr?></th>
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
<?php	 
if (!empty($dat)) {
	foreach ($dat as $d) {
		$post = get_post($d->post_id);
		$is_link = $d->status;
		$check = ($is_link) ? "有" : "無";
		
		echo	'<tr class="mainraw">';
		echo    '<td><a href="post.php?post='.$post->ID.'&action=edit">'.$post->post_title.'</a></td>';
		echo    '<td><a href="admin.php?page=wpsm&date_id='.$d->ID.'">'.$d->date.'</a></td>';		
		echo	'<td>'.$d->time.'</td>';
		echo	'<td>'.$check.'</td>';
		echo	'<td>'.$d->url.'</td>';
		echo	'</tr>';
	}
}
?>
	</tbody>
</table>
</form>

<?php
}

function get_monthly_pager($now, $next, $prev) {
	$output = '<div class="subsubsub">';
	$output .= '<a href="admin.php?page=wpsm&date='.$prev.'">前の月</a> | ';
	$output .= '<a href="admin.php?page=wpsm&date='.$now.'">当月</a> | ';
	$output .= '<a href="admin.php?page=wpsm&date='.$next.'">次の月</a>';
	$output .= '</div>';
	echo $output;
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

/* -----------------------------------------------------------

		wpsm_output_metabox
		***** generate new metabox for wordpress post editor

----------------------------------------------------------- */

function wpsm_init_adminmenu() {
	add_meta_box('schedule_manager', __('schedule'), 'wpsm_output_metabox', 'post', 'normal');
}
add_action('add_meta_boxes', 'wpsm_init_adminmenu');

function wpsm_output_metabox() {
	global $wpsm, $post, $count;
	$count = 0;
	
	$dat = $wpsm->get(array('post_id' => $post->ID));
	if (empty($dat)) $dat = array('nodata');	
?>

<style type="text/css">
a.wpsm_plus {background:#333;}
a.wpsm_plus:hover {cursor:pointer;}
a.wpsm_maenas {
	background:#333;
	display:none;
}
a.wpsm_maenas:hover {cursor:pointer;}
</style>

<script type="text/javascript">
(function($) {
	$(function() {
    $('.sc-data').datepicker({ dateFormat: 'yy-mm-dd' });
	var count=0;
	var alcount=0;
	var wpsm="wpsm_daybox";
	var wpsmaf="wpsm_daybox0";	
	count=$(".box").length;
	count=count-1;
	alcount = count;
	if (count>0) {
		$(".wpsm_maenas").css("display","inline");
	}
	var html =$('.wpsm_daybox0').html();
	 	
	$('.wpsm_plus').live('click', function() {
			wpsmaf="wpsm_daybox"+count;
			//console.log(wpsmaf);
			
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
			
			$('.sc-data').datepicker('refresh');
		});
		$('.wpsm_maenas').live('click', function() {
			console.log($(this).parent().parent());
			$(this).parent().parent().empty();				
			alcount--;
			console.log(alcount);
			
			if(1>alcount){
				$(".wpsm_maenas").css("display","none");
			}
		});
	});
})(jQuery);
</script>
<?php
	$c = 0;
	foreach($dat as $d):
	#print_r($d);
?>
<div class="wpsm_daybox<?php echo $count ?> box">
	<p class="day">
		<label>日付</label>
		<input type="text" name="wpsm_day[<?php echo $c?>]" size="50" tabindex="1" class="sc-data" autocomplete="off"<?php if(isset($d->date)):?> value="<?php echo $d->date?>"<?php endif;?> />
	</p>
	<p class="time">
		<label>時間</label>
		<input type="text" name="wpsm_time[<?php echo $c?>]" size="50" tabindex="1" id="sc-time" autocomplete="off"<?php if(isset($d->time)):?> value="<?php echo $d->time?>"<?php endif;?> />
	</p>
	<p class="description">
		<label>概要</label>
		<input type="text" name="wpsm_description[<?php echo $c?>]" size="50" tabindex="1" id="sc-description" autocomplete="off"<?php if(isset($d->description)):?> value="<?php echo $d->description?>"<?php endif;?> />
	</p>
	<p class="yoyaku">
		<label>予約可</label>
		<input type="checkbox" name="wpsm_yoyaku[<?php echo $c?>]"<?php if($d->status):?> checked="checked"<?php endif;?> />
	</p>
	<p class="URL">
		<label>URL</label>
		<input type="text" name="wpsm_url[<?php echo $c?>]" size="100" tabindex="1" id="sc-URL" autocomplete="off"<?php if(isset($d->url)):?> value="<?php echo $d->url?>"<?php endif;?> />
	</p>
	<p><a class="wpsm_plus">+</a></p>
	<p><a class="wpsm_maenas">-</a></p>
</div>
<?php
	$count++;
	$c++;
	endforeach;
}?>