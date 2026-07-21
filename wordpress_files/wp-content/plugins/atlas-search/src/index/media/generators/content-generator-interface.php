<?php

namespace AtlasSearch\Index\Media\Generators;

/**
 * Interface for content generators that can analyze media and generate content.
 */
interface Content_Generator_Interface {
	/**
	 * Check if this generator can handle the given MIME type.
	 *
	 * @param string $mime_type The MIME type to check.
	 *
	 * @return bool True if this generator can handle the MIME type.
	 */
	public function can_handle( string $mime_type ): bool;

	/**
	 * Generate content for the given source.
	 *
	 * @param string $source The source data (URL or base64).
	 *
	 * @return array Generated content with keys like description, caption, alt_text.
	 *
	 * @throws \Exception If content generation fails.
	 */
	public function generate( string $source ): array;

	/**
	 * Get supported MIME types for this generator.
	 *
	 * @return array Array of supported MIME types.
	 */
	public function get_supported_mime_types(): array;
}
