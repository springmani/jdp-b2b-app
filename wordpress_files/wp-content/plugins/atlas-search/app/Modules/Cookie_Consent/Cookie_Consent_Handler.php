<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Cookie_Consent;

use Throwable;
use WPE\AITK\Modules\Cookie_Consent\Contracts\Acknowledgement_Storage_Interface;
use WPE\AITK\Modules\Cookie_Consent\Contracts\Cookie_Consent_Interface;
use WPE\AITK\Modules\Cookie_Consent\Data\Acknowledgement_Response;
use WPE\AITK\Modules\Cookie_Consent\Data\Cookie_Consent_Config;
use WPE\AITK\Modules\Cookie_Consent\Operations\Acknowledge_Data_Collection_Mutation;
use WPE\AITK\WP\Ajax\Ajax_Response;
use WPE\AITK\WP\Ajax\Contracts\Ajax_Responder_Interface;
use WPE\AITK\WP\Asset\Script\Contracts\Script_Handler_Interface;
use WPE\AITK\WP\Asset\Script\Script_Asset;
use WPE\AITK\WP\Debug\Contracts\Logger_Interface;
use WPE\AITK\WP\GraphQL\Contracts\GraphQL_Client_Interface;
use WPE\AITK\WP\Nonce\Contracts\Nonce_Interface;
use WPE\AITK\WP\Nonce\Exceptions\Nonce_Creation_Exception;
use WPE\AITK\WP\Notice\Contracts\Notice_Renderer_Interface;
use WPE\AITK\WP\Notice\Default_Notice;
use WPE\AITK\WP\Notice\Notice_Type;
use WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface;
use WPE\AITK\WP\Script\Exceptions\Script_Injection_Exception;
use WPE\AITK\WP\User\Contracts\Capability_Checker_Interface;
use WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface;
use WP_Screen;
use WP_User;

final class Cookie_Consent_Handler implements Cookie_Consent_Interface {
	private const AJAX_SCRIPT_HANDLE = 'wpe-aitk-cookie-consent';
	private const AJAX_SCRIPT_SRC    = 'public/js/cookie-consent-notice.js';
	private const AJAX_SCRIPT_OBJECT = 'wpeAitkData';
	private const NONCE_ACTION       = 'acknowledge_data_collection_nonce';
	private const NOTICE_ID          = 'wpe-aitk-cookie-acknowledgement-notice';

	private Cookie_Consent_Config $config;
	private Acknowledgement_Storage_Interface $storage;
	private GraphQL_Client_Interface $graphql_client;
	private Ajax_Responder_Interface $ajax_responder;
	private Capability_Checker_Interface $capability_checker;
	private Current_User_Provider_Interface $user_provider;
	private Nonce_Interface $nonce;
	private Notice_Renderer_Interface $notice_renderer;
	private Script_Handler_Interface $script_handler;
	private Script_Data_Injector_Interface $script_injector;
	private Logger_Interface $logger;

	public function __construct(
		Cookie_Consent_Config $config,
		Acknowledgement_Storage_Interface $storage,
		GraphQL_Client_Interface $graphql_client,
		Ajax_Responder_Interface $ajax_responder,
		Capability_Checker_Interface $capability_checker,
		Current_User_Provider_Interface $user_provider,
		Nonce_Interface $nonce,
		Notice_Renderer_Interface $notice_renderer,
		Script_Handler_Interface $script_handler,
		Script_Data_Injector_Interface $script_injector,
		Logger_Interface $logger
	) {
		$this->config             = $config;
		$this->storage            = $storage;
		$this->graphql_client     = $graphql_client;
		$this->ajax_responder     = $ajax_responder;
		$this->capability_checker = $capability_checker;
		$this->user_provider      = $user_provider;
		$this->nonce              = $nonce;
		$this->notice_renderer    = $notice_renderer;
		$this->script_handler     = $script_handler;
		$this->script_injector    = $script_injector;
		$this->logger             = $logger;
	}

	public function acknowledge_data_collection(): void {
		$nonce_value = (string) filter_input( INPUT_POST, '_ajax_nonce', FILTER_UNSAFE_RAW );

		if ( ! $this->nonce->verify( $nonce_value, self::NONCE_ACTION ) ) {
			$this->ajax_responder->respond(
				new Ajax_Response( false, __( 'Invalid nonce.', 'wpe-aitk' ), 403 )
			);

			return;
		}

		$user_id = $this->verify_and_get_current_user_id();

		if ( ! $user_id ) {
			$this->ajax_responder->respond(
				new Ajax_Response(
					false,
					__( 'User not logged in or has no relevant capability.', 'wpe-aitk' ),
					401
				)
			);

			return;
		}

		$this->process_acknowledgement_request( $user_id );
	}

