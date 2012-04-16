<?php

if (!defined('WPSM_DB_TABLENAME')) define('WPSM_DB_TABLENAME', 'wp_wpsm_cal');//table name

// Tmp

class wp_schedule_manager {
	
	public function get($usr_opt = array()) {
		
		global $wpdb;
		
		$opt = array(
			'post_id'		=> false,
			'include'		=> false,
			'date'			=> false,
			'term_by'		=> 'day',
			'term'			=> 1,
			'order_by'		=> 'date',
			'order'			=> 'asc'
		);
		$opt = array_merge($opt, $usr_opt);
		
		$sql = array();
		
		
		if ($opt['post_id']) {// ÊäïÁ®øË®ò‰∫ãÊåáÂÆö
			$sql['where'][] = "`post_id` = '".$opt['post_id']."'";
		} else if ($opt['category']) {// „Ç´„ÉÜ„Ç¥„É™„Éï„Ç£„É´„Çø„Éº
			//SELECT * FROM wp_wpsm_cal  WHERE (`date` >= '2011-04-16' AND `date` < '2015-04-16') AND term_taxonomy_id = 1 ORDER BY `date` asc
			
			$sql['str'] = "SELECT * FROM ".WPSM_DB_TABLENAME."  INNER JOIN wp_term_relationships ON wp_wpsm_cal.post_id = wp_term_relationships.object_id";
			
			$tmpsql .= "(`term_taxonomy_id` = ";
			
			$cat = explode(',', $opt['category']);
			if (!empty($cat)) {
				foreach($cat as $c) {
					if (!is_integer($c)) {
						$tmpobj = get_term_by('slug', $c, 'category');
						$c = $tmpobj->term_id;
					}
					$catarr[] = $c;
				}
				$tmpsql .= implode(' OR `term_taxonomy_id` = ', $catarr);
			}
			
			$tmpsql .= ")";
			#exit($tmpsql);
			$sql['where'][] = $tmpsql;
			//print_r($cat);exit;
		}
		
		// Êó•‰ªòÔºàÈñãÂßãÊó•ÊôÇ„Å®ÁµÇ‰∫ÜÊó•ÊôÇÔºâ
		if ($opt['date']) {
			$opt['date'] = strtotime($opt['date']);
			$opt['date'] = date('Y-m-d', $opt['date']);
			
			$date_end = explode('-', $opt['date']);
			if ($opt['term_by'] == 'year')	$date_end[0] = (int)$date_end[0] + $opt['term'];
			if ($opt['term_by'] == 'month')	$date_end[1] = (int)$date_end[1] + $opt['term'];
			if ($opt['term_by'] == 'day')	$date_end[2] = (int)$date_end[2] + $opt['term'];
			if ($opt['term_by'] == 'week')	$date_end[2] = (int)$date_end[2] + ($opt['term']*7);
			
			$end_str = date('Y-m-d', mktime(0, 0, 0, $date_end[1], $date_end[2], $date_end[0]));
			
			$sql['where'][] = "(`date` >= '".$opt['date']."' AND `date` < '".$end_str."')";
		}
		
		if ($opt['include']) {
			$inc = array();
			$tmparr = array();
			if (!is_array($opt['include'])) {$inc[] = $opt['include'];} else {$inc = $opt['include'];}
			foreach ($inc as $i) $tmparr[] = "ID = ".$i;
			#echo $tmpsql;
			$sql['where'][] = implode(' OR ', $tmparr);
		}
		
		// SQL‰ΩúÊàê
		if (empty($sql['str'])) $sql['str'] = "SELECT * FROM ".WPSM_DB_TABLENAME;
		if (!empty($sql['where'])) $sql['str'] .= " WHERE ".implode(' AND ', $sql['where']);
		
		$sql['str'] .= " ORDER BY `".$opt['order_by']."` ".$opt['order'];
		
		#exit($sql['str']);
		#print_r($wpdb->get_results($sql['str']));
		#exit();
		
		// Test
		if (!is_admin()) {
			#echo '<br />'.strtotime($opt['date']);
			#echo '<br />'.time();
		}
		
		return $wpdb->get_results($sql['str']);
	}
	
	public function set($usr_opt = array()) {
		
		global $wpdb;
		
		$opt = array(
			'post_id'	=> get_the_ID()
		);
		if (is_array($usr_opt)) $opt = array_merge($opt, $usr_opt);
		
		#print_r($_POST);exit;
		
		if (!isset($opt['post_id'])){//日付ID単位で更新
			foreach($_POST['wpsm_date_id'] as $k => $d){
				if ($_POST['wpsm_yoyaku'][$k] == 'on') $_POST['wpsm_yoyaku'][$k] = true;
				$wpdb->update(WPSM_DB_TABLENAME,
					array(
						'date'			=> $_POST['wpsm_day'][$k],
						'time'			=> $_POST['wpsm_time'][$k],
						'description'	=> $_POST['wpsm_description'][$k],
						'status'		=> $_POST['wpsm_yoyaku'][$k],
						'url'			=> $_POST['wpsm_url'][$k]
					), array('ID' => $_POST['wpsm_date_id'][$k]));
			}
		} else {//記事単位で追加
			$wpdb->query("DELETE FROM ".WPSM_DB_TABLENAME." WHERE post_id = '".$opt['post_id']."'");
			foreach($_POST['wpsm_day'] as $k => $d){
				if ($_POST['wpsm_yoyaku'][$k] == 'on') $_POST['wpsm_yoyaku'][$k] = true;
				$wpdb->insert('wp_wpsm_cal', array(
					'date'			=> $_POST['wpsm_day'][$k],
					'time'			=> $_POST['wpsm_time'][$k],
					'description'	=> $_POST['wpsm_description'][$k],
					'description'	=> $_POST['wpsm_description'][$k],
					'status'		=> $_POST['wpsm_yoyaku'][$k],
					'url'			=> $_POST['wpsm_url'][$k],
					'post_id'		=> $opt['post_id']
				));
			}
		}
	}
	
	function __construct() {
		
		//exit(dirname(__FILE__));
	}
}


?>