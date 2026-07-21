<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Nonce;

use WPE\AITK\WP\Nonce\Contracts\Nonce_Interface;
use WPE\AITK\WP\Nonce\Exceptions\Nonce_Creation_Exception;

/**
 * Public nonce implementation (no user ID in hash).
 *
 * Suitable for public-facing contexts like analytics tracking,
 * where nonces must work for both logged-in and anonymous visitors.
 */
final class Public_Nonce implements Nonce_Interface {
	public function create( string $action ): string {
		if ( '' === $action ) {
			throw new Nonce_Creation_Exception( 'Invalid or empty action' );
		}

		$token = wp_get_session_token();
		$tick  = wp_nonce_tick( $action );

		return substr( wp_hash( $tick . '|' . $action . '|' . $token, 'nonce' ), -12, 10 );
	}

	public function verify( string $nonce, string $action ): bool {
		if ( empty( $nonce ) ) {
			return false;
		}

		$token = wp_get_session_token();
		$tick  = wp_nonce_tick( $action );

		// Check nonce generated 0-12 hours ago.
		$expected = substr( wp_hash( $tick . '|' . $action . '|' . $token, 'nonce' ), -12, 10 );

		if ( hash_equals( $expected, $nonce ) ) {
			return true;
		}

		// Check nonce generated 12-24 hours ago.
		$expected = substr( wp_hash( ( $tick - 1 ) . '|' . $action . '|' . $token, 'nonce' ), -12, 10 );

		return hash_equals( $expected, $nonce );
	}
}
