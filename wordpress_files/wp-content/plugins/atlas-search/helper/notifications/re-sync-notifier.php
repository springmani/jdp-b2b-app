<?php

namespace Wpe_Content_Engine\Helper\Notifications;

use Wpe_Content_Engine\Helper\Admin_Notice;
use Wpe_Content_Engine\Helper\Constants\Capability;

const  WPE_SMART_SEARCH_INDEX_READY = 'wpe_content_engine_re_sync_has_occurred';
const MULTISITE_PAGE                = 'wpengine-smart-search&view=sync-data';

const CAPABILITY_PAGE_MAP = [
	\AI_Toolkit_Settings_Page::SMART_SEARCH_PAGE    => [ Capability::SEARCH, Capability::HYBRID_SEARCH ],
	\AI_Toolkit_Settings_Page::RECOMMENDATIONS_PAGE => [ Capability::RECOMMENDATIONS ],
	\AI_Toolkit_Settings_Page::VECTOR_DB_PAGE       => [ Capability::VECTOR_DB ],
];

if ( ! function_exists( 'handle_re_sync_notification' ) ) {
	/**
	 * Show notification when option WPE_CONTENT_ENGINE_ASK_TO_RUN_SYNC false.
	 *
	 * @param \Wpe_Content_Engine\Helper\Admin_Notice $notification Notification.
	 *
	 * @return void
	 */
	function handle_re_sync_notification( Admin_Notice $notification ): void {
		$current_page = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );

		if (
			str_contains( $current_page, 'favicon.ico' )
			|| str_contains( $current_page, 'admin-ajax.php' )
			|| str_contains( $current_page, 'wp-json' )
		) {
			return;
		}

		if (
			\AtlasSearch\Support\WordPress\get_option( WPE_SMART_SEARCH_INDEX_READY )
		) {
			return;
		}

		if ( is_multisite() && str_ends_with( $current_page, 'page=wpengine-smart-search' ) ) {
			return;
		}

		$page = get_index_page_url();

		if ( empty( $page ) ) {
			return;
		}

		$notification->add_message(
			'<b>WP Engine Smart Search</b> requires a one-time data sync in the '
			. '<a href="admin.php?page=' . $page . '">Index Data</a> page',
			Admin_Notice::NOTICE_TYPE_WARNING
		);
	}
}

if ( ! function_exists( 'get_index_page_url' ) ) {
	/**
	 * Get the index page URL based on the current capabilities and whether it's a multisite.
	 *
	 * @return string|null The URL of the index page or null if no capabilities match.
	 */
	function get_index_page_url(): ?string {
		if ( is_multisite() ) {
			return MULTISITE_PAGE;
		}

		$capabilities = \Wpe_Content_Engine\Helper\CapabilitiesService::get_capabilities();

		if ( $capabilities instanceof \WP_Error || empty( $capabilities ) ) {
			return null;
		}

		foreach ( CAPABILITY_PAGE_MAP as $page => $required_capabilities ) {
			if ( ! empty( array_intersect( $required_capabilities, $capabilities ) ) ) {
				return $page;
			}
		}

		return null;
	}
}
