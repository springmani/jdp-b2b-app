<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\ChatKit;

use WPE\AITK\Core\Utils\Json_Decoder;
use WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface;
use WP_REST_Request;
use WP_REST_Response;

/**
 * ChatKit API Handler.
 *
 * Manages OpenAI session creation and refresh via WordPress REST API.
 */
final class API_Handler {
	/**
	 * REST API namespace.
	 */
	private const REST_NAMESPACE = 'wpengine-aitk/v1';

	/**
	 * REST API base route.
	 */
	private const REST_BASE = 'chatkit';

	/**
	 * OpenAI API endpoint.
	 */
	private const OPENAI_API_ENDPOINT = 'https://api.openai.com/v1/chatkit/sessions';

	/**
	 * Analytics user ID cookie name.
	 */
	private const EA_UID_COOKIE = 'EA_UID';

	/**
	 * Current user provider.
	 *
	 * @var \WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface
	 */
	private Current_User_Provider_Interface $user_provider;

	/**
	 * JSON decoder.
	 *
	 * @var \WPE\AITK\Core\Utils\Json_Decoder
	 */
	private Json_Decoder $json_decoder;

	/**
	 * Constructor.
	 *
	 * @param \WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface $user_provider Current user provider.
	 * @param \WPE\AITK\Core\Utils\Json_Decoder                            $json_decoder  JSON decoder service.
	 */
	public function __construct( Current_User_Provider_Interface $user_provider, Json_Decoder $json_decoder ) {
		$this->user_provider = $user_provider;
		$this->json_decoder  = $json_decoder;
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::REST_NAMESPACE,
			'/' . self::REST_BASE . '/start',
			[
				'callback'            => [ $this, 'handle_start' ],
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/' . self::REST_BASE . '/refresh',
			[
				'callback'            => [ $this, 'handle_refresh' ],
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Handle /start endpoint - create new ChatKit session.
	 */
	public function handle_start(): WP_REST_Response {
		$validation_error = $this->validate_configuration();

		if ( $validation_error instanceof WP_REST_Response ) {
			return $validation_error;
		}

		return $this->create_session();
	}

	/**
	 * Handle /refresh endpoint - refresh existing session.
	 *
	 * @param \WP_REST_Request $request The REST request.
	 */
	public function handle_refresh( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_json_params();

		if ( empty( $params['currentClientSecret'] ?? '' ) ) {
			return $this->error_response(
				__( 'Current client secret is required', 'wpe-aitk' ),
				400
			);
		}

		$validation_error = $this->validate_configuration();

		if ( $validation_error instanceof WP_REST_Response ) {
			return $validation_error;
		}

		return $this->create_session();
	}

	/**
	 * Validate API configuration.
	 *
	 * @return \WP_REST_Response|null Returns error response or null if valid.
	 */
	private function validate_configuration(): ?WP_REST_Response {
		$settings = ChatKit_Settings::from_options();

		if ( empty( $settings->get_api_key() ) ) {
			return $this->error_response(
				__( 'OpenAI API key not configured', 'wpe-aitk' ),
				500
			);
		}

		if ( empty( $settings->get_workflow_id() ) ) {
			return $this->error_response(
				__( 'Workflow ID not configured. Please configure it in ChatKit settings.', 'wpe-aitk' ),
				500
			);
		}

		return null;
	}

	/**
	 * Create a new ChatKit session.
	 */
	private function create_session(): WP_REST_Response {
		$settings    = ChatKit_Settings::from_options();
		$api_key     = $settings->get_api_key();
		$workflow_id = $settings->get_workflow_id();
		$user_id     = $this->get_user_id();

		$response = $this->call_openai_api( $api_key, $workflow_id, $user_id );

		if ( $response instanceof WP_REST_Response ) {
			return $response;
		}

		return $this->process_api_response( $response );
	}

	/**
	 * Get user ID for ChatKit session.
	 *
	 * Uses WordPress user ID for logged-in users, EA_UID cookie for guests, or random ID.
	 * Values are hashed to avoid exposing internal identifiers to the external API.
	 *
	 * @return string Pseudonymized user ID for ChatKit session.
	 */
	private function get_user_id(): string {
		$current_user_id = $this->user_provider->get_current_user_id();

		if ( null !== $current_user_id && $current_user_id > 0 ) {
			return 'user_' . wp_hash( (string) $current_user_id );
		}

		// Check for EA_UID cookie (analytics user ID) for guest users.
		$ea_uid = $this->get_ea_uid_from_cookie();

		if ( ! empty( $ea_uid ) ) {
			return 'guest_' . wp_hash( $ea_uid );
		}

		return 'guest_' . wp_generate_password( 12, false );
	}

	/**
	 * Get EA_UID value from cookie.
	 *
	 * @return string Sanitized EA_UID value or empty string.
	 */
	private function get_ea_uid_from_cookie(): string {
		$ea_uid = filter_input( INPUT_COOKIE, self::EA_UID_COOKIE, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! is_string( $ea_uid ) ) {
			return '';
		}

		return $ea_uid;
	}

	/**
	 * Call OpenAI ChatKit API.
	 *
	 * @param string $api_key     OpenAI API key.
	 * @param string $workflow_id Workflow ID.
	 * @param string $user_id     User ID.
	 *
	 * @return array<array-key, array<array-key, int|string>|int|string>|\WP_REST_Response
	 */
	//phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
	private function call_openai_api( string $api_key, string $workflow_id, string $user_id ) {
		$response = wp_remote_post(
			self::OPENAI_API_ENDPOINT,
			[
				'body'    => wp_json_encode(
					[
						'user'     => $user_id,
						'workflow' => [
							'id' => $workflow_id,
						],
					]
				),
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
					'OpenAI-Beta'   => 'chatkit_beta=v1',
				],
				'timeout' => 30,
			]
		);

		if ( is_wp_error( $response ) ) {
			return $this->error_response( $response->get_error_message(), 500 );
		}

		return $response;
	}

	/**
	 * Process OpenAI API response.
	 *
	 * @param array<array-key, array<array-key, int|string>|int|string> $response HTTP response.
	 */
	private function process_api_response( array $response ): WP_REST_Response {
		$status_code = wp_remote_retrieve_response_code( $response );
		$body_string = wp_remote_retrieve_body( $response );
		$body        = $this->json_decoder->decode( $body_string );

		if ( 200 !== $status_code ) {
			$error_message = $body['error']['message'] ?? __( 'Failed to create session', 'wpe-aitk' );

			return $this->error_response( $error_message, $status_code );
		}

		return rest_ensure_response(
			[
				'client_secret' => $body['client_secret'] ?? '',
			]
		);
	}

	/**
	 * Create error response.
	 *
	 * @param string $message Error message.
	 * @param int    $status  HTTP status code.
	 */
	private function error_response( string $message, int $status ): WP_REST_Response {
		return new WP_REST_Response(
			[ 'error' => $message ],
			$status
		);
	}
}
