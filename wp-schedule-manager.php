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