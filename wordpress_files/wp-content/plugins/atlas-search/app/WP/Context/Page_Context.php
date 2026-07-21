<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Context;

use InvalidArgumentException;
use WPE\AITK\WP\Context\Contracts\Context_Interface;
use WP_Screen;

final class Page_Context implements Context_Interface {
	/**
	 * Page ID.
	 *
	 * @var non-empty-string
	 */
	private string $id;

	/**
	 * Action (optional).
	 *
	 * @var ?string|null
	 */
	private ?string $action;

	/**
	 * Network admin (optional).
	 *
	 * @var bool|null
	 */
	private ?bool $network;

	public function __construct( string $id, ?string $action = null, ?bool $network = null ) {
		if ( empty( $id ) ) {
			throw new InvalidArgumentException( 'Page ID cannot be empty.' );
		}

		$this->id      = $id;
		$this->action  = $action;
		$this->network = $network;
	}

	public function matches_current( WP_Screen $screen ): bool {
		// Return false immediately if the screen ID is wrong.
		if ( $screen->id !== $this->id ) {
			return false;
		}

		// If action is set, it must match.
		if ( ! is_null( $this->action ) ) {
			return $this->action === $screen->action;
		}

		// If network is set, it must match.
		if ( ! is_null( $this->network ) ) {
			return $this->network === $screen->in_admin( 'network' );
		}

		return true;
	}
}
