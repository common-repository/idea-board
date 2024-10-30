<?php
/**
 * User: ideapeople
 * Mail: ideapeople@ideapeople.co.kr
 * Homepage : ideapeople@ideapeople.co.kr
 */

namespace ideapeople\board;


use ideapeople\board\setting\Setting;
use WP_Query;

class Query extends WP_Query {
	public $start_no;

	public function __construct( $query = '' ) {
		$post_sort = new PostSort();

		add_filter( 'posts_orderby', array( $post_sort, 'sort' ) );

		$query = wp_parse_args( $query, array(
			'post_type'      => PluginConfig::$board_post_type,
			'post_status'    => array(
				'publish',
				'private',
			),
			'paged'          => get_query_var( 'paged' ),
			'posts_per_page' => ! empty( $query[ 'posts_per_page' ] ) ? $query[ 'posts_per_page' ] : 10,
			'tax_query'      => array(
				'relation' => 'AND',
				array(
					'taxonomy' => PluginConfig::$board_tax,
					'field'    => 'name',
					'terms'    => @$query[ 'board' ]
				)
			),
		) );

		parent::__construct( $query );

		$this->start_no = $this->generateStartNo( get_query_var( 'paged' ), $query[ 'posts_per_page' ] );

		remove_filter( 'posts_orderby', array( $post_sort, 'sort' ) );
	}

	public static function start_no() {
		global $wp_query;

		return $wp_query->start_no;
	}

	public function get( $query_var, $default = '' ) {
		global $wp_the_query;

		$var = false;

		if ( isset( $wp_the_query->query_vars[ $query_var ] ) ) {
			$var = $wp_the_query->query_vars[ $query_var ];
		} else if ( isset( $this->query_vars[ $query_var ] ) ) {
			$var = $this->query_vars[ $query_var ];
		}

		return $var;
	}

	public function get_posts() {
		do_action( 'idea_board_pre_get_posts', $this );

		$posts = parent::get_posts();

		do_action( 'idea_board_after_get_posts', $this );

		return $posts;
	}

	private function generateStartNo( $paged, $posts_per_page = 10 ) {
		$found_posts = $this->found_posts;

		$paged = $paged == 0 ? 1 : $paged;

		$number = ( $paged - 1 ) * $posts_per_page;

		return $found_posts - $number;
	}

	public static function get_single_post( $args = array() ) {
		$pname = get_query_var( 'pname', false );
		$pname = mb_detect_encoding( $pname, 'euc-kr' ) ? iconv( 'euc-kr', 'utf-8', $pname ) : $pname;

		$args = wp_parse_args( $args, array(
			'board' => Setting::get_board()->name,
			'p'     => '',
			'paged' => 0,
			'pname' => $pname
		) );

		if ( $args[ 'pname' ] ) {
			$args[ 'post_name__in' ] = array( $args[ 'pname' ] );
		}

		$post = null;

		$query = new Query( $args );

		$GLOBALS[ 'wp_query' ] = $query;

		if ( $query->found_posts ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post = get_post();
				break;
			}
		} else {
			$p     = new \stdClass();
			$p->ID = - 1;
			$post  = new \WP_Post( $p );
		}

		$query->is_single   = true;
		$query->is_singular = true;

		return $post;
	}
}