<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Support\Debug;

use WPE\AITK\Core\Filesystem\Filesystem_Provider;
use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\Core\Zip\Zip_Creator;
use WPE\AITK\WP\Debug\Flushers\File_Flusher;
use WPE\AITK\WP\Debug\Log_Gate;
use WPE\AITK\WP\Debug\Loggers\Buffer_Logger;
use WPE\AITK\WP\Rest\Route_Registrar;

final class Debug_Subscriber extends Hook_Subscriber {
	public function register(): void {
		$this->register_debug_download_rest();
		$this->flush_logger_on_shutdown();
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 *
	 * @internal Called by the shutdown hook.
	 */
	public function on_shutdown(): void {
		if ( ! $this->container->get( Log_Gate::class )->is_enabled() ) {
			return;
		}

		$buffer  = $this->container->get( Buffer_Logger::class );
		$entries = $buffer->get_entries();

		if ( [] === $entries ) {
			return;
		}

		$this->container->get( File_Flusher::class )->flush( $entries );
		$buffer->clear();
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	private function register_debug_download_rest(): void {
		$path = $this->container->get( File_Flusher::class )->get_log_path();

		$log_handler = new Logs_Handler(
			[ $path => basename( $path ) ],
			$this->container->get( Filesystem_Provider::class ),
			$this->container->get( Zip_Creator::class )
		);

		$this->container->get( Route_Registrar::class )
						->add_controller( new Logs_Rest_Controller( $log_handler ) );
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	private function flush_logger_on_shutdown(): void {
		add_action( 'shutdown', [ $this, 'on_shutdown' ], PHP_INT_MAX );
	}
}
