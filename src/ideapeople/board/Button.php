<?php
/**
 * Created by PhpStorm.
 * User: ideapeople
 * Date: 2016-09-09
 * Time: 오후 1:48
 */

namespace ideapeople\board;

use ideapeople\board\setting\Setting;

class Button {
	public static function reply_button( $post = null ) {
		if ( Setting::get_use_forum( null, false ) ) {
			return null;
		}

		return self::button( 'reply', 'reply', 'Reply', Link::reply_link( $post ), $post );
	}

	public static function edit_button( $post = null ) {
		$post = get_post( $post );

		if ( $post->post_author != 0 && get_current_user_id() != $post->post_author && ! Capability::is_board_admin() ) {
			return null;
		}

		return self::button( 'edit', 'edit', 'Edit', Link::edit_link( $post ), $post );
	}

	public static function write_button( $post = null ) {
		return self::button( 'write', 'edit', 'Write', Link::write_link( $post ), $post );
	}

	public static function list_button( $post = null ) {
		return self::button( 'list', 'list', 'List', Link::list_link( $post ), $post );
	}

	public static function read_button( $post = null ) {
		$post = get_post( $post );

		return self::button( 'read', 'read', 'Read', get_permalink( $post->ID ), $post );
	}

	public static function prev_button() {
		$html = sprintf( '<a href="%s" class="idea-board-button">%s</a>', 'javascript:history.back();', __idea_board( 'Previous' ) );

		return $html;
	}

	public static function delete_button( $post = null ) {
		$post = get_post( $post );

		if ( $post->post_author != 0 && get_current_user_id() != $post->post_author && ! Capability::is_board_admin() ) {
			return null;
		}

		return self::button( 'delete', 'delete', 'Delete', Link::delete_link( $post ), $post );
	}

	public static function comment_edit_button( $comment_ID, $post = null ) {
		$post = get_post( $post );

		if ( ! is_user_logged_in() && Comment::is_logged_in_comment( $comment_ID ) ) {
			return null;
		}

		$html = sprintf( '<a href="%s">%s</a>', Link::comment_edit_link( $comment_ID, $post->ID ), __idea_board( 'Edit' ) );

		return $html;
	}

	public static function comment_delete_button( $comment_ID, $post = null ) {
		$post = get_post( $post );

		if ( ! is_user_logged_in() && Comment::is_logged_in_comment( $comment_ID ) ) {
			return null;
		}

		$html = sprintf( '<a href="%s">%s</a>', Link::comment_delete_link( $comment_ID, $post->ID ), __idea_board( 'Delete' ) );

		return $html;
	}

	public static function button( $type, $check_role, $title, $link, $post = null ) {
		if ( $check_role && ! Capability::current_user_can( $check_role ) ) {
			return null;
		}

		$html = sprintf( '<a href="%s" class="idea-board-button">%s</a>', $link, __idea_board( $title ) );

		$html = apply_filters( 'idea_board_button_' . $type, $html, $title, $link, $post );

		return $html;
	}
}