<!-- Diary Index STARTS -->
<?php query_posts('showposts=1&offset=0'); ?>
<div <?php post_class(); ?> >

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div id="four-post"  class="<?php sticky_class(); ?>">

      <?php
if (is_sticky()) {?>
<div class="edit">
                <ul>
                                 
                        <li class="com_left">
                                <?php comments_popup_link('0 ', '1  ', '%  '); ?>
                                </li>
                        <li>
                                <?php the_time('j') ?>
                                <?php the_time(' F ') ?>
                             
                        </li>
                        <li>
                                <?php the_category(', ') ?> | Sticky Post
                        </li>
                       
                </ul>
        </div>
        <h5> <a  href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                <?php the_title(); ?>

                </a> </h5>

                <?php the_excerpt('excerpt_length', 'new_excerpt_length'); ?>

                <?php } else { ?>
                
                <div class="edit">
                <ul>
                        <li class="com_left">
                                <?php comments_popup_link('0 ', '1  ', '%  '); ?>
                                </li>
                        <li>
                                <?php the_time('j') ?>
                                <?php the_time(' F ') ?>
                        </li>
                        <li>
                                <?php the_category(', ') ?>
                        </li>
                </ul>
        </div>
        <h1> <a  href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                <?php the_title(); ?>

                </a> </h1>


        <div id="four-post-text">
                <?php
		if ( has_post_thumbnail() ) { ?>
                <div class="postthumb" id="four-post-thumb" >
                        <?php the_post_thumbnail( 'first' ); ?>
                </div>
                <?php } else {
		// the current post lacks a thumbnail
		}
		?>

        </div>
		 <?php the_content('Continue Reading this Entry');
		}
		?>
      
        
</div>
        <?php endwhile; else: ?>
        <?php endif; ?>

</div>

<div id="container_diary">

<?php 
$npostdiary = get_option('pov_npostdiary'); 
?>

<?php $args = array(
	'offset' => 1,
	'showposts' => $npostdiary
);
?>
<?php query_posts($args); ?>
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="third-post">
                <div class="edit">
                        <ul>
                                <li class="com_left">
                                        <?php comments_popup_link('0 ', '1  ', '%  '); ?>
                                        </li>
                                <li>
                                        <?php the_time('j') ?>
                                        <?php the_time(' F ') ?>
                                </li>
                                <li>
                                        <?php the_category(', ') ?>
                                         </li>
                        </ul>
                </div>
                <h1> <a  href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                        <?php the_title(); ?>
                        </a></h1>
               
                        <?php the_excerpt('excerpt_length', 'new_excerpt_length'); ?>
             
        </div>
        <?php endwhile; else: ?>
        <?php endif; ?>
</div>
<!-- Diary Index  END --> 

