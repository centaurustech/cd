<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
require_once(dirname(__FILE__).'/../../../../config/config.inc.php');
$controller = new FrontController();
$controller->displayHeader();
?>

<link rel="stylesheet" href="<?php echo get_bloginfo('template_url') ?>/style.css" type="text/css" media="screen" />

		<div id="container">
			<div id="content" role="main">

			<?php
			/* Run the loop to output the page.
			 * If you want to overload this in a child theme then include a file
			 * called loop-page.php and that will be used instead.
			 */
			get_template_part( 'loop', 'page' );
			?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php
    get_sidebar();
$controller->displayFooter();
?>