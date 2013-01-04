<?php get_header(); ?>
	<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
			<div class="post-box clear">
				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="meta">
						<div class="author"><?php the_time( 'M j' ); ?> <span>/ <?php echo get_the_author(); ?></span></div>
					</div><!--end meta-->

					<div class="post-header">
						<h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_attr( sprintf( __( 'Permanent Link to %s', 'paperpunch' ), the_title_attribute( 'echo=false' ) ) ); ?>"><?php the_title(); ?></a></h2>
					</div><!--end post header-->
					<div class="entry clear">
						<?php the_post_thumbnail( array(250,9999), array( 'class' => ' alignleft border' ) ); ?>
						<?php the_content( __( 'read more...', 'paperpunch' ) ); ?>
						<?php edit_post_link( __( 'Edit this', 'paperpunch' ), '<p>', '</p>' ); ?>
						<?php wp_link_pages(); ?>
					</div><!--end entry-->
					<div class="post-footer clear">
						<div class="category"><?php printf(
							__( 'Filed under %s', 'paperpunch' ),
							get_the_category_list( ', ' )
						); ?></div>
						<?php if ( comments_open( get_the_ID() ) && ! post_password_required( get_the_ID() ) ) : ?>
							<div class="comments"><?php comments_popup_link(__( '<strong>0</strong>', 'paperpunch' ), __( '<strong>1</strong>', 'paperpunch' ), __( '<strong>%</strong>', 'paperpunch' ), '', '' );?></div>
						<?php endif; ?>
					</div><!--end post footer-->
				</div><!--end post-->
			</div><!--end post-box-->
		<?php endwhile; /* rewind or continue if all posts have been fetched */ ?>
		<div class="pagination clear">
			<div class="alignleft"><?php next_posts_link(__( '&larr; Older', 'paperpunch' )); ?></div>
			<div class="alignright" ><?php previous_posts_link(__( 'Newer &rarr;', 'paperpunch' )); ?></div>
		</div><!--end pagination-->
	<?php endif; ?>
</div><!--end content-->
<?php get_sidebar(); ?>
<?php get_footer(); ?>