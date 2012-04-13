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
		$opt = array(
			'date'		=> 'Today'
		);
		$opt = array_merge($opt, $usr_opt);
		
		
		// Test
		if (!is_admin()) {
			print_r($opt);
			
			echo '<br />'.strtotime($opt['date']);
			echo '<br />'.time();
			
			exit;
		}
	}
	
	public function set($usr_opt = array()) {
		
		$opt = array(
			'post_id'	=> get_the_ID()
		);
		if (is_array($usr_opt)) $opt = array_merge($opt, $usr_opt);
		
		echo 'POSTID: '.$opt['post_id'];
		#print_r($_POST);
		
		//add_post_custom();
		
		exit();
		
	}
	
	function __construct() {
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
	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br></div>';
	echo "<h2>スケジュール</h2>";
	echo <<<EOF
<div class="subsubsub"></div>

<table class="widefat" style="margin-bottom: 1em;">
	<tr>
		<td>UUUUUU</td>
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