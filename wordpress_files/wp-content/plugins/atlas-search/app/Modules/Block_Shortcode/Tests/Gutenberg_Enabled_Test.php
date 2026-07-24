<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Block_Shortcode\Tests;

use WPE\AITK\WP\Site_Health\Test\Contracts\Test_Interface;
use WPE\AITK\WP\Site_Health\Test\Data\Test_Result;
use WP_Block_Type_Registry;

final class Gutenberg_Enabled_Test implements Test_Interface {
	private const KNOWN_BLOCK = 'wpengine-smart-search/filter-facet';

	public function get_identifier(): string {
		return 'wpe_aitk_gutenberg_enabled';
	}

	public function get_label(): string {
		return __( 'Gutenberg block editor is enabled', 'wpe-aitk' );
	}

	public function is_async(): bool {
		return false;
	}

	public function run(): Test_Result {
		if ( ! $this->is_block_editor_available() ) {
			return $this->result_unavailable();
		}

		return $this->result_available();
	}

	private function is_block_editor_available(): bool {
		if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
			return false;
		}

		return WP_Block_Type_Registry::get_instance()->is_registered( self::KNOWN_BLOCK );
	}

	private function result_available(): Test_Result {
		return Test_Result::good(
			__( 'The Gutenberg block editor is enabled.', 'wpe-aitk' ),
			sprintf(
				'<p>%s</p>',
				esc_html__(
					'The WP Engine AI Toolkit blocks are registered and available in the block editor.',
					'wpe-aitk'
				)
			),
			$this->get_identifier()
		);
	}

	private function result_unavailable(): Test_Result {
		return Test_Result::critical(
			__( 'The Gutenberg block editor is not available.', 'wpe-aitk' ),
			sprintf( '<p>%s</p>', $this->description_unavailable() ),
			$this->get_identifier()
		);
	}

	private function description_unavailable(): string {
		return esc_html__( 'WP Engine AI Toolkit blocks require the Gutenberg block editor.', 'wpe-aitk' );
	}
}
