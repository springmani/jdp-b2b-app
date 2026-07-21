<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Script\Contracts;

use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;

interface Script_Data_Injector_Interface {
	/**
	 * Inject data into a registered script handle.
	 *
	 * @param string                      $handle  The script handle.
	 * @param \WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface $data    Data object implementing to_array().
	 * @param string                 $object_name Optional JS object name (for localize method).
	 *
	 * @throws \WPE\AITK\WP\Script\Exceptions\Script_Injection_Exception
	 */
	public function inject( string $handle, Array_Convertible_Interface $data, string $object_name ): void;
}
