<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @package           Wpe_Content_Engine
 *
 * @wordpress-plugin
 * Plugin Name:       WP Engine AI Toolkit
 * Plugin URI:        https://developers.wpengine.com/
 * Description:       Boost site conversions in just a few clicks with Smart Search AI, AI-Powered Recommendations, and Managed Vector Database.
 * Version:           0.3.25
 * Author:            WP Engine
 * Author URI:        https://wpengine.com/
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpengine-smart-search
 * Domain Path:       /languages
 */

use WPE\AITK\Core;
use WPE\AITK\Core\Filesystem\Filesystem_Definer;
use WPE\AITK\Core\Reflection\Reflection_Definer;
use WPE\AITK\Core\Timer\Timer_Definer;
use WPE\AITK\Core\Utils\Utils_Definer;
use WPE\AITK\Core\Zip\Zip_Definer;
use WPE\AITK\Modules\Block_Shortcode\Block_Shortcode_Subscriber;
use WPE\AITK\Modules\ChatKit\ChatKit_Definer;
use WPE\AITK\Modules\ChatKit\ChatKit_Subscriber;
use WPE\AITK\Modules\Cookie_Consent\Cookie_Consent_Definer;
use WPE\AITK\Modules\Cookie_Consent\Cookie_Consent_Subscriber;
use WPE\AITK\Modules\Smart_Attachment\Smart_Attachment_Subscriber;
use WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Custom_Search_Results_Subscriber;
use WPE\AITK\Modules\Smart_Search\Promotions\Promotions_Subscriber;
use WPE\AITK\Modules\Smart_Search\Recommendations\Recommendations_Definer;
use WPE\AITK\Modules\Smart_Search\Recommendations\Recommendations_Subscriber;
use WPE\AITK\Modules\Smart_Search\Smart_Search_Definer;
use WPE\AITK\Modules\Smart_Search\Smart_Search_Subscriber;
use WPE\AITK\Modules\Smart_Search\Sync\Sync_Subscriber;
use WPE\AITK\Modules\Smart_Search\Synonyms\Synonyms_Definer;
use WPE\AITK\Modules\Smart_Search\Synonyms\Synonyms_Subscriber;
use WPE\AITK\Modules\Support\Debug\Debug_Subscriber;
use WPE\AITK\Modules\System\System_Subscriber;
use WPE\AITK\WP\Ajax\Ajax_Definer;
use WPE\AITK\WP\Asset\Asset_Definer;
use WPE\AITK\WP\Capability\Capability_Definer;
use WPE\AITK\WP\Debug\Debug_Definer;
use WPE\AITK\WP\Filesystem\Filesystem_Definer as WP_Filesystem_Definer;
use WPE\AITK\WP\GraphQL\GraphQL_Definer;
use WPE\AITK\WP\Multisite\Multisite_Definer;
use WPE\AITK\WP\Nonce\Nonce_Definer;
use WPE\AITK\WP\Notice\Notice_Definer;
use WPE\AITK\WP\Plugin\Plugin_Definer;
use WPE\AITK\WP\Post\Post_Definer;
use WPE\AITK\WP\Rest\Rest_Definer;
use WPE\AITK\WP\Schema\Schema_Definer;
use WPE\AITK\WP\Script\Script_Definer;
use WPE\AITK\WP\Settings\Settings_Definer;
use WPE\AITK\WP\Site_Health\Site_Health_Definer;
use WPE\AITK\WP\User\User_Definer;
use Wpe_Content_Engine\Helper\Sync\GraphQL;
use Wpe_Content_Engine\WPSettings;
use Wpe_Content_Engine\Wpe_Content_Engine_Activator;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 * Todo: can be removed after migrating to new base app and utilizing it from plugin config.
 */
define( 'WPE_SMART_SEARCH_VERSION', '0.3.25' );
define( 'WPE_SMART_SEARCH_PATH', __FILE__ );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpe-content-engine-activator.php
 */
function activate_wpengine_smart_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpe-content-engine-activator.php';

	Wpe_Content_Engine_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpe-content-engine-deactivator.php
 */
function deactivate_wpengine_smart_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpe-content-engine-deactivator.php';
	Wpe_Content_Engine_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpengine_smart_search' );
