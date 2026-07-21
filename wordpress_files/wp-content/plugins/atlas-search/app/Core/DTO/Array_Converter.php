<?php

declare(strict_types=1);

namespace WPE\AITK\Core\DTO;

final class Array_Converter {
	/**
	 * @param array<string, scalar|array> $data Data to extract from.
	 * @param string $key Key to extract.
	 * @param string $init Default value if the key does not exist.
	 */
	public static function safe_string( array $data, string $key, string $init = '' ): string {
		if ( ! isset( $data[ $key ] ) || is_array( $data[ $key ] ) ) {
			return $init;
		}

		return (string) $data[ $key ];
	}

	/**
	 * @param array<string, scalar|array> $data Data to extract from.
	 * @param string $key Key to extract.
	 * @param bool $init Default value if the key does not exist.
	 */
	public static function safe_bool( array $data, string $key, bool $init = false ): bool {
		if ( ! isset( $data[ $key ] ) || is_array( $data[ $key ] ) ) {
			return $init;
		}

		return (bool) $data[ $key ];
	}

	/**
	 * @param array<string, scalar|array> $data Data to extract from.
	 * @param string $key Key to extract.
	 * @param int $init Default value if the key does not exist.
	 */
	public static function safe_int( array $data, string $key, int $init = 0 ): int {
		if ( ! isset( $data[ $key ] ) || is_array( $data[ $key ] ) ) {
			return $init;
		}

		return (int) $data[ $key ];
	}

	/**
	 * Safely extracts an array of strings from an associative array.
	 *
	 * @param array<string, scalar|array> $data Data to extract from.
	 * @param string $key Key to extract.
	 *
	 * @return array<array-key, string> Array of strings (empty array if not found or invalid).
	 */
	public static function safe_string_array( array $data, string $key ): array {
		if ( ! isset( $data[ $key ] ) || ! is_array( $data[ $key ] ) ) {
			return [];
		}

		$result = [];

		foreach ( $data[ $key ] as $value ) {
			if ( is_string( $value ) ) {
				$result[] = $value;
			}
		}

		return $result;
	}

	/**
	 * Safely extracts an array of integers from an associative array.
	 *
	 * @param array<string, scalar|array> $data Data to extract from.
	 * @param string $key Key to extract.
	 *
	 * @return array<array-key, int> Array of integers (empty array if not found or invalid).
	 */
	public static function safe_int_array( array $data, string $key ): array {
		if ( ! isset( $data[ $key ] ) || ! is_array( $data[ $key ] ) ) {
			return [];
		}

		$result = [];

		foreach ( $data[ $key ] as $value ) {
			if ( is_int( $value ) ) {
				$result[] = $value;
			}
		}

		return $result;
	}

	/**
	 * Safely extracts an array of scalars (int, float, string, bool) from an associative array.
	 *
	 * @param array<string, scalar|array> $data Data to extract from.
	 * @param string $key Key to extract.
	 *
	 * @return array<array-key, scalar> Array of scalar values (empty array if not found or invalid).
	 */
	public static function safe_scalar_array( array $data, string $key ): array {
		if ( ! isset( $data[ $key ] ) || ! is_array( $data[ $key ] ) ) {
			return [];
		}

		return array_filter( $data[ $key ], static fn ( $value ) => is_scalar( $value ) );
	}
}
