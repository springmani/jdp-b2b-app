<?php

namespace AtlasSearch\Blocks;

use AtlasSearch\Blocks\Support\WooCommerce\Callbacks as WooCallbacks;
use WP_HTML_Tag_Processor;

use function AtlasSearch\Support\WooCommerce\is_woo_enabled;
use function Wpe_Content_Engine\Helper\Acf_Support\is_acf_loaded;

/**
 * This file registers facets blocks.
 *
 * @package    Wpe_Content_Engine
 */
class WPE_Atlas_Search_Facets_Callbacks {
	private $loader;

	public function __construct( \Wpe_Content_Engine_Loader $loader ) {
		$this->loader = $loader;
	}

	public function init() {
		global $wp_version;

		if ( version_compare( $wp_version, '6.5', '<' ) ) {
			return;
		}

		$this->loader->add_action( 'init', $this, 'register_facet_blocks' );
		$this->loader->add_filter( 'block_categories_all', $this, 'register_block_categories', 10, 2 );
		$this->loader->add_filter( 'render_block', $this, 'core_search', 10, 2 );
		$this->loader->add_action(
			'pre_get_posts',
			null,
			'AtlasSearch\Blocks\WordPress\RangeFacet\filter_range_slider',
			9999999,
			1
		);
		( new WooCallbacks( $this->loader ) )->init();
	}

	public function core_search( $block_content, $block ) {
		if ( 'core/search' !== $block['blockName'] ) {
			return $block_content;
		}

		if ( ! \AtlasSearch\Hooks\filter_search_facets_enabled() ) {
			return $block_content;
		}

		// Use WP_HTML_Tag_Processor to modify the button's HTML output.
		$p = new WP_HTML_Tag_Processor( $block_content );
		// Move to the first tag in block_content (should be <form>).
		$p->next_tag();

		// Attach Interactivity API directives.
		$p->set_attribute( 'data-wp-interactive', '{ "namespace": "wpengine-smart-search-facets__store" }' );
		$p->set_attribute( 'data-wp-on--submit', 'actions.performSearch' );
		$p->set_attribute( 'data-wp-init', 'actions.initFacets' );

		$p->next_tag( [ 'tag_name' => 'input' ] );
		$p->set_attribute( 'data-wp-on--input', 'actions.setSearchValue' );

		// Return the modified button block content.
		return $p->get_updated_html();
	}

	public function register_facet_blocks() {
		$blocks = $this->get_blocks_to_register();

		foreach ( $blocks as $block ) {
			// Go up two levels to the plugin root.
			$plugin_dir = plugin_dir_path( dirname( __DIR__, 1 ) );
			register_block_type( $plugin_dir . "build/{$block}" );
		}
	}

	/**
	 * Register block categories
	 *
	 * Used in combination with the `block_categories_all` filter, to append
	 * Smart Search Blocks related categories to the Gutenberg editor.
	 *
	 * @param array $categories The array of already registered categories.
	 */
	public function register_block_categories( $categories ) {
		$smart_search_block_categories = [
			[
				'slug'  => 'wpengine-smart-search',
				'title' => __( 'WP Engine Smart Search', 'wpengine-smart-search' ),
			],
		];

		return array_merge( $categories, $smart_search_block_categories );
	}

	/**
	 * Determines which WPE Smart Search facet blocks to register based on configuration.
	 *
	 * @return string[] A list of block names.
	 */
	private function get_blocks_to_register(): array {
		$blocks = [];

		if ( \Wpe_Content_Engine\Helper\CapabilitiesService::has_capability( 'RECOMMENDATIONS' ) ) {
			$blocks[] = 'recommendations-block';
		}

		if ( \Wpe_Content_Engine\Helper\CapabilitiesService::has_capability( 'SEARCH' )
				&& \AtlasSearch\Hooks\filter_search_facets_enabled() ) {
			$blocks[] = 'filter-facet';
			$blocks[] = 'active-filters-facet';

			if ( is_woo_enabled() || is_acf_loaded() ) {
				$blocks[] = 'range-facet';
			}
		}

		// Register ChatKit block if ChatKit is enabled.
		if ( class_exists( '\WPE\AITK\Modules\ChatKit\ChatKit_Settings' ) ) {
			$chatkit_settings = \WPE\AITK\Modules\ChatKit\ChatKit_Settings::from_options();

			if ( $chatkit_settings->is_enabled() ) {
				$blocks[] = 'chatkit-block';
			}
		}

		return $blocks;
	}
}
