<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Debug\Loggers;

use WPE\AITK\WP\Debug\Contracts\Logger_Interface;

/**
 * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, SlevomatCodingStandard.Functions.DisallowEmptyFunction.EmptyFunction, Squiz.WhiteSpace.ScopeClosingBrace.ContentBefore
 */
final class Null_Logger implements Logger_Interface {
	/**
	 * @inheritDoc
	 */
	public function log( string $level, string $channel, string $message, array $context = [] ): void {}

	/**
	 * @inheritDoc
	 */
	public function debug( string $channel, string $message, array $context = [] ): void {}

	/**
	 * @inheritDoc
	 */
	public function error( string $channel, string $message, array $context = [] ): void {}

	/**
	 * @inheritDoc
	 */
	public function info( string $channel, string $message, array $context = [] ): void {}

	/**
	 * @inheritDoc
	 */
	public function notice( string $channel, string $message, array $context = [] ): void {}

	/**
	 * @inheritDoc
	 */
	public function warning( string $channel, string $message, array $context = [] ): void {}
}
