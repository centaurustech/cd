<?php global $paperpunch; ?>
	<div id="copyright">
		<p class="copyright-notice"><?php
		printf(
			__( 'Copyright &copy; %1$s %2$s.  All rights reserved.', 'paperpunch' ),
			date( 'Y' ),
			$paperpunch->copyrightName()
		); ?></p>
		<p class="attrib"><?php
		printf(
			__( '<a href="%1$s">Paperpunch Theme</a> by <a href="%2$s">The Theme Foundry</a>', 'paperpunch' ),
			'http://thethemefoundry.com/paperpunch/',
			'http://thethemefoundry.com/'
		); ?>
		</p>
	</div><!-- end copyright-->
</div><!-- end wrapper-->
<?php wp_footer(); ?>
<!--[if IE]><script type="text/javascript"> Cufon.now(); </script><![endif]-->
</body>
</html>