<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Response_Interface;
use WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface;

/**
 * Response DTO for creating or updating a synonym rule.
 */
final class Synonym_Save_Response implements Rest_Response_Interface, GraphQL_Response_Interface {
	private bool $success;

	private string $message;

	// TODO: Replace with Record object when schema supports nested objects.
	private string $id;

	private string $synonyms;

	public function __construct( bool $success, string $message, string $id, string $synonyms ) {
		$this->success  = $success;
		$this->message  = $message;
		$this->id       = $id;
		$this->synonyms = $synonyms;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		$rule = is_array( $data['rule'] ?? null ) ? $data['rule'] : [];

		return new self(
			Array_Converter::safe_bool( $data, 'success' ),
			Array_Converter::safe_string( $data, 'message' ),
			Array_Converter::safe_string( $rule, 'id' ),
			Array_Converter::safe_string( $rule, 'synonyms' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function to_array(): array {
		return [
			'id'       => $this->id,
			'message'  => $this->message,
			'success'  => $this->success,
			'synonyms' => $this->synonyms,
		];
	}
}
