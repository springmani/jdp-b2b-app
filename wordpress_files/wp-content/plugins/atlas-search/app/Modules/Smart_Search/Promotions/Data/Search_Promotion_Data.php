<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Promotions\Data;

use InvalidArgumentException;
use WPE\AITK\Modules\Smart_Search\Search_Item\Data\Search_Item_Data;
use WPE\AITK\WP\Schema\Contracts\Schema_Provider_Interface;
use WPE\AITK\WP\Settings\Contracts\Setting_Interface;

/**
 * Search Promotion Settings Value Object.
 *
 * Maintains an ordered list of promoted posts that should appear at the top of search results.
 * The array order represents the display order - first item in the array appears first in results.
 *
 * Each promotion contains the post ID and post type for efficient lookups
 * without requiring additional database queries for each item.
 */
final class Search_Promotion_Data implements Setting_Interface, Schema_Provider_Interface {
	/**
	 * Ordered list of promoted posts.
	 * Array order represents display order.
	 *
	 * @var array<int, \WPE\AITK\Modules\Smart_Search\Search_Item\Data\Search_Item_Data>
	 */
	private array $promotions;

	/**
	 * @param array<int, \WPE\AITK\Modules\Smart_Search\Search_Item\Data\Search_Item_Data|array<string, int|string>> $promotions Ordered list of promotions (objects or arrays).
	 */
	public function __construct( array $promotions = [] ) {
		// Convert all items to Search_Item_Data objects and re-index.
		$this->promotions = array_values(
			array_map(
				static function ( $item ): Search_Item_Data {
					if ( $item instanceof Search_Item_Data ) {
						return $item;
					}

					if ( is_array( $item ) ) {
						return Search_Item_Data::from_array( $item );
					}

					throw new InvalidArgumentException(
						'Promotions must be instances of Search_Item_Data or arrays'
					);
				},
				$promotions
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		if ( ! isset( $data['promotions'] ) || ! is_array( $data['promotions'] ) ) {
			return new self( [] );
		}

		return new self( $data['promotions'] );
	}

	/**
	 * Create a Search_Promotion_Data instance with type-safe Search_Item_Data objects.
	 *
	 * @param \WPE\AITK\Modules\Smart_Search\Search_Item\Data\Search_Item_Data ...$promotions Promoted items (variadic).
	 */
	public static function create( Search_Item_Data ...$promotions ): self {
		return new self( $promotions );
	}

	/**
	 * Convert the settings to an array.
	 *
	 * @return array<string, array<int, array<string, int|string>>>
	 */
	public function to_array(): array {
		return [
			'promotions' => array_map(
				static fn ( Search_Item_Data $item ): array => $item->to_array(),
				$this->promotions
			),
		];
	}

	/**
	 * Get all promoted posts as Search_Item objects in display order.
	 *
	 * @return array<int, \WPE\AITK\Modules\Smart_Search\Search_Item\Data\Search_Item_Data>
	 */
	public function get_promotions(): array {
		return $this->promotions;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_property_schemas(): array {
		return [
			'promotions' => [
				'default' => [],
				'items'   => [
					'properties' => [
						'id'        => [
							'minimum' => 0,
							'type'    => 'integer',
						],
						'post_type' => [
							'type' => 'string',
						],
					],
					'required'   => [ 'id', 'post_type' ],
					'type'       => 'object',
				],
				'type'    => 'array',
			],
		];
	}
}
