<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Test;

use WPE\AITK\WP\Rest\Contracts\Rest_Controller_Interface;
use WPE\AITK\WP\Rest\Route_Config;
use WPE\AITK\WP\Site_Health\Test\Data\Test_Request;
use WPE\AITK\WP\Site_Health\Test\Data\Test_Response;
use WPE\AITK\WP\Site_Health\Test\Data\Test_Result;
use WPE\AITK\WP\User\Capability_Checker;
use WP_REST_Server;

final class Async_Test_Controller implements Rest_Controller_Interface {
	private Test_Registry $tests;

	private Test_Result_Formatter $formatter;

	public function __construct( Test_Registry $tests, Test_Result_Formatter $formatter ) {
		$this->tests     = $tests;
		$this->formatter = $formatter;
	}

	/**
	 * @inheritDoc
	 */
	public function get_route_configs(): array {
		return [
			new Route_Config(
				'/site-health/(?P<identifier>[\w-]+)',
				WP_REST_Server::READABLE,
				[ $this, 'run_test' ],
				Capability_Checker::VIEW_SITE_HEALTH_CHECKS,
				Test_Response::class,
				Test_Request::class
			),
		];
	}

	public function run_test( Test_Request $request ): Test_Response {
		$test = $this->tests->get_test( $request->get_identifier() );

		if ( null === $test ) {
			return new Test_Response(
				$this->formatter->format(
					Test_Result::critical(
						__( 'Test not found', 'wpe-aitk' ),
						sprintf(
							'<p>%s</p>',
							esc_html__( 'The requested health check test was not found.', 'wpe-aitk' )
						),
						$request->get_identifier()
					)
				)
			);
		}

		return new Test_Response( $this->tests->execute( $test ) );
	}
}
