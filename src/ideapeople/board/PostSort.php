<?php
/**
 * Created by PhpStorm.
 * User: ideapeople
 * Date: 2016-09-17
 * Time: 오후 5:38
 */

namespace ideapeople\board;


use ideapeople\board\setting\Setting;
use ideapeople\util\wp\PostHierarchyOrderGenerator;

class PostSort {
	function sort( $query ) {
		$queries = array();

		$this->sort_notice( $queries );

		if ( Setting::get_use_forum( null, false ) ) {
			$this->sort_forum( $queries );
		} else {
			$this->sort_hierarchy( $queries );
		}

		$query = join( ',', $queries );

		return apply_filters( 'idea_board_sort_query', $query );
	}

	function sort_hierarchy( &$queries ) {
		/**
		 * @var $idea_board_post_order_generator PostHierarchyOrderGenerator
		 */
		global $idea_board_post_order_generator;

		$queries = $idea_board_post_order_generator->get_order_by_query( $queries );

		return $queries;
	}

	/**
	 * 공지사항 정렬 기능 추가
	 *
	 * @param $queries
	 *
	 * @return array
	 */
	function sort_notice( &$queries ) {
		global $wpdb;

		$queries[] = " CONVERT((SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'idea_board_is_notice' AND post_id = wp_posts.ID),DECIMAL) ";

		return $queries;
	}

	//가장 최근에 댓글이 등록된글이 최상위로 올라온다
	//가장 최근에 등록된 글이 상위로 올라온다.
	function sort_forum( &$queries ) {
		global $wpdb;

		$queries[] = " CONVERT((SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'idea_board_latest_update' AND post_id = wp_posts.ID),DATETIME) DESC";

		return $queries;
	}
}