<?php
/**
 * User: ideapeople
 * Mail: ideapeople@ideapeople.co.kr
 * Homepage : ideapeople@ideapeople.co.kr
 */

namespace ideapeople\board\view;

use ideapeople\board\Capability;
use ideapeople\board\CategoryForm;
use ideapeople\board\Query;
use ideapeople\board\SearchForm;
use ideapeople\board\setting\Setting;

class ListView extends AbstractView {
	public function render( $model = null ) {
		if ( ! Capability::current_user_can( 'list' ) ) {
			$view = new AuthFailView();
			echo $view->render( $this->model );

			return false;
		}

		$query = new Query( array(
			'board'          => Setting::get_board()->name,
			'posts_per_page' => Setting::get_post_per_page()
		) );

		$GLOBALS['wp_query'] = $query;

		$this->addAttribute( 'query', $query );

		$output = '';
		$output .= SearchForm::get_search_form();
		$output .= CategoryForm::get_category_form();
		$output .= parent::render();

		wp_reset_query();

		return $output;
	}

	public function getViewName() {
		return 'archive';
	}
}