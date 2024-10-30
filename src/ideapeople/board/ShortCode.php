<?php
/**
 * User: ideapeople
 * Mail: ideapeople@ideapeople.co.kr
 * Homepage : ideapeople@ideapeople.co.kr
 */

namespace ideapeople\board;


use ideapeople\board\setting\Setting;

class ShortCode {
	public function idea_board( $atts, $content ) {
		$atts = wp_parse_args( $atts, array(
			'name'      => '',
			'page_mode' => ''
		) );

		$page_mode = $atts[ 'page_mode' ] ? $atts[ 'page_mode' ] : CommonUtils::get_page_mode();

		$board = Setting::get_board( $atts[ 'name' ] );

		$output = PostView::get_view( $board, $page_mode );

		return $output . $content;
	}
}