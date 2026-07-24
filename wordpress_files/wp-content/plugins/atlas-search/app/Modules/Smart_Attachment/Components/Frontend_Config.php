<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Attachment\Components;

use WPE\AITK\Modules\Smart_Attachment\Data\Config_Data;
use WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface;
use WPE\AITK\WP\Script\Exceptions\Script_Injection_Exception;
use WPE_Atlas_Search_Settings_Page;

final class Frontend_Config {
	private const OBJECT_NAME = 'wpeAitkSmartAttachmentConfig';

	/**
	 * @var \WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface
	 */
	private Script_Data_Injector_Interface $script_injector;

	/**
	 * @var \WPE\AITK\Modules\Smart_Attachment\Components\Metadata_Field_Support
	 */
	private Metadata_Field_Support $field_support;

	/**
	 * @var array<string, string>
	 */
	private array $supported_types;

	/**
	 * @var string
	 */
	private string $max_upload_size;

	/**
	 * @var string
	 */
	private string $bulk_list_app_id;

	/**
	 * @var string
	 */
	private string $media_edit_app_id;

	/**
	 * @var string
	 */
	private string $single_attachment_app_id;

	/**
	 * @param \WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface $script_injector
	 * @param \WPE\AITK\Modules\Smart_Attachment\Components\Metadata_Field_Support $field_support
	 * @param array<string, string> $supported_types
	 * @param string $max_upload_size
	 * @param string $bulk_list_app_id
	 * @param string $media_edit_app_id
	 * @param string $single_attachment_app_id
	 */
	public function __construct(
		Script_Data_Injector_Interface $script_injector,
		Metadata_Field_Support $field_support,
		array $supported_types,
		string $max_upload_size,
		string $bulk_list_app_id,
		string $media_edit_app_id,
		string $single_attachment_app_id
	) {
		$this->script_injector          = $script_injector;
		$this->field_support            = $field_support;
		$this->supported_types          = $supported_types;
		$this->max_upload_size          = $max_upload_size;
		$this->bulk_list_app_id         = $bulk_list_app_id;
		$this->media_edit_app_id        = $media_edit_app_id;
		$this->single_attachment_app_id = $single_attachment_app_id;
	}

	/**
	 * Inject smart attachment configuration to the frontend.
	 */
	public function inject_config(): void {
		try {
			$this->script_injector->inject(
				WPE_Atlas_Search_Settings_Page::SETTINGS_SCRIPT,
				$this->get_config_data(),
				self::OBJECT_NAME
			);
		} catch ( Script_Injection_Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $e->getMessage() );
		}
	}

	/**
	 * Build configuration data for frontend.
	 */
	private function get_config_data(): Config_Data {
		return new Config_Data(
			$this->supported_types,
			$this->field_support->get_field_support(),
			$this->max_upload_size,
			$this->bulk_list_app_id,
			$this->media_edit_app_id,
			$this->single_attachment_app_id
		);
	}
}
