<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Search_Item\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\Core\DTO\Contracts\Array_Constructible_Interface;
use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;

/**
 * Represents a single search item with its ID and post type.
 *
 * This value object is used within the search results list to maintain
 * the post reference (ID) and post type without requiring additional
 * database queries during admin operations.
 */
final class Search_Item_Data implements Array_Convertible_Interface, Array_Constructible_Interface {
	/**
	 * Representation of the ID property of the class.
	 */
	public const KEY_ID = 'id';

	/**
	 * The post ID.
	 */
	private int $id;

	/**
	 * The post type.
	 */
	private string $post_type;

	public function __construct( int $id, string $post_type ) {
		$this->id        = $id;
		$this->post_type = $post_type;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_int( $data, 'id' ),
			Array_Converter::safe_string( $data, 'post_type' )
		);
	}

	/**
	 * Create a Search_Item_Data instance with explicit type-safe values.
	 *
	 * @param int    $id        The post ID.
	 * @param string $post_type The post type.
	 */
	public static function create( int $id, string $post_type ): self {
		return new self( $id, $post_type );
	}

	/**
	 * Get the post ID.
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get the post type.
	 */
	public function get_post_type(): string {
		return $this->post_type;
	}

	/**
	 * Convert the search item to an array.
	 *
	 * @return array<string, int|string>
	 */
	public function to_array(): array {
		return [
			'id'        => $this->id,
			'post_type' => $this->post_type,
		];
	}
}
