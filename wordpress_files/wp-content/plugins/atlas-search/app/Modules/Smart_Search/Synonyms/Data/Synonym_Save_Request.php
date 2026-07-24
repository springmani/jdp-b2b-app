<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Synonyms\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\WP\Rest\Contracts\Rest_Request_Interface;

/**
 * Request DTO for creating or updating a synonym rule.
 *
 * When `id` is empty, a new rule is created.
 * When `id` is provided, the existing rule is updated.
 */
final class Synonym_Save_Request implements Rest_Request_Interface {
	private ?string $id;

	private string $synonyms;

	public function __construct( ?string $id, string $synonyms ) {
		$this->id       = $id;
		$this->synonyms = $synonyms;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		$id = Array_Converter::safe_string( $data, 'id' );

		return new self(
			'' !== $id ? $id : null,
			Array_Converter::safe_string( $data, 'synonyms' )
		);
	}

	public function get_id(): ?string {
		return $this->id;
	}

	public function get_synonyms(): string {
		return $this->synonyms;
	}

	public function has_id(): bool {
		return null !== $this->id;
	}
}
