<?php global $options;
foreach ($options as $value) {
if (get_option( $value['id'] ) === FALSE) { $$value['id'] = $value['std']; } else { $$value['id'] = get_option( $value['id'] ); } }
?>
<?php get_header(); ?>
<?php get_sidebar(); ?>

<div id="content">

        <?php if ($pov_index == "diary") { include(TEMPLATEPATH."/diary.php"); } else { ?>
        <?php include(TEMPLATEPATH."/minimal.php");?>
        <?php } ?>
</div>
<?php get_footer(); ?>
