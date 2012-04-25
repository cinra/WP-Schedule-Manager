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
include(dirname(__FILE__).'/adminmenu.php');

wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-datepicker',  FILE_URL . 'jquery.ui.datepicker.js', array('jquery','jquery-ui-core') );

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
		
		$wpsm->get(array(
			
		));
		
	} elseif ($_GET['date_id']) {//スケジュール編集
		
		#print_r($_POST);
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
		$datestr	= ($_GET['date']) ? $_GET['date'] : $now;
		$date_a		= explode('-', $datestr);
		
		#print_r($date_a);
		
		echo '<div id="icon-options-general" class="icon32"><br></div>';
		echo "<h2>スケジュール</h2>";
		
		$next = date('Y-m-d', mktime(0, 0, 0, $date_a[1]+1, $date_a[2], $date_a[0]));
		$prev = date('Y-m-d', mktime(0, 0, 0, $date_a[1]-1, $date_a[2], $date_a[0]));
		
		get_monthly_pager($now, $next, $prev);
?>
<?php /* table class="widefat" style="margin-bottom: 1em;">
	<tr>
		<td>
			<select><?php echo $options;?></select>
		</td>
	</tr>
</table */ ?>



<?php
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
			$pp = get_post($d->post_id);
			$is_link = $d->status;
			$check = ($is_link) ? "有" : "無";
			
			#print_r($check);
			echo	'<tr class="mainraw">';
			echo    '<td><a href="post.php?post='.$pp->ID.'&action=edit">'.$pp->post_title.'</a></td>';
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

?>