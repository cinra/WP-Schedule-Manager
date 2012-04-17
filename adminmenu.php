<?php

function wpsm_init_adminmenu() {
	add_meta_box('schedule_manager', __('schedule'), 'wpsm_output_metabox', 'post', 'normal');
}
add_action('add_meta_boxes', 'wpsm_init_adminmenu');

function wpsm_output_metabox() {

	global $wpsm, $post, $count;
	$count = 0;
	//exit($post->ID);
	
	$dat = $wpsm->get(array('post_id' => $post->ID));
	if (empty($dat)) $dat = array('nodata');
	
?>

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
    $('.sc-data').datepicker({ dateFormat: 'yy-mm-dd' });

	 var count=0;
	 var alcount=0;
	 var wpsm="wpsm_daybox";
	 var wpsmaf="wpsm_daybox0";	
	 count=$(".box").length;
	 count=count-1;
	 alcount = count;
	 if (count>0) {
	 	$(".wpsm_maenas").css("display","inline");
	 }
	 	var html =$('.wpsm_daybox0').html();
	 	
			$('.wpsm_plus').live('click', function() {
			

			//console.log(count);
				
			wpsmaf="wpsm_daybox"+count;
			//console.log(wpsmaf);
			
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
			
			
			$('.sc-data').datepicker('refresh');
		});
		$('.wpsm_maenas').live('click', function() {
			console.log($(this).parent().parent());
			$(this).parent().parent().empty();				
			alcount--;
			console.log(alcount);
			
			if(1>alcount){
				$(".wpsm_maenas").css("display","none");
			}
		});
		
		//init();
	}
	
	
	
	);
})(jQuery);
</script>
<?php

	
	$c = 0;
	foreach($dat as $d):
	
	print_r($d);?>
	<div class="wpsm_daybox<?php echo $count ?> box ">
	<p class="day">
		<label>日付</label>
		<input type="text" name="wpsm_day[<?php echo $c?>]" size="50" tabindex="1" class="sc-data" autocomplete="off"<?php if(isset($d->date)):?> value="<?php echo $d->date?>"<?php endif;?> />
	</p>
	<p class="time">
		<label>時間</label>
		<input type="text" name="wpsm_time[<?php echo $c?>]" size="50" tabindex="1" id="sc-time" autocomplete="off"<?php if(isset($d->time)):?> value="<?php echo $d->time?>"<?php endif;?> />
	</p>
	<p class="description">
		<label>概要</label>
		<input type="text" name="wpsm_description[<?php echo $c?>]" size="50" tabindex="1" id="sc-description" autocomplete="off"<?php if(isset($d->description)):?> value="<?php echo $d->description?>"<?php endif;?> />
	</p>
	
	<p class="yoyaku">
		<label>予約可</label>
		<input type="checkbox" name="wpsm_yoyaku[<?php echo $c?>]"<?php if($d->status):?> checked="checked"<?php endif;?> />
	</p>
	<p class="URL">
		<label>URL</label>
		<input type="text" name="wpsm_url[<?php echo $c?>]" size="100" tabindex="1" id="sc-URL" autocomplete="off"<?php if(isset($d->url)):?> value="<?php echo $d->url?>"<?php endif;?> />
	</p>
<p><a class="wpsm_plus">+</a></p>
<p><a class="wpsm_maenas">-</a></p>
</div>
<?php $count++?>

	<?php 
	$c++;
	endforeach;
	

	


}

?>