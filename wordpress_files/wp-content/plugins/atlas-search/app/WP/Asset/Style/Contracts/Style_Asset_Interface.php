<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset\Style\Contracts;

use WPE\AITK\WP\Asset\Contracts\Asset_Interface;

interface Style_Asset_Interface extends Asset_Interface {
	public function get_media(): string;
}
