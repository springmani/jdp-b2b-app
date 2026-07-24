<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Attachment\Components;

/**
 * Determines which metadata fields are supported for different file types.
 *
 * Encapsulates the logic for mapping metadata fields (alt, caption, description)
 * to their supported MIME types.
 */
final class Metadata_Field_Support {
	/**
	 * @var array<string, array<string>>
	 */
	private array $field_support;

	/**
	 * @param array<string, string> $supported_types All supported MIME types (e.g., from Smart_Attachment_Subscriber::SUPPORTED_TYPES).
	 */
	public function __construct( array $supported_types ) {
		$this->field_support = $this->build_field_support( $supported_types );
	}

	/**
	 * Get the field support mapping.
	 *
	 * @return array<string, array<string>>
	 */
	public function get_field_support(): array {
		return $this->field_support;
	}

	/**
	 * Check if a specific field is supported for the given MIME type.
	 *
	 * @param string $field The field name (alt, caption, description).
	 * @param string $mime_type The MIME type to check.
	 */
	public function is_field_supported( string $field, string $mime_type ): bool {
		if ( ! isset( $this->field_support[ $field ] ) ) {
			return false;
		}

		return in_array( $mime_type, $this->field_support[ $field ], true );
	}

	/**
	 * Build the field support mapping.
	 *
	 * @param array<string, string> $supported_types All supported MIME types.
	 *
	 * @return array<string, array<string>>
	 */
	private function build_field_support( array $supported_types ): array {
		// Extract only image mime types (excluding PDF).
		$image_mime_types = array_filter(
			$supported_types,
			static fn ( $mime_type ) => str_starts_with( $mime_type, 'image/' )
		);

		// All mime types (images + PDF).
		$all_mime_types = array_values( $supported_types );

		// Map metadata fields to supported mime types.
		return [
			'alt'         => array_values( $image_mime_types ),
			'caption'     => array_values( $image_mime_types ),
			'description' => $all_mime_types,
		];
	}
}
