<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Docblock;

use WPE\AITK\Core\Reflection\Docblock\Contracts\Info_Interface;
use WPE\AITK\Core\Reflection\Docblock\Exceptions\Invalid_Enum_Exception;

final class String_Info implements Info_Interface {
	/**
	 * Whether this string must be non-empty.
	 */
	private bool $is_non_empty;

	/**
	 * The list of allowed string values (enum constraint).
	 *
	 * @var array<array-key, string>
	 */
	private ?array $allowed_values;

	public function __construct( bool $is_non_empty = false, ?array $allowed_values = null ) {
		$this->validate_allowed_values( $allowed_values );

		$this->is_non_empty   = $is_non_empty;
		$this->allowed_values = $allowed_values;
	}

	public function get_is_non_empty(): bool {
		return $this->is_non_empty;
	}

	/**
	 * @return array<int, string>|null The allowed values, or null if no enum constraint.
	 */
	public function get_allowed_values(): ?array {
		return $this->allowed_values;
	}

	public function is_enum(): bool {
		return null !== $this->allowed_values;
	}

	public function has_constraints(): bool {
		return $this->is_non_empty || $this->is_enum();
	}

	public function get_allowed_values_count(): int {
		return $this->is_enum() ? count( $this->allowed_values ) : 0;
	}

	/**
	 * @param array<int, string>|null $allowed_values The allowed values to validate.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Docblock\Exceptions\Invalid_Enum_Exception If the array is empty or contains non-string values.
	 */
	private function validate_allowed_values( ?array $allowed_values ): void {
		// Null is valid (no enum constraint).
		if ( null === $allowed_values ) {
			return;
		}

		// Empty array is not valid for enum.
		if ( empty( $allowed_values ) ) {
			throw new Invalid_Enum_Exception( 'Allowed values array cannot be empty. Use null for no enum.' );
		}

		// All values must be non-empty strings.
		foreach ( $allowed_values as $i => $value ) {
			if ( ! is_string( $value ) || '' === $value ) {
				throw new Invalid_Enum_Exception(
					'All allowed values must be non-empty strings. ' .
					sprintf( 'Found %s at index %d.', gettype( $value ), $i )
				);
			}
		}
	}
}
