<?php

declare(strict_types=1);

namespace WPE\AITK\WP\User\Contracts;

use WP_User;

interface Capability_Checker_Interface {
	/**
	 * Verifies if the user has ALL the specified capabilities.
	 *
	 * @param \WP_User $user
	 * @param array<string> $caps
	 */
	public function has_all( WP_User $user, array $caps ): bool;

	/**
	 * Verifies if the user has ANY of the specified capabilities.
	 *
	 * @param \WP_User $user
	 * @param array<string> $caps
	 */
	public function has_any( WP_User $user, array $caps ): bool;

	public function has( WP_User $user, string $capability ): bool;

	/**
	 * Checks if the current logged-in user has a specific capability.
	 *
	 * @param string $capability The capability to check.
	 */
	public function current_user_can( string $capability ): bool;

	/**
	 * Returns a callable that checks if the current user has the given capability.
	 * Pass null for public access (always returns true).
	 *
	 * @param string|null $capability The capability to check, or null for public access.
	 *
	 * @return callable(): bool
	 */
	public function guard( ?string $capability = null ): callable;
}
