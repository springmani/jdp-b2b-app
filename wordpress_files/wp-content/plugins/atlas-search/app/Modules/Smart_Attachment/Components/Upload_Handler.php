<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Attachment\Components;

use AtlasSearch\Index\Media\Content_Generator;
use Throwable;
use WPE\AITK\Modules\Smart_Attachment\Data\Upload_Generation_Data;
use WPE\AITK\Modules\Smart_Attachment\Smart_Attachment_Subscriber;
use WPE\AITK\WP\Settings\Contracts\Settings_Manager_Interface;
use Wpe_Content_Engine\Helper\Logging\Debug_Logger;

/**
 * Handles automatic metadata generation when attachments are uploaded.
 * Also manages settings registration for on-upload generation feature.
 */
final class Upload_Handler {
	public const SETTING_OPTION_NAME = 'wpe_aitk/smart_attachment/on_upload_metadata_generation';

	private const ALT_TEXT_META_KEY = '_wp_attachment_image_alt';

	private Settings_Manager_Interface $settings_manager;
	private Metadata_Field_Support $field_support;
	private Content_Generator $content_generator;
	private Debug_Logger $logger;

	public function __construct(
		Settings_Manager_Interface $settings_manager,
		Metadata_Field_Support $field_support,
		Content_Generator $content_generator,
		Debug_Logger $logger
	) {
		$this->settings_manager  = $settings_manager;
		$this->field_support     = $field_support;
		$this->content_generator = $content_generator;
		$this->logger            = $logger;
	}

	/**
	 * Handle attachment upload and generate metadata if enabled.
	 *
	 * @param int $attachment_id The ID of the uploaded attachment.
	 */
	public function handle_upload( int $attachment_id ): void {
		// Get the on-upload generation settings.
		$settings = $this->get_settings();

		// Check if automatic generation is enabled.
		if ( ! $this->should_generate_metadata( $settings ) ) {
			return;
		}

		// Get mime type once for reuse.
		$mime_type = $this->get_attachment_mime_type( $attachment_id );

		// Verify the attachment is supported and has enabled fields for this file type.
		if ( ! $this->is_supported( $mime_type, $settings ) ) {
			return;
		}

		// Generate metadata.
		$result = $this->content_generator->generate_content( $attachment_id );

		if ( ! $result['success'] ) {
			$this->log_generation_failure( $attachment_id, $result['error'] ?? '' );

			return;
		}

		// Save the generated metadata based on enabled fields.
		$this->save_metadata( $attachment_id, $result['data'], $settings );
	}

	/**
	 * Get the on-upload generation settings.
	 */
	private function get_settings(): ?Upload_Generation_Data {
		try {
			return $this->settings_manager->get( Upload_Generation_Data::class );
		} catch ( Throwable $e ) {
			$this->logger->log( 'Failed to retrieve on-upload generation settings: ' . $e->getMessage() );

			return null;
		}
	}

	/**
	 * Check if metadata generation should proceed.
	 *
	 * @param \WPE\AITK\Modules\Smart_Attachment\Data\Upload_Generation_Data|null $settings The settings.
	 */
	private function should_generate_metadata( ?Upload_Generation_Data $settings ): bool {
		if ( ! $settings ) {
			return false;
		}

		return $settings->is_enabled();
	}

	/**
	 * Get attachment mime type, converting false to null.
	 *
	 * @param int $attachment_id The attachment ID.
	 */
	private function get_attachment_mime_type( int $attachment_id ): ?string {
		$mime_type = get_post_mime_type( $attachment_id );

		return false !== $mime_type ? $mime_type : null;
	}

	/**
	 * Log metadata generation failure.
	 *
	 * @param int $attachment_id The attachment ID.
	 * @param string $error The generation result.
	 */
	private function log_generation_failure( int $attachment_id, string $error ): void {
		$this->logger->log(
			sprintf(
				'Failed to generate metadata for attachment %d on upload: %s',
				$attachment_id,
				$error
			)
		);
	}

	private function is_supported( ?string $mime_type, Upload_Generation_Data $settings ): bool {
		if ( ! $this->is_supported_attachment( $mime_type ) ) {
			return false;
		}

		return $this->has_supported_fields( $mime_type, $settings );
	}

