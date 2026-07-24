<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Test\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\Core\DTO\Exceptions\Invalid_Array_Data_Exception;
use WPE\AITK\WP\Rest\Contracts\Rest_Request_Interface;

final class Test_Request implements Rest_Request_Interface {
	/**
	 * @var non-empty-string
	 */
	private string $identifier;

	public function __construct( string $identifier ) {
		if ( '' === $identifier ) {
			throw Invalid_Array_Data_Exception::missing_required_field( self::class, 'identifier' );
		}

		$this->identifier = $identifier;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_string( $data, 'identifier' )
		);
	}

	public function get_identifier(): string {
		return $this->identifier;
	}
}
