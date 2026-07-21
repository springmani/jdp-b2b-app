<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset\Script\Contracts;

use WPE\AITK\WP\Asset\Contracts\Asset_Interface;

interface Script_Asset_Interface extends Asset_Interface {
	public function get_in_footer(): bool;

	public function get_strategy(): string;
}
