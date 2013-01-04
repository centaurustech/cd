<?php get_header(); ?>
<?php get_sidebar(); ?>

<div id="content">
        <div id="currentbrowsing">
                <h1>Posts founded for</h1>
                <h2>
                        <?php the_search_query(); ?>
                </h2>
        </div>
        <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
        <div id="archive-post">
                <div class="edit">
                        <li class="time">
                                <?php the_time('j') ?>
                                <?php the_time(' F ') ?>
                                <br>
                                <?php the_category(', ') ?>
                        </li>
                        <li>
                                <h1> <a  href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                                        <?php the_title(); ?>
                                        </a></h1>
                        </li>
                        <div class="commenticon2"><a>
                                <?php comments_popup_link('0 ', '1 ', '% '); ?>
                                </a> </div>
                </div>
                <div id="second-post-title">
                        <div id="archive-post-thumb" > <a href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">
                                <?php the_post_thumbnail( 'second' ); ?>
                                </a> </div>
                        <p>
                                <?php wpe_excerpt('wpe_excerptlength_index', 'wpe_excerptmore'); ?>
                        </p>
                </div>
        </div>
        <div id="contentblock"></div>
        <?php endwhile; ?>
        <?php else : ?>
        <p>No matching criteria. Please try a different search, or maybe browse through our most recent posts in the footer.</p>
        <?php endif; ?>
        <div id="pagenavi">
                <?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); } ?>
        </div>
</div>
<?php get_footer(); ?>
