<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\Core\DTO\Exceptions\Invalid_Array_Data_Exception;
use WPE\AITK\WP\Rest\Contracts\Rest_Request_Interface;

/**
 * Request DTO for deleting a synonym rule.
 */
final class Synonym_Delete_Request implements Rest_Request_Interface {
	/**
	 * @var non-empty-string
	 */
	private string $id;

	public function __construct( string $id ) {
		if ( '' === $id ) {
			throw Invalid_Array_Data_Exception::missing_required_field( self::class, 'id' );
		}

		$this->id = $id;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_string( $data, 'id' )
		);
	}

	public function get_id(): string {
		return $this->id;
	}
}
