<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html <?php language_attributes(); ?> />
<html xmlns="http://www.w3.org/1999/xhtml"  xml:lang="en" lang="en"><head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title>
<?php bloginfo('name'); ?>
<?php if ( is_single() ) { ?>
&raquo; Blog Archive
<?php } ?>
<?php wp_title(); ?>
</title>
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<link rel="shortcut icon" href="<?php echo home_url() ?>/favicon.ico" />
<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
<?php wp_head(); ?>

<!-- Don't touch this! -->
<?php global $options;
foreach ($options as $value) {
if (get_option( $value['id'] ) === FALSE) { $$value['id'] = $value['std']; } else { $$value['id'] = get_option( $value['id'] ); } }
?>

<!-- Stylesheet link -->
<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/style.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/print.css" type="text/css" media="print" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/color/style-<?php echo $pov_color; ?>.css" type="text/css" media="screen" />

<!-- Jquery Link -->
<?php wp_enqueue_script('jquery'); ?>

<!-- Javascript Link -->
<script type="text/javascript" src="<?php bloginfo('stylesheet_directory'); ?>/javascript/wordfinder_js.js"></script>
<script type="text/javascript" src="<?php bloginfo('stylesheet_directory'); ?>/javascript/jquery-ui-personalized-1.5.2.packed.js"></script>
<script src="<?php bloginfo('stylesheet_directory'); ?>/javascript/jquery.color.js" type="text/javascript"></script>
<script src="<?php bloginfo('stylesheet_directory'); ?>/javascript/jquery.animate-colors.js" type="text/javascript"></script>
</head>

<body <?php body_class($class); ?> >

<?php if ($pov_diswrap == "true") { ?>   <div id="wrap_container" class="nofixed">  <?php } else { ?>
<div id="wrap_container">
<?php } ?>

<div class="menucenter">
                        <div class="search">
                                <?php include (TEMPLATEPATH . '/searchform.php'); ?>
                        </div>

                                <?php wp_nav_menu(array( 'fallback_cb' => 'display_categories' , 'menu' => 'primary-menu', 'container_class' => 'main-menu', 'container_id' => 'wrap', 'theme_location' => 'primary-menu' ) ); ?>

</div>
        </div>

<div id="header">
        <div id="header_container"> 
                
                <!-- Content Ad Starts -->
                <div class="headad ">
                        <?php if (!get_option('pov_ad_head_disable') && !$is_paged && !$ad_shown) { include (TEMPLATEPATH . "/ads/header_ad.php"); $ad_shown = true; }?>
                </div>
                <!-- Content Ad Ends -->
                
                <div id="logo"> 
                        <!-- Don't Touch This -->
                        <?php global $options;
							foreach ($options as $value) {
							if (get_option( $value['id'] ) === FALSE) { $$value['id'] = $value['std']; } else { $$value['id'] = get_option( $value['id'] ); }
							}?>
                        <h1><a href="<?php echo get_option('home'); ?>/">
                                <?php if($pov_logo) { ?>
                                <img src="<?php echo $pov_logo;?>" alt="Go Home"/>
                                <?php } else { bloginfo('name'); } ?>
                                </a></h1>
                        <h2>
                                <?php if($pov_logo) { ?>
                                <h2></h2>
                                <?php } else { bloginfo('description'); } ?>
                        </h2>
                </div>
        </div>
        <!--Chiusura del div Header_container --> 
</div>
<!--Chiusura del div Header -->

<div id="container">
