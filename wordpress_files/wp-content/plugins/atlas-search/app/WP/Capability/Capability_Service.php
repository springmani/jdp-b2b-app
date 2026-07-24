<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Capability;

use WPE\AITK\WP\Capability\Contracts\Capability_Service_Interface;
use WPE\AITK\WP\Capability\Data\Capabilities_Response;
use WPE\AITK\WP\Capability\Operations\Get_Capabilities_Query;
use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Client_Interface;
use WPE\AITK\WP\GraphQL\Exceptions\GraphQL_Exception;

final class Capability_Service implements Capability_Service_Interface {
	private const TRANSIENT_KEY = 'aitk_capabilities';

	private GraphQL_Client_Interface $client;

	public function __construct( GraphQL_Client_Interface $client ) {
		$this->client = $client;
	}

	public function has_capability( string $capability ): bool {
		return in_array( $capability, $this->get_capabilities(), true );
	}

	/**
	 * @return array<int, string>
	 */
	public function get_capabilities(): array {
		$cached = get_transient( self::TRANSIENT_KEY );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		try {
			$query    = new Get_Capabilities_Query();
			$response = $query->execute( $this->client, Capabilities_Response::class );
			assert( $response instanceof Capabilities_Response );

			$capabilities = $response->get_capabilities();

			set_transient( self::TRANSIENT_KEY, $capabilities, MINUTE_IN_SECONDS );

			return $capabilities;
		} catch ( GraphQL_Exception $e ) {
			return [];
		}
	}
}
