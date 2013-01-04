<?php get_header(); ?>

<!-- BEGIN COL LEFT -->
				<div id="colLeft">
				<!-- archive-title -->				
						<?php if(is_month()) { ?>
						<div id="archive-title">
						Browsing articles from "<strong><?php the_time('F, Y') ?></strong>"
						</div>
						<?php } ?>
						<?php if(is_category()) { ?>
						<div id="archive-title">
						Browsing articles in "<strong><?php $current_category = single_cat_title("", true); ?></strong>"
						</div>
						<?php } ?>
						<?php if(is_tag()) { ?>
						<div id="archive-title">
						Browsing articles tagged with "<strong><?php wp_title('',true,''); ?></strong>"
						</div>
						<?php } ?>
						<?php if(is_author()) { ?>
						<div id="archive-title">
						Browsing articles by "<strong><?php wp_title('',true,''); ?></strong>"
						</div>
						<?php } ?>
					<!-- /archive-title -->
				
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
					
					
				<?php endwhile; ?>
				
				<?php else : ?>
				
				<p>Sorry, but you are looking for something that isn't here.</p>
				
				<?php endif; ?>
				<!--<div class="navigation">
							<div class="alignleft"><?php next_posts_link() ?></div>
							<div class="alignright"><?php previous_posts_link() ?></div>
				</div>-->
				<?php if (function_exists("emm_paginate")) {
					emm_paginate();
				} ?>

				</div>
				<!-- END COL LEFT -->
<?php get_sidebar(); ?>	
<?php get_footer(); ?>
