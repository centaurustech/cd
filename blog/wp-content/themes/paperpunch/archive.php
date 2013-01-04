<?php get_header(); ?>
	<?php if (have_posts()) : ?>
		<div class="post-box page-box">
			<div class="post-header">
				<?php the_post(); ?>
				<?php /* If this is a category archive */ if (is_category()) { ?>
					<h1 class="pagetitle"><?php printf(__( 'Posts from the  &#8216;%s&#8217; Category', 'paperpunch' ), single_cat_title('', false)); ?></h1>
				<?php /* If this is a tag archive */ } elseif ( is_tag() ) { ?>
					<h1 class="pagetitle"><?php printf(__( 'Posts tagged &#8216;%s&#8217;', 'paperpunch' ), single_tag_title('', false)); ?></h1>
				<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
					<h1 class="pagetitle"><?php printf( __( 'Archive for %s', 'paperpunch' ), get_the_time(  'F jS, Y', 'paperpunch' ) ); ?></h1>
				<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
					<h1 class="pagetitle"><?php printf( __( 'Archive for %s', 'paperpunch' ), get_the_time(  'F, Y', 'paperpunch' ) ); ?></h1>
				<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
					<h1 class="pagetitle"><?php printf( __( 'Archive for %s', 'paperpunch' ), get_the_time(  'Y', 'paperpunch' ) ); ?></h1>
				<?php /* If this is an author archive */ } elseif (is_author()) { ?>
					<h1 class="pagetitle"><?php printf(__( 'Posts by %s', 'paperpunch' ), get_the_author() ); ?></h1>
				<?php /* If this is a paged archive */ } elseif ( is_paged() ) { ?>
					<h1 class="pagetitle"><?php _e( 'Blog Archives', 'paperpunch' ); ?></h1>
				<?php } ?>
				<?php rewind_posts(); ?>
			</div><!--end post-header-->
			<div class="entries">
				<img class="archive-comment" src="<?php echo get_template_directory_uri(); ?>/images/comments-bubble.png" width="17" height="14" alt="<?php _e( 'comment', 'paperpunch' ); ?>"/>
				<ul>
					<?php while (have_posts()) : the_post(); ?>
						<li><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><span class="comments_number"><?php comments_number( '0', '1', '%', '' ); ?></span><span class="archdate"><?php the_time(__( 'n.j.y', 'paperpunch' )); ?></span><?php the_title(); ?></a></li>
					<?php endwhile; /* rewind or continue if all posts have been fetched */ ?>
				</ul>
			</div><!--end entries-->
		</div><!--end post-box-->
		<div class="pagination clear">
			<div class="alignleft"><?php next_posts_link(__( '&larr; Older', 'paperpunch' )); ?></div>
			<div class="alignright"><?php previous_posts_link(__( 'Newer &rarr;', 'paperpunch' )); ?></div>
		</div><!--end pagination-->
	<?php endif; ?>
</div><!--end content-->
<?php get_sidebar(); ?>
<?php get_footer(); ?>