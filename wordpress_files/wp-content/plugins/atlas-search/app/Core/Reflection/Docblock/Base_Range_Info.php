<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Docblock;

use WPE\AITK\Core\Reflection\Docblock\Contracts\Info_Interface;
use WPE\AITK\Core\Reflection\Docblock\Exceptions\Invalid_Range_Exception;

/**
 * Abstract base class for numeric types with range constraints.
 *
 * Provides common functionality for types that have min/max bounds, such as integers and floats.
 */
abstract class Base_Range_Info implements Info_Interface {
	abstract public function has_min(): bool;

	abstract public function has_max(): bool;

	public function has_constraints(): bool {
		return $this->has_min() || $this->has_max();
	}

	protected function validate_range( ?float $min, ?float $max ): void {
		if ( null !== $min && null !== $max && $min > $max ) {
			throw new Invalid_Range_Exception(
				sprintf(
					'Invalid range: minimum (%s) cannot be greater than maximum (%s).',
					$this->format_float( $min ),
					$this->format_float( $max )
				)
			);
		}
	}

	/**
	 * Formats a float for display in error messages.
	 *
	 * Removes unnecessary trailing zeros and decimal point.
	 *
	 * @param float $value The float value to format.
	 *
	 * @return string The formatted value.
	 */
	private function format_float( float $value ): string {
		$formatted = (string) $value;

		// Only remove trailing zeros if there's a decimal point.
		if ( str_contains( $formatted, '.' ) ) {
			$formatted = rtrim( $formatted, '0' );
			$formatted = rtrim( $formatted, '.' );
		}

		return $formatted;
	}
}
