<?php

$themename = "WordFinder";
$shortname = "pov";
$mx_categories_obj = get_categories('hide_empty=0');
$mx_categories = array();
foreach ($mx_categories_obj as $mx_cat) {
	$mx_categories[$mx_cat->cat_ID] = $mx_cat->cat_name;
}
$categories_tmp = array_unshift($mx_categories, "Select a category:");	
$indexscheme = array("minimal", "diary");
$colorscheme = array("Default", "Blue", "Magenta", "Green","Red","Cyan");

$options = array (

array(  "name" => "Your Logo",
            "type" => "heading",
			"desc" => "Set your logo",
       ),

array( 	"name" => "Logo Display",
			"desc" => "Insert the URL address of your logo(upload in your media gallery) Dimensions are 300px x 60px). If you leave the form empty will display your blog title and description",
			"id" => $shortname."_logo",
			"type" => "text",
			"std" => ""),		
			
array(  "name" => "Theme Index Scheme",
            "type" => "heading",
			"desc" => "Choose your type of index scheme, classic, minimal or diaries.",
       ),

array(   "name" => "Classic Scheme",
            "id" => $shortname."_index",
            "type" => "select",
            "std" => "true",
			"options" => $indexscheme
            ),	
			
array(  "name" => "Theme Color Scheme",
            "type" => "heading",
			"desc" => "Choose your color scheme.",
       ),

array(   "name" => "Blog Color Scheme",
            "id" => $shortname."_color",
            "type" => "select",
            "std" => "Default",
            "options" => $colorscheme),	
				

array(  "name" => "Scollable Wrap for blog",
            "type" => "heading",
			"desc" => "Scollable Wrap",
       ),

array(	"name" => "Disable Scrollable Wrap",
					"desc" => "If you don't want the menu navigation on top scrollable you can disable it",
					"id" => $shortname."_diswrap",
					"std" => "false",
					"type" => "checkbox"),
					
array(  "name" => "Minimal Index",
            "type" => "heading",
			"desc" => "Options for the minimal index.",
       ),
					
array(	"name" => "Disable Excerpt on Minimal Index",
					"desc" => "You can disable the excerpt for the Minimal Index Page (only after the first post)",
					"id" => $shortname."_disexcerpt",
					"std" => "false",
					"type" => "checkbox"),

array(	"name" => "Disable Thumbnails on Minimal Index",
					"desc" => "You can disable the Thumbnails for the Minimal Index Page (only after the first post)",
					"id" => $shortname."_disthumb",
					"std" => "false",
					"type" => "checkbox"),
					
	array("name" => "Numbers of post to display in Minimal Index",
			"desc" => "Insert Here the number of post do you want to display on the Minimal Index",
            "id" => $shortname."_npostminimal",
            "std" => "10",
            "type" => "text"), 
				
array(  "name" => "Diary Index",
            "type" => "heading",
			"desc" => "Options for the minimal index.",
       ),
				
		array("name" => "Numbers of post to display in Diary Index",
			"desc" => "Insert Here the number of post do you want to display on the Diary Index(use multiple of 3)",
            "id" => $shortname."_npostdiary",
            "std" => "12",
            "type" => "text"), 			
				


		

array(  "name" => "FeedBurner Account",
            "type" => "heading",
			"desc" => "Insert your Feedbuner account",
			),	
array(  "name" => "Your FeedBurner address",
        	"desc" => "Enter your Feedburner account name",
        	"id" => $shortname."_feedburner_account",
        	"type" => "text",
        	"std" => ""),			
		
array(  "name" => "Social Networks Account",
            "type" => "heading",
			"desc" => "",
			),	
array(  "name" => "Your Twitter account",
        	"desc" => "Enter your Twitter account name",
        	"id" => $shortname."_twitter_user_name",
        	"type" => "text",
        	"std" => ""),
array(  "name" => "Your Facebook account",
        	"desc" => "Enter your Facebook account name ex: aldo.rossi",
        	"id" => $shortname."_facebook_user_name",
        	"type" => "text",
        	"std" => ""),	


array(  "name" => "Sidebar Widgets and Subfooter",
            "type" => "heading",
			"desc" => "Customize the sidebar with theme widgets .",
       ),	
			
array(	"name" => "Disable Subfooter",
					"desc" => "Disabgling Subfooter the homepage will be more shorter",
					"id" => $shortname."_disubfooter",
					"std" => "false",
					"type" => "checkbox"),

array(	"name" => "RSS Feed link , Facebook Link and Twitter Link Widget on Sidebar",
					"desc" => "Disable RSS Feed link , Facebook Link and Twitter Link Widget",
					"id" => $shortname."_disocial",
					"std" => "false",
					"type" => "checkbox"),
					
array(	"name" => "Popular Posts Widget",
					"desc" => "Disable the Popular post widget - Popular Posts are ordered by comments on Sidebar",
					"id" => $shortname."_dispopularpost",
					"std" => "t",
					"type" => "checkbox"),
					
					
array(  "name" => "Footer",
            "type" => "heading",
			"desc" => "License for your blog .",
       ),
					

array(  "name" => "License Text on Footer",
					"desc" => "Enter the text for your license in the footer",
					"id" => $shortname."_footer_license",
					"type" => "text",
					"std" => "true"),

array(	"name" => "Disable Credit Link",
					"desc" => "It's completely optional , but if you like the Theme i would appreciate it if you keep the credit link at the bottom",
					"id" => $shortname."_discredit",
					"std" => "false",
					"type" => "checkbox"),
					

					
	
					
	
array(	"name" => "Header Banner Ad  (468x60px)",
			"desc" => "Enter your AdSense code, or your banner url and destination, or disable header ad.",
					"type" => "heading"),

	

array(	"name" => "Banner Ad Header - Image Location",
					"desc" => "Enter the URL for this banner ad.",
					"id" => $shortname."_ad_head_image",
					"std" => "http://www.llow.it/adsense/468ad.jpg",
					"type" => "text"),

array(	"name" => "Banner Ad Header - Destination",
					"desc" => "Enter the URL where this banner ad points to.",
					"id" => $shortname."_ad_head_url",
					"std" => "#",
					"type" => "text"),


array(	"name" => "Disable Ad",
					"desc" => "Disable the ad space",
					"id" => $shortname."_ad_head_disable",
					"std" => "false",
					"type" => "checkbox"),
					
					
					
array(	"name" => "Sidebar Banner Ad Big  (250x250)",
			"desc" => "Enter your AdSense code, or your banner url and destination, or disable header ad.",
					"type" => "heading"),

array(	"name" => "Banner Ad Sidebar Big - Image Location",
					"desc" => "Enter the URL for this banner ad.",
					"id" => $shortname."_ad_sidebar_image",
					"std" => "http://www.llow.it/adsense/250ad.jpg",
					"type" => "text"),

array(	"name" => "Banner Ad Sidebar Big - Destination",
					"desc" => "Enter the URL where this banner ad points to.",
					"id" => $shortname."_ad_sidebar_url",
					"std" => "#",
					"type" => "text"),


array(	"name" => "Disable Ad",
					"desc" => "Disable the ad space",
					"id" => $shortname."_ad_sidebar_disable",
					"std" => "false",
					"type" => "checkbox"),
					
					
					
array(  "name" => "Banner Ads Settings",
            "type" => "heading",
			"desc" => "You can setup four 125x125 banners for your blog from here",
       ), 
	   
	array("name" => "Banner-1 Image",
			"desc" => "Enter your 125x125 banner image url here.",
            "id" => $shortname."_banner1",
            "std" => "http://www.llow.it/adsense/125ad.jpg",
            "type" => "text"),     
	   
	array("name" => "Banner-1 Url",
			"desc" => "Enter the banner-1 url here.",
            "id" => $shortname."_url1",
            "std" => "#",
            "type" => "text"),    

array(	"name" => "Disable Ad",
					"desc" => "Disable the ad space",
					"id" => $shortname."_ad_sidebar_125_1_disable",
					"std" => "false",
					"type" => "checkbox"),
	      
	 
	array("name" => "Banner-2 Image",
			"desc" => "Enter your 125x125 banner image url here.",
            "id" => $shortname."_banner2",
            "std" => "http://www.llow.it/adsense/125ad.jpg",
            "type" => "text"),    
	   
	array("name" => "Banner-2 Url",
			"desc" => "Enter the banner-2 url here.",
            "id" => $shortname."_url2",
            "std" => "#",
            "type" => "text"), 

array(	"name" => "Disable Ad",
					"desc" => "Disable the ad space",
					"id" => $shortname."_ad_sidebar_125_2_disable",
					"std" => "false",
					"type" => "checkbox"),

	array("name" => "Banner-3 Image",
			"desc" => "Enter your 125x125 banner image url here.",
            "id" => $shortname."_banner3",
            "std" => "http://www.llow.it/adsense/125ad.jpg",
            "type" => "text"),    
	   
	array("name" => "Banner-3 Url",
			"desc" => "Enter the banner-3 url here.",
            "id" => $shortname."_url3",
            "std" => "#",
            "type" => "text"),

array(	"name" => "Disable Ad",
					"desc" => "Disable the ad space",
					"id" => $shortname."_ad_sidebar_125_3_disable",
					"std" => "false",
					"type" => "checkbox"),
			
	array("name" => "Banner-4 Image",
			"desc" => "Enter your 125x125 banner image url here.",
            "id" => $shortname."_banner4",
            "std" => "http://www.llow.it/adsense/125ad.jpg",
            "type" => "text"),    
	   
	array("name" => "Banner-4 Url",
			"desc" => "Enter the banner-4 url here.",
            "id" => $shortname."_url4",
            "std" => "#",
            "type" => "text"),

array(	"name" => "Disable Ad",
					"desc" => "Disable the ad space",
					"id" => $shortname."_ad_sidebar_125_4_disable",
					"std" => "false",
					"type" => "checkbox"),
	
		array(  "name" => "Google Analytics",
            "type" => "heading",
			"desc" => "Please paste your Google Analytics (or other) tracking code here.",
       ),
	
	

	array(	"name" => "Google Analytics",
			"desc" => "",
			"id" => $shortname."_google_analytics",
			"std" => "",
			"type" => "textarea"),		
	array( "type" => "close"),	

);







