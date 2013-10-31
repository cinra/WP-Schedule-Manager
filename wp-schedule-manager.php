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

/* ----------------------------------------------------------
	
	Initialize
	
---------------------------------------------------------- */

if (!defined('WPSM_PLUGIN_BASENAME'))	define('WPSM_PLUGIN_BASENAME', plugin_basename(__FILE__));
if (!defined('WPSM_PLUGIN_NAME'))		define('WPSM_PLUGIN_NAME', trim(dirname(WPSM_PLUGIN_BASENAME),'/'));
if (!defined('WPSM_PLUGIN_DIR'))			define('WPSM_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.WPSM_PLUGIN_NAME);
if (!defined('WPSM_PLUGIN_URL'))			define('WPSM_PLUGIN_URL', WP_PLUGIN_URL.'/'.WPSM_PLUGIN_NAME);
if (!defined('WPSM_DB_TABLENAME'))		define('WPSM_DB_TABLENAME', 'wp_wpsm_cal');//table name

require_once(WPSM_PLUGIN_DIR.'/class.php');
require_once(WPSM_PLUGIN_DIR.'/shortcode.php');

/* ----------------------------------------------------------
	
	Instance
	
---------------------------------------------------------- */

$wpsm = new wp_schedule_manager();

/* ----------------------------------------------------------
	
	Javascript
	
---------------------------------------------------------- */

if (is_admin()) {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-datepicker', FILE_URL.'jquery.ui.datepicker.js', array('jquery','jquery-ui-core'));
	wp_enqueue_style('wpsm-admin-css', WPSM_PLUGIN_URL.'/css/admin.css');
}

/* ----------------------------------------------------------
	
	Action Hooks
	
---------------------------------------------------------- */

add_action('edit_post', array(&$wpsm, 'set'), 10);//投稿記事またはページが更新・編集された場合（コメント含む）
add_action('save_post', array(&$wpsm, 'set'), 10);//インポート、記事・ページ編集フォーム、XMLRPC、メール投稿で記事・ページが作成・更新された場合
add_action('publish_post', array(&$wpsm, 'set'), 10);//公開記事が編集された場合
add_action('transition_post_status', array(&$wpsm, 'set'), 10);//記事が公開に変更された場合

/* ----------------------------------------------------------
	
	Admin Menu
	
---------------------------------------------------------- */

add_action('admin_menu', 'wpsm_add_sidemenu');
function wpsm_add_sidemenu() {
	add_menu_page('schedule', 'スケジュール', 7, 'wpsm', 'admin_schedule_list');
 	//add_submenu_page('wpsm', 'schedule','編集', 7, 'edit', 'admin_schedule_list');
 	add_submenu_page('wpsm', 'schedule','設定', 7, 'option', 'admin_option');
}

/* ----------------------------------------------------------
	
	Set Option Value
	
---------------------------------------------------------- */

function admin_option() {
	
	$options = array('wpsm_post_type');
	
	foreach($options as $opt) {
		if (isset($_POST[$opt])) {
			if (get_option($opt)) {
				update_option($opt, $_POST[$opt]);
			} else {
				add_option($opt, $_POST[$opt]);
			}
		}
	}
	
	global $wpsm;
	echo '<div class="wrap"><h2>設定</h2>';
?>

<form method="POST">
	<p><label>投稿タイプ</label><br />
	<input type="text" name="wpsm_post_type" size="50" tabindex="1" id="wpsm_post_type" autocomplete="off" value="<?php echo get_option('wpsm_post_type')?>" /></p>
	<p><input type="submit" name="publish" id="publish" class="button-primary" value="<?php _e('保存')?>" tabindex="5" accesskey="p" /></p>
</form>
</div>

<?php	}

/* ----------------------------------------------------------
	
	Generate Schedule List
	
---------------------------------------------------------- */

function admin_schedule_list() {
	
	global $wpsm;
	echo '<div class="wrap">';
	
	if ($_GET['post_id']) {//個別エントリ編集
		$wpsm->get(array());
	} elseif ($_GET['date_id']) {//スケジュール編集
		if (isset($_POST['wpsm_date_id'])) {
			$wpsm->set();
		}
		$dat = $wpsm->get(array(
			'include'	=> explode(',', $_GET['date_id'])
		));
		if (!empty($dat)) :
?>

<form method="POST">

<?php //ここから、全て外部関数に置き換え?>
<?php
	foreach($dat as $d):
	$post = get_post($d->post_id);
?>

<h2><?php echo $post->post_title?></h2>
<script type="text/javascript">
(function($) {
	$(function() {
   	$('.sc-data').datepicker({dateFormat:'yy-mm-dd'});
	});
})(jQuery);
</script>
<input type="hidden" name="wpsm_date_id[0]"<?php if(isset($d->ID)):?> value="<?php echo $d->ID?>"<?php endif;?> />
	<p class="day">
		<label>日付</label>
		<input type="text" name="wpsm_day[0]" size="50" tabindex="1"  id="sc-data" class="sc-data" autocomplete="off"<?php if(isset($d->date)):?> value="<?php echo $d->date?>"<?php endif;?> />
	</p>
	<p class="time">
		<label>時間</label>
		<input type="text" name="wpsm_time[0]" size="50" tabindex="1" id="sc-time" autocomplete="off"<?php if(isset($d->time)):?> value="<?php echo $d->time?>"<?php endif;?> />
	</p>
	<p class="description">
		<label>概要</label>
		<input type="text" name="wpsm_description[0]" size="50" tabindex="1" id="sc-description" autocomplete="off"<?php if(isset($d->description)):?> value="<?php echo $d->description?>"<?php endif;?> />
	</p>
	<p class="status">
		<label>予約可</label>
		<input type="checkbox" name="wpsm_status[0]"<?php if(isset($d->status) && $d->status == 1):?> checked="checked"<?php endif;?> />
	</p>
	<p class="URL">
		<label>URL</label>
		<input type="text" name="wpsm_url[0]" size="50" tabindex="1" id="sc-URL" autocomplete="off"<?php if(isset($d->url)):?> value="<?php echo $d->url?>"<?php endif;?> />
	</p>
	<?php //ここまで、外部関数に置き換え?>
	
	<input type="submit" name="publish" id="publish" class="button-primary" value="<?php _e('更新')?>" tabindex="5" accesskey="p">
</form>

<form method="POST" style="padding:20px 0 0 0;border-top:1px solid #CCC;margin:20px 0 0 0;text-align:right;">
	<input type="hidden" name="wpsm_date_id[0]"<?php if(isset($d->ID)):?> value="<?php echo $d->ID?>"<?php endif;?> />
	<input type="hidden" name="wpsm_refresh_post_id[0]"<?php if(isset($post)):?> value="<?php echo $post->ID?>"<?php endif;?> />
	<input type="hidden" name="wpsm_day[0]" value="" />
	<input type="submit" name="publish" id="publish" class="button-secondary" value="<?php _e('削除')?>" tabindex="5" accesskey="p">
</form>

<?php
	endforeach;
	else:
	#echo '<pre>';
	#print_r($_POST);
	#ho '</pre>';exit;
	echo '<meta http-equiv="refresh" content="0;URL='.admin_url().'admin.php?page=wpsm&mode=post&id='.$_POST['wpsm_refresh_post_id'][0].'" />';
	//header('location:'.admin_url().'admin.php?page=wpsm');
	endif;
} elseif ($_GET['mode']=='post') {
	global $wpdb;
?>
<script type="text/javascript">
(function($) {
	$(function() {
   	$('.sc-data').datepicker({ dateFormat: 'yy-mm-dd' });
	});
})(jQuery);
</script>
	<h2><?php echo get_the_title($_GET['id'])?></h2>
	<div class="box">
	<form method="post" enctype="multipart/form-data">
	<p class="day">
		<label>日付</label>
		<input type="text" name="wpsm_day" size="20" tabindex="1" class="sc-data" autocomplete="off"<?php if(isset($_POST['wpsm_day'])):?> value="<?php echo $_POST['wpsm_day']?>"<?php endif;?> />
		<label>時間</label>
		<input type="text" name="wpsm_time" size="20" tabindex="1" id="sc-time" autocomplete="off"<?php if(isset($_POST['wpsm_time'])):?> value="<?php echo $_POST['wpsm_time']?>"<?php endif;?> />
	</p>
	<p>
		<label style="vertical-align:top">概要</label>
		<textarea name="wpsm_description" cols="50" rows="4" class="sc-description"><?php if(isset($_POST['wpsm_description'])):?><?php echo $_POST['wpsm_description']?><?php endif;?></textarea>
	</p>
	<p class="status">
		<label>公開状態</label>
		<input type="checkbox" name="wpsm_status"<?php if(isset($d->status) && $d->status == 1):?> checked="checked"<?php endif;?> />
		<label>URL</label>
		<input type="text" name="wpsm_url" size="40" tabindex="1" class="sc-url" autocomplete="off"<?php if(isset($_POST['wpsm_url'])):?> value="<?php echo $_POST['wpsm_url']?>"<?php endif;?> />
	</p>
	<p>
		<input type="submit" name="submit" value="登録" />
	</p>
	</form>
</div>

<?php
	if ($_POST['submit'] == '登録') {
		echo "登録";
		$SCK = ($_POST['wpsm_status']=="on")? 1 : 0;
		$wpdb->insert('wp_wpsm_cal', array(
			'date'		=> $_POST['wpsm_day'],								
			'time'		=> $_POST['wpsm_time'],
			'description' => $_POST['wpsm_description'],
			'post_id'		=> $_GET['id'],
			'status' => $SCK,
			'url' => $_POST['wpsm_url']					
		));
	}
	get_list_table($wpsm->get(array(
	 	'post_id'		=> $_GET['id'],
	 	'term'		=> 1
	)), $datestr);
} else {
	$post = get_posts();
	
	$options = "";
	foreach ($post as $p) {
		$options .= '<option value="'.$p->ID.'">'.$p->post_title.'</option>';
	}
	
	// 日付を取得
	$now		= date('Y-m-01');
	$datestr	= ($_GET['date']) ? date('Y-m-d', strtotime($_GET['date'])) : $now;
	$date_a	= explode('-', $datestr);
	
	echo '<div id="icon-options-general" class="icon32"></div>';
	echo "<h2>スケジュール</h2>";
	
	$next = date('Y-m-d', mktime(0, 0, 0, $date_a[1]+1, $date_a[2], $date_a[0]));
	$prev = date('Y-m-d', mktime(0, 0, 0, $date_a[1]-1, $date_a[2], $date_a[0]));
	$datestr_last = date('Y-m-d', mktime(0, 0, 0, $date_a[1]+1, $date_a[2]-1, $date_a[0]));
	
	get_monthly_pager($now, $next, $prev);
	get_list_table($wpsm->get(array(
	 	'date'		=> $datestr,
	 	'term_by'	=> 'month',
	 	'term'		=> 1
	)), $datestr, $datestr_last);
	get_monthly_pager($now, $next, $prev);
}
echo '</div>';
}

function get_list_table($dat = array(), $datestr = "", $datelast = "") {
if (empty($datestr)) $datestr = $_GET['date'];
?>

<form>
<table class="widefat">
	<thead>
		<?php if($_GET['mode']!="post"):?><tr>
			<th scope="col" colspan="25"><?php echo $datestr?><?php if($datelast):?> 〜 <?php echo $datelast?><?php endif;?></th>
		</tr><?php endif;?>
		<tr>
		<?php if($_GET['mode'] != "post") {?> <th scope="col" colspan="1">タイトル</th><?php } ?>
			<th scope="col" colspan="1">日付</th>
			<th scope="col" colspan="1">時間</th>
			<th scope="col" colspan="1">公開状態</th>
			<th scope="col" colspan="1">URL</th>
		</tr>
	</thead>
	<tbody>
<?php	 
if (!empty($dat)) {
	foreach ($dat as $d) {
		$post = get_post($d->post_id);
		$is_link = $d->status;
		$check = ($is_link) ? '<img src="'.admin_url().'images/yes.png" />' : " -";
		
		echo	'<tr class="mainraw">';
		if($_GET['mode']=="post"){}
		else {
		echo  '<td><a href="admin.php?page=wpsm&mode=post&id='.$post->ID.'">'.$post->post_title.'</a></td>';}
		echo  '<td><a href="admin.php?page=wpsm&date_id='.$d->ID.'">'.$d->date.'</a></td>';		
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

/* -----------------------------------------------------------

		wpsm_output_metabox
		***** generate new metabox for wordpress post editor

----------------------------------------------------------- */

function wpsm_init_adminmenu() {
	$opt = get_option('wpsm_post_type');
	$post_type = (!empty($opt)) ? explode(',', $opt) : array('post');
	foreach ($post_type as $pt) add_meta_box('schedule_manager', __('スケジュール'), 'wpsm_output_metabox', $pt, 'normal');
}
add_action('add_meta_boxes', 'wpsm_init_adminmenu');

function wpsm_output_metabox() {
	global $wpsm, $post, $count;
	$count = 0;
	
	$dat = $wpsm->get(array('post_id' => $post->ID));
	if (empty($dat)) $dat = array('nodata');	
?>

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
			
			count++;
			alcount++;
			wpsm=wpsm+count;
			
			var wpsmadd='<div class="' + wpsm + ' box"></div>';
			wpsmaf="."+ wpsmaf;
			
			$(wpsmaf).after(wpsmadd);
			$("."+wpsm).append(html);
			
			wpsm_day				= "wpsm_day[" + count + "]";
			wpsm_time			= "wpsm_time[" + count + "]";
			wspm_description	= "wspm_description[" + count + "]";
			wpsm_status			= "wpsm_status[" + count + "]";
			wpsm_class			= "wpsm_class"+count;
			wpsm_URL				= "wpsm_url[" + count + "]";
			
			var obj = $("."+wpsm);
			var parent = $(this).parent().parent();
			
			obj.find("input.sc-data").attr({name:wpsm_day,value:parent.find("input.sc-data").val(), id:wpsm_class, class:""});
			obj.find("input.sc-time").attr({name:wpsm_time,value:parent.find("input.sc-time").val()});
			obj.find("textarea.sc-description").attr({name:wspm_description,value:parent.find("textarea.sc-description").val()});
			obj.find(".status input").attr({name:wpsm_status,value:0});
			obj.find("input.sc-url").attr({name:wpsm_URL,value:parent.find("input.sc-url").val()});
			
			$(".wpsm_minus").css("display","block");
						
			$("#"+wpsm_class).datepicker({ dateFormat: 'yy-mm-dd' });
			wpsmaf=wpsm;
			wpsm="wpsm_daybox";
		});
		$('.wpsm_minus').live('click', function() {
			$(this).parent().parent().empty();				
			alcount--;
			if (1>alcount) {
				$(".wpsm_minus").css("display","none");
			}
		});
	});
})(jQuery);
</script>

