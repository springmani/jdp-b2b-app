<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Debug\Loggers;

use WPE\AITK\WP\Debug\Concerns\With_Logger_Level;
use WPE\AITK\WP\Debug\Contracts\Logger_Interface;

final class Composite_Logger implements Logger_Interface {
	use With_Logger_Level;

	/**
	 * @var array<int, \WPE\AITK\WP\Debug\Contracts\Logger_Interface>
	 */
	private array $children;

	/**
	 * @param array<int, \WPE\AITK\WP\Debug\Contracts\Logger_Interface> $children
	 */
	public function __construct( array $children ) {
		$this->children = $children;
	}

	/**
	 * @inheritDoc
	 */
	public function log( string $level, string $channel, string $message, array $context = [] ): void {
		$this->assert_valid_level( $level );

		foreach ( $this->children as $child ) {
			$child->log( $level, $channel, $message, $context );
		}
	}
}
