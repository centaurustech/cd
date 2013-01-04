<?php global $paperpunch; ?>
<!DOCTYPE html>
<html <?php language_attributes( 'html' ) ?>>
<head>
	<?php if ( is_front_page() ) : ?>
		<title><?php bloginfo( 'name' ); ?></title>
	<?php elseif ( is_404() ) : ?>
		<title><?php _e( 'Page Not Found |', 'paperpunch' ); ?> <?php bloginfo( 'name' ); ?></title>
	<?php elseif ( is_search() ) : ?>
		<title><?php printf( __("Search results for '%s'", "paperpunch"), get_search_query() ); ?> | <?php bloginfo( 'name' ); ?></title>
	<?php else : ?>
		<title><?php wp_title($sep = '' ); ?> | <?php bloginfo( 'name' );?></title>
	<?php endif; ?>

	<!-- Basic Meta Data -->
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="copyright" content="<?php
		esc_attr( sprintf(
			__( 'Design is copyright %1$s The Theme Foundry', 'paperpunch' ),
			date( 'Y' )
		) );
	?>" />

	<!-- Favicon -->
	<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.ico" />

	<!-- WordPress -->
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<div class="skip-content"><a href="#content"><?php _e( 'Skip to content', 'paperpunch' ); ?></a></div>
	<div id="wrapper">
		<div id="header" class="clear">
				<?php
				$title_wrap = is_home() ? '<h1 id="title">%s</h1>' : '<div id="title">%s</div>';
				printf(
					$title_wrap,
					'<a href="' . home_url('/') . ' ">' . get_bloginfo( 'name' ) . '</a>'
				);
				?>
				<div id="description"><?php bloginfo( 'description' ); ?></div>
		</div><!--end header-->
		<?php
			wp_nav_menu(
				array(
					'theme_location'  => 'nav-1',
					'container_id'    => 'navigation',
					'container_class' => 'clear',
					'menu_id'         => 'nav',
					'fallback_cb'     => array( &$paperpunch, 'main_menu_fallback')
					)
			);
		?>
		<div id="content">