<?php
	$c = 0;
	foreach($dat as $d):
?>

<div class="wpsm_daybox<?php echo $count ?> box">
	<p class="wpsm_box_day">
		<label>日付</label>
		<input type="text" name="wpsm_day[<?php echo $c?>]" size="20" tabindex="1" class="sc-data" autocomplete="off"<?php if(isset($d->date)):?> value="<?php echo $d->date?>"<?php endif;?> />
		<label> 時間</label>
		<input type="text" name="wpsm_time[<?php echo $c?>]" size="20" tabindex="1" class="sc-time" autocomplete="off"<?php if(isset($d->time)):?> value="<?php echo $d->time?>"<?php endif;?> />
	</p>
	<p class="wpsm_box_description">
		<label style="vertical-align:top;">概要</label>
		<textarea name="wpsm_description[<?php echo $c?>]" cols="49" rows="3" class="sc-description"><?php if(isset($d->description)):?><?php echo $d->description?><?php endif;?></textarea>
	</p>
	<p class="wpsm_box_status">
		<label>公開状態</label>
		<input type="checkbox" name="wpsm_status[<?php echo $c?>]"<?php if($d->status):?> checked="checked"<?php endif;?> />
		<label>URL</label>
		<input type="text" name="wpsm_url[<?php echo $c?>]" size="38" tabindex="1" class="sc-url" autocomplete="off"<?php if(isset($d->url)):?> value="<?php echo $d->url?>"<?php endif;?> />
	</p>
	<p><a class="wpsm_plus">+ 日付を追加</a></p>
	<p><a class="wpsm_maenas wpsm_minus">日付を削除</a></p>
