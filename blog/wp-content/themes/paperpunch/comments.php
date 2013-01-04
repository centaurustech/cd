<?php if ( post_password_required() ) : ?>
	<p class="nocomments"><?php _e( 'This post is password protected. Enter the password to view comments.', 'paperpunch' ); ?></p>
	<?php 
	return; 
endif; ?>

<div id="comments">
<?php if ( have_comments() ) : ?>
	<div class="comment-number">
		<h4><?php comments_number( __( 'Leave a comment', 'paperpunch' ), __( 'One Comment', 'paperpunch' ), __( '% Comments', 'paperpunch' )); ?></h4>
		<?php if ( comments_open() ) : ?>
			<span><a href="#respond" title="<?php esc_attr_e( 'Leave a comment', 'paperpunch' ); ?>"><?php _e( 'Leave a comment', 'paperpunch' ); ?></a></span>
		<?php endif; ?>
	</div><!--end comment-number-->

	<ol class="commentlist">
		<?php wp_list_comments( 'type=comment&callback=paperpunch_custom_comment' ); ?>
	</ol>

	<div class="navigation">
		<div class="alignleft"><?php next_comments_link(__( '&laquo; Older Comments', 'paperpunch' )); ?></div>
		<div class="alignright"><?php previous_comments_link(__( 'Newer Comments &raquo;', 'paperpunch' )); ?></div>
	</div>
	<?php if ( ! empty($comments_by_type['pings']) ) : ?>
		<h3 class="pinghead"><?php _e( 'Trackbacks and Pingbacks', 'paperpunch' ); ?></h3>
		<ol class="pinglist">
			<?php wp_list_comments( 'type=pings&callback=paperpunch_list_pings' ); ?>
		</ol>

		<div class="navigation">
			<div class="alignleft"><?php next_comments_link(__( '&laquo; Older Pingbacks', 'paperpunch' )); ?></div>
			<div class="alignright"><?php previous_comments_link(__( 'Newer Pingbacks &raquo;', 'paperpunch' )); ?></div>
		</div>
	<?php endif; ?>
	<?php if ( ! comments_open() ) : ?>
		<p class="note"><?php _e( 'Comments are closed.', 'paperpunch' ); ?></p>
	<?php endif; ?>
<?php endif; ?>
</div><!--end comments-->

<?php

$req = get_option( 'require_name_email' );
$field = '<p><label for="%1$s" class="comment-field">%2$s</label><input class="text-input" type="text" name="%1$s" id="%1$s" value="%3$s" size="22" tabindex="%4$d" /></p>';
comment_form( array(
	'comment_field' => '<p><label for="comment" class="comment-field"><small>' . _x( 'Comment', 'noun', 'paperpunch' ) . '</small></label><textarea id="comment" name="comment" cols="50" rows="10" aria-required="true" tabindex="4"></textarea></p>',
	'comment_notes_before' => '',
	'comment_notes_after' => sprintf(
		'<div id="comments-rss"><a href="%1$s">%2$s</a></div>',
		esc_attr( get_post_comments_feed_link() ),
		__( 'Comments Feed', 'paperpunch' )
	),
	'fields' => array(
		'author' => sprintf(
			$field,
			'author',
			(
				$req ?
				__( 'Name <span>(required)</span>', 'paperpunch' ) :
				__( 'Name', 'paperpunch' )
			),
			esc_attr( $comment_author ),
			1
		),
		'email' => sprintf(
			$field,
			'email',
			(
				$req ?
				__( 'Email <span>(required, will not be published)</span>', 'paperpunch' ) :
				__( 'Email', 'paperpunch' )
			),
			esc_attr( $comment_author_email ),
			2
		),
		'url' => sprintf(
			$field,
			'url',
			__( 'Website', 'paperpunch' ),
			esc_attr( $comment_author_url ),
			3
		),
	),
	'label_submit' => __( 'Submit Comment', 'paperpunch' ),
	'logged_in_as' => '<p>' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out &raquo;</a>', 'paperpunch' ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink() ) ) ) . '</p>',
	'title_reply' => __( 'Leave a comment', 'paperpunch' ),
	'title_reply_to' => __( 'Leave a comment to %s', 'paperpunch' ),
) );

?>