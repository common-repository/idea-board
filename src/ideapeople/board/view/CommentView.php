<?php
/**
 * Created by PhpStorm.
 * User: ideapeople
 * Date: 2016-09-11
 * Time: 오후 2:08
 */

namespace ideapeople\board\view;


use ideapeople\board\Link;
use ideapeople\board\Query;
use ideapeople\util\http\Request;
use ideapeople\util\view\View;

class CommentView extends AbstractView {
	public function getViewName() {
		return 'comment_edit';
	}

	public function render( $model = null ) {
		$post = Query::get_single_post( array(
			'p'     => get_query_var( 'pid', 1 )
		) );

		$comment_ID = Request::get_parameter( 'comment_ID' );

		$view = apply_filters( 'pre_cap_check_comment_view', null, $comment_ID, $post->ID );

		$edit_mode = Request::get_parameter( 'edit_mode' );

		if ( $edit_mode == 'delete' ) {
			if ( $view instanceof View ) {
				$output = $view->render( $this->model );
			} else if ( is_string( $view ) && ! empty( $view ) ) {
				$output = $view;
			} else {
				$output = '<script>location.href="' . Link::delete_comment_link( $comment_ID ) . '"</script>';
			}
		} else {
			if ( $view instanceof View ) {
				$output = $view->render( $this->model );
			} else {
				add_filter( 'wp_get_current_commenter', array( $this, 'wp_get_current_commenter' ) );
				$output = parent::render( $model );
				remove_filter( 'wp_get_current_commenter', array( $this, 'wp_get_current_commenter' ) );
			}
		}

		wp_reset_query();

		return $output;
	}

	function wp_get_current_commenter( $commenter = array() ) {
		$comment_ID = Request::get_parameter( 'comment_ID' );
		$comment    = get_comment( $comment_ID );

		$commenter[ 'comment_author' ]       = $comment->comment_author;
		$commenter[ 'comment_author_email' ] = $comment->comment_author_email;
		$commenter[ 'comment_author_url' ]   = $comment->comment_author_url;

		return $commenter;
	}
}