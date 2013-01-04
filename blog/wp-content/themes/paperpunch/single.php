<?php get_header(); ?>
	<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
			<div class="post-box">
				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="meta clear">
						<div class="author"><?php the_time( 'M j' ); ?> <span>/ <?php echo get_the_author(); ?></span></div>
					</div><!--end meta-->
					<div class="post-header">
						<h1><?php the_title(); ?></h1>
						<?php the_tags( '<div class="tags">', ', ', '</div>' ); ?>
					</div><!--end post header-->
					<div class="entry clear">
						<?php the_post_thumbnail( array(250,9999), array( 'class' => ' alignleft' ) ); ?>
						<?php the_content(__( 'read more...', 'paperpunch' )); ?>
						<?php edit_post_link( __( 'Edit this', 'paperpunch' ), '<p style="clear:both">', '</p>' ); ?>
						<?php wp_link_pages(); ?>
					</div><!--end entry-->
					<div class="post-footer clear">
						<?php if ( ! comments_open() ) : ?>
							<p class="note"><?php _e( 'Comments are closed.', 'paperpunch' ); ?></p>
						<?php endif; ?>
						<div class="category"><?php printf(
							__( 'Filed under %s', 'paperpunch' ),
							get_the_category_list( ', ' )
						); ?></div>
					</div><!--end post footer-->
				</div><!--end post-->
			</div><!--end post-box-->
		<?php endwhile; /* rewind or continue if all posts have been fetched */ ?>
		<?php comments_template( '', true); ?>
	<?php endif; ?>
</div><!--end content-->
<?php get_sidebar(); ?>
<?php get_footer(); ?>