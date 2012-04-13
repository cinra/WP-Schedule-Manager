<?php

// Tmp

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


?>