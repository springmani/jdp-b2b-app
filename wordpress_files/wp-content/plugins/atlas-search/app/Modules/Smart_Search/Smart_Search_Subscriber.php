<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search;

use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\Modules\Smart_Search\Debug\Section;
use WPE\AITK\Modules\Smart_Search\Search_Item\Search_Item_Cleanup_Handler;
use WPE\AITK\Modules\Smart_Search\Tests\Index_Test;
use WPE\AITK\WP\Site_Health\Debug_Info\Debug_Info_Registry;
use WPE\AITK\WP\Site_Health\Test\Test_Registry;
use Wpe_Content_Engine\Helper\Sync\GraphQL\Client;

/**
 * Smart Search Module Subscriber.
 *
 * Handles registration and initialization of smart search features.
 */
final class Smart_Search_Subscriber extends Hook_Subscriber {
	public function register(): void {
		$cleanup_handler = $this->container->get( Search_Item_Cleanup_Handler::class );

		add_action( 'delete_post', [ $cleanup_handler, 'on_post_deleted' ] );
		add_action( 'transition_post_status', [ $cleanup_handler, 'on_post_status_changed' ], 10, 3 );

		$this->container->get( Test_Registry::class )->add(
			new Index_Test( new Client( 'wpe-content-engine', WPE_SMART_SEARCH_VERSION ) )
		);

		$this->container->get( Debug_Info_Registry::class )->add( new Section() );
	}
}
