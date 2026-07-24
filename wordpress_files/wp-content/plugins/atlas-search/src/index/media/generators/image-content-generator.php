<?php

namespace AtlasSearch\Index\Media\Generators;

use Wpe_Content_Engine\Helper\Logging\Debug_Logger;

/**
 * Content generator for image attachments.
 */
class Image_Content_Generator implements Content_Generator_Interface {
	private Debug_Logger $logger;

	public function __construct( Debug_Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Check if this generator can handle the given MIME type.
	 *
	 * @param string $mime_type The MIME type to check.
	 *
	 * @return bool True if this generator can handle the MIME type.
	 */
	public function can_handle( string $mime_type ): bool {
		return str_starts_with( $mime_type, 'image/' );
	}

	/**
	 * Generate content for the given image source.
	 *
	 * @param string $source The image source (URL or base64).
	 *
	 * @return array Generated content with description, caption, alt_text.
	 *
	 * @throws \RuntimeException If content generation fails.
	 */
	public function generate( string $source ): array {
		$query = <<<'GRAPHQL'
		query analyzeImage($source: String!, $system: String, $action: String, $domainSource: String) {
			image {
				analyze(
					input: {
						source: $source
						meta: {
							system: $system
							action: $action
							source: $domainSource
						}
					}
				) {
					altText
					caption
					description
				}
			}
		}
		GRAPHQL;

		$variables = [
			'source'       => $source,
			'system'       => \AtlasSearch\Meta\get_system_version(),
			'action'       => 'analyzeImage',
			'domainSource' => \AtlasSearch\Meta\get_domain_name(),
		];

		$result = \AtlasSearch\Index\graphql( $query, $variables );

		if ( ! is_array( $result ) ||
			! isset( $result['data']['image']['analyze'] )
		) {
			$this->logger->log( 'Invalid GraphQL response for image analysis: ' . wp_json_encode( $result ) );

			throw new \RuntimeException( 'Invalid GraphQL response structure from analyzeImage' );
		}

		$image_data = $result['data']['image']['analyze'];

		// Map altText to alt_text for API consistency.
		if ( array_key_exists( 'altText', $image_data ) ) {
			$image_data['alt_text'] = $image_data['altText'];
			unset( $image_data['altText'] );
		}

		return $image_data;
	}

	/**
	 * Get supported MIME types for this generator.
	 *
	 * @return array Array of supported MIME types.
	 */
	public function get_supported_mime_types(): array {
		$allowed_mime_types = get_allowed_mime_types();
		$image_mime_types   = [];

		// Filter to get only image MIME types.
		foreach ( $allowed_mime_types as $mime_type ) {
			if ( $this->can_handle( $mime_type ) ) {
				$image_mime_types[] = $mime_type;
			}
		}

		return $image_mime_types;
	}
}
