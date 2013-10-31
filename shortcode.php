<?php

function wpsm_shortcode($atts) {
	extract(shortcode_atts(array(
		'post_id'		=> false,
		'include'		=> false,
		'date'			=> false,
		'group'			=> 'daily',
		'post_type'		=> false,
		'term_by'		=> 'day',
		'term'			=> 1,
		'order_by'		=> 'date',
		'order'			=> 'asc'
	), $atts));
	
	global $wpsm;
	
	if (!$atts['date'] && !$atts['post_id']) {
		global $post;
		$atts['post_id'] = $post->ID;
	}
	
	$atts['group'] = 'daily';
	
	$date = $wpsm->get($atts);
	
	$output = "";
	
	$output .= '<table class="schedule-list">';
	$output .= '<tr><th class="schedule-list-day">日程</th><th class="schedule-list-time">時間</th><th class="schedule-list-preserve">予約</th></tr>';
	
	#$output .= '<tr>';
	
	foreach ($date as $day => $d) {
		$weekday = strtolower(date('D', $day));
		$output .= '<tr>';
		$output .= '<td class="day-head" rowspan="'.count($day).'">'.date('n月j日', strtotime($day)).'（'. wp_get_weekday($day).'）</td>';
		
		foreach($d as $i => $dd) {
			if ($i != 0) $output .= '<tr>';
			$output .= '<td>'.$dd->time.'</td>';
			$output .= '<td>';
			if ($dd->status && !empty($dd->url)) $output .= '<a href="'.$dd->url.'" target="_blank" class="btn_preservation">予約する</a>';
			$output .= '</td>';
			if ($i != 0) $output .= '</tr>';
		}
		
		$output .= '</td>';
		$output .= '</tr>';
	}
	
	#$output .= "</tr>";
	$output .= '</table>';
	
	return $output;
}
add_shortcode('wpsm', 'wpsm_shortcode');

?>