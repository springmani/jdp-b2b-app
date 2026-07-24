<?php

declare(strict_types=1);

namespace WPE\AITK\WP\User;

use WPE\AITK\Core\Utils\Strings_Matcher;
use WPE\AITK\WP\User\Contracts\Role_Checker_Interface;
use WP_User;

final class Role_Checker implements Role_Checker_Interface {
	/**
	 * @inheritDoc
	 */
	public function has_all( WP_User $user, array $roles ): bool {
		return Strings_Matcher::all( $roles, $user->roles );
	}

	/**
	 * @inheritDoc
	 */
	public function has_any( WP_User $user, array $roles ): bool {
		return Strings_Matcher::any( $roles, $user->roles );
	}

	public function has( WP_User $user, string $role ): bool {
		return in_array( $role, $user->roles, true );
	}
}
