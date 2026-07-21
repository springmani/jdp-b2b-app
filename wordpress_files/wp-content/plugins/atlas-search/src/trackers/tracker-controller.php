<?php

namespace AtlasSearch\Trackers;

use Wpe_Content_Engine\Helper\Logging\Debug_Logger;

use function Wpe_Content_Engine\Helper\verify_nonce_without_uid;

/**
 * Class WPE_Atlas_Search_Tracker_Controller
 *
 * This class handles the proxying of tracking requests for the WP Engine Smart Search plugin.
 * It registers the necessary REST API routes to handle tracking events and forwards the data
 * to the appropriate GraphQL endpoints.
 *
 * @package AtlasSearch\Trackers
 */
class WPE_Atlas_Search_Tracker_Controller {
	private $loader;
	private $logger;

	public function __construct( \Wpe_Content_Engine_Loader $loader, Debug_Logger $logger ) {
		$this->loader = $loader;
		$this->logger = $logger;
	}

	public function init() {
		$this->loader->add_action( 'rest_api_init', $this, 'register_routes' );
	}

	public function register_routes() {
		register_rest_route(
			'analytics/v1',
			'/behavioral-analytics/(?P<extra>.*)',
			[
				[
					'methods'             => [ 'POST' ],
					'callback'            => [ $this, 'proxy_request' ],
					'permission_callback' => static function ( $request ) {
						$params = $request->get_json_params();
						$nonce  = $params['_wpnonce'] ?? '';

						if ( ! verify_nonce_without_uid( $nonce, 'wp_rest' ) ) {
							return new \WP_Error(
								'forbidden',
								esc_html__( 'Nonce verification failed', 'wpengine-smart-search' ),
								[ 'status' => 403 ]
							);
						}

						return true;
					},
					'args'                => [
						'extra' => [
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						],
					],
				],
			]
		);
	}

	public function extract_event_action_from_url( $url ) {
		// Match the word after 'event/' in the URL.
		if ( preg_match( '/event\/([^\/]+)/', $url, $matches ) ) {
			// The first capture group is the word after 'event/'.
			return $matches[1];
		}

		return null;
	}

	public function proxy_request( \WP_REST_Request $request ) {
		$path   = $request->get_param( 'extra' );
		$action = $this->extract_event_action_from_url( $path );
		$body   = json_decode( $request->get_body(), true );

		switch ( $action ) {
			case 'page_view':
				$this->execute_graphql_query( $action, TRACK_PAGE_VIEW_GRAPHQL, $body );

				break;
			case 'search':
				$this->execute_graphql_query( $action, TRACK_SEARCH_GRAPHQL, $body );

				break;
			case 'search_click':
				$this->execute_graphql_query( $action, TRACK_SEARCH_CLICK_GRAPHQL, $body );

				break;
		}
	}

	private function execute_graphql_query( $action, $query, $body ) {
		try {
			\AtlasSearch\Index\graphql( $query, $body );
		} catch ( \Throwable $e ) {
			$this->logger->log( "GraphQL query for {$action} failed: {$e->getMessage()}" );
		}
	}
}

const TRACK_PAGE_VIEW_GRAPHQL = <<<'GRAPHQL'
mutation TrackPageView($session: Session!, $userID: String!, $data: PageViewData!) {
  tracker {
    trackPageView(session: $session, userID: $userID, data: $data) {
      code
      success
      message
    }
  }
}
GRAPHQL;

const TRACK_SEARCH_GRAPHQL = <<<'GRAPHQL'
mutation trackSearch($session: Session!, $userID: String!, $data: SearchData!) {
  tracker {
    trackSearch(session: $session, userID: $userID, data: $data) {
      code
      success
      message
    }
  }
}
GRAPHQL;

const TRACK_SEARCH_CLICK_GRAPHQL = <<<'GRAPHQL'
mutation trackSearchClick($session: Session!, $userID: String!, $data: SearchClickData!) {
  tracker {
    trackSearchClick(session: $session, userID: $userID, data: $data) {
      code
      success
      message
    }
  }
}
GRAPHQL;
