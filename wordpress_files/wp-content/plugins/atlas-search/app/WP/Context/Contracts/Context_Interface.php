<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Context\Contracts;

use WP_Screen;

interface Context_Interface {
	/**
	 * Returns true if this context matches the current admin screen.
	 *
	 * @param \WP_Screen $screen The current admin screen.
	 */
	public function matches_current( WP_Screen $screen ): bool;
}
