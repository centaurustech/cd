<?php get_header(); ?>
<?php get_sidebar(); ?>

<div id="content">
        <div id="currentbrowsing">
                <h1>Currently Browsing</h1>
                <?php if (have_posts()) : ?>
                <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
                <?php /* If this is a category archive */ if (is_category()) { ?>
                <h2>
                        <?php single_cat_title(); ?>
                </h2>
                <?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
                <h2>Posts Tagged &#8216;
                        <?php single_tag_title(); ?>
                        &#8217;</h2>
                <?php /* If this is a daily archive */ } elseif (is_day()) { ?>
                <h2>
                        <?php the_time('F jS, Y'); ?>
                </h2>
                <?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
                <h2>
                        <?php the_time('F, Y'); ?>
                </h2>
                <?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
                <h2>
                        <?php the_time('Y'); ?>
                </h2>
                <?php /* If this is an author archive */ } elseif (is_author()) { ?>
                <h2>Author Archive</h2>
                <?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
                        <h2>Blog Archives</h2>
                        <?php } ?>
        </div>
        <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
        <div id="archive-post">
                <h1> <a  href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                        <?php the_title(); ?>
                        </a></h1>
                <div class="edit">
                        <ul>
                                <li>
                                        <?php the_time('j') ?>
                                        <?php the_time(' F ') ?>
                                        //</li>
                                <li> Posted in
                                        <?php the_category(', ') ?>
                                        // </li>
                                  <li>Tags :
                                <?php the_tags(' ', ', ', ' '); ?>
                        </li>
                                <li class="com_left"><a>
                                        <?php comments_popup_link('No Comment ', '1 Comment  ', '% Comments  '); ?>
                                        </a></li>
                        </ul>
                </div>
                <div id="archive-post-text">
                        <?php
		if ( has_post_thumbnail() ) { ?>
                        <div class="postthumb archive-post-thumb" >
                                <?php the_post_thumbnail( 'first' ); ?>
                        </div>
                        <?php } else {
		// the current post lacks a thumbnail
		}
		?>
                        <?php the_content('<div ><span class="moretext">Continue Reading this Entry</span></div>'); ?>
                </div>
        </div>
        <div id="contentblock"></div>
        <?php comments_template(); ?>
        <?php endwhile; else: ?>
        <h1>Not Found</h1>
        <p>
                <?php _e('Sorry, no posts matched your criteria.'); ?>
        </p>
        <?php endif; ?>
        <?php if ($paged > 1) { ?>
        <div id="navigation">
                <div class="nextright">
                        <?php previous_posts_link('Newer Entries') ?>
                </div>
                <div class="prevleft">
                        <?php next_posts_link(' Older Entries') ?>
                </div>
        </div>
        <?php } else { ?>
        <div id="navigation">
                <div class="prevleft">
                        <?php next_posts_link(' Older Entries') ?>
                </div>
        </div>
        <?php } ?>
        <?php endif; ?>
</div>
<?php get_footer(); ?>
