<?php

function wpsm_init_adminmenu() {
	add_meta_box('schedule_manager', __('schedule'), 'wpsm_output_metabox', 'post', 'normal');
}
add_action('admin_init', 'wpsm_init_adminmenu');

function wpsm_output_metabox() {

	global $wpsm, $post;
	
	//exit($post->ID);
	
	$dat = $wpsm->get(array('post_id' => $post->ID));
	if (empty($dat)) $dat = array('nodata');
	
	echo <<<EOF

<style type="text/css">
a.wpsm_plus {
	background:#333;
}
a.wpsm_plus:hover {
	cursor:pointer;
}
a.wpsm_maenas {
	background:#333;
	display:none;
}
a.wpsm_maenas:hover {
	cursor:pointer;
}
</style>

<script type="text/javascript">
(function($) {

	$(function() {
$('#sc-data').datepicker({ dateFormat: 'yy-mm-dd' });

	 var count=0;
	 var alcount=0;
	 var wpsm="wpsm_daybox";
	 var wpsmaf="wpsm_daybox0";
	 	
		$('.wpsm_plus').live('click', function() {
			var html =$('.wpsm_daybox0').html();
			count++;
			alcount++;
			wpsm=wpsm+count;
			//console.log(html);
			var wpsmadd="<div class=" + wpsm + "></div>";
			wpsmaf="."+ wpsmaf;
			$(wpsmaf).after(wpsmadd);
			$("."+wpsm).append(html);
			//console.log($("."+wpsm).children());
			wpsm_day="wpsm_day[" + count + "]";
			wpsm_time="wpsm_time[" + count + "]";
			wspm_description="wspm_description[" + count + "]";
			wpsm_yoyaku="wpsm_yoyaku[" + count + "]";
			
			wpsm_URL="wpsm_url[" + count + "]";
			//$("."+wpsm).children(".day").children("input").attr("name",wpsm_day);
			$("."+wpsm).children(".day").children("input").attr({name:wpsm_day,value:""});
			$("."+wpsm).children(".time").children("input").attr({name:wpsm_time,value:""});
			$("."+wpsm).children(".description").children("input").attr({name:wspm_description,value:""});
			$("."+wpsm).children(".yoyaku").children("input").attr({name:wpsm_yoyaku,value:0});
			$("."+wpsm).children(".URL").children("input").attr({name:wpsm_URL,value:""});
			$(".wpsm_maenas").css("display","inline");
			
			wpsmaf=wpsm;
			wpsm="wpsm_daybox";
		});
		/*$('.wpsm_maenas').live('click', function() {
			//console.log($(this).parent().parent());
			$(this).parent().parent().css("display","none");
			$(this).parent().parent().children(".day").children("input").attr("value","delete");
			$(this).parent().parent().children(".time").children("input").attr("value","delete");
			$(this).parent().parent().children(".description").children("input").attr("value","delete");
			$(this).parent().parent().children(".yoyaku").children("input").attr("value","0");
			$(this).parent().parent().children(".URL").children("input").attr("value","delete");
						
			alcount--;
			console.log(alcount);
			
			if(1>alcount){
				$(".wpsm_maenas").css("display","none");
			}
		});*/
	});
})(jQuery);
</script>
EOF;

	echo '<div class="wpsm_daybox0">';
	
	foreach($dat as $d):
	print_r($d);?>
	<p class="day">
		<label>日付</label>
		<input type="text" name="wpsm_day[0]" size="50" tabindex="1"  id="sc-data" autocomplete="off"<?php if(isset($d->date)):?> value="<?php echo $d->date?>"<?php endif;?> />
	</p>
	<p class="time">
		<label>時間</label>
		<input type="text" name="wpsm_time[0]" size="50" tabindex="1" id="sc-time" autocomplete="off"<?php if(isset($d->time)):?> value="<?php echo $d->time?>"<?php endif;?> />
	</p>
	<p class="description">
		<label>概要</label>
		<input type="text" name="wpsm_description[0]" size="50" tabindex="1" id="sc-description" autocomplete="off"<?php if(isset($d->description)):?> value="<?php echo $d->description?>"<?php endif;?> />
	</p>
	
	<p class="yoyaku">
		<label>予約可</label>
		<input type="checkbox" name="wpsm_yoyaku[0]"<?php if(isset($d->yoyaku)):?> value="<?php echo $d->yoyaku?>"<?php endif;?> />
	</p>
	<p class="URL">
		<label>URL</label>
		<input type="text" name="wpsm_url[0]" size="100" tabindex="1" id="sc-URL" autocomplete="off"<?php if(isset($d->url)):?> value="<?php echo $d->url?>"<?php endif;?> />
	</p>
	<?php endforeach;
	
	echo <<<EOF
	<p><a class="wpsm_plus">+</a></p>
	<p><a class="wpsm_maenas">-</a></p>
</div>
EOF;
}

?>