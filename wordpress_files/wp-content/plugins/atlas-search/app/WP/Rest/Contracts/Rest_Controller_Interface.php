<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Contracts;

/**
 * REST Controller Interface.
 *
 * Defines the contract for REST API controllers that declare
 * their route configurations. Controllers are pure handler + config
 * containers; the Route_Registrar owns the dispatch pipeline
 * (request hydration, response wrapping, and exception handling).
 */
interface Rest_Controller_Interface {
	/**
	 * Returns route configurations for this controller.
	 *
	 * @return array<int, \WPE\AITK\WP\Rest\Route_Config>
	 */
	public function get_route_configs(): array;
}
