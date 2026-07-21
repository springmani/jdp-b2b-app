<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Debug;

use WPE\AITK\Core\Container\Container;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\Core\Filesystem\File_Handler;
use WPE\AITK\WP\Debug\Flushers\File_Flusher;
use WPE\AITK\WP\Debug\Loggers\Buffer_Logger;
use WPE\AITK\WP\Debug\Loggers\Composite_Logger;
use WPE\AITK\WP\Debug\Loggers\Debug_Logger;
use WPE\AITK\WP\Debug\Loggers\Null_Logger;
use WPE\AITK\WP\Multisite\WP_Multisite_Environment;

final class Debug_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Buffer_Logger::class    => static fn ( Container $c ): Buffer_Logger => new Buffer_Logger(
				$c->get( WP_Multisite_Environment::class )
			),
			Composite_Logger::class => static fn ( Container $c ): Composite_Logger => $c->get(
				Log_Gate::class
			)->is_enabled()
				? new Composite_Logger( [ $c->get( Debug_Logger::class ), $c->get( Buffer_Logger::class ) ] )
				: new Composite_Logger( [ $c->get( Null_Logger::class ) ] ),
			Debug_Logger::class     => static fn (): Debug_Logger => new Debug_Logger(),
			File_Flusher::class     => static fn ( Container $c ): File_Flusher => new File_Flusher(
				WP_CONTENT_DIR . '/smart-search.log',
				$c->get( File_Handler::class )
			),
			Log_Gate::class         => static fn (): Log_Gate => new Log_Gate(),
			Null_Logger::class      => static fn (): Null_Logger => new Null_Logger(),
		];
	}
}
