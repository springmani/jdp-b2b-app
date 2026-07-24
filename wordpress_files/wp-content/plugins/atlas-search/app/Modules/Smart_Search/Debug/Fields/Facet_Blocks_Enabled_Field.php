<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Debug\Fields;

use WPE\AITK\WP\Site_Health\Debug_Info\Data\Abstract_Debug_Info_Field;

use const AtlasSearch\Hooks\SMART_SEARCH_SEARCH_FACET_BLOCKS_ENABLED;

final class Facet_Blocks_Enabled_Field extends Abstract_Debug_Info_Field {
	public function get_key(): string {
		return 'facet_blocks_enabled';
	}

	public function get_label(): string {
		return __( 'Facet blocks enabled', 'wpe-aitk' );
	}

	public function get_value(): string {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- constant is defined in AtlasSearch\Hooks with the correct plugin prefix.
		$enabled = (bool) apply_filters( SMART_SEARCH_SEARCH_FACET_BLOCKS_ENABLED, true );

		return $enabled ? __( 'Yes', 'wpe-aitk' ) : __( 'No', 'wpe-aitk' );
	}
}
