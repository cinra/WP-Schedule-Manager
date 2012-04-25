<?php

class wp_schedule_manager {
	
	public function get($usr_opt = array()) {
		
		global $wpdb;
		
		$opt = array(
			'post_id'		=> false,
			'include'		=> false,
			'date'			=> false,
			'group'			=> false,
			'post_type'		=> false,
			'term_by'		=> 'day',
			'term'			=> 1,
			'order_by'		=> 'date',
			'order'			=> 'asc'
		);
		$opt = array_merge($opt, $usr_opt);
		
		$sql = array();
		
		if ($opt['post_id']) {// 記事ID
			$sql['where'][] = "`post_id` = '".$opt['post_id']."'";
		} else if ($opt['category']) {// カテゴリフィルター
			$sql['str'] = "SELECT * FROM ".WPSM_DB_TABLENAME;
			$sql['str'] .= " INNER JOIN wp_term_relationships ON wp_wpsm_cal.post_id = wp_term_relationships.object_id";
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
			$sql['where'][] = $tmpsql;
		}
		
		// 投稿タイプでフィルター
		if ($opt['post_type']) {
			$post_type = explode(',', $opt['post_type']);
			$tmpsql = "(`post_type` = '";
			$tmpsql .= implode("' OR `post_type` = '", $post_type);
			$tmpsql .= "')";
			$sql['where'][] = $tmpsql;
		}
		
		// 日付を処理
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
		
		// 表示する記事を選択
		if ($opt['include']) {
			$inc = array();
			$tmparr = array();
			if (!is_array($opt['include'])) {$inc[] = $opt['include'];} else {$inc = $opt['include'];}
			foreach ($inc as $i) $tmparr[] = "ID = ".$i;
			$sql['where'][] = implode(' OR ', $tmparr);
		}
		
		// SQL作成
		if (empty($sql['str'])) $sql['str'] = "SELECT * FROM ".WPSM_DB_TABLENAME;
		if (!empty($sql['where'])) $sql['str'] .= " WHERE ".implode(' AND ', $sql['where']);
		
		$sql['str'] .= " ORDER BY `".$opt['order_by']."` ".$opt['order'];
		
		$dat = $wpdb->get_results($sql['str']);
		
		if (!$opt['group']) return $dat;
		
		return $this->grouping($opt['group'], $dat);//グルーピング
	}
	
	public function set($usr_opt = array()) {
		
		global $wpdb;
		
		if (!isset($_POST['wpsm_date_id']) && !isset($_POST['wpsm_day'])) return;
		
		$opt = array(
			'post_id'	=> get_the_ID()
		);
		if (is_array($usr_opt)) $opt = array_merge($opt, $usr_opt);
		
		if (!isset($opt['post_id'])){//日付ID単位で更新
			foreach($_POST['wpsm_date_id'] as $k => $d){
				if ($_POST['wpsm_yoyaku'][$k] == 'on') $_POST['wpsm_yoyaku'][$k] = true;
				$wpdb->update(WPSM_DB_TABLENAME, array(
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
					'post_type'		=> $_POST['post_type'],
					'status'		=> $_POST['wpsm_yoyaku'][$k],
					'url'			=> $_POST['wpsm_url'][$k],
					'post_id'		=> $opt['post_id']
				));
			}
		}
	}
	
	public function grouping($group = 'daily', $raw = array()) {
		$dat = array();
		if (!empty($raw) && is_array($raw)) {
			switch ($group) {
				case 'daily':
				default:
					foreach ($raw as $r) {
						$dat[$r->date][] = $r;
					}
				break;
			}
		}
		return $dat;
	}
	
	function __construct() {
		
	}
}


?>