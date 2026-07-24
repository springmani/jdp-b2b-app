<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Utils;

use function json_decode;

/**
 * JSON Decoder service.
 *
 * Provides safe JSON decoding functionality.
 */
final class Json_Decoder {
	/**
	 * Decode JSON string to array.
	 *
	 * @param string $json JSON string to decode.
	 *
	 * @return array<array-key, array<array-key, bool|int|string>|bool|int|string>
	 */
	public function decode( string $json ): array {
		// phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- JsonDecoder service encapsulates json_decode usage.
		$decoded = json_decode( $json, true );

		return is_array( $decoded ) ? $decoded : [];
	}
}
