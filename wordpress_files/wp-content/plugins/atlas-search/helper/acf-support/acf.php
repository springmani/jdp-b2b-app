<?php

namespace Wpe_Content_Engine\Helper\Acf_Support;

use WP_Post;
use WP_Term;
use WP_User;
use Wpe_Content_Engine\Helper\String_Transformation;

class Acf {
	public const SMART_SEARCH_FILTER_ACF_EXCLUDED_FIELDS_NAMES = 'wpe_smartsearch/acf/excluded_field_names';

	/**
	 * @var array
	 */
	public const ACF_UNSUPPORTED_TYPES = [
		Acf_Factory::IMAGE,
		Acf_Factory::PASSWORD,
		Acf_Factory::GALLERY,
	];

	/**
	 * @var array
	 */
	public const ACF_NESTED_TYPES = [
		Acf_Factory::FLEXIBLE_CONTENT,
		Acf_Factory::GROUP,
		Acf_Factory::POST_OBJECT,
		Acf_Factory::RELATIONSHIP,
		Acf_Factory::LINK,
		Acf_Factory::TAXONOMY,
		Acf_Factory::REPEATER,
		Acf_Factory::USER,
		Acf_Factory::GOOGLE_MAP,
		Acf_Factory::FILE,
	];

	/**
	 * @var array
	 */
	private $field_structure;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var array
	 */
	private $coordinates = [];

	/**
	 * @var array
	 */
	private $field_type_map = [];

	public function __construct( array $field_structure, array $data ) {
		$this->field_structure = $field_structure;
		$this->build_field_type_map( $field_structure );
		$this->data = $this->format_data_according_structure( $data );
	}

	public function get_data(): array {
		return $this->data;
	}

	/**
	 * Apply filter to exclude specific field names from indexing.
	 *
	 * @param array  $excluded_field_names An array of field names excluded from indexing by default.
	 * @param string $field_name Field name to check if it should be excluded from indexing.
	 *
	 * @return bool True if the field name should be excluded from indexing, false otherwise.
	 */
	public function verify_field_excluded( $excluded_field_names, $field_name ): bool {
		// Todo: redundant function after enabling strict typing.
		return ! empty( $field_name )
				&& ! empty( $excluded_field_names )
				&& is_array( $excluded_field_names )
				&& in_array( $field_name, $excluded_field_names, true );
	}

	/**
	 * Get coordinates collected during field processing.
	 *
	 * @return array Array of coordinates for GraphQL mutation.
	 */
	public function get_coordinates(): array {
		return $this->coordinates;
	}

	public static function is_acf_loaded(): bool {
		return class_exists( 'ACF' );
	}

	/**
	 * @param string $post_type Post type.
	 *
	 * @return bool
	 */
	public static function acf_exists_for_post_type( string $post_type ): bool {
		return is_acf_loaded() && ! empty( acf_get_field_groups( [ 'post_type' => $post_type ] ) );
	}

	/**
	 * @param mixed $data Data.
	 *
	 * @return mixed
	 */
	protected function convert_empty_data_to_null( $data ) {
		if ( '' === $data || false === $data ) {
			return null;
		}

		if ( is_array( $data ) || is_object( $data ) ) {
			foreach ( $data as &$value ) {
				$value = $this->convert_empty_data_to_null( $value );
			}
		}

		return $data;
	}

	/**
	 * @param mixed $data Data.
	 */
	protected function remove_empty_keys( &$data ) {
		if ( ! is_array( $data ) ) {
			return;
		}

		foreach ( $data as $key => &$value ) {
			if ( '' === $key ) {
				unset( $data[ $key ] );

				continue;
			}

			$this->remove_empty_keys( $value );
		}
	}

