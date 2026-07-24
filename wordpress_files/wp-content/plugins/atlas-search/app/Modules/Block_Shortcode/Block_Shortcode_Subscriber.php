<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Block_Shortcode;

use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\Modules\Block_Shortcode\Tests\Gutenberg_Enabled_Test;
use WPE\AITK\WP\Site_Health\Test\Test_Registry;

/**
 * Block Shortcode Subscriber.
 *
 * Registers shortcodes for all WP Engine Smart Search blocks.
 */
final class Block_Shortcode_Subscriber extends Hook_Subscriber {
	/**
	 * Block shortcode renderer.
	 *
	 * @var \WPE\AITK\Modules\Block_Shortcode\Block_Shortcode_Renderer
	 */
	private Block_Shortcode_Renderer $renderer;

	public function register(): void {
		$this->renderer = new Block_Shortcode_Renderer();

		$this->container->get( Test_Registry::class )->add(
			new Gutenberg_Enabled_Test()
		);

		add_action( 'init', [ $this, 'register_shortcodes' ] );
	}

	/**
	 * Register shortcodes for all blocks.
	 */
	public function register_shortcodes(): void {
		$shortcodes = [
			'wpe_aitk_active_filters' => 'wpengine-smart-search/active-filters-facet',
			'wpe_aitk_chatkit'        => 'wpengine-smart-search/chatkit',
			'wpe_aitk_filter_facet'   => 'wpengine-smart-search/filter-facet',
			'wpe_aitk_range_facet'    => 'wpengine-smart-search/range-facet',
		];

		foreach ( $shortcodes as $tag => $block_name ) {
			add_shortcode(
				$tag,
				fn ( array $atts ) => $this->renderer->create_shortcode_callback(
					$block_name,
					$atts
				)()
			);
		}
	}
}
