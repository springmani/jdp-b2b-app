<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Nonce\Contracts;

/**
 * Creates and verifies nonces using a consistent algorithm.
 *
 * Implementations define the hashing strategy (e.g. with or without user ID).
 * Creator and verifier are unified because they must use the same algorithm.
 */
interface Nonce_Interface {
	/**
	 * Creates a nonce for the given action.
	 *
	 * @param non-empty-string $action The action for which to create the nonce.
	 *
	 * @throws \WPE\AITK\WP\Nonce\Exceptions\Nonce_Creation_Exception If the nonce could not be created.
	 *
	 * @return string The created nonce.
	 */
	public function create( string $action ): string;

	/**
	 * Verify a nonce value.
	 *
	 * @param string $nonce  The nonce value to verify.
	 * @param string $action The action the nonce was created for.
	 *
	 * @return bool True if the nonce is valid.
	 */
	public function verify( string $nonce, string $action ): bool;
}
