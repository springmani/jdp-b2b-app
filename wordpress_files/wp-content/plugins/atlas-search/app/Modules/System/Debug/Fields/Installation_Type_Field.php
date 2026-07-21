<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\System\Debug\Fields;

use WPE\AITK\WP\Multisite\Contracts\Multisite_Environment_Interface;
use WPE\AITK\WP\Site_Health\Debug_Info\Data\Abstract_Debug_Info_Field;

final class Installation_Type_Field extends Abstract_Debug_Info_Field {
	private Multisite_Environment_Interface $multisite;

	public function __construct( Multisite_Environment_Interface $multisite ) {
		$this->multisite = $multisite;
	}

	public function get_key(): string {
		return 'installation_type';
	}

	public function get_label(): string {
		return __( 'Installation type', 'wpe-aitk' );
	}

	public function get_value(): string {
		if ( ! $this->multisite->is_multisite() ) {
			return __( 'Single site', 'wpe-aitk' );
		}

		if ( $this->multisite->is_network_activated() ) {
			return __( 'Multisite — Network activated', 'wpe-aitk' );
		}

		return __( 'Multisite — Single site activated', 'wpe-aitk' );
	}
}
