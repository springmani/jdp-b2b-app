<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Multisite;

use WPE\AITK\WP\Multisite\Contracts\Multisite_Environment_Interface;
use WPE\AITK\WP\Multisite\Contracts\Site_Repository_Interface;

final class WP_Site_Repository implements Site_Repository_Interface {
	private Multisite_Environment_Interface $environment;

	/**
	 * Cached site IDs to avoid repeated queries.
	 *
	 * @var array<int>|null
	 */
	private ?array $cached_site_ids = null;

	public function __construct( Multisite_Environment_Interface $environment ) {
		$this->environment = $environment;
	}

	/**
	 * @inheritDoc
	 */
	public function get_all_site_ids(): array {
		if ( ! $this->environment->is_network_activated() ) {
			return [ $this->environment->get_current_blog_id() ];
		}

		if ( null === $this->cached_site_ids ) {
			$this->cached_site_ids = array_map(
				'intval',
				get_sites( [
					'fields' => 'ids',
					'number' => 0,
				] )
			);
		}

		return $this->cached_site_ids;
	}

	public function get_site_name( int $site_id ): string {
		if ( ! $this->environment->is_multisite() ) {
			return get_bloginfo( 'name' );
		}

		$site = get_site( $site_id );

		if ( null === $site ) {
			return '';
		}

		return $site->blogname;
	}
}
