<?php
use ideapeople\board\Button;
use ideapeople\board\Comment;
use ideapeople\util\http\Request;

$comment_ID = Request::get_parameter( 'comment_ID' );
$comment    = get_comment( $comment_ID );

comment_form( array(
	'id_form'      => 'idea-board-comment-form',
	'class_form'   => 'idea-board-reset idea-board-comment-form idea-board-validate',
	'title_reply'  => __( 'Comments' ) . ' ' . __( 'Edit' ),
	'label_submit' => __( 'Comments' ) . ' ' . __( 'Edit' ),
	'action'       => admin_url( '/admin-ajax.php' ) . '?mode=edit&action=idea_handle_comment_submission'
), get_query_var( 'pid' ) );

echo Button::read_button( $comment->comment_post_ID );
?>

<script>
	(function ($) {
		$(document).ready(function () {
			$('#comment').val('<?php echo esc_attr( $comment->comment_content ); ?>');
			$('#comment_password').val('<?php echo Comment::get_comment_password( $comment_ID ); ?>');
		});
	})(jQuery);
</script>
