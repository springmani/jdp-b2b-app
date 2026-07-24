<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Notice\Contracts;

interface Notice_With_SVG_Interface {
	public function get_svg(): string;
}
