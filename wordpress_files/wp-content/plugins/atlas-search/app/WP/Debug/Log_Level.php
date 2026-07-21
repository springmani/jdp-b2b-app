<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Debug;

final class Log_Level {
	public const DEBUG   = 'debug';
	public const ERROR   = 'error';
	public const INFO    = 'info';
	public const NOTICE  = 'notice';
	public const WARNING = 'warning';

	private const ALLOWED = [
		self::DEBUG,
		self::ERROR,
		self::INFO,
		self::NOTICE,
		self::WARNING,
	];

	public static function is_valid( string $level ): bool {
		return in_array( $level, self::ALLOWED, true );
	}
}
