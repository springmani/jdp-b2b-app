<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\System\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\WP\Settings\Contracts\Setting_Interface;

/**
 * Typed representation of the core plugin connection settings.
 */
final class Content_Engine_Connection implements Setting_Interface {
	private const SYNC = '/sync';

	private const SITES = '/sites';

	private string $sync_url;

	private string $sites_url;

	private string $access_token;

	public function __construct( string $sync_url = '', string $access_token = '', string $sites_url = '' ) {
		$this->sync_url     = $sync_url;
		$this->sites_url    = ! empty( $sites_url ) ? $sites_url : $this->build_sites_url( $sync_url );
		$this->access_token = $access_token;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_string( $data, 'url' ),
			Array_Converter::safe_string( $data, 'access_token' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function to_array(): array {
		return [
			'access_token' => $this->access_token,
			'url'          => $this->sync_url,
		];
	}

	public function get_sync_url(): string {
		return $this->sync_url;
	}

	public function get_sites_url(): string {
		return $this->sites_url;
	}

	public function get_access_token(): string {
		return $this->access_token;
	}

	private function build_sites_url( string $url ): string {
		if ( str_ends_with( $url, self::SYNC ) ) {
			return substr( $url, 0, -5 ) . self::SITES;
		}

		return str_replace( self::SYNC, self::SITES, $url );
	}
}
