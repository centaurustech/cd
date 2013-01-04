<?php get_header(); ?>
<?php get_sidebar(); ?>

<div id="content">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="post" id="post-<?php the_ID(); ?>">
                <h1 class="titlepage">
                        <?php the_title(); ?>
                </h1>
                <?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>
                <?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
        </div>
        <?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>
