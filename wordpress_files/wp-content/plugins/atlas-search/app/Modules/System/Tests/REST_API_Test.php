<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\System\Tests;

use WPE\AITK\WP\Site_Health\Test\Contracts\Test_Interface;
use WPE\AITK\WP\Site_Health\Test\Data\Test_Result;

final class REST_API_Test implements Test_Interface {
	public function get_identifier(): string {
		return 'wpe_aitk_rest_api';
	}

	public function get_label(): string {
		return __( 'WordPress REST API is accessible', 'wpe-aitk' );
	}

	public function is_async(): bool {
		return false;
	}

	public function run(): Test_Result {
		if ( ! $this->is_rest_available() ) {
			return $this->result_unavailable();
		}

		return $this->result_available();
	}

	private function is_rest_available(): bool {
		if ( empty( rest_url() ) ) {
			return false;
		}

		$routes = rest_get_server()->get_routes();

		return ! empty( $routes );
	}

	private function result_available(): Test_Result {
		return Test_Result::good(
			__( 'The WordPress REST API is accessible.', 'wpe-aitk' ),
			sprintf( '<p>%s</p>', $this->description_available() ),
			$this->get_identifier()
		);
	}

	private function result_unavailable(): Test_Result {
		return Test_Result::critical(
			__( 'The WordPress REST API is not accessible.', 'wpe-aitk' ),
			sprintf( '<p>%s</p>', $this->description_unavailable() ),
			$this->get_identifier()
		);
	}

	private function description_available(): string {
		return esc_html__( 'The REST API is enabled.', 'wpe-aitk' );
	}

	private function description_unavailable(): string {
		return esc_html__( 'The REST API is required for this plugin to function properly.', 'wpe-aitk' );
	}
}
