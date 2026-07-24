<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Notice;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\WP\Notice\Contracts\Notice_With_SVG_Interface;

final class WP_Engine_Notice extends Base_Notice implements Notice_With_SVG_Interface {
	/**
	 * SVG icon for the notice.
	 *
	 * @var string
	 */
	private string $svg;

	/**
	 * @inheritDoc
	 */
	public function __construct(
		string $message,
		string $type,
		string $svg,
		bool $dismissible = false,
		string $id = '',
		array $additional_classes = [],
		array $attributes = []
	) {
		parent::__construct( $message, $type, $dismissible, $id, $additional_classes, $attributes );

		$this->svg = $svg;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_string( $data, 'message' ),
			Array_Converter::safe_string( $data, 'type' ),
			Array_Converter::safe_string( $data, 'svg' ),
			Array_Converter::safe_bool( $data, 'dismissible' ),
			Array_Converter::safe_string( $data, 'id' ),
			Array_Converter::safe_string_array( $data, 'additional_classes' ),
			Array_Converter::safe_scalar_array( $data, 'attributes' )
		);
	}

	public function get_svg(): string {
		return $this->svg;
	}

	/**
	 * @inheritDoc
	 */
	public function to_array(): array {
		return array_merge( parent::to_array(), [ 'svg' => $this->svg ] );
	}
}
