<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @package    Wpe_Content_Engine
 *
 * @subpackage Wpe_Content_Engine/includes
 */

use Wpe_Content_Engine\Helper\API\Content_Generation_Controller;
use Wpe_Content_Engine\Helper\API\Post_Types_Controller;
use Wpe_Content_Engine\Helper\API\Range_Facet_Controller;
use Wpe_Content_Engine\Helper\API\Search_Config_Controller;
use Wpe_Content_Engine\Helper\API\Semantic_Search_Controller;
use Wpe_Content_Engine\Helper\API\Settings_Controller;
use Wpe_Content_Engine\Helper\API\Sync_Data\Sync_Data_Controller;
use Wpe_Content_Engine\Helper\API\Taxonomies_Controller;
use Wpe_Content_Engine\Helper\Admin_Notice;
use Wpe_Content_Engine\Helper\Constants\Security;
use Wpe_Content_Engine\Helper\Logging\Debug_Logger;
use Wpe_Content_Engine\Helper\Search\Search;
use Wpe_Content_Engine\Helper\Search\Search_Config;
use Wpe_Content_Engine\Helper\Sync\GraphQL\Client;
use Wpe_Content_Engine\Settings_Interface;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package    Wpe_Content_Engine
 *
 * @subpackage Wpe_Content_Engine/includes
 */
class Wpe_Content_Engine {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @var      \Wpe_Content_Engine_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * @var \Wpe_Content_Engine\Settings_Interface $settings Settings.
	 */
	protected Settings_Interface $settings;

	/**
	 * @var \Wpe_Content_Engine\Helper\Sync\GraphQL\Client $client Sync API Client.
	 */
	protected Client $client;

	/**
	 * @var \Wpe_Content_Engine\Helper\Search\Search_Config
	 */
	private Search_Config $search_config;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @param \Wpe_Content_Engine\Settings_Interface         $settings Settings.
	 * @param \Wpe_Content_Engine\Helper\Sync\GraphQL\Client $client API Client.
	 */
	public function __construct( Settings_Interface $settings, Client $client ) {
		$this->client   = $client;
		$this->settings = $settings;

		$this->load_dependencies();
		$this->set_locale();
		$this->search_config = new Search_Config();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->register_blocks();

		if ( \Wpe_Content_Engine\Helper\CapabilitiesService::has_capability( 'RECOMMENDATIONS' ) ) {
			$this->register_trackers();
		}
	}

	public function register_blocks() {
		$facets = new \AtlasSearch\Blocks\WPE_Atlas_Search_Facets_Callbacks( $this->loader );
		$facets->init();
	}

	public function register_trackers() {
		$logger   = new Debug_Logger();
		$trackers = new \AtlasSearch\Trackers\WPE_Atlas_Search_Tracker_Controller( $this->loader, $logger );
		$trackers->init();
	}

	/**
	 * ORN-205 We were asked to disable delete hooks for posts. We can talk about it post Q2
	 *
	 * @param int           $post_id Post ID.
	 * @param \WP_Post      $post WP Post.
	 * @param bool          $update Not used.
	 * @param \WP_Post|null $post_before Previous post.
	 */
	public function post_upsert_handler( int $post_id, WP_Post $post, bool $update, ?WP_Post $post_before ) {
		$should_be_indexed_old_status = isset( $post_before ) && \AtlasSearch\Index\should_be_indexed( $post_before );
		$should_be_indexed_new_status = \AtlasSearch\Index\should_be_indexed( $post );

		\AtlasSearch\Index\admin_notice_handler(
			static function () use ( $should_be_indexed_old_status, $should_be_indexed_new_status, $post ) {
				if ( $should_be_indexed_new_status ) {
					\AtlasSearch\Index\index_post( $post, $post->ID );
				}

				if ( $should_be_indexed_old_status && ! $should_be_indexed_new_status ) {
					\AtlasSearch\Index\delete_post( $post, $post->ID );
				}
			}
		);

		$this->search_config->get_config();
	}

	public function post_delete_handler( int $post_id, WP_Post $post ) {
		\AtlasSearch\Index\admin_notice_handler(
			static function () use ( $post_id, $post ) {
				if ( \AtlasSearch\Index\should_be_indexed( $post ) ) {
					\AtlasSearch\Index\delete_post( $post, $post_id );
				}
			}
		);

		$this->search_config->get_config();
	}

	public function block_editor_notices_callback() {
		check_ajax_referer( 'ajax-nonce', 'security' );
		$admin_notices = new Admin_Notice();
		$messages      = $admin_notices->get_messages();

		header( 'Content-Type: application/json' );
		echo wp_json_encode(
			[
				'hasError' => ! empty( $messages ),
				'message'  => $messages,
			]
		);
		die;
	}

