<?php
/**
 * Created by PhpStorm.
 * User: ideapeople
 * Date: 2016-08-24
 * Time: 오후 10:54
 */

namespace ideapeople\board;


use ideapeople\util\html\Form;

class CommonUtils {
	public static function role_as_select_box( $name, $selected = null, $args = array() ) {
		$roles = new Roles();

		$args = wp_parse_args( $args, array(
			'multiple' => true,
			'class'    => "chosen-select"
		) );

		$options = array();

		foreach ( $roles->get_roles() as $role => $role_name ) {
			$options[ $role ] = $role_name;
		}

		return Form::select( $name, $options, $selected, $args );
	}

	public static function get_post_page( $post = null ) {
		if ( $post = get_post( $post ) ) {
			if ( $post->post_type == PluginConfig::$board_post_type && $post->ID ) {
				$v = Post::get_board_page( $post->ID );

				if ( $v->post_type != PluginConfig::$board_post_type ) {
					return $v;
				}
			}
		}

		/**
		 * @var $wp_the_query \WP_Query
		 */
		global $wp_the_query;

		if ( empty( $wp_the_query->query_vars ) ) {
			return false;
		}

		if ( $wp_the_query->query_vars[ 'page_id' ] ) {
			return get_post( $wp_the_query->query_vars[ 'page_id' ] );
		} else if ( $wp_the_query->query_vars[ 'pagename' ] ) {
			return get_page_by_path( $wp_the_query->query_vars[ 'pagename' ] );
		}

		return false;
	}

	public static function get_post_page_id( $post = null ) {
		return self::get_post_page( $post )->ID;
	}

	public static function get_post_page_link( $post = null ) {
		return get_permalink( self::get_post_page_id( $post ) );
	}

	public static function get_page_mode() {
		$page_mode = get_query_var( 'page_mode' ) ? get_query_var( 'page_mode' ) : $_REQUEST[ 'page_mode' ];

		return $page_mode;
	}
}