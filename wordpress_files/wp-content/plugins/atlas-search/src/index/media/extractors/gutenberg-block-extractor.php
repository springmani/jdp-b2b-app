<?php
declare(strict_types=1);

namespace AtlasSearch\Index\Media;

final class Gutenberg_Block_Extractor implements Media_Extractor_Interface {
	public const MEDIA_BLOCK_TYPES = [
		'core/image',
		'core/video',
		'core/audio',
		'core/file',
		'core/cover',
		'core/media-text',
		'core/gallery',
	];

	public function extract( string $content ): array {
		if ( ! has_blocks( $content ) ) {
			return [];
		}

		$ids    = [];
		$blocks = parse_blocks( $content );
		$this->collect( $blocks, $ids );

		return $ids;
	}

	/**
	 * Recursively collect attachment IDs from blocks.
	 *
	 * @param array $blocks Array of blocks to process.
	 * @param array $ids Reference to array where attachment IDs will be collected.
	 */
	private function collect(array $blocks, array &$ids): void {
		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && in_array( $block['blockName'], self::MEDIA_BLOCK_TYPES, true ) ) {
				$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : [];

				// Common attachment id fields across core/media blocks.
				foreach ( [ 'id', 'mediaId', 'imageId', 'attachmentId' ] as $key ) {
					if ( isset( $attrs[ $key ] ) && is_numeric( $attrs[ $key ] ) ) {
						$ids[] = (int) $attrs[ $key ];
					}
				}
			}

			// Gallery (core/gallery) can hold images in innerBlocks with ids.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$this->collect( $block['innerBlocks'], $ids );
			}
		}
	}
}
