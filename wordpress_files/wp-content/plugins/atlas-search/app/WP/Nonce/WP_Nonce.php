<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Nonce;

use WPE\AITK\WP\Nonce\Contracts\Nonce_Interface;
use WPE\AITK\WP\Nonce\Exceptions\Nonce_Creation_Exception;

/**
 * WordPress nonce implementation (includes user ID in hash).
 *
 * Suitable for authenticated contexts where the current user is known.
 */
final class WP_Nonce implements Nonce_Interface {
	public function create( string $action ): string {
		if ( '' === $action ) {
			throw new Nonce_Creation_Exception( 'Invalid or empty action' );
		}

		return wp_create_nonce( $action );
	}

	public function verify( string $nonce, string $action ): bool {
		return ! empty( $nonce ) && false !== wp_verify_nonce( $nonce, $action );
	}
}
