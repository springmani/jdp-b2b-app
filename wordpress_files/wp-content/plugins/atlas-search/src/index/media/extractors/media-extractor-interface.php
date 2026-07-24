<?php
declare(strict_types=1);

namespace AtlasSearch\Index\Media;

interface Media_Extractor_Interface {
	/**
	 * @param string $content The content extract media IDs from.
	 *
	 * @return int[] Array of attachment IDs found in the post.
	 */
	public function extract( string $content ): array;
}