</div>
<?php
	$count++;
	$c++;
	endforeach;
}


/* ----------------------------------------------------------
	
	Utilities
	
---------------------------------------------------------- */

function wp_get_weekday($date) {
	$wk = array('日','月','火','水','木','金','土');
	return $wk[date('w', strtotime($date))];
}

/* -----------------------------------------------------------

		wpsm_install

----------------------------------------------------------- */

add_action('activate_' . WPSM_PLUGIN_BASENAME, 'wpsm_install');
function wpsm_install() {
	
	global $wpdb;
	
	#if (wp_schedule_manager::table_exist(WPSM_DB_TABLENAME)) return;// テーブルが既にあるかどうかチェック
	if (strtolower($wpdb->get_var( "SHOW TABLES LIKE '".WPSM_DB_TABLENAME."'")) == strtolower(WPSM_DB_TABLENAME)) return;
	
	$charset_collate = '';
	if ($wpdb->has_cap('collation')) {
		if (!empty( $wpdb->charset)) $charset_collate = "DEFAULT CHARACTER SET ".$wpdb->charset;
		if (!empty( $wpdb->collate)) $charset_collate .= " COLLATE ".$wpdb->collate;
	}
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS `".WPSM_DB_TABLENAME."` (
		`ID` BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`date` DATE NOT NULL ,
		`time` TIME NULL DEFAULT NULL ,
		`description` TEXT NULL DEFAULT NULL ,
		`post_id` BIGINT( 20 ) NOT NULL ,
		`post_type` VARCHAR( 20 ) NOT NULL,
		`status` TINYINT NOT NULL ,
		`url` VARCHAR( 255 ) NULL DEFAULT NULL ,
		INDEX ( `date`, `post_id`, `post_type`, `status` )
		) ".$charset_collate.";");
	
	return (strtolower($wpdb->get_var( "SHOW TABLES LIKE '".WPSM_DB_TABLENAME."'")) == strtolower(WPSM_DB_TABLENAME)) ? true:false;
}


?>