	public function post_notices() {
		wp_enqueue_script(
			'display-post-notice',
			plugins_url( '../public/js/display-post-notice.js', __FILE__ ),
			[ 'jquery', 'wp-editor' ],
			$this->get_version()
		);

		wp_localize_script( 'display-post-notice', 'ajax_var', [ 'nonce' => wp_create_nonce( 'ajax-nonce' ) ] );
	}

	/**
	 * Run the loader to execute all  the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Handle re-sync notification.
	 * Called on admin_init hook to ensure wp_get_current_user() is available.
	 *
	 * @return void
	 */
	public function handle_re_sync_notification(): void {
		\Wpe_Content_Engine\Helper\Notifications\handle_re_sync_notification( new Admin_Notice() );
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    \Wpe_Content_Engine_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	public function add_custom_rest_headers( $served ) {
		foreach ( Security::HEADERS as $header ) {
			header( $header );
		}

		return $served;
	}

	public function enqueue_user_session_store_script() {
		wp_enqueue_script(
			'user-session-store',
			plugins_url( '../public/js/user-session-store.js', __FILE__ ),
			[],
			$this->get_version(),
			true
		);

		// Pass acknowledgement status to JavaScript.
		wp_localize_script(
			'user-session-store',
			'userSessionConfig',
			[
				'isAcknowledged' => ( get_option(
					'wpe_aitk_data_collection_acknowledged'
				) || ( is_multisite() && get_network_option(
					null,
					'wpe_aitk_network_data_collection_acknowledged'
				) ) ) ? 'true' : 'false',
			]
		);
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 */
	public static function get_plugin_name() {
		return 'atlas-search';
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 */
	public static function get_version() {
		return defined( 'WPE_SMART_SEARCH_VERSION' ) ? WPE_SMART_SEARCH_VERSION : '1.0.0';
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wpe_Content_Engine_Loader. Orchestrates the hooks of the plugin.
	 * - Wpe_Content_Engine_I18n. Defines internationalization functionality.
	 * - Wpe_Content_Engine_Admin. Defines all hooks for the admin area.
	 * - Wpe_Content_Engine_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 */
	private function load_dependencies() {
		/**
		 * Load Core WP plugin functions
		 */
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-wpe-content-engine-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-wpe-content-engine-i18n.php';

		$this->loader = new Wpe_Content_Engine_Loader();

		/**
		 * Helper classes used to batch sync data
		 */
		require_once plugin_dir_path( __DIR__ ) . 'settings-interface.php';
		require_once plugin_dir_path( __DIR__ ) . 'core-wp-wrapper/wp-progress-bar.php';
		require_once plugin_dir_path( __DIR__ ) . 'wp-settings.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/capabilities-service.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/string-transformation.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/progress-bar-info-trait.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/constants/batch-sync-type-names.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/constants/security.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/constants/json-schema-type.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/constants/order.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/constants/order-by.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/constants/capability.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/constants/post-status.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/constants/sync-response-status.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/constants/post-type.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/logging/debug-logger.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/logging/server-log-info.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/json-schema/property.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/json-schema/json-schema.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/json-schema/primitive-type-property.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/json-schema/number-property.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/json-schema/integer-property.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/json-schema/boolean-property.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/json-schema/string-property.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/json-schema/array-property.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/acf-support/acf-functions.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/acf-support/acf.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/acf-support/acf-factory.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/acf-support/types/abstract-type.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/acf-support/types/number.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/acf-support/types/text.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/acf-support/types/email.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/client-interface.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/graphql/client.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/batches/sync-interface.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/multisite-network-sync.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/batches/custom-post-type.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/batches/post.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/batches/page.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/batches/options/progress.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/batches/options/batch-options.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/batches/options/resume-options.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/batches/options/sync-lock-status.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/batches/options/sync-lock-state.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/batches/batch-sync-factory.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/sync/batches/sync-lock-manager.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/admin-notice.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/nonce.php';

		require_once plugin_dir_path( __DIR__ ) . 'helper/search/search.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/search/search-config.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/api/search-config-controller.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/search/config/configurable.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/search/config/post-config.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/exceptions/client-query-exception.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/exceptions/missing-settings-exception.php';

		/**
		 * API Imports
		 */
		require_once plugin_dir_path( __DIR__ ) . 'helper/api/settings-controller.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/api/taxonomies-controller.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/api/post-types-controller.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/api/range-facet-controller.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/api/sync-data/response.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/api/sync-data/sync-data-controller.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/api/semantic-search-controller.php';
		require_once plugin_dir_path( __DIR__ ) . 'helper/api/content-generation-controller.php';

		/**
		 * Settings page class
		 */
		require_once WPE_SMART_SEARCH_SETTINGS_PAGE_PATH . 'settings-callbacks.php';
		require_once WPE_SMART_SEARCH_SETTINGS_PAGE_PATH . 'ai-toolkit-settings-page.php';

		/**
		 * Notifications
		 */
		require_once plugin_dir_path( __DIR__ ) . 'helper/notifications/re-sync-notifier.php';


		/**
		 * WP CLI Commands
		 */
		require_once plugin_dir_path( __DIR__ ) . 'commands/class-wpe-content-engine-sync-data.php';

		/**
		 * Src imports
		 */
		require_once plugin_dir_path( __DIR__ ) . 'src/meta.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/post.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/semantic-search.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/batch.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/hooks/filters.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/migrations/rename-post-keys-config.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/support/wp-graphql/pagination.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/support/wp-graphql/filters.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/support/wordpress/page.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/support/wordpress/multisite.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/support/wordpress/options.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/support/wordpress/transients.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/query/meta.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/query/taxonomy.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/query/date.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/blocks/wordpress/filter.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/blocks/wordpress/post-type.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/blocks/range-facet/range-facet-functions.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/support/woocommerce/filters.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/support/woocommerce/fields.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/support/woocommerce/queries.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/blocks/support/woocommerce/callbacks.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/blocks/blocks-callbacks.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/blocks/recommendations-block/query-loop.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/blocks/recommendations-block/function.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/blocks/wordpress/taxonomy.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/support/fusion/filters.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/media/generators/content-generator-interface.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/media/generators/image-content-generator.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/media/generators/pdf-content-generator.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/media/content-generator.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/media/extractors/media-extractor-interface.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/media/extractors/gutenberg-block-extractor.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/media/extractors/classic-editor-extractor.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/media/extractors/gallery-shortcode-extractor.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/media/media-id-extractor.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/index/media/media.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/support/gravitysmtp/filters.php';
		require_once plugin_dir_path( __DIR__ ) . 'src/trackers/tracker-controller.php';

		require_once plugin_dir_path( __DIR__ ) . 'app/WP/Context/Contracts/Context_Interface.php';
		require_once plugin_dir_path( __DIR__ ) . 'app/WP/Context/Post_Context.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wpe_Content_Engine_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 */
	private function set_locale() {
		$plugin_i18n = new Wpe_Content_Engine_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all  the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks() {
		$settings_page = new WPE_Atlas_Search_Settings_Page( $this->loader );
		$settings_page->init_page();

		if ( ! is_wpe_smart_search_network_activated() ) {
			( new AI_Toolkit_Settings_Page() )->init_page();
		}

		$this->loader->add_action( 'enqueue_block_editor_assets', $this, 'post_notices', 9 );
		$this->loader->add_action( 'wp_ajax_block_editor_notices', $this, 'block_editor_notices_callback', 9 );

		// Register admin message handling.
		$this->loader->add_action( 'admin_notices', new Admin_Notice(), 'show_messages', 10, 0 );

		// Handle re-sync notification on admin_init to ensure wp_get_current_user() is available.
		$this->loader->add_action( 'admin_init', $this, 'handle_re_sync_notification', 10, 0 );

		// Post hooks.
		$this->loader->add_action( 'wp_after_insert_post', $this, 'post_upsert_handler', 10, 4 );
		$this->loader->add_action( 'delete_post', $this, 'post_delete_handler', 10, 2 );

		// REST API.
		$this->loader->add_action( 'rest_api_init', new Settings_Controller(), 'register_routes', 10, 0 );
		$this->loader->add_action( 'rest_api_init', new Taxonomies_Controller(), 'register_routes', 10, 0 );
		$this->loader->add_action( 'rest_api_init', new Post_Types_Controller(), 'register_routes', 10, 0 );
		$this->loader->add_action( 'rest_api_init', new Range_Facet_Controller(), 'register_routes', 10, 0 );

		// Search API.
		$this->loader->add_action( 'rest_api_init', new Search_Config_Controller(), 'register_routes', 10, 0 );

		$this->loader->add_action(
			'rest_api_init',
			new Sync_Data_Controller( $this->client, $this->settings ),
			'register_routes',
			10,
			0
		);

		$this->loader->add_action(
			'rest_api_init',
			new Semantic_Search_Controller(),
			'register_routes',
			10,
			0
		);

		$this->loader->add_action(
			'rest_api_init',
			new Content_Generation_Controller(),
			'register_routes',
			10,
			0
		);


		$this->loader->add_filter( 'rest_pre_serve_request', $this, 'add_custom_rest_headers', 10, 2 );
	}

	/**
	 * Register all the hooks related to the public-facing functionality of the plugin.
	 */
	private function define_public_hooks() {
		$this->register_search_hooks();
	}

	/**
	 * Register search-related hooks if the SEARCH capability is available.
	 */
	private function register_search_hooks() {
		if ( ! \Wpe_Content_Engine\Helper\CapabilitiesService::has_capability( 'SEARCH' ) ) {
			return;
		}

		$search = new Search(
			$this->client,
			$this->settings,
			new Search_Config(),
			new Debug_Logger()
		);

		$this->loader->add_filter( 'posts_pre_query', $search, 'get_ce_posts', 100, 2 );
		$this->loader->add_filter( 'found_posts', $search, 'found_posts', 100, 2 );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_user_session_store_script' ] );
	}
}
