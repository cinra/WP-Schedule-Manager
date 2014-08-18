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

$is_link = array(0 => '予約を受け付けない', 1 => 'メールフォームで予約を受け付ける', 2 => '別サイトで予約を受け付ける', 3 => '予約受付終了');

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
 	add_submenu_page('wpsm', 'schedule','設定', 7, 'option', 'admin_option');
}

/* ----------------------------------------------------------
	
	Utilities
	
---------------------------------------------------------- */

function wp_get_weekday($date) {
	$wk = array('日','月','火','水','木','金','土');
	return $wk[date('w', strtotime($date))];
}

function get_monthly_pager($now, $next, $prev) {
	$output = '<div class="subsubsub">';
	$output .= '<a href="admin.php?page=wpsm&date='.$prev.'">前の月</a> | ';
	$output .= '<a href="admin.php?page=wpsm&date='.$now.'">当月</a> | ';
	$output .= '<a href="admin.php?page=wpsm&date='.$next.'">次の月</a>';
	$output .= '</div>';
	echo $output;
}

function get_postid_from_date($date_id) {
	global $wpdb;
	return $wpdb->get_var("SELECT `post_id` FROM `wp_wpsm_cal` WHERE `ID` = ".$date_id);
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
	
	global $wpdb, $wpsm;
	
	echo '<div class="wrap">';
	
	switch ($_GET['mode']) {
		// 記事別スケジュールカレンダー＋フォーム
		case 'post':
		
		$wpsm->set(array(
		 	'post_id'	=> $_GET['id'],
		 	'add'			=> true
		));
		
		echo '<div id="icon-options-general" class="icon32"></div>';
		wpsm_view_post($_GET['id']);
		wpsm_view_calendar(array(
		 	'post_id'		=> $_GET['id']
		));
		break;
		
		// 日付編集画面
		case 'date':
		
		$post_id = get_postid_from_date($_GET['id']);
		$wpsm->set();
		
		if (!isset($_POST['wpsm_day'])) {
			echo '<div id="icon-options-general" class="icon32"></div>';
			echo '<h2><a href="?page=wpsm&mode=post&id='.$post_id.'">'.get_the_title($post_id).'</a></h2>';
			wpsm_view_date($_GET['id']);
		} else {
			echo '<div id="icon-options-general" class="icon32"></div>';
			wpsm_view_post($post_id);
			wpsm_view_calendar(array(
			 	'post_id'		=> $post_id,
			 	'mode'			=> 'post'
			));
		}
		break;
		
		// 月別スケジュールカレンダー
		default:
		echo '<div id="icon-options-general" class="icon32"></div>';
		echo "<h2>スケジュール</h2>";
		
		$now		= date('Y-m-01');
		$datestr	= ($_GET['date']) ? date('Y-m-d', strtotime($_GET['date'])) : $now;
		$date_a	= explode('-', $datestr);
		
		wpsm_view_calendar(array(
		 	'date'		=> $datestr,
		 	'term_by'	=> 'month',
		 	'term'		=> 1,
		 	'pager'		=> true,
		 	'now'			=> $now,
		 	'next'		=> date('Y-m-d', mktime(0, 0, 0, $date_a[1]+1, $date_a[2], $date_a[0])),
		 	'prev'		=> date('Y-m-d', mktime(0, 0, 0, $date_a[1]-1, $date_a[2], $date_a[0])),
		 	'datestr_last'	=> date('Y-m-d', mktime(0, 0, 0, $date_a[1]+1, $date_a[2]-1, $date_a[0]))
		));
		break;
	}
	
	echo '</div>';
}

/* ----------------------------------------------------------
	
	View Post
	
---------------------------------------------------------- */

