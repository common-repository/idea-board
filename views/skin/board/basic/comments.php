<?php
use ideapeople\board\Button;

/**
 * @param $comment
 * @param $args
 * @param $depth
 */
if ( ! function_exists( 'idea_board_comment_list' ) ):
	function idea_board_comment_list( $comment, $args, $depth ) {
		$comment_ID = $comment->comment_ID; ?>
		<li class="idea-board-comment-body" id="comment-<?php comment_ID() ?>">
			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="idea-board-comment-awaiting-moderation"><?php _e_idea_board( 'Comments awaiting approval' ); ?></p>
			<?php endif; ?>
			<div class="idea-board-comment-user-avatar">
				<?php echo get_avatar( $comment->comment_author_email, 30 ); ?>
			</div>
			<div class="idea-board-comment-author"><?php comment_author( $comment_ID ); ?></div>
			<div class="idea-board-comment-date"><?php comment_date( 'Y-m-d' ); ?></div>
			<div class="idea-board-comment-content"><?php comment_text( $comment_ID ); ?></div>

			<div class="idea-board-comment-buttons">
				<?php echo Button::comment_delete_button( $comment_ID ) ?>
				<?php echo Button::comment_edit_button( $comment_ID ) ?>
				<?php
				comment_reply_link( array_merge( $args, array(
					'depth'     => $depth,
					'max_depth' => $args['max_depth']
				) ) );
				?>
			</div>
		</li>
		<?php
	}
endif;

?>
<div id="idea-board-comments" class="idea-board-comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="idea-board-comments-title">
			<?php
			printf( _nx( 'One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'idea-board' ), number_format_i18n( get_comments_number() ), get_the_title() );
			?>
		</h2>

		<ol class="idea-board-comment-list">
			<?php
			wp_list_comments( array(
				'callback' => 'idea_board_comment_list',
				'type'     => 'comment'
			) );
			?>
		</ol>
	<?php endif; ?>

	<?php
	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="idea-board-no-comments"><?php _e( 'Comments are closed.' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form( array(
		'action'      => admin_url( '/admin-ajax.php' ) . '?action=idea_handle_comment_submission',
		'id_form'     => 'idea-board-comment-form',
		'class_form'  => 'idea-board-reset idea-board-comment-form idea-board-validate',
		'title_reply' => __( 'Comments' ) . ' ' . __( 'Edit' ),
	) );
	?>
</div>
