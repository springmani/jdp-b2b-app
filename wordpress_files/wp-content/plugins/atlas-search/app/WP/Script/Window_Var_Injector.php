<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Script;

use WPE\AITK\WP\Script\Exceptions\Script_Injection_Exception;

final class Window_Var_Injector extends Base_Script_Injector {
	private const INLINE_SCRIPT_TEMPLATE = 'window.%s = %s;';

	/**
	 * @inheritDoc
	 *
	 * @throws \WPE\AITK\WP\Script\Exceptions\Script_Injection_Exception If injection fails.
	 */
	protected function do_inject( string $handle, array $data_array, string $object_name ): void {
		$json_data = wp_json_encode( $data_array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		if ( false === $json_data ) {
			throw Script_Injection_Exception::json_encode_failed( $handle, $object_name );
		}

		$inline_script = sprintf( self::INLINE_SCRIPT_TEMPLATE, esc_js( $object_name ), $json_data );

		if ( ! wp_add_inline_script( $handle, $inline_script, 'before' ) ) {
			throw Script_Injection_Exception::inline_script_failed( $handle, $object_name );
		}
	}
}
