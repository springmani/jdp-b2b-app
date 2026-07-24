<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Search_Item;

use WPE\AITK\Modules\Smart_Search\Search_Item\Data\Search_Item_Data;

/**
 * Recursively removes all occurrences of a specific post ID
 * from a multidimensional raw data array.
 *
 * Targets array elements where the Search_Item_Data ID key
 * matches the given post ID, and re-indexes sequential arrays after removal.
 */
final class Search_Item_Remover {
	/**
	 * @param array<array-key, scalar|array> $data    The raw data array.
	 * @param int                            $post_id The post ID to remove.
	 *
	 * @return array<array-key, scalar|array> Filtered data with sequential arrays re-indexed.
	 */
	public function remove_from_data( array $data, int $post_id ): array {
		$is_list = array_values( $data ) === $data;
		$result  = $this->filter_elements( $data, $post_id );

		if ( $is_list && $result !== $data ) {
			return array_values( $result );
		}

		return $result;
	}

	/**
	 * Filter array elements, removing matching items and recursing into nested arrays.
	 *
	 * @param array<array-key, scalar|array> $data    The raw data array.
	 * @param int                            $post_id The post ID to remove.
	 *
	 * @return array<array-key, scalar|array> Filtered data.
	 */
	private function filter_elements( array $data, int $post_id ): array {
		foreach ( $data as $key => $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}

			if ( $this->is_matching_item( $value, $post_id ) ) {
				unset( $data[ $key ] );

				continue;
			}

			$data[ $key ] = $this->remove_from_data( $value, $post_id );
		}

		return $data;
	}

	/**
	 * Check if an array element is a search item matching the given post ID.
	 *
	 * @param array<array-key, scalar|array> $value   The array element to check.
	 * @param int                            $post_id The post ID to match.
	 */
	private function is_matching_item( array $value, int $post_id ): bool {
		return ( $value[ Search_Item_Data::KEY_ID ] ?? null ) === $post_id;
	}
}
