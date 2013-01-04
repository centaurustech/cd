<!-- Module HomeSlider --> 
{if isset($homeslider)}
{literal}
<script type="text/javascript">
$(document).ready(function(){
	var homeslider_loop = true;
	var homeslider_speed = {/literal}{$homeslider.speed}{literal};
	var homeslider_pause = {/literal}{$homeslider.pause}{literal};
	
	if (!homeslider_speed == undefined)
		var homeslider_speed = 300;
	if (!homeslider_pause == undefined)
		var homeslider_pause = 6000;
	
	
	$('#homeslider').bxSlider({
		infiniteLoop: true,
	    hideControlOnEnd: true,
	    pager: true,
	    autoHover: true,
	    auto: true,
	    speed: homeslider_speed,
	    pause: homeslider_pause
	});
});
</script>
{/literal}
{/if}

{if isset($homeslider_slides)}
<ul id="homeslider">
{foreach from=$homeslider_slides item=slide}
	{if $slide.active}
		<li><a href="{$slide.url}"><img src="modules/homeslider/images/{$slide.image}" alt="{$slide.legend}" height="{$homeslider.height}" width="{$homeslider.width}"></a></li>
	{/if}
{/foreach}
</ul>
{/if}
<!-- /Module HomeSlider -->