<?php

namespace AtlasSearch\Index\Media;

use AtlasSearch\Index\Media\Generators\Content_Generator_Interface;
use AtlasSearch\Index\Media\Generators\Image_Content_Generator;
use AtlasSearch\Index\Media\Generators\PDF_Content_Generator;
use Wpe_Content_Engine\Helper\Logging\Debug_Logger;

/**
 * Simplified content generator for media attachments.
 *
 * Uses composition to delegate generation and saving to specialized classes.
 */
class Content_Generator {
	private Debug_Logger $logger;

	/**
	 * @var \AtlasSearch\Index\Media\Generators\Content_Generator_Interface[]
	 */
	private array $generators;

	public function __construct( Debug_Logger $logger ) {
		$this->logger = $logger;

		// Initialize content generators.
		$this->generators = [
			new Image_Content_Generator( $logger ),
			new PDF_Content_Generator( $logger ),
		];
	}

	/**
	 * Generate content for an attachment without saving.
	 *
	 * @param int $attachment_id The attachment ID.
	 *
	 * @return array Generated content with keys: success, data, error
	 */
	public function generate_content( int $attachment_id ): array {
		$mime_type = get_post_mime_type( $attachment_id );

		if ( ! $mime_type ) {
			return $this->error( "Could not determine MIME type for attachment {$attachment_id}" );
		}

		$source = \AtlasSearch\Index\Media\get_attachment_source( $attachment_id );

		if ( empty( $source ) ) {
			return $this->error( "Could not get source for attachment {$attachment_id}" );
		}

		// Find a generator that can handle this MIME type.
		$generator = $this->find_generator_for_mime_type( $mime_type );

		if ( ! $generator ) {
			return $this->error( "Unsupported MIME type: {$mime_type}" );
		}

		try {
			$content = $generator->generate( $source );

			return [
				'success' => true,
				'data'    => $content,
				'error'   => null,
			];
		} catch ( \Throwable $e ) {
			return $this->error( 'Failed to generate content: ' . $e->getMessage() );
		}
	}

	/**
	 * Find a generator that can handle the given MIME type.
	 *
	 * @param string $mime_type The MIME type to find a generator for.
	 *
	 * @return \AtlasSearch\Index\Media\Generators\Content_Generator_Interface|null The generator or null if none found.
	 */
	private function find_generator_for_mime_type( string $mime_type ): ?Content_Generator_Interface {
		foreach ( $this->generators as $generator ) {
			if ( $generator->can_handle( $mime_type ) ) {
				return $generator;
			}
		}

		return null;
	}

	/**
	 * Create an error response.
	 *
	 * @param string $message Error message.
	 *
	 * @return array Error response.
	 */
	private function error( string $message ): array {
		$this->logger->log( $message );

		return [
			'success' => false,
			'data'    => null,
			'error'   => $message,
		];
	}
}
