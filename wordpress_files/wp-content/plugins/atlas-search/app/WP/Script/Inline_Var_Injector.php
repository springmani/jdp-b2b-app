<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Script;

use WPE\AITK\WP\Script\Exceptions\Script_Injection_Exception;

final class Inline_Var_Injector extends Base_Script_Injector {
	/**
	 * {@inheritDoc}
	 *
	 * @throws \WPE\AITK\WP\Script\Exceptions\Script_Injection_Exception If injection fails.
	 */
	protected function do_inject( string $handle, array $data_array, string $object_name ): void {
		if ( ! wp_localize_script( $handle, $object_name, $data_array ) ) {
			throw Script_Injection_Exception::localize_script_failed( $handle, $object_name );
		}
	}
}
