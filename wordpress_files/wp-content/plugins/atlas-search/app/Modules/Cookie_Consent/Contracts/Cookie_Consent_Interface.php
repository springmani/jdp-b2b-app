<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Cookie_Consent\Contracts;

/**
 * Contract for Cookie Consent subscribers.
 *
 * Defines the public-facing API for cookie consent acknowledgement,
 * script enqueueing, and admin notice display — shared by both
 * single-site and network-admin implementations.
 */
interface Cookie_Consent_Interface {
	/**
	 * Handle AJAX request for data collection acknowledgement.
	 */
	public function acknowledge_data_collection(): void;

	/**
	 * Enqueue JavaScript for AJAX functionality.
	 */
	public function enqueue_admin_scripts(): void;

	/**
	 * Display admin notice for data collection acknowledgement.
	 */
	public function display_admin_notice(): void;
}
