<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Tests;

use WPE\AITK\Modules\System\Data\Content_Engine_Connection;
use WPE\AITK\WP\Site_Health\Test\Contracts\Test_Interface;
use WPE\AITK\WP\Site_Health\Test\Data\Test_Result;
use Wpe_Content_Engine\Helper\Sync\GraphQL\Client;
use Wpe_Content_Engine\WPSettings;

final class Index_Test implements Test_Interface {
	private const GRAPHQL_PING = '{ __typename }';

	private Client $client;

	public function __construct( Client $client ) {
		$this->client = $client;
	}

	public function get_identifier(): string {
		return 'wpe_aitk_index';
	}

	public function get_label(): string {
		return __( 'WP Engine AITK index connectivity', 'wpe-aitk' );
	}

	public function is_async(): bool {
		return true;
	}

	public function run(): Test_Result {
		$connection   = Content_Engine_Connection::from_array(
			get_option( WPSettings::WPE_CONTENT_ENGINE_OPTION_NAME, [] )
		);
		$url          = $connection->get_sync_url();
		$access_token = $connection->get_access_token();

		if ( empty( $url ) || empty( $access_token ) ) {
			return $this->result_not_configured();
		}

		$this->client->query( $url, self::GRAPHQL_PING, [], $access_token );

		return $this->result_reachable();
	}

	private function result_not_configured(): Test_Result {
		return Test_Result::critical(
			__( 'WP Engine AITK index is not configured.', 'wpe-aitk' ),
			sprintf(
				'<p>%s</p>',
				esc_html__( 'The plugin URL or access token is not set. Visit the plugin settings.', 'wpe-aitk' )
			),
			$this->get_identifier()
		);
	}

	private function result_reachable(): Test_Result {
		return Test_Result::good(
			__( 'WP Engine AITK index is reachable.', 'wpe-aitk' ),
			sprintf(
				'<p>%s</p>',
				esc_html__(
					'The plugin can connect to the search index using the configured URL and access token.',
					'wpe-aitk'
				)
			),
			$this->get_identifier()
		);
	}
}
