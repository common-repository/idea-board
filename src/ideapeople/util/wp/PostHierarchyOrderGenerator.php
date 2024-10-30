<?php
/**
 * Created by PhpStorm.
 * User: ideapeople
 * Date: 2016-08-25
 * Time: 오후 5:26
 */

namespace ideapeople\util\wp;


use wpdb;

/**
 * Class PostOrderGenerator
 * @package ideapeople\util\wp
 *
 * $this->loader->add_filter( 'the_title', $orderGenerator, 'the_title', 10, 2 );
 */
class PostHierarchyOrderGenerator {
	public $grp_key;
	public $ord_key;
	public $depth_key;
	public $post_type;

	/**
	 * PostOrderGenerator constructor.
	 *
	 * @param $grp_key
	 * @param $ord_key
	 * @param $depth_key
	 * @param $post_type
	 */
	public function __construct( $post_type, $grp_key, $ord_key, $depth_key ) {
		$this->post_type = $post_type;
		$this->grp_key   = $grp_key;
		$this->ord_key   = $ord_key;
		$this->depth_key = $depth_key;

		add_action( 'save_post_' . $post_type, array( $this, 'update_post_order' ) );
	}

	public function the_title( $title, $id ) {
		$post = get_post( $id );

		if ( ! $post ) {
			return false;
		}

		if ( $post->post_type == $this->post_type ) {
			$depth   = PostUtils::get_post_meta( $post, $this->depth_key );
			$element = '';
			for ( $i = 0; $i < $depth; $i ++ ) {
				if ( is_admin() ) {
					$title = '— ' . $title;
				} else {
					$element .= sprintf( '<i class="depth"></i>' );
				}
			}
			$title = $element . $title;
		}

		return $title;
	}

	public function get_depth( $post = null ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		if ( $post->post_type == $this->post_type ) {
			$depth = PostUtils::get_post_meta( $post, $this->depth_key );

			return $depth;
		}

		return false;
	}

	public function create_grp() {
		/* @var $wpdb wpdb */
		global $wpdb;

		$query = "SELECT ifnull(MIN(meta_value*1),0)-1 FROM {$wpdb->postmeta} where meta_key='{$this->grp_key}'";

		$v = $wpdb->get_var( $query );

		return $v;
	}

	public function update_post_order( $post ) {
		/* @var $wpdb wpdb */
		global $wpdb;

		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		$grp_key = PostUtils::get_post_meta( $post->ID, $this->grp_key, false );

		if ( $grp_key ) {
			return false;
		}

		if ( ! $post->post_parent ) {
			//신규글
			$grp   = $this->create_grp();
			$ord   = 0;
			$depth = 0;
		} else {
			//답글
			$parent_post = get_post( $post->post_parent );

			$parent_ord = PostUtils::get_post_meta( $parent_post->ID, $this->ord_key, 0 );
			$grp        = PostUtils::get_post_meta( $parent_post->ID, $this->grp_key, 0 );
			$ord        = $parent_ord + 1;
			$depth      = PostUtils::get_post_meta( $parent_post->ID, $this->depth_key, 0 ) + 1;

			$q = "SELECT * FROM {$wpdb->postmeta} WHERE 1 = 1 AND (meta_key = '{$this->grp_key}' AND meta_value = {$grp})";

			foreach ( $wpdb->get_results( $q, ARRAY_A ) as $result ) {
				$q = "update {$wpdb->postmeta} set meta_value=(meta_value*1)+1 where post_id='{$result['post_id']}' and meta_value > {$parent_ord} and meta_key='{$this->ord_key}'";
				$wpdb->query( $q );
			}
		}

		PostUtils::insert_or_update_meta( $post->ID, $this->grp_key, $grp );
		PostUtils::insert_or_update_meta( $post->ID, $this->ord_key, $ord );
		PostUtils::insert_or_update_meta( $post->ID, $this->depth_key, $depth );

		return true;
	}

	/**
	 * @param $request
	 * @param $query \WP_Query
	 *
	 * @return string
	 */
	public function posts_orderby_request( $request, $query ) {
		$post_type = $query->query_vars[ 'post_type' ];

		if ( is_array( $post_type ) ) {
			$in_post_type = in_array( $this->post_type, $post_type );
		} else {
			$in_post_type = $this->post_type == $post_type;
		}

		if ( ! $in_post_type ) {
			return $request;
		}

		$queries = $this->get_order_by_query( array() );

		$q = join( " , ", $queries );

		return $q;
	}

	public function get_order_by_query( $queries ) {
		global $wpdb;

		$queries[] = " CONVERT((SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '{$this->grp_key}' AND post_id = wp_posts.ID),DECIMAL) ";
		$queries[] = " CONVERT((SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '{$this->ord_key}' AND post_id = wp_posts.ID),DECIMAL) ";

		return $queries;
	}
}