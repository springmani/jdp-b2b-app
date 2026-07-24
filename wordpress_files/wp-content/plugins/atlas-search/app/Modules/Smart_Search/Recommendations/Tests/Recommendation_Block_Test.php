<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Recommendations\Tests;

use WPE\AITK\WP\Site_Health\Test\Contracts\Test_Interface;
use WPE\AITK\WP\Site_Health\Test\Data\Test_Result;
use WP_Block_Type_Registry;

final class Recommendation_Block_Test implements Test_Interface {
	private const BLOCK_NAME = 'wpengine-smart-search/recommendations';

	public function get_identifier(): string {
		return 'wpe_aitk_recommendations_block';
	}

	public function get_label(): string {
		return __( 'Recommendations block is accessible', 'wpe-aitk' );
	}

	public function is_async(): bool {
		return false;
	}

	public function run(): Test_Result {
		if ( ! $this->is_block_registered() ) {
			return $this->result_not_registered();
		}

		return $this->result_registered();
	}

	private function is_block_registered(): bool {
		if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
			return false;
		}

		return WP_Block_Type_Registry::get_instance()->is_registered( self::BLOCK_NAME );
	}

	private function result_registered(): Test_Result {
		return Test_Result::good(
			__( 'The Recommendations block is registered and accessible.', 'wpe-aitk' ),
			sprintf(
				'<p>%s</p>',
				esc_html__(
					'The WP Engine AI Toolkit Recommendations block is available for use in the block editor.',
					'wpe-aitk'
				)
			),
			$this->get_identifier()
		);
	}

	private function result_not_registered(): Test_Result {
		return Test_Result::critical(
			__( 'The Recommendations block is not registered.', 'wpe-aitk' ),
			sprintf( '<p>%s</p>', $this->description_not_registered() ),
			$this->get_identifier()
		);
	}

	private function description_not_registered(): string {
		return esc_html__( 'The WP Engine AI Toolkit Recommendations block could not be found.', 'wpe-aitk' );
	}
}
