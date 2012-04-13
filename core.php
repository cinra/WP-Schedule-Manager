<?php

if (!defined('WPSM_DB_TABLENAME')) define('WPSM_DB_TABLENAME', 'wp_wpsm_cal');//table name

// Tmp

class wp_schedule_manager {
	
	public function get($usr_opt = array()) {
		
		global $wpdb;
		
		$opt = array(
			'post_id'		=> false,
			'date'			=> false,
			'term_by'		=> 'day',
			'term'			=> 1,
			'order_by'		=> 'date',
			'order'			=> 'asc'
		);
		$opt = array_merge($opt, $usr_opt);
		#print_r($opt);#exit;
		
		$sql = "SELECT * FROM ".WPSM_DB_TABLENAME;
		$where = array();
		
		if ($opt['post_id']) $where[] = "`post_id` = '".$opt['post_id']."'";
		
		if ($opt['date']) {
			$opt['date'] = strtotime($opt['date']);
			$opt['date'] = date('Y-m-d', $opt['date']);
			
			$date_end = explode('-', $opt['date']);
			if ($opt['term_by'] == 'year')	$date_end[0] = (int)$date_end[0] + $opt['term'];
			if ($opt['term_by'] == 'month')	$date_end[1] = (int)$date_end[1] + $opt['term'];
			if ($opt['term_by'] == 'day')	$date_end[2] = (int)$date_end[2] + $opt['term'];
			if ($opt['term_by'] == 'week')	$date_end[2] = (int)$date_end[2] + ($opt['term']*7);
			
			#print_r($date_end);
			
			$end_str = date('Y-m-d', mktime(0, 0, 0, $date_end[1], $date_end[2], $date_end[0]));
			
			$where[] = "(`date` >= '".$opt['date']."' AND `date` < '".$end_str."')";
		}
		
		if (!empty($where)) $sql .= " WHERE ".implode(' AND ', $where);
		
		$sql .= " ORDER BY `".$opt['order_by']."` ".$opt['order'];
		
		#exit($sql);
		print_r($wpdb->get_results($sql));
		#exit();
		
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
		
		//exit(dirname(__FILE__));
	}
}


?>