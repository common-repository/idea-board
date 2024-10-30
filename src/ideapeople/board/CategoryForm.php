<?php
/**
 * User: ideapeople
 * Mail: ideapeople@ideapeople.co.kr
 * Homepage : ideapeople@ideapeople.co.kr
 */

namespace ideapeople\board;


use ideapeople\board\setting\Setting;
use ideapeople\util\http\Request;

class CategoryForm {
	public static function get_category_form( $board_term = null, $page_permalink = false ) {
		ob_start();

		$board          = Setting::get_board( $board_term );
		$categories     = Setting::get_categories_array( $board );
		$query_category = Request::get_parameter( 'idea_board_category' );

		if ( ! $page_permalink ) {
			$page_permalink = CommonUtils::get_post_page_link();;
		}

		if ( empty( $categories ) ) {
			return false;
		}
		?>
		<div class="idea-board-category idea-board-reset">
			<label for="idea-board-category" class="idea-board-hidden"><?php _e_idea_board( 'Category' ) ?></label>
			<ul id="idea-board-category">
				<li <?php echo $query_category == 'all' || empty( $query_category ) ? 'class="active"' : '' ?>>
					<a href="<?php echo add_query_arg( array( 'idea_board_category' => null ), $page_permalink ); ?>"><?php _e_idea_board( 'All' ) ?></a>
				</li>
				<?php foreach ( $categories as $value ) : ?>
					<li <?php echo $value == $query_category ? 'class="active"' : '' ?>>
						<a href="<?php echo add_query_arg( array( 'idea_board_category' => $value ), $page_permalink ); ?>"><?php echo $value ?></a>
					</li>
				<?php endforeach ?>
			</ul>
		</div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return apply_filters( 'idea_board_category_form', $content, compact( 'board', 'categories', 'query_category' ) );
	}

	public function _search_query() {
		add_filter( 'posts_where', array( $this, 'search_query' ) );
	}

	public function _reset_search_query() {
		remove_filter( 'posts_where', array( $this, 'search_query' ) );
	}

	public function search_query( $where ) {
		global $wpdb;

		$category = Request::get_parameter( 'idea_board_category' );

		if ( $category ) {
			$where .= $wpdb->prepare( "AND (SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'idea_board_category' AND {$wpdb->postmeta}.post_id={$wpdb->posts}.ID)=%s", $category );
		}

		return $where;
	}
}