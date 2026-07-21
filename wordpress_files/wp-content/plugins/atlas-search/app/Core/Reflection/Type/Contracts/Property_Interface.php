<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Type\Contracts;

use WPE\AITK\Core\Reflection\Docblock\Contracts\Info_Interface;

/**
 * Common interface for all property type objects.
 *
 * Provides consistent API for accessing type information, default values,
 * nullability, and type-specific constraints.
 */
interface Property_Interface {
	/**
	 * Gets the native type name.
	 *
	 * @return value-of<\WPE\AITK\Core\Reflection\Property_Parser::SUPPORTED_TYPES>
	 */
	public function get_type(): string;

	/**
	 * Checks if this property is required (non-nullable).
	 *
	 * @return bool True if required, false if nullable.
	 */
	public function get_required(): bool;

	/**
	 * Gets the constraint information for this property.
	 *
	 * @return \WPE\AITK\Core\Reflection\Docblock\Contracts\Info_Interface The constraint info object.
	 */
	public function get_info(): Info_Interface;
}
