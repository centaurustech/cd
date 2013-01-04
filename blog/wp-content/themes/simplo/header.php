<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta name="keywords" content="<?php echo get_option('simplo_keywords'); ?>" />
<meta name="description" content="<?php echo get_option('simplo_description'); ?>" />
<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<?php if(get_option('simplo_style')!=''){?>
	<link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/<?php echo get_option('simplo_style'); ?>" media="screen" />
	<?php }else{?>
	<link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/blue.css" media="screen" />
	<?php }?>

<link href="<?php bloginfo('template_directory'); ?>/css/ddsmoothmenu.css" rel="stylesheet" type="text/css" />
<link href="<?php bloginfo('template_directory'); ?>/css/prettyPhoto.css" rel="stylesheet" type="text/css" />
<script language="JavaScript" type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery-1.4.2.min.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery.form.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/cufon-yui.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/DIN_400-DIN_700.font.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/ddsmoothmenu.js"></script>
<script language="JavaScript" type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery.prettyPhoto.js"></script>

<script type="text/javascript">
<?php if(get_option('simplo_cufon')!="no"):?>
		Cufon.replace('#topMenu ul li a',{hover: true})('#colRight h2')('.reply',{hover:true})('#footer h2');
		<?php endif ?>
</script>
<script type="text/javascript">
$(document).ready(function(){
	//DROPDOWN MENU INIT
	ddsmoothmenu.init({
	mainmenuid: "topMenu", //menu DIV id
	orientation: 'h', //Horizontal or vertical menu: Set to "h" or "v"
	classname: 'ddsmoothmenu', //class added to menu's outer DIV
	//customtheme: ["#1c5a80", "#18374a"],
	contentsource: "markup" //"markup" or ["container_id", "path_to_menu_file"]
	});
	
	// PRETTY PHOTO INIT
	$("a[rel^='prettyPhoto']").prettyPhoto();
});
</script>
<?php wp_head();?>
</head>

<body <?php body_class(); ?>>
<!-- BEGIN MAIN WRAPPER -->
<div id="mainWrapper">
	<!-- BEGIN WRAPPER -->
	<div id="wrapper">
		<!-- BEGIN HEADER -->
		<div id="header">
			 <div id="logo"><a href="<?php bloginfo('url'); ?>/"><img src="<?php echo get_option('simplo_logo_img'); ?>" alt="<?php echo get_option('simplo_logo_alt'); ?>" /></a> <?php echo get_settings('blogdescription');?></div>
			<div id="topSocial">Social Stuff:
				<?php if(get_option('simplo_delicious_link')!=""){ ?>
					<a href="<?php echo get_option('simplo_delicious_link'); ?>" title="Delicious"><img src="<?php bloginfo('template_directory'); ?>/images/delicious_32.png" alt="Delicious" /></a>
					<?php }?>
				<?php if(get_option('simplo_linkedin_link')!=""){ ?>
					<a href="<?php echo get_option('simplo_linkedin_link'); ?>" title="LinkedIn"><img src="<?php bloginfo('template_directory'); ?>/images/linkedin_32.png" alt="LinkedIn" /></a>
					<?php }?>
				<?php if(get_option('simplo_facebook_link')!=""){ ?>
					<a href="<?php echo get_option('simplo_facebook_link'); ?>" title="Join Us!"><img src="<?php bloginfo('template_directory'); ?>/images/facebook_32.png" alt="Facebook" /></a>
					<?php }?>
				<?php if(get_option('simplo_twitter_link')!=""){ ?>
					<a href="<?php echo get_option('simplo_twitter_link'); ?>" title="Follow Us!"><img src="<?php bloginfo('template_directory'); ?>/images/twitter_32.png" alt="Twitter" /></a>
					<?php }?>
				<a href="<?php bloginfo('rss2_url'); ?>"><img src="<?php bloginfo('template_directory'); ?>/images/rss_32.png" alt="RSS Feed" /></a>
			</div>
		</div>
		<!-- END HEADER -->
		<!-- BEGIN TOP MENU -->
			<?php if ( function_exists( 'wp_nav_menu' ) ){
					wp_nav_menu( array( 'theme_location' => 'main-menu', 'container_id' => 'topMenu', 'container_class' => 'ddsmoothmenu', 'fallback_cb'=>'primarymenu') );
				}else{
					primarymenu();
			}?>
            <!-- END TOP MENU -->
			
		<!-- BEGIN CONTENT -->
			<div id="content" class="twocols">
