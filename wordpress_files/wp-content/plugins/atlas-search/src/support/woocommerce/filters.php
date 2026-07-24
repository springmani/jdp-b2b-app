<?php

namespace AtlasSearch\Support\WooCommerce;

use const AtlasSearch\Hooks\SMART_SEARCH_EXTRA_SEARCH_CONFIG_FIELDS;
use const AtlasSearch\Hooks\SMART_SEARCH_HOOK_EXTRA_FIELDS;

const SMART_SEARCH_WOOCOMMERCE_SUPPORT_ENABLED_OPTION = 'smart_search_woocommerce_support_enabled';


add_action(
	'woocommerce_loaded',
	static function () {
		if ( ! get_option( SMART_SEARCH_WOOCOMMERCE_SUPPORT_ENABLED_OPTION ) ) {
			do_action( 'qm/notice', 'WP Engine Smart Search: WooCommerce support is DISABLED' );

			return;
		}

		add_filter( SMART_SEARCH_HOOK_EXTRA_FIELDS, __NAMESPACE__ . '\add_extra_fields_to_product', 10, 2 );
		add_filter( SMART_SEARCH_EXTRA_SEARCH_CONFIG_FIELDS, __NAMESPACE__ . '\add_extra_search_config_fields', 10, 2 );
		add_filter( 'pre_get_posts', __NAMESPACE__ . '\price_filter', 10, 1 );
		add_filter( 'wpe_smartsearch/get_order_by', __NAMESPACE__ . '\get_order_by', 100, 3 );
		add_action( 'woocommerce_product_query', __NAMESPACE__ . '\enable_search', 10, 1 );
		// catalog_visibility is now applied as a search filter in generate_filters(), not as a meta_query hook.

		do_action( 'qm/notice', 'WP Engine Smart Search: WooCommerce support is ENABLED' );
	}
);

add_action(
	'activated_plugin',
	static function ( string $plugin ) {
		if ( 'woocommerce/woocommerce.php' !== $plugin ) {
			return;
		}

		update_option( SMART_SEARCH_WOOCOMMERCE_SUPPORT_ENABLED_OPTION, 'yes' );
	}
);

add_action(
	'plugins_loaded',
	static function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( false !== get_option( SMART_SEARCH_WOOCOMMERCE_SUPPORT_ENABLED_OPTION ) ) {
			return;
		}

		update_option( SMART_SEARCH_WOOCOMMERCE_SUPPORT_ENABLED_OPTION, 'yes' );
	},
	20
);

/**
 * Checks if WooCommerce is enabled.
 *
 * @return bool True if WooCommerce is enabled, false otherwise.
 */
function is_woo_enabled(): bool {
	return class_exists( 'WooCommerce' );
}