function wpsm_view_post($post_id) {
global $post;
#print_r($post);
$post = get_post($post_id);
the_post();
?>

<script type="text/javascript">
(function($) {
	$(function() {
   	$('.sc-data').datepicker({ dateFormat: 'yy-mm-dd' });
	});
})(jQuery);
</script>
	<h2><?php echo get_the_title($post_id)?></h2>
	<div class="box">
	<form method="post" enctype="multipart/form-data">
	<p class="day">
		<label>日付</label>
		<input type="text" name="wpsm_day[0]" size="20" tabindex="1" class="sc-data" autocomplete="off"<?php if(isset($_POST['wpsm_day'][0])):?> value="<?php echo $_POST['wpsm_day'][0]?>"<?php endif;?> />
	</p>
	<p>
		<label style="vertical-align:top">時間／概要</label>
		<textarea name="wpsm_time[0]" cols="50" rows="4" class="sc-description"><?php if(isset($_POST['wpsm_time'][0])):?><?php echo stripslashes($_POST['wpsm_time'][0])?><?php endif;?></textarea>
	</p>
	<p class="status">
		<input type="radio" name="wpsm_status[0]" value="0"<?php if((isset($_POST['wpsm_status'][0]) && $_POST['wpsm_status'][0] == 0) || !isset($_POST['wpsm_status'][0])):?> checked="checked"<?php endif;?> />
		<label>予約を受け付けない</label>
		<input type="radio" name="wpsm_status[0]" value="1"<?php if(isset($_POST['wpsm_status'][0]) && $_POST['wpsm_status'][0] == 1):?> checked="checked"<?php endif;?> />
		<label>メールフォームで予約を受け付ける</label>
		<input type="radio" name="wpsm_status[0]" value="2"<?php if(isset($_POST['wpsm_status'][0]) && $_POST['wpsm_status'][0] == 2):?> checked="checked"<?php endif;?> />
		<label>別サイトで予約を受け付ける</label>
		<input type="radio" name="wpsm_status[0]" value="3"<?php if(isset($_POST['wpsm_status'][0]) && $_POST['wpsm_status'][0] == 3):?> checked="checked"<?php endif;?> />
		<label>予約受付終了</label>
	</p>
	<p class="url">
		<label>URL</label>
		<input type="text" name="wpsm_url[0]" size="40" tabindex="1" class="sc-url" autocomplete="off"<?php if(isset($_POST['wpsm_url'][0])):?> value="<?php echo $_POST['wpsm_url'][0]?>"<?php endif;?> />
	</p>
	<p>
		<input type="submit" name="publish" id="publish" class="button-primary" value="<?php _e('追加登録')?>" tabindex="5" accesskey="p">
	</p>
	</form>
</div>

<?php
}

/* ----------------------------------------------------------
	
	View Date
	
---------------------------------------------------------- */