register_deactivation_hook( __FILE__, 'deactivate_wpengine_smart_search' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpe-content-engine.php';

// Path definitions for the atlas search settings page.
define( 'WPE_SMART_SEARCH_SETTINGS_PAGE_PATH', plugin_dir_path( __FILE__ ) . '/includes/smart-search-settings/' );
define( 'WPE_SMART_SEARCH_SETTINGS_PAGE_URL', plugin_dir_url( __FILE__ ) . 'includes/smart-search-settings/' );
define( 'WPE_SMART_SEARCH_ASSET_MANIFEST', WPE_SMART_SEARCH_SETTINGS_PAGE_PATH . '/build/asset-manifest.json' );

require_once WPE_SMART_SEARCH_SETTINGS_PAGE_PATH . 'settings-callbacks.php';

require_once plugin_dir_path( __FILE__ ) . 'src/support/wordpress/utils.php';
define( 'SMART_SEARCH_FILE', plugin_basename( __FILE__ ) );

if ( \AtlasSearch\Support\WordPress\is_network_activated( SMART_SEARCH_FILE ) ) {
	$GLOBALS['is_smart_search_network_activated'] = true;
}

/**
 * @return bool .
 */
function is_wpe_smart_search_network_activated(): bool {
	return ! empty( $GLOBALS['is_smart_search_network_activated'] );
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_wpe_content_engine() {
	require_once plugin_dir_path( __FILE__ ) . 'settings-interface.php';
	require_once plugin_dir_path( __FILE__ ) . 'wp-settings.php';
	require_once plugin_dir_path( __FILE__ ) . 'helper/client-interface.php';
	require_once plugin_dir_path( __FILE__ ) . 'helper/sync/graphql/client.php';
	require_once plugin_dir_path( __FILE__ ) . 'helper/search/abstract-search-option-resolver.php';
	require_once plugin_dir_path( __FILE__ ) . 'helper/search/promotions-resolver.php';
	require_once plugin_dir_path( __FILE__ ) . 'helper/search/custom-search-results-resolver.php';

	$client = new GraphQL\Client( Wpe_Content_Engine::get_plugin_name(), Wpe_Content_Engine::get_version() );
	$plugin = new Wpe_Content_Engine( new WPSettings(), $client );
	$plugin->run();
}

run_wpe_content_engine();

/**
 * Load the Composer autoloader.
 */
$autoloader = __DIR__ . '/vendor/autoload.php';

if ( ! file_exists( $autoloader ) ) {
	add_action(
		'admin_notices',
		static function () {
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'WP Engine AI Toolkit: Plugin dependencies are missing.', 'wpengine-smart-search' );
			echo esc_html__( 'Please deactivate and reinstall the plugin.', 'wpengine-smart-search' );
			echo '</p></div>';
		}
	);

	return;
}

require_once $autoloader;

// Init plugin on the 'plugins_loaded' action.
add_action(
	'plugins_loaded',
	/**
	 * @throws \WPE\AITK\Core\Container\Exceptions\Container_Creation_Exception
	 * @throws \Psr\Container\ContainerExceptionInterface
	 */
	static function () {
		Core::instance()->init( [
			Ajax_Definer::class,
			Asset_Definer::class,
			Capability_Definer::class,
			ChatKit_Definer::class,
			Cookie_Consent_Definer::class,
			Filesystem_Definer::class,
			Debug_Definer::class,
			WP_Filesystem_Definer::class,
			Zip_Definer::class,
			GraphQL_Definer::class,
			Multisite_Definer::class,
			Nonce_Definer::class,
			Notice_Definer::class,
			Plugin_Definer::class,
			Post_Definer::class,
			Recommendations_Definer::class,
			Reflection_Definer::class,
			Rest_Definer::class,
			Schema_Definer::class,
			Script_Definer::class,
			Settings_Definer::class,
			Site_Health_Definer::class,
			Smart_Search_Definer::class,
			Synonyms_Definer::class,
			Timer_Definer::class,
			User_Definer::class,
			Utils_Definer::class,
		], [
			Block_Shortcode_Subscriber::class,
			ChatKit_Subscriber::class,
			Cookie_Consent_Subscriber::class,
			Custom_Search_Results_Subscriber::class,
			Debug_Subscriber::class,
			Promotions_Subscriber::class,
			Recommendations_Subscriber::class,
			Smart_Attachment_Subscriber::class,
			Smart_Search_Subscriber::class,
			Sync_Subscriber::class,
			Synonyms_Subscriber::class,
			System_Subscriber::class,
		] );
	}
);