	/**
	 * @param array $data Data.
	 *
	 * @return array
	 */
	protected function format_data_according_structure( array $data ): array {
		if ( empty( $this->field_structure ) || empty( $data ) ) {
			return [];
		}

		/**
		 * Filter ACF fields from being indexed to WPE Engine Smart Search.
		 *
		 * This filter prevents ACF fields to be indexed using the field name. This is very useful for a number of reasons:
		 *  - Preventing unnecessary data from being indexed, increases performance.
		 *  - Prevents errors from being thrown when indexing data ( Errors like: Limit of total fields [1000] has been exceeded )
		 *
		 *
		 * example:
		 *
		 * You would want to prevent ACF fields with names 'acf_field_name1', 'acf_field_name2', 'acf_field_name3'
		 * are not indexed.  *
		 *
		 * add_filter( 'wpe_smartsearch/acf/excluded_field_names', function ( $excluded_field_names ) {
		 *      $custom_excluded_field_names= array(
		 *          'acf_field_name1',
		 *          'acf_field_name2',
		 *          'acf_field_name3',
		 *      );
		 *
		 *      return array_merge($excluded_field_names,$custom_excluded_field_names );
		 *  },
		 *  10,
		 *  1
		 * );
		 */
		$excluded_field_names = apply_filters( self::SMART_SEARCH_FILTER_ACF_EXCLUDED_FIELDS_NAMES, [] );

		$field_data = [];

		foreach ( $this->field_structure as $field_group ) {
			if ( empty( $field_group['fields'] ) || empty( $field_group['title'] ) ) {
				continue;
			}

			$group_title = String_Transformation::camel_case( (string) $field_group['title'] );

			$field_data[ $group_title ] = $this->process_fields( $field_group['fields'], $data, $excluded_field_names );
		}

		return $field_data;
	}

	protected function remove_unsupported_data( &$data, $excluded_field_names ) {
		if ( $this->is_wp_instance( $data ) ) {
			$data = \AtlasSearch\Index\filter_wp_object_to_array( $data );

			return;
		}

		if ( ! is_array( $data ) ) {
			return;
		}

		foreach ( $data as $key => &$value ) {
			if ( empty( $value ) ) {
				continue;
			}

			if ( $this->is_wp_instance( $value ) ) {
				$data[ $key ] = \AtlasSearch\Index\filter_wp_object_to_array( $value );

				continue;
			}

			// Check if this field is an unsupported type based on the field type map.
			if ( isset( $this->field_type_map[ $key ] ) &&
				in_array( $this->field_type_map[ $key ], self::ACF_UNSUPPORTED_TYPES, true ) ) {
				unset( $data[ $key ] );

				continue;
			}

			if ( '' === $key || ! $this->should_be_indexed( $value, $key, $excluded_field_names ) ) {
				unset( $data[ $key ] );

				continue;
			}

			$this->remove_unsupported_data( $value, $excluded_field_names );

			// Re-validate after recursion (important for empty arrays).
			if ( ! $this->should_be_indexed( $value, $key, $excluded_field_names ) ) {
				unset( $data[ $key ] );
			}
		}
	}

	protected function process_fields( array $fields, array $data, array $excluded_field_names ): array {
		$result = [];

		foreach ( $fields as $field ) {
			$name = ! empty( $field['name'] ) ? (string) $field['name'] : '';
			$type = ! empty( $field['type'] ) ? (string) $field['type'] : '';

			if ( ! $this->is_field_valid( $name, $type, $data ) ) {
				continue;
			}

			// Todo: sanitize per type.
			$value = $this->sanitize_value( $type, $data[ $name ], $excluded_field_names );
			$key   = String_Transformation::camel_case( $name, [ '_' ] );

			$result[ $key ] = $value;
		}

		return $result;
	}

	protected function is_field_valid( string $name, string $type, array $data ): bool {
		return array_key_exists( $name, $data ) && ! in_array( $type, $this::ACF_UNSUPPORTED_TYPES, true );
	}

