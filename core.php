<?php

// Tmp

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

?>