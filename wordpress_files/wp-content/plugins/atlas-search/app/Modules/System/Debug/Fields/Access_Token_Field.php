<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\System\Debug\Fields;

use WPE\AITK\Modules\System\Data\Content_Engine_Connection;
use WPE\AITK\WP\Site_Health\Debug_Info\Data\Abstract_Debug_Info_Field;

final class Access_Token_Field extends Abstract_Debug_Info_Field {
	private Content_Engine_Connection $connection;

	public function __construct( Content_Engine_Connection $connection ) {
		$this->connection = $connection;
	}

	public function get_key(): string {
		return 'access_token_set';
	}

	public function get_label(): string {
		return __( 'Access token set', 'wpe-aitk' );
	}

	public function get_value(): string {
		return ! empty( $this->connection->get_access_token() )
			? __( 'Yes', 'wpe-aitk' )
			: __( 'No', 'wpe-aitk' );
	}

	public function is_private(): bool {
		return true;
	}
}
