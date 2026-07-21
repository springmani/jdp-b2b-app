<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Debug\Contracts;

interface Logger_Interface {
	/**
	 * @param array<string, scalar> $context
	 */
	public function log( string $level, string $channel, string $message, array $context = [] ): void;

	/**
	 * @param array<string, scalar> $context
	 */
	public function debug( string $channel, string $message, array $context = [] ): void;

	/**
	 * @param array<string, scalar> $context
	 */
	public function error( string $channel, string $message, array $context = [] ): void;

	/**
	 * @param array<string, scalar> $context
	 */
	public function info( string $channel, string $message, array $context = [] ): void;

	/**
	 * @param array<string, scalar> $context
	 */
	public function notice( string $channel, string $message, array $context = [] ): void;

	/**
	 * @param array<string, scalar> $context
	 */
	public function warning( string $channel, string $message, array $context = [] ): void;
}
