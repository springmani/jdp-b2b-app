<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Data;

use InvalidArgumentException;
use WPE\AITK\WP\Schema\Contracts\Schema_Provider_Interface;
use WPE\AITK\WP\Settings\Contracts\Setting_Interface;

/**
 * Custom Search Results Settings Value Object.
 *
 * Maintains a collection of search phrases with their associated custom search results.
 * Each search phrase maps to an ordered list of search items that should appear
 * when users search for that specific phrase.
 */
final class Custom_Search_Results_Data implements Setting_Interface, Schema_Provider_Interface {
	/**
	 * Collection of search phrase configurations.
	 *
	 * @var array<int, \WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Data\Search_Phrase_Data>
	 */
	private array $search_phrases;

	/**
	 * @param array<int, \WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Data\Search_Phrase_Data|array<string, scalar|array>> $search_phrases Collection of search phrases (objects or arrays).
	 */
	public function __construct( array $search_phrases = [] ) {
		// Convert all items to Search_Phrase_Data objects and re-index.
		$this->search_phrases = array_values(
			array_map(
				static function ( $item ): Search_Phrase_Data {
					if ( $item instanceof Search_Phrase_Data ) {
						return $item;
					}

					if ( is_array( $item ) ) {
						return Search_Phrase_Data::from_array( $item );
					}

					throw new InvalidArgumentException(
						'Search phrases must be instances of Search_Phrase_Data or arrays'
					);
				},
				$search_phrases
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		if ( ! isset( $data['search_phrases'] ) || ! is_array( $data['search_phrases'] ) ) {
			return new self( [] );
		}

		return new self( $data['search_phrases'] );
	}

	/**
	 * Create a Custom_Search_Results_Data instance with type-safe Search_Phrase_Data objects.
	 *
	 * @param \WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Data\Search_Phrase_Data ...$search_phrases Search phrases (variadic).
	 */
	public static function create( Search_Phrase_Data ...$search_phrases ): self {
		return new self( $search_phrases );
	}

	/**
	 * Get all search phrase configurations as Search_Phrase_Data objects.
	 *
	 * @return array<int, \WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Data\Search_Phrase_Data>
	 */
	public function get_search_phrases(): array {
		return $this->search_phrases;
	}

	/**
	 * Find search items for a specific search phrase.
	 *
	 * Performs case-insensitive exact match on the phrase.
	 *
	 * @param string $phrase The search phrase to look up.
	 *
	 * @return \WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Data\Search_Phrase_Data|null Returns null if phrase not found.
	 */
	public function find_by_phrase( string $phrase ): ?Search_Phrase_Data {
		$normalized_phrase = strtolower( trim( $phrase ) );

		foreach ( $this->search_phrases as $search_phrase_data ) {
			$stored_phrase = strtolower( trim( $search_phrase_data->get_phrase() ) );

			if ( $stored_phrase === $normalized_phrase ) {
				return $search_phrase_data;
			}
		}

		return null;
	}

	/**
	 * Convert the settings to an array.
	 *
	 * @return array<string, array<int, array<string, scalar|array>>>
	 */
	public function to_array(): array {
		return [
			'search_phrases' => array_map(
				static fn ( Search_Phrase_Data $phrase ): array => $phrase->to_array(),
				$this->search_phrases
			),
		];
	}

	/**
	 * @inheritDoc
	 */
	// phpcs:ignore SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
	public static function get_property_schemas(): array {
		return [
			'search_phrases' => [
				'default' => [],
				'items'   => [
					'properties' => [
						'phrase'       => [
							'type' => 'string',
						],
						'search_items' => [
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
					],
					'required'   => [ 'phrase', 'search_items' ],
					'type'       => 'object',
				],
				'type'    => 'array',
			],
		];
	}
}
