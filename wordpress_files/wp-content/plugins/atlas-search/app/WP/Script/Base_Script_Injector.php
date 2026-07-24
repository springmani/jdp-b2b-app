<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Script;

use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;
use WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface;
use WPE\AITK\WP\Script\Exceptions\Script_Injection_Exception;

abstract class Base_Script_Injector implements Script_Data_Injector_Interface {
	final public function inject( string $handle, Array_Convertible_Interface $data, string $object_name ): void {
		$data_array = $data->to_array();

		$this->validate_parameters( $handle, $object_name, $data_array );
		$this->do_inject( $handle, $data_array, $object_name );
	}

	/**
	 * Perform the actual injection logic.
	 * Subclasses implement their specific injection strategy.
	 *
	 * @param string $handle      The script handle.
	 * @param array<string, scalar|array>  $data_array  The data to inject.
	 * @param string $object_name The JavaScript object name.
	 *
	 * @throws \WPE\AITK\WP\Script\Exceptions\Script_Injection_Exception If injection fails.
	 */
	abstract protected function do_inject( string $handle, array $data_array, string $object_name ): void;

	/**
	 * Validate injection parameters.
	 *
	 * @param string $handle      The script handle.
	 * @param string $object_name The JavaScript object name.
	 * @param array<string, scalar|array>  $data_array  The data to inject.
	 *
	 * @throws \WPE\AITK\WP\Script\Exceptions\Script_Injection_Exception
	 */
	private function validate_parameters( string $handle, string $object_name, array $data_array ): void {
		if ( empty( $handle ) ) {
			throw Script_Injection_Exception::empty_handle();
		}

		if ( empty( $object_name ) ) {
			throw Script_Injection_Exception::empty_object_name( $handle );
		}

		if ( empty( $data_array ) ) {
			throw Script_Injection_Exception::empty_data_array( $handle, $object_name );
		}
	}
}