	protected function sanitize_value( string $type, $value, array $excluded_field_names ) {
		if ( Acf_Factory::FILE === $type ) {
			$value = $this->extract_file_metadata( $value );
		}

		if ( in_array( $type, [ Acf_Factory::NUMBER, Acf_Factory::RANGE ], true ) ) {
			$value = preg_match( '/^-?\d+$/', $value ) ? (int) $value : (float) $value;
		}

		// Build a coordinates array for geo queries from ACF Map fields.
		if (
			Acf_Factory::GOOGLE_MAP === $type &&
			! empty( $value['lat'] ) &&
			! empty( $value['lng'] )
		) {
			$this->coordinates[] = [
				'lat' => (float) $value['lat'],
				'lon' => (float) $value['lng'],
			];
		}

		// Recursively scan for flexible content at any nesting level.
		if ( is_array( $value ) ) {
			$value = $this->recursively_process_flexible_content( $value, $excluded_field_names );

			if ( Acf_Factory::GOOGLE_MAP !== $type ) {
				$this->recursively_extract_coordinates( $value );
			}
		}

		$this->remove_empty_keys( $value );
		$this->remove_unsupported_data( $value, $excluded_field_names );

		return $this->convert_empty_data_to_null( $value );
	}

	/**
	 * Build a map of field names to their types by recursively traversing field structure.
	 *
	 * @param array $field_structure Field structure array.
	 */
	private function build_field_type_map( array $field_structure ): void {
		foreach ( $field_structure as $field_group ) {
			if ( empty( $field_group['fields'] ) ) {
				continue;
			}

			$this->map_fields_recursive( $field_group['fields'] );
		}
	}

	/**
	 * Recursively map field names to their types.
	 *
	 * @param array $fields Array of fields to map.
	 */
	private function map_fields_recursive( array $fields ): void {
		foreach ( $fields as $field ) {
			$name = ! empty( $field['name'] ) ? (string) $field['name'] : '';
			$type = ! empty( $field['type'] ) ? (string) $field['type'] : '';

			if ( ! empty( $name ) && ! empty( $type ) ) {
				$this->field_type_map[ $name ] = $type;
			}

			// Handle nested fields in flexible content, repeater, and group fields.
			if ( ! empty( $field['layouts'] ) && is_array( $field['layouts'] ) ) {
				// Flexible content layouts.
				foreach ( $field['layouts'] as $layout ) {
					if ( ! empty( $layout['sub_fields'] ) ) {
						$this->map_fields_recursive( $layout['sub_fields'] );
					}
				}

				continue;
			}

			if ( empty( $field['sub_fields'] ) || ! is_array( $field['sub_fields'] ) ) {
				continue;
			}

			// Repeater or group sub-fields.
			$this->map_fields_recursive( $field['sub_fields'] );
		}
	}

	/**
	 * Process flexible content to namespace sub-fields by layout.
	 *
	 * This prevents Elasticsearch mapping conflicts when different layouts
	 * use the same field name with different types.
	 *
	 * @param array $flexible_content_items Array of flexible content layout items.
	 * @param array $excluded_field_names   Field names to exclude from indexing.
	 *
	 * @return array Processed flexible content with namespaced sub-fields.
	 */
	private function process_flexible_content( array $flexible_content_items, array $excluded_field_names ): array {
		$processed = [];

		foreach ( $flexible_content_items as $layout_item ) {
			if ( ! is_array( $layout_item ) || empty( $layout_item['acf_fc_layout'] ) ) {
				// If the item doesn't have a layout, keep it as-is.
				$processed[] = $layout_item;

				continue;
			}

			$layout_name = $layout_item['acf_fc_layout'];

			// Collect all sub-fields (excluding acf_fc_layout).
			$sub_fields = [];

			foreach ( $layout_item as $field_name => $field_value ) {
				if ( 'acf_fc_layout' === $field_name ) {
					continue;
				}

				// Recursively scan for flexible content (direct or nested inside groups/other structures).
				if ( is_array( $field_value ) ) {
					$field_value = $this->recursively_process_flexible_content( $field_value, $excluded_field_names );
				}

				$sub_fields[ $field_name ] = $field_value;
			}

			// Namespace sub-fields under the layout name.
			$processed[] = [ $layout_name => $sub_fields ];
		}

		return $processed;
	}

