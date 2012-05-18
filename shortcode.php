<?php

function wpsm_shortcode($atts) {
	extract(shortcode_atts(array(
		'post_id'		=> false,
		'include'		=> false,
		'date'			=> false,
		'group'			=> false,
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
	
	$date = $wpsm->get($atts);
	
	$output = "";
	
	$output .= '<div id="schedule-table">';
	$output .= '<dl class="tbl_clearfix">';
	
	foreach ($date as $k => $d) {
		$weekday = strtolower(date('D', $d->date));
		$p = get_post($d->post_id);
		$output .= '<dt class="day week-'.$weekday.'" rel="day-'.$k.'">';
		$output .= date('n月j日', strtotime($d->date)).'（'. wp_get_weekday($d->date).'）</dt>';
		$output .= '<dd class="day-'.$k.' week-'.$weekday.'" rel="day-'.$k.'">';
			$output .= '<dl class="item">';
				$output .= '<dt>'.$d->time.'</dt>';
				$output .= '<dd>'.$d->description.'</dd>';
			$output .= '</dl>';
		$output .= '</dd>';
	}
	
	$output .= "</dl>";
	$output .= '</div>';
	
	return $output;
}
add_shortcode('wpsm', 'wpsm_shortcode');

?>