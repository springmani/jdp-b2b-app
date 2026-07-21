<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Schema\Contracts;

/**
 * Defines the contract for building WordPress-compatible JSON schemas from PHP class definitions.
 *
 * phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
 */
interface Schema_Builder_Interface {
	/**
	 * Builds a complete REST API JSON Schema Draft-04 for a class.
	 *
	 * @return array<array-key, mixed> The complete REST API schema.
	 *
	 * @throws \WPE\AITK\WP\Schema\Exceptions\Schema_Generation_Exception If schema generation fails.
	 */
	public function build_rest_schema( string $class_name, string $title, ?string $description = null ): array;

	/**
	 * Builds a settings schema for WordPress settings registration.
	 *
	 * @return array<array-key, mixed> The settings schema.
	 *
	 * @throws \WPE\AITK\WP\Schema\Exceptions\Schema_Generation_Exception If schema generation fails.
	 */
	public function build_settings_schema( string $class_name ): array;

	/**
	 * Builds WordPress-compatible properties schema from class properties.
	 *
	 * @return array<array-key, mixed> Array of property schemas keyed by property name.
	 *
	 * @throws \WPE\AITK\WP\Schema\Exceptions\Schema_Generation_Exception If schema generation fails.
	 */
	public function build_properties_schema( string $class_name ): array;
}
