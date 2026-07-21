<?php
declare(strict_types=1);

namespace AtlasSearch\Index\Media;

final class Media_ID_Extractor {
	private array $extractors;

	public function __construct(array $extractors = []) {
		$this->extractors = $extractors;
	}

	public static function create_default(): self {
		return new self([
			new Gutenberg_Block_Extractor(),
			new Classic_Editor_Extractor(),
			new Gallery_Shortcode_Extractor(),
		]);
	}

	public function extract_from_post( \WP_Post $post ): array {
		$media_ids = [];

		foreach ( $this->extractors as $extractor ) {
			$media_ids = array_merge( $media_ids, $extractor->extract( $post->post_content ) );
		}

		return $this->sanitize_and_validate( $media_ids );
	}

	private function sanitize_and_validate( array $media_ids ): array {
		$media_ids = array_unique( array_map( 'intval', array_filter( $media_ids ) ) );

		return array_filter(
			$media_ids,
			static fn ( $id ) => 'attachment' === get_post_type( $id )
		);
	}
}
