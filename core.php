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
			wpsm_yoyaku="wpsm_yoyaku[" + count + "]";
			wpsm_URL="wpsm_url[" + count + "]";
			//$("."+wpsm).children(".day").children("input").attr("name",wpsm_day);
			$("."+wpsm).children(".day").children("input").attr({name:wpsm_day,value:""});

			$("."+wpsm).children(".time").children("input").attr({name:wpsm_time,value:""});
			$("."+wpsm).children(".yoyaku").children("input").attr({name:wpsm_yoyaku,value:0});
			$("."+wpsm).children(".URL").children("input").attr({name:wpsm_URL,value:""});
			$(".wpsm_maenas").css("display","inline");
			
			
			
			//
			wpsmaf=wpsm;
			wpsm="wpsm_daybox";
						

		});
		$('.wpsm_maenas').live('click', function() {
			console.log($(this).parent().parent());
			$(this).parent().parent().css("display","none");
			$(this).parent().parent().children(".day").children("input").attr("value","delete");
			$(this).parent().parent().children(".time").children("input").attr("value","delete");
			$(this).parent().parent().children(".yoyaku").children("input").attr("value","0");
			$(this).parent().parent().children(".URL").children("input").attr("value","delete");
						
			alcount--;
			console.log(alcount);
			
			if(1>alcount){
				$(".wpsm_maenas").css("display","none");
			}
			
			
		});
	});
})(jQuery);
</script>

<div class="wpsm_daybox0">
	<p class="day">
	<label >日付</label>
	<input type="text" name="wpsm_day[0]" size="50" tabindex="1" value="" id="sc-data" autocomplete="off">
	</p>
	<p class="time">
	<label >時間</label>
	<input type="text" name="wpsm_time[0]" size="50" tabindex="1" value="" id="sc-time" autocomplete="off">
	</p>
	<p class="yoyaku">
	<label >予約可</label>
	<input type="checkbox" name="wpsm_yoyaku[0]" value="0">
	</p>
	<p class="URL">
	<label >URL</label>
	<input type="text" name="wpsm_url[0]" size="100" tabindex="1" value="" id="sc-URL" autocomplete="off">
	<p><a class="wpsm_plus">+</a></p>
	<p><a class="wpsm_maenas">-</a></p>
</div>




EOF;
}

?>