<?php

/**
 * This file enqueues scripts and styles.
 *
 * @package    Wpe_Content_Engine
 */
class AI_Toolkit_Settings_Page {
	public const PAGE                 = 'wpengine-ai-toolkit';
	public const SMART_SEARCH_PAGE    = 'wpengine-ai-toolkit&amp;view=smart-search';
	public const SETTINGS_PAGE        = 'wpengine-ai-toolkit&amp;view=settings';
	public const RECOMMENDATIONS_PAGE = 'wpengine-ai-toolkit&amp;view=recommendations';
	public const VECTOR_DB_PAGE       = 'wpengine-ai-toolkit&amp;view=vector-database';
	public const CHATKIT_PAGE         = 'wpengine-ai-toolkit&amp;view=chatkit';
	public const DOCUMENTATION_PAGE   = 'wpengine-ai-toolkit&amp;view=documentation';
	public const SEARCH_SETTINGS_PAGE = 'wpengine-ai-toolkit&amp;view=search-settings';

	private string $error = '';

	/**
	 * @var string[]|null
	 */
	private ?array $caps = null;

	public function __construct() {
		$capabilities = \Wpe_Content_Engine\Helper\CapabilitiesService::get_capabilities();

		if ( ! $capabilities instanceof WP_Error ) {
			$this->caps = $capabilities;

			return;
		}

		$this->error = $capabilities->get_error_message();
	}

	public function init_page() {
		add_action( 'admin_enqueue_scripts', [ $this, 'inject_script_data' ], 100 );
		add_action( 'admin_menu', [ $this, 'add_settings_menu' ] );
		add_filter( 'parent_file', [ $this, 'maybe_override_submenu_file' ] );
	}

	public function inject_script_data(): void {
		wp_localize_script(
			WPE_Atlas_Search_Settings_Page::SETTINGS_SCRIPT,
			'AITKCapabilities',
			[
				'capabilities' => $this->caps,
				'error'        => $this->error,
			]
		);

		wp_localize_script(
			WPE_Atlas_Search_Settings_Page::SETTINGS_SCRIPT,
			'AITKPluginName',
			[
				'name' => $this->resolve_plugin_name(),
			]
		);
	}

	public function add_settings_menu() {
		$icon        = include_once __DIR__ . '/views/smart-search-menu-icon.php';
		$plugin_name = $this->resolve_plugin_name();

		add_menu_page(
			esc_html( $plugin_name ),
			esc_html( $plugin_name ),
			'manage_options',
			self::PAGE,
			[ $this, 'render_settings_page' ],
			$icon
		);

		// In multisite (network-activated), site admins only see Recommendations.
		if ( is_wpe_smart_search_network_activated() ) {
			add_submenu_page(
				self::PAGE,
				'Recommendations',
				'Recommendations',
				'manage_options',
				self::RECOMMENDATIONS_PAGE,
				'__return_null',
				1
			);

			return;
		}

		// Single-site: platform-aware menu.
		if ( $this->has_extended_features() ) {
			// On-platform menu: Smart Search, Recommendations, Vector DB, ChatKit, Settings.
			add_submenu_page(
				self::PAGE,
				'Smart Search',
				'Smart Search',
				'manage_options',
				self::PAGE,
				'__return_null',
				1
			);

			add_submenu_page(
				self::PAGE,
				'Recommendations',
				'Recommendations',
				'manage_options',
				self::RECOMMENDATIONS_PAGE,
				'__return_null',
				2
			);

			add_submenu_page(
				self::PAGE,
				'Vector Database',
				'Vector Database',
				'manage_options',
				self::VECTOR_DB_PAGE,
				'__return_null',
				3
			);

			add_submenu_page(
				self::PAGE,
				'ChatKit',
				'ChatKit',
				'manage_options',
				self::CHATKIT_PAGE,
				'__return_null',
				4
			);

			add_submenu_page(
				self::PAGE,
				'Settings',
				'Settings',
				'manage_options',
				self::SETTINGS_PAGE,
				'__return_null',
				5
			);

			return;
		}

		// Off-platform menu: Dashboard (DashboardView), Search Settings, Recommendations, Documentation.
		add_submenu_page( self::PAGE, 'Dashboard', 'Dashboard', 'manage_options', self::PAGE, '__return_null', 1 );

		add_submenu_page(
			self::PAGE,
			'Search Settings',
			'Search Settings',
			'manage_options',
			self::SEARCH_SETTINGS_PAGE,
			'__return_null',
			2
		);

		add_submenu_page(
			self::PAGE,
			'Recommendations',
			'Recommendations',
			'manage_options',
			self::RECOMMENDATIONS_PAGE,
			'__return_null',
			3
		);

		add_submenu_page(
			self::PAGE,
			'Documentation',
			'Documentation',
			'manage_options',
			self::DOCUMENTATION_PAGE,
			'__return_null',
			4
		);
	}

	public function render_settings_page() {
		?>
		<div class="wpengine-smart-search-page">
			<div id='wpengine-ai-toolkit-root'></div>
		</div>
		<?php
	}

	/**
	 * Overrides the “submenu file” that determines which admin submenu item gains
	 * the `current` CSS class. Without this, WordPress incorrectly gives the
	 * “Model” subpage the `current` class when the “Taxonomies” subpage is active.
	 *
	 * @param string $parent_file The original parent file.
	 *
	 * @return string The $parent_file unaltered. Only the $submenu_file global is altered.
	 */
	public function maybe_override_submenu_file( $parent_file ) {
		global $submenu_file;

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$view = filter_input( INPUT_GET, 'view', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( self::PAGE === $page && ( null === $view || 'dashboard' === $view || 'smart-search' === $view ) ) {
			$submenu_file = self::PAGE; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
		}

		if ( self::PAGE === $page && 'search-settings' === $view ) {
			$submenu_file = self::SEARCH_SETTINGS_PAGE; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
		}

		if ( self::PAGE === $page && 'recommendations' === $view ) {
			$submenu_file = self::RECOMMENDATIONS_PAGE; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
		}

		if ( self::PAGE === $page && 'vector-database' === $view ) {
			$submenu_file = self::VECTOR_DB_PAGE; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
		}

		if ( self::PAGE === $page && 'chatkit' === $view ) {
			$submenu_file = self::CHATKIT_PAGE; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
		}

		if ( self::PAGE === $page && 'documentation' === $view ) {
			$submenu_file = self::DOCUMENTATION_PAGE; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
		}

		if ( self::PAGE === $page && 'settings' === $view ) {
			$submenu_file = self::SETTINGS_PAGE; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
		}

		return $parent_file;
	}

	private function has_extended_features(): bool {
		return is_array( $this->caps ) && in_array( 'EXTENDED_FEATURES', $this->caps, true );
	}

	private function resolve_plugin_name(): string {
		return $this->has_extended_features()
			? \WPE\AITK\WP\Plugin\Plugin_Name_Resolver::ON_PLATFORM_NAME
			: \WPE\AITK\WP\Plugin\Plugin_Name_Resolver::OFF_PLATFORM_NAME;
	}
}
