<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Response_Interface;
use WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface;

/**
 * Response DTO for deleting a synonym rule.
 */
final class Synonym_Delete_Response implements Rest_Response_Interface, GraphQL_Response_Interface {
	private bool $success;

	private string $message;

	public function __construct( bool $success, string $message ) {
		$this->success = $success;
		$this->message = $message;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_bool( $data, 'success' ),
			Array_Converter::safe_string( $data, 'message' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function to_array(): array {
		return [
			'message' => $this->message,
			'success' => $this->success,
		];
	}
}
