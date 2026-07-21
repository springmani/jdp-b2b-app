<?php

declare(strict_types=1);

namespace WPE\AITK\WP\User;

use WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface;
use WP_User;

final class Current_User_Provider implements Current_User_Provider_Interface {
	public function get_current_user(): ?WP_User {
		$user = wp_get_current_user();

		return $user->exists() ? $user : null;
	}

	public function get_current_user_id(): ?int {
		$user = wp_get_current_user();

		return $user->exists() ? $user->ID : null;
	}
}
