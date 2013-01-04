<?php

// Template for pingbacks/trackbacks
function paperpunch_list_pings($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment;
	?>
	<li id="comment-<?php comment_ID(); ?>"><?php comment_author_link(); ?>
	<?php
}

function paperpunch_custom_comment ( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	$comment_user = null;
	if ( ! empty( $comment->user_id ) ) {
		$comment_user = get_user_by( 'id', $comment->user_id );
	} elseif ( ! empty( $comment->comment_author_email ) ) {
		$comment_user = get_user_by( 'email', $comment->user_id );
	}

	$admin_comment_class = (
		! empty( $comment_user->wp_capabilities ) &&
		is_array( $comment_user->wp_capabilities ) &&
		in_array( 'administrator', $comment_user->wp_capabilities )
	) ? 'admin-comment' : '';

	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>" >
		<div class="comment-box clear">
			<div class="c-head clear <?php echo $admin_comment_class; ?>">
				<?php comment_author_link(); ?> <span>/ <?php comment_date( 'M j Y' ); ?></span>
			</div>
			<div class="c-grav"><?php echo get_avatar( $comment, '32' ); ?></div>
			<div class="c-body">
				<?php if ($comment->comment_approved == '0' ) : ?>
					<p><?php _e( '<em><strong>Please Note:</strong> Your comment is awaiting moderation.</em>', 'paperpunch' ); ?></p>
				<?php endif; ?>
				<?php comment_text(); ?>
				<?php comment_type( ( '' ), __( 'Trackback', 'paperpunch' ), __( 'Pingback', 'paperpunch' ) ); ?>
				<div class="reply">
					<?php echo comment_reply_link(array( 'depth' => $depth, 'max_depth' => $args['max_depth'])); ?>
				</div>
				<?php edit_comment_link( __('edit', 'paperpunch'),'<p>','</p>' ); ?>
			</div><!--end c-body-->
		</div><!--end comment-box-->
	<?php
}

// eof