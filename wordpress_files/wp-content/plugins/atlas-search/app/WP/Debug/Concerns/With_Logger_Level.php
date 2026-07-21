<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Debug\Concerns;

use WPE\AITK\WP\Debug\Exceptions\Invalid_Log_Level_Exception;
use WPE\AITK\WP\Debug\Log_Level;

trait With_Logger_Level {
	/**
	 * @param array<string, scalar> $context
	 */
	abstract public function log( string $level, string $channel, string $message, array $context = [] ): void;

	/**
	 * @param array<string, scalar> $context
	 */
	public function debug( string $channel, string $message, array $context = [] ): void {
		$this->log( Log_Level::DEBUG, $channel, $message, $context );
	}

	/**
	 * @param array<string, scalar> $context
	 */
	public function error( string $channel, string $message, array $context = [] ): void {
		$this->log( Log_Level::ERROR, $channel, $message, $context );
	}

	/**
	 * @param array<string, scalar> $context
	 */
	public function info( string $channel, string $message, array $context = [] ): void {
		$this->log( Log_Level::INFO, $channel, $message, $context );
	}

	/**
	 * @param array<string, scalar> $context
	 */
	public function notice( string $channel, string $message, array $context = [] ): void {
		$this->log( Log_Level::NOTICE, $channel, $message, $context );
	}

	/**
	 * @param array<string, scalar> $context
	 */
	public function warning( string $channel, string $message, array $context = [] ): void {
		$this->log( Log_Level::WARNING, $channel, $message, $context );
	}

	protected function assert_valid_level( string $level ): void {
		if ( ! Log_Level::is_valid( $level ) ) {
			throw new Invalid_Log_Level_Exception(
				sprintf( 'Unsupported log level "%s"', $level )
			);
		}
	}
}
