<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Attachment\Data;

use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;

final class Config_Data implements Array_Convertible_Interface {
	/**
	 * An array of ext => mimetype that are being supported for metadata generation.
	 *
	 * @var array<string, string>
	 */
	private array $supported_types;

	/**
	 * Maps metadata fields to supported mime types.
	 * Keys are metadata fields (alt, caption, description).
	 * Values are arrays of supported mime types for that field.
	 *
	 * @var array<string, array<string>>
	 */
	private array $metadata_field_support;

	/**
	 * Max upload size in MB.
	 * API limit is 50MB.
	 *
	 * @var string
	 */
	private string $max_upload_size;

	/**
	 * ID attribute of the app for the List View Bulk.
	 *
	 * @var string
	 */
	private string $bulk_list_app_id;

	/**
	 * ID attribute of the app for the Media Edit in Grid View.
	 *
	 * @var string
	 */
	private string $media_edit_app_id;

	/**
	 * ID attribute of the app for the Single Attachment Page.
	 *
	 * @var string
	 */
	private string $single_attachment_app_id;

	/**
	 * @param array<string, string> $supported_types
	 * @param array<string, array<string>> $metadata_field_support
	 * @param string $max_upload_size
	 * @param string $bulk_list_app_id
	 * @param string $media_edit_app_id
	 * @param string $single_attachment_app_id
	 */
	public function __construct(
		array $supported_types,
		array $metadata_field_support,
		string $max_upload_size,
		string $bulk_list_app_id,
		string $media_edit_app_id,
		string $single_attachment_app_id
	) {
		$this->supported_types          = $supported_types;
		$this->metadata_field_support   = $metadata_field_support;
		$this->max_upload_size          = $max_upload_size;
		$this->bulk_list_app_id         = $bulk_list_app_id;
		$this->media_edit_app_id        = $media_edit_app_id;
		$this->single_attachment_app_id = $single_attachment_app_id;
	}

	/**
	 * Do not change keys!
	 *
	 * @inheritDoc
	 *
	 * @see wordpress/wpengine-smart-search/includes/smart-search-settings/src/shared/types.ts
	 */
	public function to_array(): array {
		return [
			'bulkListAppID'         => $this->bulk_list_app_id,
			'maxUploadSize'         => $this->max_upload_size,
			'mediaEditAppID'        => $this->media_edit_app_id,
			'metadataFieldSupport'  => $this->metadata_field_support,
			'singleAttachmentAppID' => $this->single_attachment_app_id,
			'supportedTypes'        => $this->supported_types,
		];
	}
}
