<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Debug\Fields;

use WPE\AITK\WP\Site_Health\Debug_Info\Data\Abstract_Debug_Info_Field;

use const AtlasSearch\Hooks\SMART_SEARCH_EXCLUDED_POST_TYPES;

final class Excluded_Post_Types_Field extends Abstract_Debug_Info_Field {
	public function get_key(): string {
		return 'excluded_post_types';
	}

	public function get_label(): string {
		return __( 'Excluded post types', 'wpe-aitk' );
	}

	public function get_value(): string {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- constant is defined in AtlasSearch\Hooks with the correct plugin prefix.
		$excluded = apply_filters( SMART_SEARCH_EXCLUDED_POST_TYPES, [] );

		return is_array( $excluded ) && ! empty( $excluded )
			? implode( ', ', $excluded )
			: __( 'None', 'wpe-aitk' );
	}
}