	/**
	 * Check if an array is a flexible content array.
	 *
	 * A flexible content array contains items with 'acf_fc_layout' keys.
	 * We check all items to handle cases where some items might be malformed,
	 * filtered out, or the array is sparse (e.g., data[0] missing but data[3] exists).
	 *
	 * @param array $data Array to check.
	 *
	 * @return bool True if it's a flexible content array.
	 */
	private function is_flexible_content_array( array $data ): bool {
		// Empty array is not flexible content.
		if ( empty( $data ) ) {
			return false;
		}

		// Check all items - if any item is an array with acf_fc_layout, it's flexible content.
		// This handles sparse arrays where data[0] might not exist due to filtering.
		foreach ( $data as $item ) {
			// Ensure item is an array and not null/scalar.
			if ( ! is_array( $item ) ) {
				continue;
			}

			// If this item has acf_fc_layout, it's flexible content.
			if ( isset( $item['acf_fc_layout'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Recursively scan data for flexible content arrays and process them.
	 *
	 * This method handles flexible content that may be nested inside other field types
	 * like groups or repeaters, which the direct type check in sanitize_value() would miss.
	 *
	 * @param mixed $data              The data to scan.
	 * @param array $excluded_field_names Field names to exclude from processing.
	 *
	 * @return mixed The processed data.
	 */
	private function recursively_process_flexible_content( $data, array $excluded_field_names ) {
		// Only process arrays.
		if ( ! is_array( $data ) ) {
			return $data;
		}

		// Check if THIS array is flexible content.
		if ( $this->is_flexible_content_array( $data ) ) {
			return $this->process_flexible_content( $data, $excluded_field_names );
		}

		// Otherwise, recursively check child arrays.
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$data[ $key ] = $this->recursively_process_flexible_content( $value, $excluded_field_names );
			}
		}

		return $data;
	}

	private function recursively_extract_coordinates( array $data ): void {
		if ( $this->is_google_map_coordinates( $data ) ) {
			$this->coordinates[] = [
				'lat' => (float) $data['lat'],
				'lon' => (float) $data['lng'],
			];

			return;
		}

		foreach ( $data as $value ) {
			if ( is_array( $value ) ) {
				$this->recursively_extract_coordinates( $value );
			}
		}
	}

	private function is_google_map_coordinates( array $data ): bool {
		return ! empty( $data['lat'] ) && ! empty( $data['lng'] );
	}

	private function is_wp_instance( $data ): bool {
		return $data instanceof WP_Post || $data instanceof WP_Term || $data instanceof WP_User;
	}

	private function should_be_indexed( $value, $key, $excluded_field_names ): bool {
		if (
			is_array( $value ) &&
			isset( $value['type'] ) &&
			in_array( $value['type'], self::ACF_UNSUPPORTED_TYPES, true )
		) {
			return false;
		}

		return ! $this->verify_field_excluded( $excluded_field_names, $key );
	}

	private function extract_file_metadata( $file_data ): ?array {
		if ( empty( $file_data ) ) {
			return null;
		}

		if ( is_array( $file_data ) ) {
			$extracted = $this->map_attachment_fields( $file_data );

			return ! empty( $extracted ) ? $extracted : null;
		}

		$attachment_id = null;

		if ( is_numeric( $file_data ) ) {
			$attachment_id = (int) $file_data;
		}

		if ( is_string( $file_data ) && $this->is_valid_http_url( $file_data ) ) {
			$attachment_id = attachment_url_to_postid( $file_data );

			if ( ! $attachment_id ) {
				return [ 'url' => $file_data ];
			}
		}

		if ( null === $attachment_id ) {
			return null;
		}

		$data = acf_get_attachment( $attachment_id );

		if ( ! $data ) {
			return null;
		}

		return $this->map_attachment_fields( $data );
	}

	private function map_attachment_fields( array $attachment_array ): array {
		$useful_fields = [
			'title',
			'caption',
			'description',
			'alt',
			'url',
			'filename',
			'filesize',
			'name',
			'mime_type',
			'type',
			'subtype',
		];

		return array_filter(
			array_intersect_key( $attachment_array, array_flip( $useful_fields ) ),
			static fn ( $value ) => ! empty( $value )
		);
	}

	private function is_valid_http_url( string $url ): bool {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );

		return $scheme && in_array( strtolower( $scheme ), [ 'http', 'https' ], true );
	}
}
