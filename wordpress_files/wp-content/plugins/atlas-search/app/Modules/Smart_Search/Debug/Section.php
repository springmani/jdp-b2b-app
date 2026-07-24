<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Debug;

use WPE\AITK\Modules\Smart_Search\Debug\Fields\Excluded_Post_Types_Field;
use WPE\AITK\Modules\Smart_Search\Debug\Fields\Facet_Blocks_Enabled_Field;
use WPE\AITK\Modules\Smart_Search\Debug\Fields\Id_Prefix_Field;
use WPE\AITK\WP\Site_Health\Debug_Info\Contracts\Debug_Info_Section_Interface;

final class Section implements Debug_Info_Section_Interface {
	public function get_section_key(): string {
		return 'wpengine-aitk-smart-search';
	}

	public function get_section_label(): string {
		return __( 'Smart Search', 'wpe-aitk' );
	}

	/**
	 * @return array<\WPE\AITK\WP\Site_Health\Debug_Info\Contracts\Debug_Info_Field_Interface>
	 */
	public function get_fields(): array {
		return [
			new Excluded_Post_Types_Field(),
			new Facet_Blocks_Enabled_Field(),
			new Id_Prefix_Field(),
		];
	}
}
