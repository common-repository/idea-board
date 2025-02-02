<?php
/**
 * User: ideapeople
 * Mail: ideapeople@ideapeople.co.kr
 * Homepage : ideapeople@ideapeople.co.kr
 */

namespace ideapeople\board;


use WP_Roles;

class Roles {
	public function get_roles() {
		/* @var $wp_roles WP_Roles */
		global $wp_roles;

		$roles               = array();
		$roles['all']        = __idea_board( 'All open' );
		$roles['isLogin']    = __idea_board( 'Login Users' );
		$roles['onlyAuthor'] = __idea_board( 'Only Author' );

		$roles = array_merge( $roles, $wp_roles->get_names() );

		return $roles;
	}

	public function add_role_caps() {
		$roles     = array( PluginConfig::$board_admin_role, 'administrator' );
		$post_type = PluginConfig::$board_post_type;

		foreach ( $roles as $the_role ) {
			$role = get_role( $the_role );

			if ( ! $role ) {
				continue;
			}

			$role->add_cap( "read" );
			$role->add_cap( "read_{$post_type}" );
			$role->add_cap( "read_private_{$post_type}s" );

			$role->add_cap( "edit_{$post_type}" );
			$role->add_cap( "edit_{$post_type}s" );
			$role->add_cap( "edit_others_{$post_type}s" );
			$role->add_cap( "edit_published_{$post_type}s" );

			$role->add_cap( "publish_{$post_type}s" );

			$role->add_cap( "delete_{$post_type}s" );
			$role->add_cap( "delete_others_{$post_type}s" );
			$role->add_cap( "delete_private_{$post_type}s" );
			$role->add_cap( "delete_published_{$post_type}s" );
		}
	}

	public function add_roles() {
		$check = get_role( PluginConfig::$board_admin_role . '_ADMIN' );

		if ( ! $check ) {
			add_role( PluginConfig::$board_admin_role . '_ADMIN',
				'IDEA-BOARD-ADMIN',
				array(
					'read'          => true,
					'edit_posts'    => true,
					'publish_posts' => true,
					'upload_files'  => true
				)
			);
		}
	}

	public function remove_roles() {
		remove_role( PluginConfig::$board_admin_role . '_ADMIN' );
	}
}