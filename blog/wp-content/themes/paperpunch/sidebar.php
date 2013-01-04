<?php global $paperpunch; ?>
<div id="sidebar">
	<ul>
		<?php if ( ! dynamic_sidebar('sidebar-1') ) : ?>
			<li class="widget widget_recent_entries">
				<h2 class="widgettitle"><?php _e('Recent Articles', 'paperpunch'); ?></h2>
				<ul>
					<?php
					$side_posts_query = new WP_Query('posts_per_page=10');
					while( $side_posts_query->have_posts() ) :
						$side_posts_query->the_post();
						?>
						<li><a href= "<?php echo get_permalink(); ?>"><?php the_title(); ?></a></li>
					<?php endwhile; ?>
				</ul>
			</li>
		<?php endif; ?>
	</ul>
</div><!--end sidebar-->