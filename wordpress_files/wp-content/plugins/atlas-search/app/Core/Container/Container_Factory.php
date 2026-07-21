<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use WPE\AITK\Core\Container\Contracts\Container_Factory_Interface;
use WPE\AITK\Core\Container\Exceptions\Container_Creation_Exception;
use WPE\AITK\Core\Definer\Contracts\Definer_Factory_Interface;
use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;

final class Container_Factory implements Container_Factory_Interface {
	/**
	 * @var \WPE\AITK\Core\Definer\Contracts\Definer_Factory_Interface
	 */
	private Definer_Factory_Interface $definer_factory;

	public function __construct( Definer_Factory_Interface $definer_factory ) {
		$this->definer_factory = $definer_factory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function create_container( array $definers ): ContainerInterface {
		$container = new Container();

		foreach ( $definers as $definer_class ) {
			$definer = $this->definer_factory->make( $definer_class );

			$this->register_definer( $container, $definer );
		}

		$container->finalize();

		return $container;
	}

	/**
	 * @param \WPE\AITK\Core\Container\Container                         $container Plugin service container.
	 * @param \WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface $definer Service definer instance to register in the container.
	 *
	 * @throws \WPE\AITK\Core\Container\Exceptions\Container_Creation_Exception Thrown if the definer cannot be registered due to a service already being defined.
	 */
	private function register_definer( Container $container, Service_Definer_Interface $definer ): void {
		$definitions = $definer->define();

		foreach ( $definitions as $id => $callable ) {
			try {
				$container->register( $id, $callable );
			} catch ( ContainerExceptionInterface $e ) {
				throw new Container_Creation_Exception( $e->getMessage() );
			}
		}
	}
}
