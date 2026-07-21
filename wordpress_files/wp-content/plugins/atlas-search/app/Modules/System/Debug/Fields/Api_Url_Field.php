<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\System\Debug\Fields;

use WPE\AITK\Modules\System\Data\Content_Engine_Connection;
use WPE\AITK\WP\Site_Health\Debug_Info\Data\Abstract_Debug_Info_Field;

final class Api_Url_Field extends Abstract_Debug_Info_Field {
	private Content_Engine_Connection $connection;

	public function __construct( Content_Engine_Connection $connection ) {
		$this->connection = $connection;
	}

	public function get_key(): string {
		return 'api_url_configured';
	}

	public function get_label(): string {
		return __( 'API URL configured', 'wpe-aitk' );
	}

	public function get_value(): string {
		return ! empty( $this->connection->get_sync_url() )
			? __( 'Yes', 'wpe-aitk' )
			: __( 'No', 'wpe-aitk' );
	}

	public function get_debug(): string {
		$url = $this->connection->get_sync_url();

		return ! empty( $url ) ? $url : __( 'Not set', 'wpe-aitk' );
	}
}
