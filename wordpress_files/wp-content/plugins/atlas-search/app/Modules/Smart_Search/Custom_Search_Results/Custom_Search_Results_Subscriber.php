<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Custom_Search_Results;

use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Data\Custom_Search_Results_Data as Data;
use WPE\AITK\WP\Settings\Settings_Manager;

final class Custom_Search_Results_Subscriber extends Hook_Subscriber {
	public const OPTION = 'wpe_aitk/smart_search/custom_search_results';

	public function register(): void {
		// Register CSR setting.
		$this->container->get( Settings_Manager::class )->add( self::OPTION, Data::class );
	}
}
