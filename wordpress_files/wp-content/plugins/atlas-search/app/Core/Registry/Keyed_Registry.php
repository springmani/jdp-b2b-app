<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Registry;

use WPE\AITK\Core\Registry\Contracts\Keyed_Object_Registry_Interface;
use WPE\AITK\Core\Registry\Exceptions\Invalid_Service_Type;
use WPE\AITK\Core\Registry\Exceptions\Unknown_Service_Key;

/**
 * Immutable keyed factory for any interface/class.
 *
 * @template T of object
 *
 * @implements \WPE\AITK\Core\Registry\Contracts\Keyed_Object_Registry_Interface<T>
 */
abstract class Keyed_Registry implements Keyed_Object_Registry_Interface {
	/**
	 * @var class-string<T>
	 */
	private string $type;

	/**
	 * @var array<string, object>
	 */
	private array $services;

	/**
	 * @param array<string, object> $services @psalm-param array<string, T> $services.
	 *
	 * @phpstan-param array<string, T> $services
	 *
	 * @throws \WPE\AITK\Core\Registry\Exceptions\Invalid_Service_Type If any service does not match the expected type.
	 */
	public function __construct( array $services ) {
		$this->verify_type( $this->expected_type() );
		$this->verify_services( $services );
	}

	public function get_type(): string {
		return $this->type;
	}

	/**
	 * @param string $key The key to retrieve an object for.
	 *
	 * @phpstan-return T
	 *
	 * @throws \WPE\AITK\Core\Registry\Exceptions\Unknown_Service_Key If no service is registered under the given key.
	 */
	public function get( string $key ): object {
		if ( ! isset( $this->services[ $key ] ) ) {
			throw new Unknown_Service_Key(
				sprintf( "No service registered under key '%s'.", $key )
			);
		}

		$service = $this->services[ $key ];
		assert( $service instanceof $this->type );

		return $service;
	}

	public function has( string $key ): bool {
		return isset( $this->services[ $key ] );
	}

	/**
	 * @return list<string>
	 */
	public function keys(): array {
		return array_values( array_map(
			static fn ( $k ): string => (string) $k,
			array_keys( $this->services )
		) );
	}

	/**
	 * @return array<string, object> @psalm-return array<string, T>
	 *
	 * @phpstan-return array<string, T>
	 */
	public function all(): array {
		return $this->services;
	}

	/**
	 * Subclasses must declare the expected type.
	 *
	 * @return class-string<T>
	 */
	abstract protected function expected_type(): string;

	/**
	 * @param class-string $type The expected class or interface name.
	 *
	 * @throws \WPE\AITK\Core\Registry\Exceptions\Invalid_Service_Type If the type is not a valid class or interface.
	 */
	private function verify_type( string $type ): void {
		if ( '' === $type || ( ! interface_exists( $type ) && ! class_exists( $type ) ) ) {
			throw new Invalid_Service_Type(
				sprintf( "Type '%s' is not a valid class/interface.", $type )
			);
		}

		$this->type = $type;
	}

	/**
	 * @param array<string, object> $services @psalm-param array<string, T> $services.
	 *
	 * @phpstan-param array<string, T> $services
	 *
	 * @throws \WPE\AITK\Core\Registry\Exceptions\Invalid_Service_Type If any service does not match the expected type.
	 */
	private function verify_services( array $services ): void {
		foreach ( $services as $key => $service ) {
			if ( ! is_object( $service ) ) {
				throw new Invalid_Service_Type(
					sprintf( "Service for key '%s' must be an object.", $key )
				);
			}

			if ( ! $service instanceof $this->type ) {
				$got = get_class( $service );

				throw new Invalid_Service_Type(
					sprintf( "Service '%s' is '%s', expected instance of '%s'.", $key, $got, $this->type )
				);
			}
		}

		$this->services = $services;
	}
}
