<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\ChatKit;

use WPE\AITK\WP\Ajax\Ajax_Responder;
use WPE\AITK\WP\Ajax\Ajax_Response;
use WPE\AITK\WP\Nonce\Contracts\Nonce_Interface;
use WPE\AITK\WP\User\Contracts\Capability_Checker_Interface;
use WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface;

/**
 * ChatKit Settings Handler.
 *
 * Handles AJAX requests for getting and saving ChatKit settings.
 */
final class ChatKit_Settings_Handler {
	/**
	 * Nonce action for REST API.
	 */
	private const NONCE_ACTION = 'wp_rest';

	/**
	 * Required capability to manage settings.
	 */
	private const REQUIRED_CAPABILITY = 'manage_options';

	/**
	 * Nonce field name.
	 */
	private const NONCE_FIELD = 'nonce';

	/**
	 * Current user provider.
	 *
	 * @var \WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface
	 */
	private Current_User_Provider_Interface $user_provider;

	/**
	 * Capability checker.
	 *
	 * @var \WPE\AITK\WP\User\Contracts\Capability_Checker_Interface
	 */
	private Capability_Checker_Interface $capability_checker;

	/**
	 * Nonce handler.
	 *
	 * @var \WPE\AITK\WP\Nonce\Contracts\Nonce_Interface
	 */
	private Nonce_Interface $nonce;

	/**
	 * AJAX responder.
	 *
	 * @var \WPE\AITK\WP\Ajax\Ajax_Responder
	 */
	private Ajax_Responder $ajax_responder;

	public function __construct(
		Current_User_Provider_Interface $user_provider,
		Capability_Checker_Interface $capability_checker,
		Nonce_Interface $nonce,
		Ajax_Responder $ajax_responder
	) {
		$this->user_provider      = $user_provider;
		$this->capability_checker = $capability_checker;
		$this->nonce              = $nonce;
		$this->ajax_responder     = $ajax_responder;
	}

	/**
	 * Handle GET request for ChatKit settings via AJAX.
	 */
	public function handle_get(): void {
		$user = $this->user_provider->get_current_user();

		// Only administrators can access settings containing API keys.
		if ( ! $user || ! $this->capability_checker->has( $user, self::REQUIRED_CAPABILITY ) ) {
			$this->ajax_responder->respond(
				new Ajax_Response( false, 'Unauthorized access.', 403 )
			);

			return;
		}

		$settings = ChatKit_Settings::from_options();

		$this->ajax_responder->respond(
			new Ajax_Response( true, '', 200, $settings )
		);
	}

	/**
	 * Handle POST request to save ChatKit settings via AJAX.
	 *
	 * @throws \WPE\AITK\WP\Nonce\Exceptions\Nonce_Verification_Exception
	 */
	public function handle_save(): void {
		if ( ! $this->verify_save_permissions() ) {
			return;
		}

		$settings = ChatKit_Settings::from_post_data();
		$settings->save();

		$this->ajax_responder->respond(
			new Ajax_Response( true, 'Settings saved successfully.', 200 )
		);
	}

	/**
	 * Verify nonce and permissions for saving settings.
	 *
	 * @return bool True if verification passes, false otherwise.
	 */
	private function verify_save_permissions(): bool {
		// Verify nonce.
		$nonce_value = (string) filter_input( INPUT_POST, self::NONCE_FIELD, FILTER_UNSAFE_RAW );

		if ( ! $this->nonce->verify( $nonce_value, self::NONCE_ACTION ) ) {
			$this->ajax_responder->respond(
				new Ajax_Response( false, 'Invalid security token.', 403 )
			);

			return false;
		}

		// Verify user has required capability.
		$user = $this->user_provider->get_current_user();

		if ( ! $user || ! $this->capability_checker->has( $user, self::REQUIRED_CAPABILITY ) ) {
			$this->ajax_responder->respond(
				new Ajax_Response( false, 'Insufficient permissions to save settings.', 403 )
			);

			return false;
		}

		return true;
	}
}
