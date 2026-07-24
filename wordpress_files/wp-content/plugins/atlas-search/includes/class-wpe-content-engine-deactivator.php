<?php
/**
 * Fired during plugin deactivation
 *
 * @package    Wpe_Content_Engine
 *
 * @subpackage Wpe_Content_Engine/includes
 */

use Wpe_Content_Engine\Helper\Sync\Batches\Options\Batch_Options;
use Wpe_Content_Engine\Helper\Sync\Batches\Sync_Lock_Manager;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @package    Wpe_Content_Engine
 *
 * @subpackage Wpe_Content_Engine/includes
 */
class Wpe_Content_Engine_Deactivator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 */
	public static function deactivate() {
		\AtlasSearch\Support\WordPress\delete_option( Batch_Options::OPTIONS_WPE_CONTENT_ENGINE_SYNC_RESUME );
		( new Sync_Lock_Manager() )->clear_status();
	}
}
