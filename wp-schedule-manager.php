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
	
	function __construct() {
		//exit(dirname(__FILE__));
	}
}

add_action('admin_menu', 'wpsm_add_sidemenu');
function wpsm_add_sidemenu() {
	
	add_menu_page('schedule', 'schedule', 7, __FILE__, 'toppage');
	
}

function toppage() {
	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br></div>';
	echo "<h2>スケジュール</h2>";
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