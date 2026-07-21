<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Sync;

use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\Modules\Smart_Search\Sync\Data\Last_Sync_Data;
use WPE\AITK\WP\Settings\Settings_Manager;

/**
 * Sync Subscriber.
 *
 * Handles registration of sync-related settings.
 */
final class Sync_Subscriber extends Hook_Subscriber {
	/**
	 * Option name for last sync data.
	 */
	public const OPTION = 'wpe_aitk/smart_search/sync/last_sync';

	/**
	 * Register sync settings.
	 */
	public function register(): void {
		$this->container->get( Settings_Manager::class )->add( self::OPTION, Last_Sync_Data::class );
	}
}
