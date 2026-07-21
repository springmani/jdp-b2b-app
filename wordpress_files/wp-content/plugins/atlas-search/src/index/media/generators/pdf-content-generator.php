<?php

namespace AtlasSearch\Index\Media\Generators;

use Wpe_Content_Engine\Helper\Logging\Debug_Logger;

/**
 * Content generator for PDF attachments.
 */
class PDF_Content_Generator implements Content_Generator_Interface {
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
		return 'application/pdf' === $mime_type;
	}

	/**
	 * Generate content for the given PDF source.
	 *
	 * @param string $source The PDF source (URL or base64).
	 *
	 * @return array Generated content with description, caption, alt_text.
	 *
	 * @throws \RuntimeException If content generation fails.
	 */
	public function generate( string $source ): array {
		$query = <<<'GRAPHQL'
		query extractPDF($source: String!, $system: String, $action: String, $domainSource: String) {
			pdf {
				extract(
					input: {
						source: $source
						meta: {
							system: $system
							action: $action
							source: $domainSource
						}
					}
				) {
					content
				}
			}
		}
		GRAPHQL;

		$variables = [
			'source'       => $source,
			'system'       => \AtlasSearch\Meta\get_system_version(),
			'action'       => 'extractPDF',
			'domainSource' => \AtlasSearch\Meta\get_domain_name(),
		];

		$result = \AtlasSearch\Index\graphql( $query, $variables );

		if ( ! is_array( $result ) ||
			! isset( $result['data']['pdf']['extract'] )
		) {
			$this->logger->log( 'Invalid GraphQL response for PDF extraction: ' . wp_json_encode( $result ) );

			throw new \RuntimeException( 'Invalid GraphQL response structure from extractPDF' );
		}

		$pdf_data = $result['data']['pdf']['extract'];

		// For PDFs, use content as description.
		return [
			'description' => $pdf_data['content'] ?? '',
			'caption'     => '',
			'alt_text'    => '',
		];
	}

	/**
	 * Get supported MIME types for this generator.
	 *
	 * @return array Array of supported MIME types.
	 */
	public function get_supported_mime_types(): array {
		return [
			'application/pdf',
		];
	}
}
