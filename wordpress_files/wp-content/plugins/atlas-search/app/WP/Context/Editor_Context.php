<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Context;

use WPE\AITK\WP\Context\Contracts\Context_Interface;
use WP_Screen;

final class Editor_Context implements Context_Interface {
	/**
	 * @var array<string>
	 */
	private array $post_types;

	/**
	 * @param array<string> $post_types Optional. An array of post types to match. Default is an empty array, which matches any post type.
	 */
	public function __construct( array $post_types = [] ) {
		$this->post_types = $post_types;
	}

	public function matches_current( WP_Screen $screen ): bool {
		if ( ! empty( $this->post_types ) && ! in_array( $screen->post_type, $this->post_types, true ) ) {
			return false;
		}

		return $screen->is_block_editor();
	}
}
