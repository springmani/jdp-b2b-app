<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Ajax\Contracts;

interface Ajax_Config_Interface {
	public function get_action(): string;

	public function get_nonce(): string;
}
