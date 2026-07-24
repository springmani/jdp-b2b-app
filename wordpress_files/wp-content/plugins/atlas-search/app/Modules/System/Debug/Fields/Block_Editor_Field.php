<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\System\Debug\Fields;

use WPE\AITK\WP\Site_Health\Debug_Info\Data\Abstract_Debug_Info_Field;

final class Block_Editor_Field extends Abstract_Debug_Info_Field {
	public function get_key(): string {
		return 'block_editor';
	}

	public function get_label(): string {
		return __( 'Block editor available', 'wpe-aitk' );
	}

	public function get_value(): string {
		return class_exists( 'WP_Block_Type_Registry' )
			? __( 'Yes', 'wpe-aitk' )
			: __( 'No', 'wpe-aitk' );
	}
}