	/**
	 * Check if the mime type is supported for metadata generation.
	 *
	 * @param string|null $mime_type The mime type.
	 */
	private function is_supported_attachment( ?string $mime_type ): bool {
		if ( ! $mime_type ) {
			return false;
		}

		return in_array( $mime_type, Smart_Attachment_Subscriber::SUPPORTED_TYPES, true );
	}

	/**
	 * Check if any enabled fields are supported by this file type.
	 *
	 * @param string|null $mime_type The mime type.
	 * @param \WPE\AITK\Modules\Smart_Attachment\Data\Upload_Generation_Data $settings The settings.
	 */
	private function has_supported_fields( ?string $mime_type, Upload_Generation_Data $settings ): bool {
		if ( ! $mime_type ) {
			return false;
		}

		foreach ( $settings->get_enabled_fields() as $field ) {
			if ( $this->field_support->is_field_supported( $field, $mime_type ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Save the generated metadata to the attachment.
	 *
	 * @param int $attachment_id The attachment ID.
	 * @param array<string, string> $data The generated metadata.
	 * @param \WPE\AITK\Modules\Smart_Attachment\Data\Upload_Generation_Data $settings The settings indicating which fields to save.
	 */
	private function save_metadata( int $attachment_id, array $data, Upload_Generation_Data $settings ): void {
		$this->save_alt_text( $attachment_id, $data, $settings );
		$this->save_post_data( $attachment_id, $data, $settings );
	}

	/**
	 * Save alt text metadata if enabled.
	 *
	 * @param int $attachment_id The attachment ID.
	 * @param array<string, string> $data The generated metadata.
	 * @param \WPE\AITK\Modules\Smart_Attachment\Data\Upload_Generation_Data $settings The settings.
	 */
	private function save_alt_text( int $attachment_id, array $data, Upload_Generation_Data $settings ): void {
		if ( ! $settings->is_alt_enabled() || empty( $data['alt_text'] ) ) {
			return;
		}

		update_post_meta( $attachment_id, self::ALT_TEXT_META_KEY, sanitize_text_field( $data['alt_text'] ) );
	}

	/**
	 * Save caption and description post data if enabled.
	 *
	 * @param int $attachment_id The attachment ID.
	 * @param array<string, string> $data The generated metadata.
	 * @param \WPE\AITK\Modules\Smart_Attachment\Data\Upload_Generation_Data $settings The settings.
	 */
	private function save_post_data( int $attachment_id, array $data, Upload_Generation_Data $settings ): void {
		$update_data = $this->prepare_post_update_data( $data, $settings );

		if ( empty( $update_data ) ) {
			return;
		}

		$this->update_attachment_post( $attachment_id, $update_data );
	}

	/**
	 * Prepare post update data based on enabled settings.
	 *
	 * @param array<string, string> $data The generated metadata.
	 * @param \WPE\AITK\Modules\Smart_Attachment\Data\Upload_Generation_Data $settings The settings.
	 *
	 * @return array<string, string>
	 */
	private function prepare_post_update_data( array $data, Upload_Generation_Data $settings ): array {
		$update_data = [];

		if ( $settings->is_caption_enabled() && ! empty( $data['caption'] ) ) {
			$update_data['post_excerpt'] = sanitize_text_field( $data['caption'] );
		}

		if ( $settings->is_description_enabled() && ! empty( $data['description'] ) ) {
			$update_data['post_content'] = wp_kses_post( $data['description'] );
		}

		return $update_data;
	}

	/**
	 * Update attachment post and log errors.
	 *
	 * @param int $attachment_id The attachment ID.
	 * @param array<string, string> $update_data The data to update.
	 */
	private function update_attachment_post( int $attachment_id, array $update_data ): void {
		$update_data['ID'] = $attachment_id;
		$result            = wp_update_post( $update_data, true );

		if ( ! is_wp_error( $result ) ) {
			return;
		}

		$this->logger->log(
			sprintf(
				'Failed to save metadata for attachment %d: %s',
				$attachment_id,
				$result->get_error_message()
			)
		);
	}
}
