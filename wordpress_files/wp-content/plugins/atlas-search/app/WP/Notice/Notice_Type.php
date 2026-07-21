<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Notice;

final class Notice_Type {
	public const WP_ENGINE = 'wpengine';
	public const ERROR     = 'error';
	public const WARNING   = 'warning';
	public const SUCCESS   = 'success';
	public const INFO      = 'info';

	public const PRIORITY = [
		self::ERROR     => 2,
		self::INFO      => 4,
		self::SUCCESS   => 5,
		self::WARNING   => 3,
		self::WP_ENGINE => 1,
	];
}
