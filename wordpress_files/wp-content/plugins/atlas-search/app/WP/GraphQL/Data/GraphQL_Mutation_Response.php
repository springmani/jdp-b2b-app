<?php

declare(strict_types=1);

namespace WPE\AITK\WP\GraphQL\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Response_Interface;

abstract class GraphQL_Mutation_Response implements GraphQL_Response_Interface {
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
		return new static(
			Array_Converter::safe_bool( $data, 'success' ),
			Array_Converter::safe_string( $data, 'message' )
		);
	}

	public function is_success(): bool {
		return $this->success;
	}

	public function get_message(): string {
		return $this->message;
	}
}
