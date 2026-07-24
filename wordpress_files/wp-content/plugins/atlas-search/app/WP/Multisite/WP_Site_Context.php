<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Multisite;

use WPE\AITK\WP\Multisite\Contracts\Multisite_Environment_Interface;
use WPE\AITK\WP\Multisite\Contracts\Site_Context_Interface;
use WPE\AITK\WP\Multisite\Exceptions\Site_Context_Exception;

final class WP_Site_Context implements Site_Context_Interface {
	private Multisite_Environment_Interface $environment;

	public function __construct( Multisite_Environment_Interface $environment ) {
		$this->environment = $environment;
	}

	public function switch_to_site( int $site_id ): void {
		if ( ! $this->environment->is_network_activated() ) {
			return;
		}

		// phpcs:ignore Generic.PHP.ForbiddenFunctions.FoundWithAlternative
		if ( ! switch_to_blog( $site_id ) ) {
			throw new Site_Context_Exception(
				sprintf( 'Failed to switch to site context for blog ID %d.', $site_id )
			);
		}
	}

	public function restore(): void {
		if ( ! $this->environment->is_network_activated() ) {
			return;
		}

		// phpcs:ignore Generic.PHP.ForbiddenFunctions.FoundWithAlternative
		if ( ! restore_current_blog() ) {
			throw new Site_Context_Exception( 'Failed to restore the previous site context.' );
		}
	}
}
