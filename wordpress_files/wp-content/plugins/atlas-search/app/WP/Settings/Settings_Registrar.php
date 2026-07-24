<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings;

use Throwable;
use WPE\AITK\WP\Schema\Contracts\Schema_Builder_Interface;
use WPE\AITK\WP\Settings\Contracts\Settings_Registrar_Interface;
use WPE\AITK\WP\Settings\Exceptions\Setting_Registration_Exception;
use WP_Error;

/**
 * Registers all declared settings with WordPress REST API.
 */
final class Settings_Registrar implements Settings_Registrar_Interface {
	public const SETTINGS_GROUP = 'wpe_aitk';

	private Schema_Builder_Interface $schema_builder;

	public function __construct( Schema_Builder_Interface $schema_builder ) {
		$this->schema_builder = $schema_builder;
	}

	/**
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Registration_Exception If the data class cannot be instantiated with defaults.
	 */
	public function register( string $option_name, string $data_class ): void {
		try {
			$default = ( new $data_class() )->to_array();
		} catch ( Throwable $e ) {
			throw Setting_Registration_Exception::registration_failed(
				$option_name,
				sprintf( 'Cannot create default instance of %s: %s', $data_class, $e->getMessage() )
			);
		}

		register_setting(
			$this->get_group_name( $option_name ),
			$option_name,
			[
				'default'           => $default,
				'sanitize_callback' => $this->create_sanitize_callback( $data_class ),
				'show_in_rest'      => [
					'schema' => $this->schema_builder->build_settings_schema( $data_class ),
				],
				'type'              => 'object',
			]
		);
	}

	/**
	 * Derives the settings group from the option name.
	 *
	 * Option names are expected to follow the pattern "namespace/section/name"
	 * (e.g. "aitk/config/recommendations"). The group is built from all segments
	 * except the last, joined with underscores (e.g. "aitk_config").
	 * Options without "/" fall back to the default group.
	 */
	private function get_group_name( string $option_name ): string {
		$parts = explode( '/', $option_name );
		array_pop( $parts );

		if ( empty( $parts ) ) {
			return self::SETTINGS_GROUP;
		}

		return implode( '_', $parts );
	}

	/**
	 * @param class-string<\WPE\AITK\WP\Settings\Contracts\Setting_Interface> $data_class The data class.
	 *
	 * @return callable The sanitization callback.
	 */
	private function create_sanitize_callback( string $data_class ): callable {
		return static function ( $value ) use ( $data_class ) {
			if ( ! is_array( $value ) ) {
				return new WP_Error(
					'rest_invalid_type',
					sprintf( 'Expected array for %s, got %s', $data_class, get_debug_type( $value ) )
				);
			}

			try {
				return $data_class::from_array( $value )->to_array();
			} catch ( Throwable $e ) {
				return new WP_Error( 'rest_invalid_param', $e->getMessage() );
			}
		};
	}
}
