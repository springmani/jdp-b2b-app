<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\System\Debug\Fields;

use WPE\AITK\WP\Site_Health\Debug_Info\Data\Abstract_Debug_Info_Field;

final class WordPress_Version_Field extends Abstract_Debug_Info_Field {
	public function get_key(): string {
		return 'wordpress_version';
	}

	public function get_label(): string {
		return __( 'WordPress version', 'wpe-aitk' );
	}

	public function get_value(): string {
		return get_bloginfo( 'version' );
	}
}
