<?php

/*
----- Table of Contents

	1.  Load other functions
	2.  Set up theme specific variables
	3.  Image max width
	4.  Register Sidebars
	5.  Main Menu Fallback
	6.  Navigation Function
	7.  Define theme options
	8.  Theme option return functions
				I.    Logo Functions
				II.   Follow Functions
				III.  Footer Functions
				IV.   Alertbox Functions
				V.    Side Image Functions
				VI.   CSS Functions
	9.  Enqueue Client Files
	10. Print Header Items

*/

/*---------------------------------------------------------
	1. Load other functions
------------------------------------------------------------ */
locate_template( array( 'functions' . DIRECTORY_SEPARATOR . 'comments.php' ), true );
locate_template( array( 'functions' . DIRECTORY_SEPARATOR . 'ttf-admin.php' ), true );


if (!class_exists( 'Paperpunch' )) {
	class Paperpunch extends TTFCore {

		/*---------------------------------------------------------
			2. Setup theme specific variables
		------------------------------------------------------------ */
		function Paperpunch () {

			$this->themename = "Paperpunch";
			$this->themeurl = "http://thethemefoundry.com/paperpunch/";
			$this->shortname = "P";
			$this->domain = 'paperpunch';

			add_action( 'init', array(&$this, 'registerMenus' ));
			add_action( 'setup_theme_paperpunch', array(&$this, 'setOptions' ) );

			add_action( 'wp_head', array( &$this, 'printHeaderItems' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueueClientFiles' ) );

			parent::TTFCore();
		}

		/*---------------------------------------------------------
			3. Image max width
		------------------------------------------------------------ */
		function addContentWidth() {
			global $content_width;
			if ( ! isset( $content_width ) ) {
				$content_width = 657;
			}
		}

		/*---------------------------------------------------------
			4. Register Sidebars
		------------------------------------------------------------ */
		function registerSidebars() {
			register_sidebar( array(
				'name'=> __( 'Sidebar', 'paperpunch' ),
				'id' => 'sidebar-1',
				'before_widget' => '<li id="%1$s" class="widget %2$s">',
				'after_widget' => '</li>',
				'before_title' => '<h2 class="widgettitle">',
				'after_title' => '</h2>',
				) );
			}

		/*---------------------------------------------------------
			5. Main Menu Fallback
		------------------------------------------------------------ */
		function main_menu_fallback() {
			?>
			<div id="navigation" class="clear">
				<ul id="nav">
					<?php
						wp_list_pages( 'title_li=&number=9' );
					?>
				</ul>
			</div>
			<?php
			}

		/*---------------------------------------------------------
			6. Navigation Functions
		------------------------------------------------------------ */
		function registerMenus() {
			register_nav_menu( 'nav-1', __( 'Top Navigation', 'paperpunch' ) );
		}

		/*---------------------------------------------------------
			7. Define theme options
		------------------------------------------------------------ */
		function setOptions() {

			/*
				OPTION TYPES:
				- checkbox: name, id, desc, std, type
				- radio: name, id, desc, std, type, options
				- text: name, id, desc, std, type
				- colorpicker: name, id, desc, std, type
				- select: name, id, desc, std, type, options
				- textarea: name, id, desc, std, type, options
			*/

			$this->options = array(

				array(
					"name" => __( 'Custom Logo Image <span>insert your custom logo image in the header</span>', 'paperpunch' ),
					"type" => "subhead"),

				array(
					"name" => __( 'Enable custom logo image', 'paperpunch' ),
					"id" => $this->shortname."_logo",
					"desc" => __( 'Check to use a custom logo in the header.', 'paperpunch' ),
					"std" => "false",
					"pro" => 'true',
					"type" => "checkbox"),

				array(
					"name" => __( 'Logo URL', 'paperpunch' ),
					"id" => $this->shortname."_logo_img",
					"desc" => sprintf( __( 'Upload an image or enter an URL for your image.', 'paperpunch' ), '<code>' . STYLESHEETPATH . '/images/</code>' ),
					"std" => '',
					"pro" => 'true',
					"upload" => true,
					"class" => "logo-image-input",
					"type" => "upload"),

				array(
					"name" => __( 'Logo image <code>alt</code> attribute', 'paperpunch' ),
					"id" => $this->shortname."_logo_img_alt",
					"desc" => __( 'Specify the <code>&lt;alt&gt;</code> attribute for your logo image.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "text"),

				array(
					"name" => __( 'Display tagline', 'paperpunch' ),
					"id" => $this->shortname."_tagline",
					"desc" => __( 'Check to show your tagline below your logo.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "checkbox"),

				array(
					"name" => __( 'Follow Icons <span>control the follow icons in the top right of your header</span>', 'paperpunch' ),
					"type" => "subhead"),

				array(
					"name" => __( 'Enable Twitter', 'paperpunch' ),
					"id" => $this->shortname."_twitter_toggle",
					"desc" => __( 'Hip to Twitter? Check this box.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "checkbox"),

				array(
					"name" => __( 'Enable Facebook', 'paperpunch' ),
					"id" => $this->shortname."_facebook_toggle",
					"desc" => __( 'Check this box to show a link to your Facebook page.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "checkbox"),

				array(
					"name" => __( 'Enable Flickr', 'paperpunch' ),
					"id" => $this->shortname."_flickr_toggle",
					"desc" => __( 'Check this box to show a link to Flickr.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "checkbox"),

				array(
					"name" => __( 'Enable email', 'paperpunch' ),
					"id" => $this->shortname."_email_toggle",
					"desc" => __( 'Check this box to show a link to email updates.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "checkbox"),

				array(
					"name" => __( 'Disable all', 'paperpunch' ),
					"id" => $this->shortname."_follow_disable",
					"desc" => __( 'Check this box to hide all follow icons (including RSS). This option overrides any other settings.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "checkbox"),

				array(
					"name" => __( 'Twitter link', 'paperpunch' ),
					"id" => $this->shortname."_twitter",
					"desc" => __( 'Enter your twitter link here.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "text"),

				array(
					"name" => __( 'Facebook link', 'paperpunch' ),
					"id" => $this->shortname."_facebook",
					"desc" => __( 'Enter your Facebook link.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "text"),

				array(
					"name" => __( 'Flickr link', 'paperpunch' ),
					"id" => $this->shortname."_flickr",
					"desc" => __( 'Enter your Flickr link.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "text"),

				array(
					"name" => __( 'Email link', 'paperpunch' ),
					"id" => $this->shortname."_email",
					"desc" => __( 'Enter your email updates link.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "text"),

				array(
					"name" => __( 'Color Scheme <span>customize your color scheme</span>', 'paperpunch' ),
					"type" => "subhead"),

				array(
					"name" => __( 'Customize colors', 'paperpunch' ),
					"id" => $this->shortname."_background_css",
					"desc" => __( 'If enabled your theme will use the layouts and colors you choose below.', 'paperpunch' ),
					"std" => "disabled",
					"pro" => 'true',
					"type" => "select",
					"options" => array(
						"disabled" => __( 'Disabled', 'paperpunch' ),
						"enabled" => __( 'Enabled', 'paperpunch' ))),

				array(
					"name" => __( 'Background color', 'paperpunch' ),
					"id" => $this->shortname."_background_color",

					"desc" => __( 'The background will not match your color selection exactly, your color choice is blended with a transparent background image to give it texture. Use hex values and be sure to include the leading #.', 'paperpunch' ),
					"std" => "#1d4377",
					"pro" => 'true',
					"type" => "colorpicker"),

				array(
					"name" => __( 'Link color', 'paperpunch' ),
					"id" => $this->shortname."_link_color",
					"desc" => __( 'Use hex values and be sure to include the leading #.', 'paperpunch' ),
					"std" => "#1d4377",
					"pro" => 'true',
					"type" => "colorpicker"),

				array(
					"name" => __( 'Link hover color', 'paperpunch' ),
					"id" => $this->shortname."_hover_color",
					"desc" => __( 'Use hex values and be sure to include the leading #.', 'paperpunch' ),
					"std" => "#0f1a29",
					"pro" => 'true',
					"type" => "colorpicker"),

				array(
					"name" => __( 'Alert Box <span>toggle your custom alert box</span>', 'paperpunch' ),
					"type" => "subhead"),

				array(
					"name" => __( 'Alert Box on/off switch', 'paperpunch' ),
					"id" => $this->shortname."_alertbox_state",
					"desc" => __( 'Toggle the alert box on or off.', 'paperpunch' ),
					"std" => "off",
					"pro" => 'true',
					"type" => "select",
					"options" => array(
						"off" => __( 'Off', 'paperpunch' ),
						"on" => __( 'On', 'paperpunch' ))),

				array(
					"name" => __( 'Alert Header', 'paperpunch' ),
					"id" => $this->shortname."_alertbox_title",
					"desc" => __( 'The heading for your alert.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "text"),

				array(
					"name" => __( 'Alert Message', 'paperpunch' ),
					"id" => $this->shortname."_alertbox_content",
					"desc" => __( 'You may use HTML in the message.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "textarea",
					"options" => array(
						"rows" => "8",
						"cols" => "70")),

				array(
					"name" => __( 'Sidebar Image <span>control your sidebar image state</span>', 'paperpunch' ),
					"type" => "subhead"),

				array(
					"name" => __( 'Image state', 'paperpunch' ),
					"desc" => sprintf( __( 'Add your images to the sidebar rotation by uploading them to the %s directory.', 'paperpunch' ), '<code>' . STYLESHEETPATH . '/images/sidebar/</code>' ),
					"id" => $this->shortname."_sideimg_state",
					"std" => "hide",
					"pro" => 'true',
					"type" => "select",
					"options" => array(
						"rotate" => __( 'Rotating images', 'paperpunch' ),
						"static" => __( 'Static image', 'paperpunch' ),
						"custom" => __( 'Custom code', 'paperpunch' ),
						"specific" => __( 'Page or post specific', 'paperpunch' ),
						"hide" => __( 'Do not show an image', 'paperpunch' ))),

				array(
					"name" => __( 'Image <code>alt</code> attribute', 'paperpunch' ),
					"id" => $this->shortname."_sideimg_alt",
					"desc" => __( 'The <code>alt</code> attribute for your sidebar image(s). Will default to your blog title if left blank.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "text"),

				array(
					"name" => __( 'Static image', 'paperpunch' ),
					"id" => $this->shortname."_sideimg_url",
					"desc" => sprintf( __( 'Set the <em>Image State</em> to "Static Image" and upload your image to the %s directory.', 'paperpunch' ), '<code>' . STYLESHEETPATH . '/images/sidebar/</code>' ),
					"std" => '',
					"pro" => 'true',
					"type" => "text"),

				array(
					"name" => __( 'Image link', 'paperpunch' ),
					"id" => $this->shortname."_sideimg_link",
					"desc" => __( 'Define a hyperlink for your sidebar image. If left empty the anchor tags will not be included.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "text"),

				array(
					"name" => __( 'Custom code', 'paperpunch' ),
					"id" => $this->shortname."_sideimg_custom",
					"desc" => __( 'Replace your sidebar image with custom code. The <em>Image State</em> must be set to "Custom code" for this to work.', 'paperpunch' ),
					"std" => '',
					"pro" => 'true',
					"type" => "textarea",
					"options" => array(
						"rows" => "5",
						"cols" => "40")),

				array(
					"name" => __( 'Footer <span>customize your footer</span>', 'paperpunch' ),
					"type" => "subhead"),

				array(
					"name" => __( 'Copyright notice', 'paperpunch' ),
					"id" => $this->shortname."_copyright_name",
					"desc" => __( 'Your name or the name of your business.', 'paperpunch' ),
					"std" => __( 'Your Name Here', 'paperpunch' ),
					"type" => "text"),

				array(
					"name" => __( 'Stats code', 'paperpunch' ),
					"id" => $this->shortname."_stats_code",
					"desc" => sprintf( __( 'If you would like to use Google Analytics or any other tracking script in your footer just paste it here. The script will be inserted before the closing %s tag.', 'paperpunch' ), '<code>&#60;/body&#62;</code>' ),
					"std" => '',
					"type" => "textarea",
					"options" => array(
						"rows" => "5",
						"cols" => "40")),
			);
		}

		/*---------------------------------------------------------
			8. Theme option return functions
		------------------------------------------------------------ */

		/*---------------------------------------------------------
			I. Logo Functions
		------------------------------------------------------------ */

		/*---------------------------------------------------------
			II. Follow Functions
		------------------------------------------------------------ */

		/*---------------------------------------------------------
			III. Footer Functions
		------------------------------------------------------------ */
		function copyrightName() {
			return stripslashes( wp_filter_post_kses(get_option($this->shortname.'_copyright_name' )) );
		}

		/*---------------------------------------------------------
			IV. Alertbox Functions
		------------------------------------------------------------ */

		/*---------------------------------------------------------
			V. Side Image Functions
		------------------------------------------------------------ */

		/*---------------------------------------------------------
			VI. CSS Functions
		------------------------------------------------------------ */

		/*---------------------------------------------------------
			9. Enqueue Client Files
		------------------------------------------------------------ */
		function enqueueClientFiles() {
			global $wp_styles;

			if ( ! is_admin() ) {

				wp_enqueue_style(
					'paperpunch-style',
					get_bloginfo( 'stylesheet_url' ),
					'',
					null
				);

				wp_enqueue_style(
					'paperpunch-ie-style',
					get_template_directory_uri() . '/stylesheets/ie.css',
					array( 'paperpunch-style' ),
					null
				);
				$wp_styles->add_data( 'paperpunch-ie-style', 'conditional', 'lt IE 8' );

				wp_enqueue_style(
					'paperpunch-ie6-style',
					get_template_directory_uri() . '/stylesheets/ie6.css',
					array( 'paperpunch-ie-style' ),
					null
				);
				$wp_styles->add_data( 'paperpunch-ie6-style', 'conditional', 'IE 6' );

				wp_enqueue_script(
					'paperpunch-cufon',
					get_template_directory_uri() . '/javascripts/cufon.js',
					'',
					null
				);

				wp_enqueue_script( 'paperpunch-chunkfive',
					get_template_directory_uri() . '/javascripts/ChunkFive.font.js',
					array( 'paperpunch-cufon' ),
					null
				);

				wp_enqueue_script( 'paperpunch-junctionregular',
					get_template_directory_uri() . '/javascripts/JunctionRegular.font.js',
					array( 'paperpunch-cufon' ),
					null
				);

				wp_enqueue_script( 'jquery' );

				if ( is_singular() ) {
					wp_enqueue_script( 'comment-reply' );
				}
			}
		}

		/*---------------------------------------------------------
			10. Print Header Items
		------------------------------------------------------------ */
		function printHeaderItems() {
			?>
			<!--[if lte IE 7]>
				<script type="text/javascript">
					sfHover=function(){var sfEls=document.getElementById("nav").getElementsByTagName("LI");for(var i=0;i<sfEls.length;i++){sfEls[i].onmouseover=function(){this.className+=" sfhover";}
					sfEls[i].onmouseout=function(){this.className=this.className.replace(new RegExp(" sfhover\\b"),"");}}}
					if (window.attachEvent)window.attachEvent("onload",sfHover);
				</script>
			<![endif]-->

			<!--[if IE 6]>
				<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/javascripts/pngfix.js"></script>
				<script type="text/javascript"> DD_belatedPNG.fix( '#navigation, div.comments a' );</script>
			<![endif]-->
			<script type="text/javascript">
				if ( 'undefined' != typeof Cufon ) {
					Cufon.replace( '#title', { fontFamily: 'ChunkFive' });
					Cufon.replace( '.post-header h1, .post-header h2, #description, #navigation li a, .meta span, div.tags, .entry h2, .entry h3, .entries h3, .entry h4, div.category, div.entries ul li span, #sidebar h2.widgettitle, #sidebar li.widget_tag_cloud, div.comment-number h4, .c-head span, div.reply a, div#comments-rss, #footer div.widget h2', { hover: true, fontFamily: 'JunctionRegular' });
				}
			</script>
			<?php
		}

	}
}

/* SETTING EVERYTHING IN MOTION */
function load_paperpunch_pro_theme() {
	$GLOBALS['paperpunch'] = new Paperpunch;
}

add_action( 'after_setup_theme', 'load_paperpunch_pro_theme' );