</div>
<!--end container-->

<!--Don't Touch This-->
<?php global $options;
foreach ($options as $value) {
if (get_option( $value['id'] ) === FALSE) { $$value['id'] = $value['std']; } else { $$value['id'] = get_option( $value['id'] ); } }
?>

<!--Include SubFooter-->
<?php if ($pov_disubfooter== "true") { } else { ?>
<?php include(TEMPLATEPATH."/sidebar-footer.php");?>
<?php } ?>

<!--Footer-->
<div id="footer">
        <div id="footer_container">
                            <ul class="right">
                                <li > Powered By:<a href="http://www.wordpress.org">Wordpress</a> //</li>
                                <li > Template: <a href="http://wordfinder.llow.it/wordpress">Wordfinder</a> </li>
                                <?php if ($pov_discredit== "true") { } else { ?>
<li >// Design by: <a href="http://www.llow.it">llowit</a> </li>
<?php } ?>
                        </ul>
                <ul>
                
                        <li><!--Text License-->
                                <?php $footer_license="Insert Here your tipology of license" ?>
                                <?php if (get_option('pov_footer_license')) { $footer_license = get_option('pov_footer_license') ; } ?>
                                <a><?php echo date("Y"); ?>
                                <?php bloginfo('name'); ?>
                                </a> <?php echo $footer_license; ?> </li>
    
                </ul>
        </div>
</div>
<!--Google Analytics From Dashboard-->
<?php 
$pov_google_analytics = get_option('pov_google_analytics');
if ($pov_google_analytics != '') { echo stripslashes($pov_google_analytics); }
?>
<?php wp_footer(); ?>


</body>

</html>