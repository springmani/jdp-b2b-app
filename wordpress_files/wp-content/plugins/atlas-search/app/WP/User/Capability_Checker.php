<?php

declare(strict_types=1);

namespace WPE\AITK\WP\User;

use WPE\AITK\Core\Utils\Strings_Matcher;
use WPE\AITK\WP\User\Contracts\Capability_Checker_Interface;
use WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface;
use WP_User;

final class Capability_Checker implements Capability_Checker_Interface {
	public const MANAGE_OPTIONS          = 'manage_options';
	public const MANAGE_NETWORK_OPTIONS  = 'manage_network_options';
	public const VIEW_SITE_HEALTH_CHECKS = 'view_site_health_checks';

	/**
	 * @var \WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface
	 */
	private Current_User_Provider_Interface $user_provider;

	/**
	 * @param \WPE\AITK\WP\User\Contracts\Current_User_Provider_Interface $user_provider Current user provider.
	 */
	public function __construct( Current_User_Provider_Interface $user_provider ) {
		$this->user_provider = $user_provider;
	}

	/**
	 * @inheritDoc
	 */
	public function has_all( WP_User $user, array $caps ): bool {
		return Strings_Matcher::all( $caps, $this->get_active_caps( $user ) );
	}

	/**
	 * @inheritDoc
	 */
	public function has_any( WP_User $user, array $caps ): bool {
		return Strings_Matcher::any( $caps, $this->get_active_caps( $user ) );
	}

	public function has( WP_User $user, string $capability ): bool {
		return $user->has_cap( $capability );
	}

	public function current_user_can( string $capability ): bool {
		$user = $this->user_provider->get_current_user();

		if ( null === $user ) {
			return false;
		}

		return $this->has( $user, $capability );
	}

	public function guard( ?string $capability = null ): callable {
		if ( null === $capability ) {
			return static fn (): bool => true;
		}

		return fn (): bool => $this->current_user_can( $capability );
	}

	/**
	 * Returns the active capability names for a user.
	 *
	 * @param \WP_User $user The user to check.
	 *
	 * @return array<string> Active capability names.
	 */
	private function get_active_caps( WP_User $user ): array {
		return array_keys( array_filter( $user->allcaps ?? [] ) );
	}
}
