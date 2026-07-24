<?php

namespace Wpe_Content_Engine\Helper;

	/**
	 * Modified wp_create_nonce function to create nonce without user ID dependency.
	 *
	 * Creates a cryptographic token tied to a specific action, user session,
	 * and window of time.
	 *
	 * @param string|int $action Scalar value to add context to the nonce.
	 *
	 * @return string The token.
	 */
function create_nonce_without_uid( $action = -1 ) {
	$token = wp_get_session_token();
	$i     = wp_nonce_tick( $action );

	return substr( wp_hash( $i . '|' . $action . '|' . $token, 'nonce' ), -12, 10 );
}

	/**
	 * Modified wp_verify_nonce function to verify nonce without user ID dependency
	 *
	 * Verifies that a correct security nonce was used with time limit.
	 *
	 * A nonce is valid for between 12 and 24 hours (by default).
	 *
	 * @param string     $nonce Nonce value that was used for verification, usually via a form field.
	 * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
	 *
	 * @return int|false 1 if the nonce is valid and generated between 0-12 hours ago,
	 *                   2 if the nonce is valid and generated between 12-24 hours ago.
	 *                   False if the nonce is invalid.
	 */
function verify_nonce_without_uid( $nonce, $action = -1 ) {
	$nonce = (string) $nonce;

	if ( empty( $nonce ) ) {
		return false;
	}

	$token = wp_get_session_token();
	$i     = wp_nonce_tick( $action );

	// Verify nonce generated 0-12 hours ago without UID check.
	$expected = substr( wp_hash( $i . '|' . $action . '|' . $token, 'nonce' ), -12, 10 );

	if ( hash_equals( $expected, $nonce ) ) {
		return 1;
	}

	// Verify nonce generated 12-24 hours ago without UID check.
	$expected = substr( wp_hash( ( $i - 1 ) . '|' . $action . '|' . $token, 'nonce' ), -12, 10 );

	if ( hash_equals( $expected, $nonce ) ) {
		return 2;
	}

	/**
	 * Fires when nonce verification fails.
	 *
	 * @param string $nonce The invalid nonce.
	 * @param string|int $action The nonce action.
	 * @param \Wpe_Content_Engine\Helper\WP_User $user The current user object.
	 * @param string $token The user's session token.
	 */
	$user = wp_get_current_user();
	do_action( 'wp_verify_nonce_failed', $nonce, $action, $user, $token );

	// Invalid nonce.
	return false;
}
