<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Data;

use InvalidArgumentException;
use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\Core\DTO\Contracts\Array_Constructible_Interface;
use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;
use WPE\AITK\Modules\Smart_Search\Search_Item\Data\Search_Item_Data;

/**
 * Represents a search phrase with its associated custom search results.
 *
 * This value object links a specific search phrase to an ordered list of search items
 * that should be returned when users search for that phrase. The array order represents
 * the display order in search results.
 */
final class Search_Phrase_Data implements Array_Convertible_Interface, Array_Constructible_Interface {
	/**
	 * The search phrase that triggers these custom results.
	 */
	private string $phrase;

	/**
	 * Ordered list of search items.
	 * Array order represents display order.
	 *
	 * @var array<int, \WPE\AITK\Modules\Smart_Search\Search_Item\Data\Search_Item_Data>
	 */
	private array $search_items;

	/**
	 * @param string                                                $phrase       The search phrase.
	 * @param array<int, \WPE\AITK\Modules\Smart_Search\Search_Item\Data\Search_Item_Data|array<string, int|string>> $search_items Ordered list of search items (objects or arrays).
	 */
	public function __construct( string $phrase, array $search_items = [] ) {
		$this->phrase = $phrase;

		// Convert all items to Search_Item_Data objects.
		$this->search_items = array_values(
			array_map(
				static function ( $item ): Search_Item_Data {
					if ( $item instanceof Search_Item_Data ) {
						return $item;
					}

					if ( is_array( $item ) ) {
						return Search_Item_Data::from_array( $item );
					}

					throw new InvalidArgumentException(
						'Search items must be instances of Search_Item_Data or arrays'
					);
				},
				$search_items
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		$search_items = [];

		if ( isset( $data['search_items'] ) && is_array( $data['search_items'] ) ) {
			$search_items = $data['search_items'];
		}

		return new self(
			Array_Converter::safe_string( $data, 'phrase' ),
			$search_items
		);
	}

	/**
	 * Create a Search_Phrase_Data instance with type-safe Search_Item_Data objects.
	 *
	 * @param string            $phrase       The search phrase.
	 * @param \WPE\AITK\Modules\Smart_Search\Search_Item\Data\Search_Item_Data ...$search_items Search items (variadic).
	 */
	public static function create( string $phrase, Search_Item_Data ...$search_items ): self {
		return new self( $phrase, $search_items );
	}

	/**
	 * Get the search phrase.
	 */
	public function get_phrase(): string {
		return $this->phrase;
	}

	/**
	 * Get all search items as Search_Item objects in display order.
	 *
	 * @return array<int, \WPE\AITK\Modules\Smart_Search\Search_Item\Data\Search_Item_Data>
	 */
	public function get_search_items(): array {
		return $this->search_items;
	}

	/**
	 * Get only the post IDs in display order.
	 *
	 * Useful for filtering search results without needing the full Search_Item objects.
	 *
	 * @return array<int, int>
	 */
	public function get_search_item_ids(): array {
		return array_map(
			static fn ( Search_Item_Data $item ): int => $item->get_id(),
			$this->search_items
		);
	}

	/**
	 * Get search items as WP_Post objects in display order.
	 *
	 * Fetches all WordPress post objects in a single query for efficiency.
	 * Only published posts are returned. Posts that no longer exist, are not published,
	 * or are not found will be filtered out.
	 * The order of the returned posts matches the search item order exactly.
	 *
	 * @return array<int, \WP_Post>
	 */
	public function get_search_item_posts(): array {
		$post_ids = array_filter( $this->get_search_item_ids(), static fn ( int $id ): bool => $id > 0 );

		if ( empty( $post_ids ) ) {
			return [];
		}

		$posts = get_posts(
			[
				'posts_per_page' => 100,
				'post_status'    => 'publish',
				'post__in'       => $post_ids,
			]
		);

		// Create a lookup map indexed by post ID.
		$posts_by_id = array_column( $posts, null, 'ID' );

		// Map post IDs to their posts in the correct order, filtering out missing ones.
		return array_values(
			array_filter(
				array_map(
					static fn ( int $post_id ) => $posts_by_id[ $post_id ] ?? null,
					$post_ids
				)
			)
		);
	}

	/**
	 * Convert the search phrase data to an array.
	 *
	 * @return array<string, scalar|array>
	 */
	public function to_array(): array {
		return [
			'phrase'       => $this->phrase,
			'search_items' => array_map(
				static fn ( Search_Item_Data $item ): array => $item->to_array(),
				$this->search_items
			),
		];
	}
}
