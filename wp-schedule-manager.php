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

class wp_schedule_manager {
	
	public function get($usr_opt = array()) {
		
		global $wpdb;
		
		$opt = array(
			'post_id'	=> false,
			'date'		=> false
		);
		$opt = array_merge($opt, $usr_opt);
		
		$where = array();
		
		if ($opt['post_id']) $where[] = '`post_id` = '.$opt['post_id'];
		
		$sql = "SELECT * FROM ".WPSM_DB_TABLENAME." WHERE ".implode(' AND ', $where);
		#exit($sql);
		print_r($wpdb->get_results($sql));
		exit();
		
		// Test
		if (!is_admin()) {
			#print_r($opt);
			
			echo '<br />'.strtotime($opt['date']);
			echo '<br />'.time();
			
			#exit;
		}
	}
	
	public function set($usr_opt = array()) {
		
		global $wpdb;
		
		$opt = array(
			'post_id'	=> get_the_ID()
		);
		if (is_array($usr_opt)) $opt = array_merge($opt, $usr_opt);
		
		$wpdb->query("DELETE FROM ".WPSM_DB_TABLENAME." WHERE post_id = '".$opt['post_id']."'");
		foreach($_POST['wpsm_day'] as $k => $d){
			$wpdb->insert('wp_wpsm_cal', array(
				'date'			=> $_POST['wpsm_day'][$k],
				'time'			=> $_POST['wpsm_time'][$k],
				'description'	=> $_POST['wpsm_description'][$k],
				'url'			=> $_POST['wpsm_url'][$k],
				'post_id'		=> $opt['post_id']
			));
		}
	}
	
	function __construct() {
		
		if (!defined('WPSM_DB_TABLENAME')) define('WPSM_DB_TABLENAME', 'wp_wpsm_cal');//table name
		
		
		//exit(dirname(__FILE__));
	}
}

add_action('save_post', array(&$wpsm, 'set'), 10);

add_action('admin_menu', 'wpsm_add_sidemenu');
function wpsm_add_sidemenu() {
	add_menu_page('schedule', 'schedule', 7, 'wpsm', 'admin_schedule_list');
 	add_submenu_page('wpsm', 'schedule', 7, 'wpsm', 'admin_schedule_list');
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
			<th scope="col" colspan="2">テスト</th>
		</tr>
	</thead>
	<tbody>
		<tr class="mainraw">
			<td>テスト</td>
			<td>テスト</td>
		</tr>
		<tr class="mainraw">
			<td>テスト</td>
			<td>テスト</td>
		</tr>
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