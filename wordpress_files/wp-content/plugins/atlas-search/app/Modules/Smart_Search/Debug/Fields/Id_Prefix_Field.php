<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Debug\Fields;

use WPE\AITK\WP\Site_Health\Debug_Info\Data\Abstract_Debug_Info_Field;

use const AtlasSearch\Hooks\SMART_SEARCH_HOOK_ID_PREFIX;

final class Id_Prefix_Field extends Abstract_Debug_Info_Field {
	public function get_key(): string {
		return 'id_prefix';
	}

	public function get_label(): string {
		return __( 'Document ID prefix', 'wpe-aitk' );
	}

	public function get_value(): string {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- constant is defined in AtlasSearch\Hooks with the correct plugin prefix.
		$prefix = apply_filters( SMART_SEARCH_HOOK_ID_PREFIX, false );

		return $prefix ? (string) $prefix : __( 'Not set', 'wpe-aitk' );
	}
}
