<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Utils;

final class Strings_Matcher {
	/**
	 * Check if all required strings are present in the actual set.
	 *
	 * @param array<string> $required
	 * @param array<string> $actual
	 */
	public static function all( array $required, array $actual ): bool {
		return count( array_intersect( $required, $actual ) ) === count( $required );
	}

	/**
	 * Check if any of the required strings are present in the actual set.
	 *
	 * @param array<string> $required
	 * @param array<string> $actual
	 */
	public static function any( array $required, array $actual ): bool {
		return ! empty( array_intersect( $required, $actual ) );
	}
}
