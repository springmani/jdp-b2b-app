<?php

declare(strict_types=1);

namespace WPE\AITK\WP\User\Contracts;

use WP_User;

interface Current_User_Provider_Interface {
	public function get_current_user(): ?WP_User;

	public function get_current_user_id(): ?int;
}