	public function enqueue_admin_scripts(): void {
		if ( ! $this->verify_notice_screens() ) {
			return;
		}

		$this->script_handler->enqueue( new Script_Asset( self::AJAX_SCRIPT_HANDLE, self::AJAX_SCRIPT_SRC ) );

		try {
			$data = new Data\Cookie_Consent_Ajax_Config(
				$this->config->get_ajax_action(),
				$this->nonce->create( self::NONCE_ACTION )
			);

			$this->script_injector->inject( self::AJAX_SCRIPT_HANDLE, $data, self::AJAX_SCRIPT_OBJECT );
		} catch ( Script_Injection_Exception | Nonce_Creation_Exception $e ) {
			$this->logger->error( 'cookie_consent', $e->getMessage() );
		}
	}

	public function display_admin_notice(): void {
		if ( ! $this->should_display_notice() ) {
			return;
		}

		$this->notice_renderer->render( new Default_Notice(
			$this->get_notice_content(),
			Notice_Type::WARNING,
			false,
			self::NOTICE_ID
		) );
	}

	private function process_acknowledgement_request( int $user_id ): void {
		try {
			$response = ( new Acknowledge_Data_Collection_Mutation(
				home_url(),
				(string) $user_id
			) )->execute( $this->graphql_client, Acknowledgement_Response::class );

			if ( ! $response->is_success() ) {
				$this->ajax_responder->respond(
					new Ajax_Response( false, $response->get_message(), 422 )
				);

				return;
			}

			$this->storage->persist();

			$this->ajax_responder->respond(
				new Ajax_Response(
					true,
					esc_html__( 'Data collection acknowledged successfully.', 'wpe-aitk' ),
					200
				)
			);
		} catch ( Throwable $e ) {
			$this->logger->error( 'cookie_consent', $e->getMessage() );

			$this->ajax_responder->respond(
				new Ajax_Response(
					false,
					esc_html__( 'Failed to process data collection acknowledgement.', 'wpe-aitk' ),
					500
				)
			);
		}
	}

	private function get_notice_content(): string {
		return '<h3>Cookie acknowledgement required</h3>' .
				'<p>By clicking \'I understand\', you acknowledge that you have read and agree to the following ' .
				'pertaining to AI-Powered Recommendations:</p>' .
				'<p>The Recommendations Tool is powered by a WP Engine cookie on your website. The cookie collects ' .
				'traffic and session data as described ' .
				'<a href="https://developers.wpengine.com/docs/wp-engine-ai-toolkit/recommendations/' .
				'recommendations-api/#cookie-based-data-collection" target="_blank" rel="noopener noreferrer">' .
				'here</a>, which allows you to track your visitors\' behavior on your site. WP Engine ' .
				'processes this data in accordance with the terms of its ' .
				'<a href="https://wpengine.com/legal/dpa/" target="_blank" rel="noopener noreferrer">' .
				'Data Privacy Addendum</a>. You are responsible for obtaining any required consents of your visitors ' .
				'to enable this cookie. Without this acknowledgement, the cookie functionality in AI-Powered ' .
				'Recommendations will not be accessible, however, this will not affect the accuracy of this tool. ' .
				'Learn more about our Recommendations API ' .
				'<a href="https://developers.wpengine.com/docs/wp-engine-ai-toolkit/recommendations/' .
				'recommendations-api/" target="_blank" rel="noopener noreferrer">here</a>.</p>' .
				'<p><button type="button" id="cookie-accept" class="button button-primary">I understand</button></p>';
	}

	private function verify_notice_screens(): bool {
		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen ) {
			return false;
		}

		$view = (string) filter_input( INPUT_GET, 'view' );

		if ( $this->config->get_plugin_page_view() !== $view ) {
			return false;
		}

		return $this->config->get_page_context()->matches_current( $screen );
	}

	private function should_display_notice(): bool {
		if ( ! $this->verify_notice_screens() ) {
			return false;
		}

		if ( $this->storage->is_acknowledged() ) {
			return false;
		}

		return (bool) $this->verify_and_get_current_user_id();
	}

	private function verify_and_get_current_user_id(): ?int {
		$user = $this->user_provider->get_current_user();

		if (
			! $user instanceof WP_User
			|| ! $this->capability_checker->has( $user, $this->config->get_user_capability() )
		) {
			return null;
		}

		return $user->ID;
	}
}
