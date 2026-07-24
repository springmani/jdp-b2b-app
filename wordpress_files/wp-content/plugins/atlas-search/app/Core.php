<?php

declare(strict_types=1);

namespace WPE\AITK;

use WPE\AITK\Core\Concerns\Without_Clone;
use WPE\AITK\Core\Concerns\Without_Construct;
use WPE\AITK\Core\Concerns\Without_Sleep;
use WPE\AITK\Core\Concerns\Without_Wakeup;
use WPE\AITK\Core\Container\Container_Factory;
use WPE\AITK\Core\Definer\Definer_Factory;

final class Core {
	use Without_Construct;
	use Without_Clone;
	use Without_Wakeup;
	use Without_Sleep;

	/**
	 * Plugin singleton instance.
	 *
	 * @var ?self
	 */
	private static ?self $instance = null;

	/**
	 * Singleton instance implementation.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Bootstraps the plugin by initializing the service container and registering all runtime components.
	 *
	 * This method can be invoked during the `plugins_loaded` action hook to ensure WordPress is fully loaded.
	 *
	 * @param array<class-string<\WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface>>    $definers Class Definers array.
	 * @param array<class-string<\WPE\AITK\Core\Subscriber\Contracts\Hook_Subscriber_Interface>> $subscribers Subscribers array.
	 *
	 * @throws \WPE\AITK\Core\Container\Exceptions\Container_Creation_Exception Throws if the container cannot be created.
	 * @throws \Psr\Container\ContainerExceptionInterface Throws if a service cannot be resolved.
	 */
	public function init( array $definers = [], array $subscribers = [] ): void {
		$container = ( new Container_Factory( new Definer_Factory() ) )->create_container( $definers );

		foreach ( $subscribers as $subscriber_class ) {
			( new $subscriber_class( $container ) )->register();
		}
	}
}
