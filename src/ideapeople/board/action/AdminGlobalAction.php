<?php
/**
 * Created by PhpStorm.
 * User: ideapeople
 * Date: 2016-09-08
 * Time: 오전 9:43
 */

namespace ideapeople\board\action;


use ideapeople\board\PluginConfig;
use ideapeople\board\setting\GlobalSetting;
use ideapeople\util\common\Utils;
use ideapeople\util\wp\AdminSettingUtils;

class AdminGlobalAction {
	/**
	 * @return static
	 */
	public static function instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * @var AdminSettingUtils
	 */
	public $setting;

	public function __construct() {
		$this->setting = new AdminSettingUtils( 'idea_board_global', 'idea_board_global_option', 'idea_board_global_options', array(
			$this,
			'add_fields'
		) );
	}

	public function add_fields() {
		add_settings_section(
			'section_1',
			__idea_board( 'Forum Setting' ),
			array( $this, 'view_section_1' ),
			$this->setting->slug
		);

		$this->setting->add_field( array(
			'section' => 'section_1',
			'type'    => 'checkbox',
			'name'    => 'idea_board_use_permalink_struct',
			'id'      => 'idea_board_use_permalink_struct',
			'label'   => __idea_board( 'Use permalink structure' ),
			'value'   => 1,
			'checked' => GlobalSetting::get_use_permalink_struct(),
			'after'   => __idea_board( 'It operates wordpress permalink structure will be activated' )
		) );

		$this->setting->add_field( array(
			'section' => 'section_1',
			'name'    => 'idea_board_max_update_file_size',
			'id'      => 'idea_board_max_update_file_size',
			'label'   => __idea_board( 'Possible file upload size (MB)' ),
			'value'   => GlobalSetting::get_max_update_file_size(),
			'after'   => sprintf( __idea_board( 'MB <p>The maximum upload size is <strong>%s</strong> available</p>' ), Utils::bytes( wp_max_upload_size(), 0, '%01.2f %s' ) )
		) );

		$this->setting->add_field( array(
			'section'       => 'section_1',
			'name'          => 'idea_board_file_mimes',
			'id'            => 'idea_board_file_mimes',
			'label'         => __idea_board( 'Upload file types to allow' ),
			'multiple'      => true,
			'type'          => 'select',
			'value'         => GlobalSetting::get_file_mimes(),
			'default_value' => wp_get_mime_types(),
			'cssClass'      => 'chosen-select'
		) );
	}

	public function view_section_1() {
	}

	public function add_page() {
		$this->setting->add_submenu_page(
			'edit.php?post_type=' . PluginConfig::$board_post_type,
			__idea_board( 'Settings' ),
			__idea_board( 'Settings' ),
			'idea_board_global_settings'
		);
	}
}