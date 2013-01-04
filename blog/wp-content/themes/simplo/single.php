<?php get_header(); ?>

<!-- BEGIN COL LEFT -->
				<div id="colLeft">
				
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>	
					<div class="postItem">
						
						<div class="categs"><?php the_category(', ') ?></div>
						<div class="meta">
							<div><?php the_time('M j, Y') ?></div>
							<div class="icoAuthor"><?php the_author_link(); ?></div>
							<div class="icoComments"><?php comments_popup_link('No Comments', '1 Comment ', '% Comments'); ?></div>
						</div>
						<h1><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h1>
						
						<?php the_content(__('Continue reading &raquo;')); ?> 
					</div>
					 <?php comments_template(); ?>
					
				<?php endwhile; ?>
				
				<?php else : ?>
				
				<p>Sorry, but you are looking for something that isn't here.</p>
				
				<?php endif; ?>
				

				</div>
				<!-- END COL LEFT -->
<?php get_sidebar(); ?>	
<?php get_footer(); ?>
