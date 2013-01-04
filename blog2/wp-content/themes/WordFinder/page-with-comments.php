<?php
/*
Template Name: Page With Comments Template
(To enable comments on a page, change a page's template by selection this one for it on your post edit page in in the WordPress admin)
*/
?>
<?php get_header(); ?>
<?php get_sidebar(); ?>

<div id="content">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div id="post-<?php the_ID(); ?>">
                <h1 class="titlepage">
                        <?php the_title(); ?>
                </h1>
                <div class="em-above">
                        <?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>
                        <?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
                </div>
                <!--em-above--> 
        </div>
        <?php comments_template(); ?>
        <?php endwhile; endif; ?>
        <div class="metapage"> </div>
        <!--metapage--> 
</div>
<!--content-->

<?php get_footer(); ?>
