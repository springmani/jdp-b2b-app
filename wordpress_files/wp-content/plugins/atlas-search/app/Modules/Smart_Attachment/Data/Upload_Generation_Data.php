<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Attachment\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\WP\Settings\Contracts\Setting_Interface;

/**
 * Controls which metadata fields should be automatically generated when a supported attachment is successfully uploaded to the media library.
 */
final class Upload_Generation_Data implements Setting_Interface {
	/**
	 * Whether automatic metadata generation on successful upload is enabled.
	 */
	private bool $enabled;

	/**
	 * Whether to automatically generate alt text.
	 */
	private bool $alt;

	/**
	 * Whether to automatically generate caption.
	 */
	private bool $caption;

	/**
	 * Whether to automatically generate description.
	 */
	private bool $description;

	public function __construct(
		bool $enabled = false,
		bool $alt = false,
		bool $caption = false,
		bool $description = false
	) {
		$this->enabled     = $enabled;
		$this->alt         = $alt;
		$this->caption     = $caption;
		$this->description = $description;
	}

	/**
	 * @param array<string, bool> $data The settings data.
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_bool( $data, 'enabled' ),
			Array_Converter::safe_bool( $data, 'alt' ),
			Array_Converter::safe_bool( $data, 'caption' ),
			Array_Converter::safe_bool( $data, 'description' )
		);
	}

	/**
	 * Get the enabled status.
	 */
	public function is_enabled(): bool {
		return $this->enabled;
	}

	/**
	 * Check if alt text generation is enabled.
	 */
	public function is_alt_enabled(): bool {
		return $this->alt;
	}

	/**
	 * Check if caption generation is enabled.
	 */
	public function is_caption_enabled(): bool {
		return $this->caption;
	}

	/**
	 * Check if description generation is enabled.
	 */
	public function is_description_enabled(): bool {
		return $this->description;
	}

	/**
	 * Convert the settings to an array.
	 *
	 * @return array<string, bool>
	 */
	public function to_array(): array {
		return [
			'alt'         => $this->alt,
			'caption'     => $this->caption,
			'description' => $this->description,
			'enabled'     => $this->enabled,
		];
	}

	/**
	 * Get list of enabled metadata fields.
	 *
	 * @return array<string>
	 */
	public function get_enabled_fields(): array {
		$all_fields = $this->to_array();

		// Remove 'enabled' key as it's not a metadata field.
		unset( $all_fields['enabled'] );

		// Filter to get only enabled fields and return their keys.
		return array_keys( array_filter( $all_fields ) );
	}
}
