<?php

declare(strict_types=1);

namespace AtlasSearch\Index\Media;

final class Classic_Editor_Extractor implements Media_Extractor_Interface {
	public function extract(string $content): array {
		if ( preg_match_all( '/wp-image-(\d+)/', $content, $matches ) ) {
			return array_map( 'intval', $matches[1] ) ?? [];
		}

		return [];
	}
}
