</div>
		<!-- END CONTENT -->
	</div>
	<!-- END WRAPPER -->
	<!-- BEGIN FOOTER -->
	<div id="footer">
	<div id="footerWidgets">
		<?php /* Widgetized sidebar */
	if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer') ) : ?><?php endif; ?>
	</div>
	<!-- BEGIN COPYRIGHT -->
	<div id="copyright">
		<?php if (get_option('simplo_copyright') <> ""){
			echo stripslashes(stripslashes(get_option('simplo_copyright')));
			}else{
				echo 'Just go to Theme Options Page and edit copyright text';
			}?> 
			<div id="site5bottom"><a href="http://www.site5.com/p/php-hosting/">PHP Web Hosting</a></div>
	</div>
	<!-- END COPYRIGHT -->	
	</div>
		
	<!-- END FOOTER -->
</div>
<!-- END MAIN WRAPPER -->
<?php if (get_option(' simplo_analytics') <> "") { 
		echo stripslashes(stripslashes(get_option('simplo_analytics'))); 
	} ?>
<?php wp_footer(); ?>
</body>
</html>
