<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Response_Interface;
use WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface;

/**
 * Response DTO for listing all synonym rules.
 */
final class Synonyms_List_Response implements Rest_Response_Interface, GraphQL_Response_Interface {
	private int $total;

	// TODO: Replace with array<Record> when schema supports nested objects.
	/**
	 * @var array<int, array{id: string, synonyms: string}>
	 */
	private array $rules;

	/**
	 * @param int                                              $total Total number of rules.
	 * @param array<int, array{id: string, synonyms: string}>  $rules Array of synonym rules.
	 */
	public function __construct( int $total, array $rules ) {
		$this->total = $total;
		$this->rules = $rules;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_int( $data, 'total' ),
			is_array( $data['rules'] ?? null ) ? $data['rules'] : []
		);
	}

	/**
	 * @inheritDoc
	 */
	public function to_array(): array {
		return [
			'rules' => $this->rules,
			'total' => $this->total,
		];
	}
}
