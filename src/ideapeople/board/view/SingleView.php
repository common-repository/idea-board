<?php
/**
 * Created by PhpStorm.
 * User: ideapeople
 * Date: 2016-08-24
 * Time: 오후 4:48
 */

namespace ideapeople\board\view;


use ideapeople\board\Post;
use ideapeople\board\Query;
use ideapeople\board\setting\Setting;
use ideapeople\util\view\View;

class SingleView extends AbstractView {
	public function render( $model = null ) {
		$post = Query::get_single_post( array(
			'p' => get_query_var( 'pid' )
		) );

		if ( $post->ID == - 1 ) {
			die( '404 post error' );
		}

		$view = apply_filters( 'pre_cap_check_read_view', null, $post );

		if ( $view instanceof View ) {
			$output = $view->render( $this->model );
		} else {
			Post::update_read_cnt();

			$output = '';
			$output .= sprintf( '<article id="idea-board-post-%s" class="idea-board-article">', $post->ID );

			$output = apply_filters( 'idea_board_single_start', $output );

			$output .= parent::render( $model );
			$output .= $this->comments_template( Setting::get_board_id() );

			$output = apply_filters( 'idea_board_single_end', $output, $post );

			$output .= '</article>';
		}

		wp_reset_query();

		return $output;
	}

	public function comments_template( $term_id ) {
		ob_start();

		if ( Setting::get_use_comment( $term_id ) ) {
			if ( Setting::get_use_comment_skin( $term_id ) ) {
				add_filter( 'comments_open', array( $this, 'comments_open' ), 999 );
				add_filter( 'comments_template', array( $this, '_comments_template' ) );

				comments_template();

				remove_filter( 'comments_template', array( $this, '_comments_template' ) );
				remove_filter( 'comments_open', array( $this, 'comments_open' ), 999 );
			} else {
				comments_template();
			}
		}

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	public function comments_open( $open ) {
		return Post::get_post()->comment_status;
	}

	public function _comments_template( $template ) {
		$skin_path = Setting::get_board_skin_path();

		return $skin_path . '/comments.php';
	}

	public function getViewName() {
		return 'single';
	}
}