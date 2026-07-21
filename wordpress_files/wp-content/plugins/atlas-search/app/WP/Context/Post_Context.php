<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Context;

use InvalidArgumentException;
use WPE\AITK\WP\Context\Contracts\Context_Interface;
use WP_Screen;

final class Post_Context implements Context_Interface {
	public const MODE_EDIT = 'edit';
	public const MODE_ADD  = 'add';
	public const MODE_LIST = 'list';

	public const ALL_MODES = [
		self::MODE_EDIT,
		self::MODE_ADD,
		self::MODE_LIST,
	];

	/**
	 * Post type to match.
	 *
	 * @var non-empty-string
	 */
	private string $post_type;

	/**
	 * Mode to match, or null for any mode.
	 *
	 * @var ?string|null
	 */
	private ?string $mode;

	/**
	 * @param string      $post_type The post type to match, e.g. 'post', 'page', or a custom post type.
	 * @param string|null $mode The mode to match. One of 'single', 'edit', 'add', 'list', or null for any mode.
	 */
	public function __construct( string $post_type, ?string $mode = null ) {
		$this->validate_mode( $mode );

		$this->post_type = $post_type;
		$this->mode      = $mode;
	}

	/**
	 * Check if the current screen matches the context.
	 *
	 * @param \WP_Screen $screen The current screen object.
	 *
	 * @return bool True if the screen matches the context, false otherwise.
	 */
	public function matches_current( WP_Screen $screen ): bool {
		if ( $screen->post_type !== $this->post_type ) {
			return false;
		}

		if ( empty( $this->mode ) ) {
			return true;
		}

		return $this->matches_mode( $screen );
	}

	private function matches_mode( WP_Screen $screen ): bool {
		switch ( $this->mode ) {
			case self::MODE_EDIT:
				return 'post' === $screen->base;
			case self::MODE_ADD:
				return 'post' === $screen->base && 'add' === $screen->action;
			case self::MODE_LIST:
				return 'edit' === $screen->base;
		}

		return false;
	}

	/**
	 * Validate the mode.
	 *
	 * @param string|null $mode The mode to validate.
	 *
	 * @throws \InvalidArgumentException If the mode is invalid.
	 */
	private function validate_mode( ?string $mode ): void {
		if ( null === $mode || in_array( $mode, self::ALL_MODES, true ) ) {
			return;
		}

		throw new InvalidArgumentException(
			sprintf(
				'Invalid mode "%s". Allowed: %s',
				esc_html( $mode ),
				esc_html( implode( ', ', self::ALL_MODES ) )
			)
		);
	}
}
