<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Debug\Loggers;

use WPE\AITK\WP\Debug\Concerns\With_Logger_Level;
use WPE\AITK\WP\Debug\Contracts\Logger_Interface;

final class Debug_Logger implements Logger_Interface {
	use With_Logger_Level;

	private const PREFIX = '[AITK]';

	private string $prefix;

	public function __construct( string $prefix = self::PREFIX ) {
		$this->prefix = $prefix;
	}

	/**
	 * Output: [AITK] [synonyms] INFO: Created synonym {"term":"foo"}
	 *
	 * @param array<string, scalar> $context
	 */
	public function log( string $level, string $channel, string $message, array $context = [] ): void {
		$this->assert_valid_level( $level );

		$entry = sprintf( '%s [%s] %s: %s', $this->prefix, $channel, strtoupper( $level ), $message );

		if ( [] !== $context ) {
			$entry .= ' ' . wp_json_encode( $context, JSON_UNESCAPED_SLASHES );
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $entry );
	}
}
