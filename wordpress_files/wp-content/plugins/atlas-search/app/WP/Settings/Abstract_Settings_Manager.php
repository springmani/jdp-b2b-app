<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings;

use Throwable;
use WPE\AITK\WP\Debug\Contracts\Logger_Interface;
use WPE\AITK\WP\Settings\Contracts\Setting_Interface;
use WPE\AITK\WP\Settings\Contracts\Settings_Manager_Interface;
use WPE\AITK\WP\Settings\Contracts\Settings_Repository_Interface;
use WPE\AITK\WP\Settings\Exceptions\Setting_Not_Registered_Exception;

abstract class Abstract_Settings_Manager implements Settings_Manager_Interface {
	protected Settings_Repository_Interface $repository;

	protected Logger_Interface $logger;

	public function __construct( Settings_Repository_Interface $repository, Logger_Interface $logger ) {
		$this->repository = $repository;
		$this->logger     = $logger;
	}

	/**
	 * @inheritDoc
	 *
	 * @template T of \WPE\AITK\WP\Settings\Contracts\Setting_Interface
	 *
	 * @param class-string<T> $data_class The data class to retrieve.
	 *
	 * @return \WPE\AITK\WP\Settings\Contracts\Setting_Interface|null
	 */
	public function get( string $data_class ): ?Setting_Interface {
		$option_name  = $this->repository->get_option_name( $data_class );
		$option_value = $this->retrieve_option( $option_name );

		if ( null === $option_value ) {
			return null;
		}

		if ( ! is_array( $option_value ) ) {
			$this->logger->warning(
				'settings_manager',
				sprintf(
					'%s "%s" has invalid type %s, expected array. Returning null.',
					$this->get_setting_label(),
					$option_name,
					get_debug_type( $option_value )
				)
			);

			return null;
		}

		$instance = $this->hydrate( $option_name, $option_value, $data_class );

		assert( null === $instance || $instance instanceof $data_class );

		return $instance;
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Not_Registered_Exception If option is not registered.
	 */
	public function update( string $option_name, Setting_Interface $setting ): bool {
		if ( ! $this->repository->is_registered( $option_name ) ) {
			throw Setting_Not_Registered_Exception::for_option( $option_name );
		}

		return $this->persist_option( $option_name, $setting->to_array() );
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Not_Registered_Exception If the class is not registered.
	 */
	public function get_raw( string $option_name ): ?array {
		if ( ! $this->has( $option_name ) ) {
			throw Setting_Not_Registered_Exception::for_option( $option_name );
		}

		$option_value = $this->retrieve_option( $option_name );

		return is_array( $option_value ) ? $option_value : null;
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Not_Registered_Exception If the class is not registered.
	 */
	public function update_raw( string $option_name, array $data ): bool {
		if ( ! $this->has( $option_name ) ) {
			throw Setting_Not_Registered_Exception::for_option( $option_name );
		}

		return $this->persist_option( $option_name, $data );
	}

	public function delete( string $option_name ): bool {
		return $this->remove_option( $option_name );
	}

	public function has( string $option_name ): bool {
		return $this->repository->is_registered( $option_name );
	}

	public function add( string $option_name, string $data_class ): void {
		$this->repository->set( $option_name, $data_class );
	}

	/**
	 * @inheritDoc
	 */
	public function get_all(): array {
		return $this->repository->get_all();
	}

	/**
	 * @param string $option_name
	 *
	 * @return array<array-key, scalar|array<array-key, scalar>>|scalar|null
	 */
	//phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
	abstract protected function retrieve_option( string $option_name );

	/**
	 * @param string $option_name
	 * @param array<array-key, scalar|array<array-key, scalar>> $data
	 */
	abstract protected function persist_option( string $option_name, array $data ): bool;

	abstract protected function remove_option( string $option_name ): bool;

	abstract protected function get_setting_label(): string;

	/**
	 * @template T of \WPE\AITK\WP\Settings\Contracts\Setting_Interface
	 *
	 * @param string          $option_name  Option name for logging.
	 * @param array<array-key, scalar|array<array-key, scalar>> $option_value Raw value from the option store.
	 * @param class-string<T> $data_class   The data class to hydrate.
	 */
	protected function hydrate( string $option_name, array $option_value, string $data_class ): ?Setting_Interface {
		try {
			return $data_class::from_array( $option_value );
		} catch ( Throwable $e ) {
			$this->logger->warning(
				'settings_manager',
				sprintf(
					'%s "%s" has invalid structure: %s. Returning null.',
					$this->get_setting_label(),
					$option_name,
					$e->getMessage()
				)
			);

			return null;
		}
	}
}
