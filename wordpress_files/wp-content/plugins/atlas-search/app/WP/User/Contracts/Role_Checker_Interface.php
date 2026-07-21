<?php

declare(strict_types=1);

namespace WPE\AITK\WP\User\Contracts;

use WP_User;

interface Role_Checker_Interface {
	/**
	 * Verifies if the user has ALL the specified roles.
	 *
	 * @param \WP_User $user
	 * @param array<string> $roles
	 */
	public function has_all( WP_User $user, array $roles ): bool;

	/**
	 * Verifies if the user has ANY of the specified roles.
	 *
	 * @param \WP_User $user
	 * @param array<string> $roles
	 */
	public function has_any( WP_User $user, array $roles ): bool;

	/**
	 * Checks if the user has a specific role.
	 *
	 * @param \WP_User $user
	 * @param string $role
	 */
	public function has( WP_User $user, string $role ): bool;
}
