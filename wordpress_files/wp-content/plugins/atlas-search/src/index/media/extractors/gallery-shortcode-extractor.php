<?php
declare(strict_types=1);

namespace AtlasSearch\Index\Media;

final class Gallery_Shortcode_Extractor implements Media_Extractor_Interface {
	public function extract( string $content ): array {
		$media_ids = [];

		preg_match_all( '/\[gallery[^\]]*\]/', $content, $shortcodes );

		if ( ! empty( $shortcodes[0] ) ) {
			foreach ( $shortcodes[0] as $shortcode ) {
				$shortcode_content = trim( $shortcode, '[]' );
				$atts              = shortcode_parse_atts( $shortcode_content );

				if ( ! empty( $atts['ids'] ) ) {
					$media_ids = array_merge( $media_ids, array_map( 'intval', explode( ',', $atts['ids'] ) ) );
				}
			}
		}

		return $media_ids;
	}
}
