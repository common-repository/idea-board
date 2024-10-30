<?php
/**
 * User: ideapeople
 * Mail: ideapeople@ideapeople.co.kr
 * Homepage : ideapeople@ideapeople.co.kr
 */

namespace ideapeople\board;


use ideapeople\util\http\Request;

class Link {
	public static function delete_comment_link( $comment_ID ) {
		$comment = get_comment( $comment_ID );
		$post    = get_post( $comment->comment_post_ID );

		if ( $post->post_type == PluginConfig::$board_post_type ) {
			$url = add_query_arg( array(
				'comment_ID' => $comment_ID,
				'return_url' => urlencode( get_permalink( $post->ID ) )
			), admin_url( '/admin-ajax.php' ) . '?action=idea_board_comment_delete' );;

			return $url;
		}

		return null;
	}

	public static function default_link_args( $post = null ) {
		$post = get_post( $post );

		$paged = get_query_var( 'paged' );

		$searchType  = Request::get_parameter( 'searchType', false );
		$searchValue = Request::get_parameter( 'searchValue', false );
		$category    = Request::get_parameter( 'idea_board_category', false );

		$args = array(
			'paged'               => $paged == 0 ? false : $paged,
			'pid'                 => $post ? $post->ID : false,
			'idea_board_category' => $category,
			'searchType'          => $searchType,
			'searchValue'         => $searchValue
		);

		if ( ! PluginConfig::is_using_permalink() ) {
			$args[ 'page_id' ] = get_query_var( 'page_id' );
		}

		return $args;
	}

	public static function reply_link( $post = null, $link = '' ) {
		$post = get_post( $post );

		$args = wp_parse_args( array(
			'page_mode' => 'edit',
			'pid'       => false,
			'parent'    => $post->ID
		), self::default_link_args( $post ) );

		if ( PluginConfig::is_using_permalink() ) {
			$post_page = CommonUtils::get_post_page( $post );

			unset( $args[ 'page_mode' ] );
			unset( $args[ 'parent' ] );

			$link = get_permalink( $post_page->ID ) . "idea_board/reply/{$post->ID}/";
		}

		$link = add_query_arg( $args, $link );

		return $link;
	}

	public static function post_type_link( $post_link = '', $post, $link = '' ) {
		$post = get_post( $post );

		if ( $post->post_type != PluginConfig::$board_post_type ) {
			return $post_link;
		}

		$args = wp_parse_args( array(
			'pid'       => $post->ID,
			'page_mode' => 'read'
		), self::default_link_args( $post ) );

		if ( PluginConfig::is_using_permalink() ) {
			$post_page = CommonUtils::get_post_page( $post );

			unset( $args[ 'pid' ] );
			unset( $args[ 'page_mode' ] );

			if ( $post_page ) {
				$link = get_permalink( $post_page->ID ) . "/idea_board/read/{$post->post_name}/";
			}
		} else {
			$args[ 'p' ] = get_query_var( 'p' );
		}

		return add_query_arg( $args, $link );
	}

	public static function delete_action_link( $post = null ) {
		$post = get_post( $post );

		$page = Post::get_board_page( $post->ID );

		if ( is_object( $page ) && $page->post_type != PluginConfig::$board_post_type ) {
			return add_query_arg( array(
				'pid'                                     => $post->ID,
				'mode'                                    => 'delete',
				'return_url'                              => get_permalink( $page->ID ),
				PluginConfig::$idea_board_edit_nonce_name => wp_create_nonce( PluginConfig::$idea_board_edit_nonce_action ),
			), self::edit_ajax_link() );
		}

		return false;
	}

	public static function delete_link( $post = null, $link = '' ) {
		$post = get_post( $post );

		$page = Post::get_board_page( $post->ID );

		$args = wp_parse_args( array(
			'page_mode'  => 'edit',
			'pid'        => $post->ID,
			'edit_mode'  => 'delete',
			'return_url' => get_permalink( $page->ID )
		), self::default_link_args( $post ) );

		if ( PluginConfig::is_using_permalink() ) {
			$post_page = CommonUtils::get_post_page( $post );
			unset( $args[ 'pid' ] );
			unset( $args[ 'page_mode' ] );
			$link = "/{$post_page->post_name}/idea_board/edit/{$post->ID}";
		}

		$link = add_query_arg( $args, $link );

		return $link;
	}

	public static function write_link( $post = null, $link = false ) {
		$post = get_post( $post );

		$args = wp_parse_args( array(
			'page_mode' => 'edit',
			'pid'       => false
		), self::default_link_args( $post ) );

		if ( PluginConfig::is_using_permalink() ) {
			$post_page = CommonUtils::get_post_page( $post );
			unset( $args[ 'page_mode' ] );
			$link = "/{$post_page->post_name}/idea_board/edit";
		}

		$link = add_query_arg( $args, $link );

		return $link;
	}

	public static function comment_delete_link( $comment_ID, $post = null, $link = '' ) {
		$post = get_post( $post );

		$args = wp_parse_args( array(
			'page_mode'  => 'comment_edit',
			'edit_mode'  => 'delete',
			'comment_ID' => $comment_ID
		), self::default_link_args( $post ) );

		if ( PluginConfig::is_using_permalink() ) {
			$post_page = CommonUtils::get_post_page( $post );

			unset( $args[ 'page_mode' ] );
			unset( $args[ 'pid' ] );

			$link = "/{$post_page->post_name}/idea_board/comment_edit/{$post->ID}";
		}


		$link = add_query_arg( $args, $link );

		return $link;
	}

	public static function comment_edit_link( $comment_ID, $post = null, $link = '' ) {
		$post = get_post( $post );

		$args = wp_parse_args( array(
			'page_mode'  => 'comment_edit',
			'comment_ID' => $comment_ID
		), self::default_link_args( $post ) );

		if ( PluginConfig::is_using_permalink() ) {
			$post_page = CommonUtils::get_post_page( $post );

			unset( $args[ 'page_mode' ] );
			unset( $args[ 'pid' ] );

			$link = "/{$post_page->post_name}/idea_board/comment_edit/{$post->ID}";
		}

		$link = add_query_arg( $args, $link );

		return $link;
	}

	public static function edit_link( $post = null, $link = false ) {
		$post = get_post( $post );

		$args = wp_parse_args( array(
			'page_mode' => 'edit'
		), self::default_link_args( $post ) );

		if ( PluginConfig::is_using_permalink() ) {
			$post_page = CommonUtils::get_post_page( $post );

			unset( $args[ 'page_mode' ] );
			unset( $args[ 'pid' ] );

			$link = "/{$post_page->post_name}/idea_board/edit/{$post->ID}";
		}

		$link = add_query_arg( $args, $link );

		return $link;
	}

	public static function list_link( $post = null, $link = false ) {
		$args = wp_parse_args( array(
			'page_mode' => 'list',
			'pid'       => false
		), self::default_link_args( $post ) );

		if ( PluginConfig::is_using_permalink() ) {
			$post_page = CommonUtils::get_post_page( $post );

			unset( $args[ 'page_mode' ] );

			$link = "/{$post_page->post_name}/idea_board/";
		}

		$link = add_query_arg( $args, $link );

		return $link;
	}

	public static function edit_ajax_link( $post = null ) {
		$link = admin_url( '/admin-ajax.php' );

		$args = wp_parse_args( array(
			'action' => PluginConfig::$board_ajax_edit_name,
			'pid'    => false
		), self::default_link_args( $post ) );

		$url = add_query_arg( $args, $link );

		return $url;
	}
}