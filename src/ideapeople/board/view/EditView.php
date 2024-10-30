<?php
/**
 * User: ideapeople
 * Mail: ideapeople@ideapeople.co.kr
 * Homepage : ideapeople@ideapeople.co.kr
 */

namespace ideapeople\board\view;


use ideapeople\board\CommonUtils;
use ideapeople\board\Link;
use ideapeople\board\PluginConfig;
use ideapeople\board\Query;
use ideapeople\board\setting\Setting;
use ideapeople\util\html\Form;
use ideapeople\util\http\Request;
use ideapeople\util\view\View;

class EditView extends AbstractView {
	public function render( $model = null ) {
		$post = Query::get_single_post( array(
			'board' => Setting::get_board()->name,
			'p'     => get_query_var( 'pid', 1 )
		) );

		$view = apply_filters( 'pre_cap_check_edit_view', null, $post, Setting::get_board() );

		$edit_mode = Request::get_parameter( 'edit_mode' );

		if ( $edit_mode == 'delete' ) {
			if ( $view instanceof View ) {
				$output = $view->render( $this->model );
			} else {
				$output = '<script>location.href="' . Link::delete_action_link( $post ) . '"</script>';
			}
		} else {
			if ( $view instanceof View ) {
				$output = $view->render( $this->model );
			} else {
				$action_url     = Link::edit_ajax_link();
				$parent         = get_query_var( 'parent', 0 );
				$page_permalink = CommonUtils::get_post_page_link();
				$post_page_id   = CommonUtils::get_post_page_id();
				$tax_key        = sprintf( "tax_input[%s][]", PluginConfig::$board_tax );

				$args = compact( 'post', 'action_url', 'parent', 'page_permalink', 'post_page_id', 'tax_key' );

				$args['board'] = $this->board;

				$output = '';
				$output .= sprintf( '<form action="%s" method="post" enctype="multipart/form-data" class="idea-board-validate">', $action_url );

				$output = apply_filters( 'idea_board_edit_form_start', $output, $args );

				$output .= parent::render( $model );

				$hidden_fields = array(
					'parent'                                  => $parent,
					$tax_key                                  => $this->board->term_id,
					'ID'                                      => $post->ID,
					'return_url'                              => $page_permalink,
					'action'                                  => 'idea_board_edit_post',
					'meta_input[idea_board_page_id]'          => $post_page_id,
					PluginConfig::$idea_board_edit_nonce_name => wp_create_nonce( PluginConfig::$idea_board_edit_nonce_action )
				);

				$hidden_fields = apply_filters( 'idea_board_edit_hidden_fields', $hidden_fields, $args );

				foreach ( $hidden_fields as $key => $value ) {
					$output .= Form::hidden( $key, $value );
				}

				$output = apply_filters( 'idea_board_edit_form_end', $output, $args );

				$output .= '</form>';
			}
		}

		wp_reset_query();

		return $output;
	}

	public function getViewName() {
		return 'edit';
	}
}