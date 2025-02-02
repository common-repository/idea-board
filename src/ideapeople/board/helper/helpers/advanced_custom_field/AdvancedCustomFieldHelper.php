<?php
/**
 * Created by PhpStorm.
 * User: ideapeople
 * Date: 2016-09-07
 * Time: 오후 1:03
 */

namespace ideapeople\board\helper\helpers\advanced_custom_field;


use ideapeople\board\helper\core\AbstractHelper;
use ideapeople\util\wp\CustomField;

class AdvancedCustomFieldHelper extends AbstractHelper {
	public function run() {
		add_filter( 'idea_board_edit_hidden_fields', array( $this, 'add_nonce' ) );
		add_filter( 'idea_board_custom_fields', array( $this, 'get_edit_page_meta_fields' ), 10, 3 );
	}

	public function add_nonce( $fields ) {
		$fields['acf_nonce'] = wp_create_nonce( 'input' );
		
		return $fields;
	}

	public function get_edit_page_meta_fields( $meta, $board, $post ) {
		$field_groups = $this->get_board_field_groups( $board );
		$rows         = array();

		foreach ( $field_groups as $group ) {
			$fields = apply_filters( 'acf/field_group/get_fields', array(), $group['id'] );

			foreach ( $fields as $field ) {
				$rows[] = $field;
			}
		}

		foreach ( $rows as $row ) {
			$value = null;

			if ( $post ) {
				$value = get_field( $row['name'], $post->ID );
			}

			$f = new CustomField( array(
				'name'       => 'fields[' . $row['key'] . ']',
				'label'      => $row['label'],
				'field_type' => $row['type'],
				'require'    => $row['required'],
				'value'      => $value
			) );

			switch ( $row['type'] ) {
				case 'select':
					$f->default_value = $row['choices'];
					break;
			}

			$meta[] = $f;
		}

		return $meta;
	}

	public function get_board_field_groups( $board ) {
		$acfs         = apply_filters( 'acf/get_field_groups', array() );
		$field_groups = array();

		if ( $acfs ) {
			foreach ( $acfs as $acf ) {
				$acf['location'] = apply_filters( 'acf/field_group/get_location', array(), $acf['id'] );

				foreach ( $acf['location'] as $group_id => $group ) {
					if ( is_array( $group ) ) {
						foreach ( $group as $rule_id => $rule ) {
							if ( $rule['param'] == 'taxonomy' && $rule['value'] == $board->term_id ) {
								$field_groups[] = $acf;
							}
						}
					}
				}
			}
		}

		return $field_groups;
	}

	public function get_name() {
		return 'advanced-custom-fields/acf.php';
	}
}