function wpsm_view_date($date_id) {
	global $wpsm;
	$dat = $wpsm->get(array('include' => explode(',', $date_id)));
	
	foreach ($dat as $d):
?>

<form method="POST">
<script type="text/javascript">
(function($) {
	$(function() {
   	$('.sc-data').datepicker({dateFormat:'yy-mm-dd'});
	});
})(jQuery);
</script>
<input type="hidden" name="wpsm_date_id[0]"<?php if(isset($d->date_id)):?> value="<?php echo $d->date_id?>"<?php endif;?> />
	<p class="day">
		<label>日付</label>
		<input type="text" name="wpsm_day[0]" size="50" tabindex="1"  id="sc-data" class="sc-data" autocomplete="off"<?php if(isset($d->date)):?> value="<?php echo $d->date?>"<?php endif;?> />
	</p>
	<p class="time">
		<label style="vertical-align:top">時間／概要</label>
		<textarea name="wpsm_time[0]" cols="50" rows="4"><?php if(isset($d->time)):?><?php echo stripslashes($d->time)?><?php endif;?></textarea>
	</p>
	<p class="status">
		<input type="radio" name="wpsm_status[0]" value="0"<?php if(isset($d->status) && $d->status == 0):?> checked="checked"<?php endif;?> />
		<label>予約を受け付けない</label>
		<input type="radio" name="wpsm_status[0]" value="1"<?php if(isset($d->status) && $d->status == 1):?> checked="checked"<?php endif;?> />
		<label>メールフォームで予約を受け付ける</label>
		<input type="radio" name="wpsm_status[0]" value="2"<?php if(isset($d->status) && $d->status == 2):?> checked="checked"<?php endif;?> />
		<label>別サイトで予約を受け付ける</label>
		<input type="radio" name="wpsm_status[0]" value="3"<?php if(isset($d->status) && $d->status == 3):?> checked="checked"<?php endif;?> />
		<label>予約受付終了</label>
	</p>
	<p class="URL">
		<label>URL</label>
		<input type="text" name="wpsm_url[0]" size="50" tabindex="1" id="sc-URL" autocomplete="off"<?php if(isset($d->url)):?> value="<?php echo $d->url?>"<?php endif;?> />
	</p>
	
	<input type="submit" name="publish" id="publish" class="button-primary" value="<?php _e('更新')?>" tabindex="5" accesskey="p">
</form>

<form method="POST" style="padding:20px 0 0 0;border-top:1px solid #CCC;margin:20px 0 0 0;text-align:right;">
	<input type="hidden" name="wpsm_date_id[0]"<?php if(isset($d->date_id)):?> value="<?php echo $d->date_id?>"<?php endif;?> />
	<input type="hidden" name="wpsm_refresh_post_id[0]"<?php if(isset($post)):?> value="<?php echo $post->ID?>"<?php endif;?> />
	<input type="hidden" name="wpsm_day[0]" value="" />
	<input type="submit" name="publish" id="publish" class="button-secondary" value="<?php _e('削除')?>" tabindex="5" accesskey="p">
</form>


<?php
endforeach;
}


/* ----------------------------------------------------------
	
	Generate Schedule Calendar
	
---------------------------------------------------------- */