function mytheme_add_admin() {

    global $themename, $shortname, $options;

    if ( $_GET['page'] == basename(__FILE__) ) {
    
        if ( 'save' == $_REQUEST['action'] ) {

                foreach ($options as $value) {
                    update_option( $value['id'], $_REQUEST[ $value['id'] ] ); }

                foreach ($options as $value) {
                    if( isset( $_REQUEST[ $value['id'] ] ) ) { update_option( $value['id'], $_REQUEST[ $value['id'] ]  ); } else { delete_option( $value['id'] ); } }

                header("Location: themes.php?page=dashboard.php&saved=true");
                die;

        } else if( 'reset' == $_REQUEST['action'] ) {

            foreach ($options as $value) {
                delete_option( $value['id'] ); 
                update_option( $value['id'], $value['std'] );}

            header("Location: themes.php?page=dashboard.php&reset=true");
            die;

        }
    }

      add_theme_page($themename." Options", "$themename Options", 'edit_themes', basename(__FILE__), 'mytheme_admin');

}

function mytheme_admin() {

    global $themename, $shortname, $options;

    if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings saved.</strong></p></div>';
    if ( $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings reset.</strong></p></div>';
    
    
?>

<div class="wrap">
<h2><b><?php echo $themename; ?> theme options</b></h2>
<form method="post">
        <table class="optiontable" >
                <?php foreach ($options as $value) { 
    
	
if ($value['type'] == "text") { ?>
                <tr align="left">
                        <th scope="row"><?php echo $value['name']; ?>:</th>
                        <td><input name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_option( $value['id'] ) != "") { echo get_option( $value['id'] ); } else { echo $value['std']; } ?>" size="40" /></td>
                </tr>
                <tr>
                        <td colspan=2><small><?php echo $value['desc']; ?> </small>
                                <hr /></td>
                </tr>
                <?php } elseif ($value['type'] == "textarea") { ?>
                <tr align="left">
                        <th scope="row"><?php echo $value['name']; ?>:</th>
                        <td><textarea name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" cols="40" rows="5"/>
                                <?php if ( get_option( $value['id'] ) != "") { echo stripslashes(get_option($value['id'] )); } else { echo $value['std']; } ?>
                                </textarea></td>
                </tr>
                <tr>
                        <td colspan=2><small><?php echo $value['desc']; ?> </small>
                                <hr /></td>
                </tr>
                <?php } elseif ($value['type'] == "select") { ?>
                <tr align="left">
                        <th scope="top"><?php echo $value['name']; ?>:</th>
                        <td><select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
                                        <?php foreach ($value['options'] as $option) { ?>
                                        <option<?php if ( get_option( $value['id'] ) == $option) { echo ' selected="selected"'; }?>><?php echo $option; ?></option>
                                        <?php } ?>
                                </select></td>
                </tr>
                <tr>
                        <td colspan=2><small><?php echo $value['desc']; ?> </small>
                                <hr /></td>
                </tr>
                <?php } elseif ($value['type'] == "checkbox") { ?>
                <tr  >
                        <th scope="top" align="left"><?php echo __($value['name'],'thematic'); ?></th>
                        <td><?php
					if(get_option($value['id'])){
						$checked = "checked=\"checked\"";
					}else{
						$checked = "";
					}
				?>
                                <input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="true" <?php echo $checked; ?> />
                                <label for="<?php echo $value['id']; ?>"></label></td>
                </tr>
                <tr>
                        <td colspan=2><small><?php echo $value['desc']; ?> </small>
                                <hr /></td>
                </tr>
                <?php } elseif ($value['type'] == "heading") { ?>
                <tr valign="top">
                        <td colspan="2" style="text-align: left;"><h2 style="color:grey;"><?php echo $value['name']; ?></h2></td>
                </tr>
                <tr>
                        <td colspan=2><small>
                                <p style="color:green; margin:0 0;" > <?php echo $value['desc']; ?> </P>
                                </small>
                                <hr /></td>
                </tr>
                <?php } ?>
                <?php 
}
?>
        </table>
        <p class="submit">
                <input name="save" type="submit" value="Save changes" />
                <input type="hidden" name="action" value="save" />
        </p>
</form>
<form method="post">
        <p class="submit">
                <input name="reset" type="submit" value="Reset" />
                <input type="hidden" name="action" value="reset" />
        </p>
</form>
<p>Visit us: <a href="http://www.llow.it/" >llow.it</a>. </p>
<?php
}
add_action('admin_menu', 'mytheme_add_admin'); ?>
