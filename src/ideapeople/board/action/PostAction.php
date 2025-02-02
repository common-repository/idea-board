<?php
/**
 * Created by PhpStorm.
 * User: ideapeople
 * Date: 2016-08-24
 * Time: 오후 11:43
 */

namespace ideapeople\board\action;


use ideapeople\board\Capability;
use ideapeople\board\Link;
use ideapeople\board\PluginConfig;
use ideapeople\board\Post;
use ideapeople\board\PostView;
use ideapeople\board\setting\Setting;
use ideapeople\util\http\Request;
use ideapeople\util\wp\MetaUtils;
use ideapeople\util\wp\PostUtils;

/**
 * Class BoardPostAction
 * @package ideapeople\board\action
 */
class PostAction {
	public $post_data;

	public function __construct( $post_data = null ) {
		if ( empty( $post_data ) ) {
			$this->post_data = wp_parse_args( $post_data, $_REQUEST );
		}
	}

	public function idea_board_edit_post( $post_data = null ) {
		if ( empty( $post_data ) ) {
			$post_data = $this->post_data;
		}

		$post_data = wp_parse_args( $post_data, array(
			'post_content' => Request::get_parameter( 'idea_board_post_content' ),
			'post_parent'  => Request::get_parameter( 'parent', 0 )
		) );

		if ( Setting::get_use_forum( null, false ) && $post_data[ 'post_parent' ] ) {
			wp_die( 'Forum will not be registered to comment. ' );
		}

		$post_data[ 'post_type' ]   = PluginConfig::$board_post_type;
		$post_data[ 'post_status' ] = 'publish';
		$post_data[ 'post_author' ] = ! is_user_logged_in() ? - 1 : null;

		if ( ! Capability::is_board_admin() ) {
			$post_data[ 'post_content' ] = strip_shortcodes( $post_data[ 'post_content' ] );
		}

		do_action( 'idea_board_action_post_edit_pre', $post_data );

		$error      = new \WP_Error();
		$post_id    = ! empty( $post_data[ 'ID' ] ) ? $post_data[ 'ID' ] : $post_data[ 'pid' ];
		$return_url = Request::get_parameter( 'return_url', null );
		$nonce      = $post_data[ PluginConfig::$idea_board_edit_nonce_name ];

		if ( ! $this->is_valid_nonce( $nonce ) ) {
			wp_die();
		}

		if ( ! is_user_logged_in() && ! isset( $post_data[ 'post_password' ] ) && empty( $post_data[ 'post_password' ] ) ) {
			wp_die();
		}

		if ( isset( $post_data[ 'mode' ] ) ) {
			$mode = $post_data[ 'mode' ];
		} else {
			if ( empty( $post_id ) || $post_id == - 1 ) {
				$mode = 'insert';
			} else {
				$mode = 'update';
			}
		}

		$board = idea_board_sanitize_board( $post_data );

		if ( ! $board ) {
			wp_die( 'BOARD REQUIRED' );
		}

		$view = apply_filters( 'pre_cap_check_edit_view', null, get_post( $post_id ), $board );

		PostView::handle_view( $view );

		switch ( $mode ) {
			case 'insert':
				unset( $post_data[ 'ID' ] );

				if ( empty( $post_data[ 'comment_status' ] ) ) {
					$post_data[ 'comment_status' ] = Setting::get_default_comment_status( $board->term_id );
				}

				$post_id = wp_insert_post( $post_data, $error );

				break;
			case 'update':
				if ( Capability::is_board_admin() ) {
					unset( $post_data[ 'post_author' ] );
				}

				$post_id = wp_update_post( $post_data, $error );

				break;
			case 'delete':
				$post = Post::get_post( $post_id );

				$post_id = wp_trash_post( $post->ID );
				break;
			default :
				break;
		}

		if ( $return_url ) {
			if ( $mode == 'delete' ) {
				$return_url = add_query_arg( array( 'pid' => false, 'page_mode' => 'list' ), $return_url );
			} else {
				$return_url = Link::post_type_link( '', $post_id, $return_url );
			}
		}

		do_action( 'idea_board_action_post_edit_after', $post_data, $post_id, $board, $mode );

		if ( $return_url ) {
			wp_redirect( $return_url );
		}

		die;
	}

	public function post_update_private_meta( $board_term, $post_id ) {
		if ( $post_id ) {
			PostUtils::insert_or_update_meta( $post_id, 'idea_board_remote_ip', $_SERVER[ 'REMOTE_ADDR' ] );
			PostUtils::insert_or_update_meta( $post_id, 'idea_board_term', $board_term );

			Post::update_latest_post( $post_id );
		}
	}

	public function post_update_tax( $post, $tax_input = array() ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		if ( ! is_array( $tax_input ) ) {
			return false;
		}

		foreach ( $tax_input as $taxonomy => $tags ) {
			$taxonomy_obj = get_taxonomy( $taxonomy );

			if ( ! $taxonomy_obj ) {
				continue;
			}

			if ( is_array( $tags ) ) {
				$tags = array_filter( $tags );
			}

			wp_set_post_terms( $post->ID, $tags, PluginConfig::$board_tax );
		}

		return true;
	}

	public function post_update_public_meta( $post, $meta_values = array() ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		$idea_board_public_meta_keys = Post::get_public_meta_keys();
		$idea_board_force_meta_keys  = Post::get_force_meta_keys();

		foreach ( $idea_board_public_meta_keys as $key ) {
			if ( is_array( $meta_values ) ) {
				$meta_keys = array_keys( $meta_values ? $meta_values : array() );

				if ( in_array( $key, $meta_keys ) ) {
					$new_meta_value = $meta_values[ $key ];

					PostUtils::insert_or_update_meta( $post->ID, $key, $new_meta_value );
				} else {
					if ( ! MetaUtils::has_meta( 'post', $key, $post->ID ) ) {
						add_post_meta( $post->ID, $key, false );
					} else {
						if ( in_array( $key, $idea_board_force_meta_keys ) ) {
							update_post_meta( $post->ID, $key, false );
						}
					}
				}
			} else {
				if ( ! MetaUtils::has_meta( 'post', $key, $post->ID ) ) {
					add_post_meta( $post->ID, $key, false );
				}
			}
		}

		if ( Setting::is_only_secret() ) {
			PostUtils::insert_or_update_meta( $post->ID, 'idea_board_is_secret', true );
		}

		return true;
	}

	public function update_idea_post( $post_id ) {
		$post = get_post( $post_id );

		if ( $post->post_type != PluginConfig::$board_post_type ) {
			return false;
		}

		$this->post_update_tax( $post_id, @$this->post_data[ 'tax_input' ] );
		$this->post_update_public_meta( $post_id, @$this->post_data[ 'meta_input' ] );

		if ( is_array( $this->post_data ) && isset( $this->post_data[ 'tax_input' ] ) ) {
			$tax = $this->post_data[ 'tax_input' ][ PluginConfig::$board_tax ];
			$this->post_update_private_meta( $tax[ count( $tax ) - 1 ], $post_id );
		}

		return $post_id;
	}

	public function is_valid_nonce( $nonce ) {
		$action = PluginConfig::$idea_board_edit_nonce_action;

		return $nonce && wp_verify_nonce( $nonce, $action );
	}
}