function wpsm_view_calendar($user_opt = array()) {
	global $wpsm, $is_link;
	
	$opt = array_merge(array(
		'pager' => false,
		'mode'	=> $_GET['mode']
	), $user_opt);
	
	$dat = $wpsm->get($opt);
	
	if ($opt['pager']) get_monthly_pager($opt['now'], $opt['next'], $opt['prev']);
?>

<form>
<table class="widefat">
	<thead>
		<?php if($opt['mode']!="post"):?><tr>
			<th scope="col" colspan="25"><?php echo $opt['date']?><?php if($opt['datestr_last']):?> 〜 <?php echo $opt['datestr_last']?><?php endif;?></th>
		</tr><?php endif;?>
		<tr>
		<?php if($opt['mode'] != "post") {?> <th scope="col" colspan="1">タイトル</th><?php } ?>
			<th scope="col" colspan="1">日付</th>
			<th scope="col" colspan="1">時間／概要</th>
			<th scope="col" colspan="1">予約状態</th>
			<th scope="col" colspan="1">URL</th>
		</tr>
	</thead>
	<tbody>
<?php	 
if (!empty($dat)) {
	foreach ($dat as $d) {
		$post = get_post($d->post_id);
		//$check = ($is_link) ? '<img src="'.admin_url().'images/yes.png" />' : " -";
		
		echo	'<tr class="mainraw">';
		if($opt['mode']=="post"){}
		else {
		echo  '<td><a href="admin.php?page=wpsm&mode=post&id='.$post->ID.'">'.$post->post_title.'</a></td>';}
		echo  '<td><a href="admin.php?page=wpsm&mode=date&id='.$d->date_id.'">'.$d->date.'</a></td>';		
		echo	'<td>'.$d->time.'</td>';
		echo	'<td>'.$is_link[$d->status].'</td>';
		echo	'<td>'.$d->url.'</td>';
		echo	'</tr>';
	}
}
?>
	</tbody>
</table>
</form>
<?php
	if ($opt['pager']) get_monthly_pager($opt['now'], $opt['next'], $opt['prev']);
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
	var count = 0;
	var alcount = 0;
	var wpsm = "wpsm_daybox";
	var wpsmaf = ".wpsm_daybox0";	
	count = $(".box").length - 1;
	alcount = count;
	if (count > 0) {
		$(".wpsm_minus").css("display","inline");
	}
	
	var basehtml = $('.wpsm_daybox0').html();
	$('body').after('<div class="' + wpsm + ' box" id="wpsm_tmpbox">' + basehtml + '</div>');
	$('#wpsm_tmpbox').hide().find('input').attr({'name':''});
	
	var html = $('#wpsm_tmpbox').html();
	
	$('.wpsm_plus').live('click', function() {
			console.log('count:' + count);
			wpsmaf=".wpsm_daybox" + count;
			
			count++;
			alcount++;
			wpsm=wpsm+count;
			
			var wpsmadd='<div class="' + wpsm + ' box">' + html + '</div>';
			
			//console.log(wpsmaf);
			$(wpsmaf).after(wpsmadd);
			//$("."+wpsm).append(html);
			
			wpsm_day				= "wpsm_day[" + count + "]";
			wpsm_time			= "wpsm_time[" + count + "]";
			wpsm_status			= "wpsm_status[" + count + "]";
			wpsm_class			= "wpsm_class"+count;
			wpsm_URL				= "wpsm_url[" + count + "]";
			
			var obj = $("."+wpsm);
			var parent = $(this).parent().parent();
			
			obj.find("input.sc-data").attr({name:wpsm_day,value:"", id:wpsm_class, class:""});
			obj.find("textarea.sc-time").attr({name:wpsm_time,value:""});
			obj.find("input.sc-status").each(function() {
				var opt = {name:wpsm_status,value:$(this).val()};
				//if ($(this).attr('checked') == 'checked') opt.checked = 'checked';
				$(this).attr(opt);
			});
			obj.find("input.sc-url").attr({name:wpsm_URL,value:""});
			
			$(".wpsm_minus").css("display","block");
						
			$("#"+wpsm_class).datepicker({ dateFormat: 'yy-mm-dd' });
			wpsmaf = wpsm;
			wpsm = "wpsm_daybox";
		});
		$('.wpsm_minus').live('click', function() {
			$(this).parent().parent().empty();				
			alcount--;
			if (1 > alcount) {
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
	</p>
	<p class="wpsm_box_time">
		<label style="vertical-align:top;">時間／概要</label>
		<textarea name="wpsm_time[<?php echo $c?>]" cols="49" rows="3" class="sc-time"><?php if(isset($d->time)):?><?php echo stripslashes($d->time)?><?php endif;?></textarea>
	</p>
	<p class="wpsm_box_status">
		<input type="radio" name="wpsm_status[<?php echo $c?>]" value="0"<?php if((isset($d->status) && $d->status == 0) || !isset($d->status)):?> checked="checked"<?php endif;?> class="sc-status" />
		<label>予約を受け付けない</label>
		<input type="radio" name="wpsm_status[<?php echo $c?>]" value="1"<?php if(isset($d->status) && $d->status == 1):?> checked="checked"<?php endif;?> class="sc-status" />
		<label>メールフォームで予約を受け付ける</label>
		<input type="radio" name="wpsm_status[<?php echo $c?>]" value="2"<?php if(isset($d->status) && $d->status == 2):?> checked="checked"<?php endif;?> class="sc-status" />
		<label>別サイトで予約を受け付ける</label>
		<input type="radio" name="wpsm_status[<?php echo $c?>]" value="3"<?php if(isset($d->status) && $d->status == 3):?> checked="checked"<?php endif;?> class="sc-status" />
		<label>予約受付終了</label>
	</p>
	<p class="wpsm_box_url">
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
?>