<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Support\Debug;

use WPE\AITK\Modules\Support\Debug\Data\Debug_Logs_Download_Response;
use WPE\AITK\Modules\Support\Debug\Data\Debug_Logs_Status_Response;
use WPE\AITK\WP\Rest\Contracts\Rest_Controller_Interface;
use WPE\AITK\WP\Rest\Route_Config;
use WPE\AITK\WP\User\Capability_Checker;
use WP_REST_Server;

final class Logs_Rest_Controller implements Rest_Controller_Interface {
	public const DEBUG_LOGS        = '/debug-logs';
	public const DEBUG_LOGS_STATUS = '/debug-logs/status';

	private Logs_Handler $handler;

	public function __construct( Logs_Handler $handler ) {
		$this->handler = $handler;
	}

	/**
	 * @inheritDoc
	 */
	public function get_route_configs(): array {
		return [
			new Route_Config(
				self::DEBUG_LOGS,
				WP_REST_Server::READABLE,
				[ $this->handler, 'create_download' ],
				Capability_Checker::MANAGE_OPTIONS,
				Debug_Logs_Download_Response::class
			),
			new Route_Config(
				self::DEBUG_LOGS_STATUS,
				WP_REST_Server::READABLE,
				[ $this->handler, 'get_status' ],
				Capability_Checker::MANAGE_OPTIONS,
				Debug_Logs_Status_Response::class
			),
		];
	}
}
