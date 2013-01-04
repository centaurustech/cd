<?php global $options;
foreach ($options as $value) {
if (get_option( $value['id'] ) === FALSE) { $$value['id'] = $value['std']; } else { $$value['id'] = get_option( $value['id'] ); } }
?>

<div id="sidebar">
        <?php 	/* Widgetized sidebar, if you have the plugin installed. */
					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
        <?php endif; ?>
        
        <!-- Popular Posts -->
        
        <?php if ($pov_disocial == "true") { } else { ?>
        <h3 >Social Network</h3>
        <ul>    
		  			 <?php $feedburner_account="#" ?><?php if (get_settings('pov_feedburner_account')) { $feedburner_account = get_settings('pov_feedburner_account') ; } ?>
                <li class="nobullet"><img class="icon2" src="<?php bloginfo('template_directory'); ?>/images/rss_16.png" alt="Subscribe!" width="16" height="16" /><a href="http://feeds.feedburner.com/<?php echo                $feedburner_account ?>"> Feed RSS | Subscribe Feed </a> </li>
                <?php $twit_user_name="#" ?><?php if (get_settings('pov_twitter_user_name')) { $twit_user_name = get_settings('pov_twitter_user_name') ; } ?>
                <li class="nobullet"><img class="icon2" src="<?php bloginfo('template_directory'); ?>/images/twitter_16.png" alt="Follow Me!" width="16" height="16" /><a href="http://twitter.com/<?php echo $twit_user_name;                ?>"> Twitter | Follow Me </a> </li>
                <?php $fac_user_name="#" ?><?php if (get_settings('pov_facebook_user_name')) { $fac_user_name = get_settings('pov_facebook_user_name') ; } ?>
                <li class="nobullet"><img class="icon2" src="<?php bloginfo('template_directory'); ?>/images/facebook_16.png" alt="Add Me!" width="16" height="16" /><a href="http://www.facebook.com/<?php echo $twit_user_name;                ?>"> Facebook | Add Me </a> </li>
        </ul>
        <?php } ?>
        
        <!-- Popular Posts -->
        <?php if ($pov_dispopularpost == "true") { } else { ?>
        <h3 >Popular Post</h3>
        <div class="pop">
               			
                        <?php $popular = new WP_Query('orderby=comment_count&posts_per_page=5'); ?>
                        <?php while ($popular->have_posts()) : $popular->the_post(); ?>
                        <h4>
                                <?php the_time('j') ?>
                                <?php the_time(' F ') ?>
                                in
                                <?php the_category(', ') ?>
                        </h4>
                        <h2><a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                                </a></h2>
                        <?php endwhile; ?>
                
        </div>
        <?php } ?>
        
        <!-- begin ads -->
        <?php if ($pov_disads == "true") { } else { ?>
        <div class="box">
                <div class="sideadsbig postthumb"	> 
                        <!-- Content Ad Starts -->
                        <?php if (!get_option('pov_ad_sidebar_disable') && !$is_paged && !$ad_shown) { include (TEMPLATEPATH . "/ads/sidebar_ad_big.php"); $ad_shown = true; }?>
                        <!-- Content Ad Ends -->                           
                </div>
        <?php } ?>
        
           <?php if ($pov_ad_sidebar_125_2_disable == "true") { } else { ?>
                <div class="sideads2 postthumb "	>
                        <?php 
			$ban2 = get_option('pov_banner2'); 
			$url2 = get_option('pov_url2'); 
			?>
         <a href="<?php echo ($url2); ?>" rel="bookmark" title=""><img src="<?php echo ($ban2); ?>" alt="" /></a> </div>
          <?php } ?>
          
                         
         <?php if ($pov_ad_sidebar_125_1_disable == "true") { } else { ?>
                <div class="sideads postthumb">
                        <?php 
			$ban1 = get_option('pov_banner1'); 
			$url1 = get_option('pov_url1'); 
			?>
         <a href="<?php echo ($url1); ?>" rel="bookmark" title=""><img src="<?php echo ($ban1); ?>" alt="" /></a> </div>
          <?php } ?>
          
         <?php if ($pov_ad_sidebar_125_4_disable == "true") { } else { ?>
                <div class="sideads4 postthumb">
                        <?php 
			$ban4 = get_option('pov_banner4'); 
			$url4 = get_option('pov_url4'); 
			?>
          <a href="<?php echo ($url4); ?>" rel="bookmark" title=""><img src="<?php echo ($ban4); ?>" alt="" /></a> </div>
          <?php } ?>
          
          <?php if ($pov_ad_sidebar_125_3_disable == "true") { } else { ?>
                <div class="sideads3 postthumb"	>
                        <?php 
			$ban3 = get_option('pov_banner3'); 
			$url3 = get_option('pov_url3'); 
			?>
         <a href="<?php echo ($url3); ?>" rel="bookmark" title=""><img src="<?php echo ($ban3); ?>" alt="" /></a> </div>
         <?php } ?>
        </div>
        <!-- end ads --> 
        
</div>
