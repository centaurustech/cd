<!-- Minimal Index STARTS -->

<?php query_posts('showposts=1&offset=0'); ?>

<div <?php post_class(); ?> >
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<div id="first-post" class="<?php sticky_class(); ?>">
        <?php
if (is_sticky()) {?>
        <h1> <a  href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                <?php the_title(); ?>
                </a> </h1>
        <div class="edit">
                <ul>
                        <li>
                                <?php the_time('j') ?>
                                <?php the_time(' F ') ?>
                                //</li>
                        <li> Posted in
                                <?php the_category(', ') ?> 
                                 </li>
                        <li>Sticky Post</li>
                        <li class="com_left">
                                <?php comments_popup_link('No Comment ', '1 Comment  ', '% Comments  '); ?>
                        </li>
                </ul>
        </div>
        <div id="first-post-text">
                <?php the_excerpt('Continue Reading this Entry'); ?>
        </div>
        
        <?php } else { ?>
        <h1> <a  href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                <?php the_title(); ?>
                </a> </h1>
        <div class="edit">
                <ul>
                        <li>
                                <?php the_time('j') ?>
                                <?php the_time(' F ') ?>
                                //</li>
                        <li> Posted in
                                <?php the_category(', ') ?>
                                </li>

                        <li class="com_left">
                                <?php comments_popup_link('No Comment ', '1 Comment  ', '% Comments  '); ?>
                        </li>
                </ul>
        </div>
        <div id="first-post-text">
                <?php
		if ( has_post_thumbnail() ) { ?>
                <div class="postthumb first-post-thumb" >
                        <?php the_post_thumbnail( 'first' ); ?>
                </div>
                <?php } else {
		// the current post lacks a thumbnail
		}
		?>
<?php the_content('<div ><span class="moretext">Continue Reading this Entry</span></div>');
		}
		?>
        </div>
        <?php endwhile; else: ?>
        <?php endif; ?>
</div>
</div>

<?php 
$npostminimal = get_option('pov_npostminimal'); 
?>

<div class="fpost"></div>
<?php $args = array(
	'offset' => 1,
	'showposts' => $npostminimal
);
?>
<?php query_posts($args); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<div class="second-post">

<?php if ($pov_disthumb == "true") { ?>   <?php } else { ?>
<?php		if ( has_post_thumbnail() ) { ?>
                <div class="postthumb second-post-thumb" >
                        <?php the_post_thumbnail( 'second' ); ?>
                </div>                
                <?php } else {
		// the current post lacks a thumbnail
		}
?> 
<?php } ?>



        <div class="second-post-title">
                <h1> <a  href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                        <?php the_title(); ?>
                        </a></h1>
                <div class="edit">
                        <ul>
                                <li>
                                        <?php the_time('j') ?>
                                        <?php the_time(' F ') ?>
                                        // </li>
                                <li>Posted In
                                        <?php the_category(', ') ?>
                                         </li>

                                <li class="com_left">
                                        <?php comments_popup_link('No Comment ', '1 Comment  ', '% Comments  '); ?>
                                </li>
                        </ul>
                </div>
                
     

                <?php if ($pov_disexcerpt == "true") { ?>   <?php } else { ?>
<?php the_excerpt('excerpt_length', 'new_excerpt_length'); ?>
<?php } ?>
                 
        </div>
</div>
<?php endwhile; else: ?>
<?php endif; ?>

<!-- Minimal Index END --> 
