<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\System\Debug\Fields;

use WPE\AITK\WP\Site_Health\Debug_Info\Data\Abstract_Debug_Info_Field;

final class Rest_Api_Field extends Abstract_Debug_Info_Field {
	public function get_key(): string {
		return 'rest_api';
	}

	public function get_label(): string {
		return __( 'REST API available', 'wpe-aitk' );
	}

	public function get_value(): string {
		return function_exists( 'rest_url' ) && ! empty( rest_url() )
			? __( 'Yes', 'wpe-aitk' )
			: __( 'No', 'wpe-aitk' );
	}
}
