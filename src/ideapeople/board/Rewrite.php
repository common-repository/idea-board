<?php
/**
 * Created by PhpStorm.
 * User: ideapeople
 * Date: 2016-09-19
 * Time: 오전 10:24
 */

namespace ideapeople\board;


class Rewrite {
	public function add_rewrite_rules() {
		add_rewrite_tag( '%page_mode%', '([^&]+)' );
		add_rewrite_tag( '%pid%', '([^&]+)' );
		add_rewrite_tag( '%pname%', '([^/]+)' );
		add_rewrite_tag( '%parent%', '([^/]+)' );

		if ( ! PluginConfig::is_using_permalink() ) {
			return;
		}

		$priority = 'top';

		$root_slug = 'idea_board';

		$root_rule = "^([^/]+)/{$root_slug}";

		$list_rule  = "{$root_rule}/?$";
		$list_rule2 = "{$root_rule}/page/([0-9]+)/?$";

		$edit_rule1    = "{$root_rule}/edit/?$";
		$edit_rule2    = "{$root_rule}/edit/([0-9]+)/?$";
		$reply_rule1   = "{$root_rule}/reply/([^/]+)/?$";
		$read_rule1    = "{$root_rule}/read/([^/]+)/?$";
		$comment_rule1 = "{$root_rule}/comment_edit/([^/]+)/?$";

		add_rewrite_rule( $list_rule, 'index.php?pagename=$matches[1]&page_mode=list', $priority );
		add_rewrite_rule( $list_rule2, 'index.php?pagename=$matches[1]&page_mode=list&paged=$matches[2]', $priority );

		add_rewrite_rule( $edit_rule1, 'index.php?pagename=$matches[1]&page_mode=edit', $priority );
		add_rewrite_rule( $edit_rule2, 'index.php?pagename=$matches[1]&page_mode=edit&pid=$matches[2]', $priority );

		add_rewrite_rule( $reply_rule1, 'index.php?pagename=$matches[1]&page_mode=edit&parent=$matches[2]', $priority );

		add_rewrite_rule( $read_rule1, 'index.php?pagename=$matches[1]&pname=$matches[2]&page_mode=read', $priority );

		add_rewrite_rule( $comment_rule1, 'index.php?pagename=$matches[1]&pid=$matches[2]&page_mode=comment_edit', $priority );

		flush_rewrite_rules( false );
	}
}