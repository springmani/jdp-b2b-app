<?php
/**
 * Fired during plugin activation
 *
 * @package    Wpe_Content_Engine
 *
 * @subpackage Wpe_Content_Engine/includes
 */

namespace Wpe_Content_Engine;

use Wpe_Content_Engine\Helper\Admin_Notice;
use Wpe_Content_Engine\Helper\Sync\Batches\Options\Batch_Options;

use function Wpe_Content_Engine\Helper\Notifications\get_index_page_url;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    Wpe_Content_Engine
 *
 * @subpackage Wpe_Content_Engine/includes
 */
class Wpe_Content_Engine_Activator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 */
	public static function activate() {
		\AtlasSearch\Support\WordPress\delete_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME );

		$settings = new WPSettings();

		if ( $settings ) {
			$admin_notice = new Admin_Notice();

			$page = get_index_page_url();

			$admin_notice->add_message(
				'<b>WP Engine Smart Search</b> requires a one-time data sync if you have made any modifications to your content'
					. ' or models. Please run sync in the <a href="admin.php?page=' . $page . '">'
					. 'Index Data</a> page.',
				Admin_Notice::NOTICE_TYPE_WARNING
			);
		}

		// Run config migration.
		// TODO: During activation config field option is empty.
		// Find a way to get the real value.
		\AtlasSearch\Migrations\rename_post_keys_config();
	}
}
