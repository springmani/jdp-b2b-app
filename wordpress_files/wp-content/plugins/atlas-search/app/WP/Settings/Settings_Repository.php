<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings;

use WPE\AITK\WP\Settings\Contracts\Setting_Interface;
use WPE\AITK\WP\Settings\Contracts\Settings_Repository_Interface;
use WPE\AITK\WP\Settings\Exceptions\Setting_Not_Registered_Exception;
use WPE\AITK\WP\Settings\Exceptions\Setting_Registration_Exception;

final class Settings_Repository implements Settings_Repository_Interface {
	/**
	 * @var array<string, class-string<\WPE\AITK\WP\Settings\Contracts\Setting_Interface>>
	 */
	private array $option_to_class = [];

	/**
	 * @var array<class-string<\WPE\AITK\WP\Settings\Contracts\Setting_Interface>, string>
	 */
	private array $class_to_option = [];

	public function set( string $option_name, string $class_name ): void {
		$this->validate_option_name( $option_name );
		$this->validate_class( $class_name );

		$this->option_to_class[ $option_name ] = $class_name;
		$this->class_to_option[ $class_name ]  = $option_name;
	}

	public function get_option_name( string $data_class ): string {
		if ( ! isset( $this->class_to_option[ $data_class ] ) ) {
			throw Setting_Not_Registered_Exception::for_class( $data_class );
		}

		return $this->class_to_option[ $data_class ];
	}

	public function is_registered( string $option_name ): bool {
		return isset( $this->option_to_class[ $option_name ] );
	}

	/**
	 * @inheritDoc
	 */
	public function get_all(): array {
		return $this->option_to_class;
	}

	/**
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Registration_Exception If the option name or class is invalid.
	 */
	private function validate_option_name( string $option_name ): void {
		if ( '' === trim( $option_name ) ) {
			throw Setting_Registration_Exception::invalid_option_name( $option_name );
		}

		if ( isset( $this->option_to_class[ $option_name ] ) ) {
			throw Setting_Registration_Exception::already_registered( $option_name );
		}
	}

	/**
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Registration_Exception If the class does not exist, is missing the interface, or is already registered.
	 */
	private function validate_class( string $class_name ): void {
		if ( ! class_exists( $class_name ) ) {
			throw Setting_Registration_Exception::class_not_found( $class_name );
		}

		if ( ! is_subclass_of( $class_name, Setting_Interface::class ) ) {
			throw Setting_Registration_Exception::missing_interface( $class_name, Setting_Interface::class );
		}

		if ( isset( $this->class_to_option[ $class_name ] ) ) {
			throw Setting_Registration_Exception::duplicate_class( $class_name, $this->class_to_option[ $class_name ] );
		}
	}
}
