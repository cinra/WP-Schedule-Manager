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
			$sql['where'][] = "(wp_posts.`post_status` = 'publish')";
			$sql['str'] = "SELECT * FROM ".WPSM_DB_TABLENAME;
			$sql['str'] .= " (INNER JOIN wp_term_relationships ON wp_wpsm_cal.post_id = wp_term_relationships.object_id)";
			$sql['str'] .= " INNER JOIN wp_posts ON wp_wpsm_cal.post_id = wp_posts.ID";
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
			$tmpsql = "(wp_wpsm_cal.`post_type` = '";
			$tmpsql .= implode("' OR wp_wpsm_cal.`post_type` = '", $post_type);
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
			if ($opt['term_by'] == 'day')		$date_end[2] = (int)$date_end[2] + $opt['term'];
			if ($opt['term_by'] == 'week')	$date_end[2] = (int)$date_end[2] + ($opt['term']*7);

			$end_str = date('Y-m-d', mktime(0, 0, 0, $date_end[1], $date_end[2], $date_end[0]));

			$sql['where'][] = "(`date` >= '".$opt['date']."' AND `date` < '".$end_str."')";
		}

		// 表示する記事を選択
		if ($opt['include']) {
			$inc = array();
			$tmparr = array();
			if (!is_array($opt['include'])) {$inc[] = $opt['include'];} else {$inc = $opt['include'];}
			foreach ($inc as $i) $tmparr[] = "wp_wpsm_cal.`ID` = ".$i;
			$sql['where'][] = implode(' OR ', $tmparr);
		}

		// SQL作成
		if (empty($sql['str'])) {
			$sql['where'][] = "(wp_posts.`post_status` = 'publish')";
			$sql['str'] = "SELECT *, wp_wpsm_cal.`ID` AS date_id FROM ".WPSM_DB_TABLENAME;
			$sql['str'] .= " INNER JOIN wp_posts ON wp_wpsm_cal.post_id = wp_posts.ID";
		}
		if (!empty($sql['where'])) $sql['str'] .= " WHERE ".implode(' AND ', $sql['where']);

		$sql['str'] .= " ORDER BY `".$opt['order_by']."` ".$opt['order'];

		$dat = $wpdb->get_results($sql['str']);
		#echo $wpdb->last_query;

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

		if (!isset($opt['post_id']) && $_POST['wpsm_date_id']){//日付ID単位で更新
			foreach($_POST['wpsm_date_id'] as $k => $d){
				if (empty($_POST['wpsm_day'][$k])) {//日付が未入力の場合
					$wpdb->query("DELETE FROM ".WPSM_DB_TABLENAME." WHERE ID = '".$_POST['wpsm_date_id'][$k]."'");
				} else {
					$wpdb->update(WPSM_DB_TABLENAME, array(
						'date'			=> $_POST['wpsm_day'][$k],
						'time'			=> $_POST['wpsm_time'][$k],
						'description'	=> "",
						'status'			=> $_POST['wpsm_status'][$k],
						'ticket'			=> $_POST['wpsm_ticket'][$k],
						'url'			=> $_POST['wpsm_url'][$k]
					), array('ID' => $_POST['wpsm_date_id'][$k]));
				}
			}
		} else {//記事単位で追加
			if ($opt['add'] != true) $wpdb->query("DELETE FROM ".WPSM_DB_TABLENAME." WHERE post_id = '".$opt['post_id']."'");
			foreach($_POST['wpsm_day'] as $k => $d){
				$wpdb->insert('wp_wpsm_cal', array(
					'date'			=> $_POST['wpsm_day'][$k],
					'time'			=> $_POST['wpsm_time'][$k],
					'description'	=> "",
					'post_type'		=> $_POST['post_type'],
					'status'			=> $_POST['wpsm_status'][$k],
					'ticket'			=> $_POST['wpsm_ticket'][$k],
					'url'				=> $_POST['wpsm_url'][$k],
					'post_id'		=> $opt['post_id']
				));
			}
		}

	}

	public function grouping($group = 'daily', $raw = array()) {
		$dat = array();
		if (!empty($raw) && is_array($raw)) {
			switch ($group) {
				case 'daily-id':
					foreach ($raw as $r) {
						$dat_not_sorted[$r->date][$r->ID][] = $r;
					}
					$arr = array();
					$not_first = false;
					foreach ($dat_not_sorted as $n => $s) {//日付毎
						foreach ($s as $k => $d) {//記事毎（$k:記事ID）
							foreach($d as $kk => $dd) {$arr[$kk] = $dd->time;}//時間順にソート
							asort($arr);

							foreach ($arr as $ak => $a) {
								if (!$not_first) $sorter[$k] = $a;
								$dat_tmp[$k][] = $d[$ak];
								$not_first = true;
							}

							unset($arr);
							$not_first = false;
						}
						asort($sorter);

						foreach($sorter as $sk => $sv) {
							//foreach ($dat_tmp[$k] as $dddd) $arr2[] = $dddd;
							$dat[$n][$sk] = $dat_tmp[$sk];
						}
						unset($arr2);
						unset($sorter);
						unset($dat_tmp);

						$not_first = false;
					}
				break;

				case 'daily':
				default:
					foreach ($raw as $r) {
						$dat_not_sorted[$r->date][] = $r;
					}
					$arr = array();
					foreach ($dat_not_sorted as $k => $d) {

						foreach($d as $kk => $dd) {$arr[$kk] = $dd->time;}
						asort($arr);

						foreach ($arr as $ak => $a) $dat[$k][] = $d[$ak];
						unset($arr);
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