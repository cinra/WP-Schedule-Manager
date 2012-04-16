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
		
		$sql = array();
		
		
		if ($opt['post_id']) {// 投稿記事指定
			$sql['where'][] = "`post_id` = '".$opt['post_id']."'";
		} else if ($opt['category']) {// カテゴリフィルター
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
		
		// 日付（開始日時と終了日時）
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
		
		// SQL作成
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