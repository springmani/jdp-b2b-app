<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Notice;

use WPE\AITK\Core\DTO\Array_Converter;

final class Default_Notice extends Base_Notice {
	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_string( $data, 'message' ),
			Array_Converter::safe_string( $data, 'type' ),
			Array_Converter::safe_bool( $data, 'dismissible' ),
			Array_Converter::safe_string( $data, 'id' ),
			Array_Converter::safe_string_array( $data, 'additional_classes' ),
			Array_Converter::safe_scalar_array( $data, 'attributes' )
		);
	}
}
