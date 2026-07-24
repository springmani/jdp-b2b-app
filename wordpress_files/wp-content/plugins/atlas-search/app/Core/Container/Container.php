<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Container;

use Psr\Container\ContainerInterface;
use WPE\AITK\Core\Concerns\Without_Clone;
use WPE\AITK\Core\Concerns\Without_Sleep;
use WPE\AITK\Core\Concerns\Without_Wakeup;
use WPE\AITK\Core\Container\Exceptions\Container_Immutable_Exception;
use WPE\AITK\Core\Container\Exceptions\Service_Already_Defined_Exception;
use WPE\AITK\Core\Container\Exceptions\Service_Not_Found_Exception;
use WPE\AITK\Core\Container\Exceptions\Service_Resolution_Exception;

final class Container implements ContainerInterface {
	use Without_Clone;
	use Without_Wakeup;
	use Without_Sleep;

	/**
	 * @var array<class-string, callable(\Psr\Container\ContainerInterface): object>
	 */
	private array $factories = [];

	/**
	 * @var array<class-string, object>
	 */
	private array $instances = [];

	/**
	 * Indicates whether the container is locked and cannot accept new registrations.
	 *
	 * @var bool
	 */
	private bool $locked = false;

	public function __construct() {
		$this->instances[ self::class ] = $this;
	}

	/**
	 * Register a factory for a service ID (class-string).
	 *
	 * @param string                                              $id ID of the service to register.
	 * @param callable(\Psr\Container\ContainerInterface): object $factory Factory function that returns an instance of the service.
	 *
	 * @template T of object
	 *
	 * @phpstan-param class-string<T> $id ID of the service to register — the class name.
	 *
	 * @phpstan-param callable(\Psr\Container\ContainerInterface): T $factory Factory function that returns an instance of the service.
	 *
	 * @throws \WPE\AITK\Core\Container\Exceptions\Container_Immutable_Exception Thrown if the container is locked and cannot accept new registrations.
	 * @throws \WPE\AITK\Core\Container\Exceptions\Service_Already_Defined_Exception Thrown if the service ID is already registered.
	 * @throws \WPE\AITK\Core\Container\Exceptions\Service_Not_Found_Exception Thrown if the service ID does not correspond to an existing class.
	 */
	public function register( string $id, callable $factory ): void {
		if ( $this->locked ) {
			throw new Container_Immutable_Exception(
				sprintf( "Cannot register service '%s' after container is finalized.", $id )
			);
		}

		if ( ! class_exists( $id ) ) {
			throw new Service_Not_Found_Exception(
				sprintf( "Cannot register service '%s': class does not exist.", $id )
			);
		}

		if ( $this->has( $id ) ) {
			throw new Service_Already_Defined_Exception(
				sprintf( "Service '%s' is already defined.", $id )
			);
		}

		$this->factories[ $id ] = $factory;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @template T of object
	 *
	 * @phpstan-param class-string<T> $id
	 *
	 * @return object
	 *
	 * @phpstan-return T
	 *
	 * @throws \WPE\AITK\Core\Container\Exceptions\Service_Not_Found_Exception Thrown when service not found.
	 * @throws \WPE\AITK\Core\Container\Exceptions\Service_Resolution_Exception Thrown when service cannot be resolved.
	 */
	public function get( string $id ): object {
		if ( isset( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		if ( ! isset( $this->factories[ $id ] ) ) {
			throw new Service_Not_Found_Exception( sprintf( "Service '%s' not found.", $id ) );
		}

		$instance = $this->factories[ $id ]( $this );

		if ( ! is_object( $instance ) ) {
			throw new Service_Resolution_Exception(
				sprintf(
					"The callable registered for '%s' did not return an object. Got '%s' instead.",
					$id,
					gettype( $instance )
				)
			);
		}

		// phpcs:ignore SlevomatCodingStandard.Classes.ModernClassNameReference.ClassNameReferencedViaFunctionCall
		$class = get_class( $instance );

		if ( ! class_exists( $id ) || $class !== $id ) {
			throw new Service_Resolution_Exception(
				sprintf(
					"The callable registered for '%s' returned '%s', which does not implement the expected type '%s'.",
					$id,
					$class,
					$id
				)
			);
		}

		$this->instances[ $id ] = $instance;
		unset( $this->factories[ $id ] );

		return $instance;
	}

	public function has( string $id ): bool {
		return isset( $this->instances[ $id ] ) || isset( $this->factories[ $id ] );
	}

	public function finalize(): void {
		$this->locked = true;
	}

	/**
	 * @param class-string $id ID of the service to retrieve.
	 *
	 * @template T of object
	 *
	 * @phpstan-param class-string<T> $id ID of the service to register — the class name.
	 *
	 * @phpstan-return T Instance of the service with the given ID.
	 *
	 * @throws \Psr\Container\ContainerExceptionInterface Thrown if the service cannot be retrieved due to a container error.
	 * @throws \Psr\Container\NotFoundExceptionInterface Thrown if the service with the given ID does not exist in the container.
	 */
	public function __invoke( string $id ): object {
		return $this->get( $id );
	}
}
