<?php
// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint


declare(strict_types=1);

namespace WPE\AITK\Modules\Block_Shortcode;

/**
 * Block Shortcode Renderer.
 *
 * Provides generic shortcode rendering for all WP Engine Smart Search blocks.
 */
final class Block_Shortcode_Renderer {
	/**
	 * Render a block from shortcode attributes.
	 *
	 * @param string                                     $block_name The full block name (e.g., 'wpengine-smart-search/chatkit').
	 * @param array<string, string|int|bool|float|array> $atts       Shortcode attributes.
	 * @param array<string, string|int|bool|float|array> $defaults   Default attribute values.
	 *
	 * @return string Rendered block HTML.
	 */
	public function render( string $block_name, array $atts, array $defaults = [] ): string {
		// Merge attributes with defaults.
		$shortcode_atts = array_merge( $defaults, $atts );

		// Convert attributes from lowercase to camelCase.
		$block_attributes = $this->convert_attributes_to_camel_case( $shortcode_atts );

		// Render the block.
		return render_block(
			[
				'attrs'     => $block_attributes,
				'blockName' => $block_name,
			]
		);
	}

	/**
	 * Create a shortcode callback for a specific block.
	 *
	 * @param string                                     $block_name The full block name.
	 * @param array<string, string|int|bool|float|array> $atts       Shortcode attributes.
	 * @param array<string, string|int|bool|float|array> $defaults   Default attribute values.
	 *
	 * @return callable The shortcode callback function.
	 */
	public function create_shortcode_callback( string $block_name, array $atts, array $defaults = [] ): callable {
		return fn (): string => $this->render( $block_name, $atts, $defaults );
	}

	/**
	 * Convert attribute keys from lowercase to camelCase.
	 *
	 * WordPress converts shortcode attributes to lowercase, so we need to convert them back
	 * to camelCase format expected by the blocks.
	 *
	 * @param array<string, string|int|bool|float|array<string, string|int|bool|float>> $attributes Attributes with lowercase keys.
	 *
	 * @return array<string, string|int|bool|float|array<string, string|int|bool|float>> Attributes with camelCase keys.
	 */
	private function convert_attributes_to_camel_case( array $attributes ): array {
		$camel_case_attributes = [];

		foreach ( $attributes as $key => $value ) {
			$camel_case_key                           = $this->to_camel_case( $key );
			$camel_case_attributes[ $camel_case_key ] = $this->convert_attribute_value( $value );
		}

		return $camel_case_attributes;
	}

	/**
	 * Convert attribute value to appropriate type.
	 *
	 * @param string|int|bool|float|array<string, string|int|bool|float> $value The attribute value.
	 *
	 * @return string|int|bool|float|array<string, string|int|bool|float> The converted value.
	 */
	private function convert_attribute_value( $value ) {
		if ( ! is_string( $value ) ) {
			return $value;
		}

		// Try to convert comma-separated values to array.
		$converted_array = $this->try_convert_comma_separated_string( $value );

		if ( null !== $converted_array ) {
			return $converted_array;
		}

		$converted_boolean = $this->try_convert_boolean_string( $value );

		if ( null !== $converted_boolean ) {
			return $converted_boolean;
		}

		return $this->try_convert_numeric_string( $value );
	}

	/**
	 * Try to convert a comma-separated string to an array.
	 *
	 * @param string $value The string value.
	 *
	 * @return array<int, string>|null Array if conversion successful, null otherwise.
	 */
	private function try_convert_comma_separated_string( string $value ): ?array {
		// Skip empty strings or JSON-looking strings.
		if ( '' === $value || '[' === $value[0] || '{' === $value[0] ) {
			return null;
		}

		// If it doesn't contain a comma, skip.
		if ( false === strpos( $value, ',' ) ) {
			return null;
		}

		// Split by comma and trim whitespace from each value.
		$values = array_map( 'trim', explode( ',', $value ) );

		// Filter out empty values that might result from trailing commas.
		return array_values( array_filter( $values, static fn ( $v ) => '' !== $v ) );
	}

	/**
	 * Try to convert a boolean string to a boolean value.
	 *
	 * @param string $value The string value.
	 *
	 * @return bool|null Boolean if conversion successful, null otherwise.
	 */
	private function try_convert_boolean_string( string $value ): ?bool {
		$lower_value = strtolower( $value );

		if ( in_array( $lower_value, [ 'true', 'false' ], true ) ) {
			return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}

		return null;
	}

	/**
	 * Try to convert a numeric string to int or float.
	 *
	 * @param string $value The string value.
	 *
	 * @return string|int|float Converted number or original string if not numeric.
	 */
	private function try_convert_numeric_string( string $value ) {
		if ( ! is_numeric( $value ) ) {
			return $value;
		}

		return false !== strpos( $value, '.' ) ? (float) $value : (int) $value;
	}

	/**
	 * Convert a string to camelCase.
	 *
	 * @param string $str The string to convert.
	 *
	 * @return string The camelCase string.
	 */
	private function to_camel_case( string $str ): string {
		// Replace underscores and hyphens with spaces.
		$str = str_replace( [ '_', '-' ], ' ', $str );

		// Capitalize first letter of each word.
		$str = ucwords( $str );

		// Remove spaces.
		$str = str_replace( ' ', '', $str );

		// Make first letter lowercase.
		return lcfirst( $str );
	}
}
