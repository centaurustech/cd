<!-- BEGIN COL RIGHT -->
	<div id="colRight">
	<div id="searchBox" class="clearfix">
			<form id="searchform" action="" method="get">
				<input id="s" type="text" name="s" value=""/>
				<input id="searchsubmit" type="submit" value="SEARCH"/>
			</form>
		</div>
		<?php /* Widgetized sidebar */
	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('sidebar') ) : ?><?php endif; ?>
	</div>
<!-- END COL RIGHT -->