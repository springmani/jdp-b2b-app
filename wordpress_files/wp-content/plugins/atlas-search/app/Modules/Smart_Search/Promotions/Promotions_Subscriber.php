<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Promotions;

use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\Modules\Smart_Search\Promotions\Data\Search_Promotion_Data as Data;
use WPE\AITK\WP\Settings\Settings_Manager;

final class Promotions_Subscriber extends Hook_Subscriber {
	public const OPTION = 'wpe_aitk/smart_search/search_promotions';

	public function register(): void {
		// Register promotions setting.
		$this->container->get( Settings_Manager::class )->add( self::OPTION, Data::class );
	}
}
