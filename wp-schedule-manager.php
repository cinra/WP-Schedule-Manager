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
</style>

<script type="text/javascript">
(function($) {
	$(function() {
		$('.wpsm_plus').live('click', function() {
			var box = $(this);
			//box.parent().append(box.get(0));
			box.parent().parent().append(box.get(0));
			console.log(box.get(0));
		});
	});
})(jQuery);
</script>

<div class="wpsm_daybox">
	<p>がはははははは</p>
	<p><a class="wpsm_plus">+</a></p>
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