<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Test\Contracts;

use WPE\AITK\WP\Site_Health\Test\Data\Test_Result;

interface Test_Interface {
	/**
	 * Unique identifier for this test, used as the array key in WordPress.
	 */
	public function get_identifier(): string;

	/**
	 * Human-readable label shown in the Site Health UI.
	 */
	public function get_label(): string;

	/**
	 * Whether this test should run asynchronously via REST after page load.
	 */
	public function is_async(): bool;

	/**
	 * Executes the test and returns a result.
	 *
	 * Implementations should NOT catch exceptions. The framework wraps
	 * all calls and converts uncaught exceptions into critical results.
	 */
	public function run(): Test_Result;
}
