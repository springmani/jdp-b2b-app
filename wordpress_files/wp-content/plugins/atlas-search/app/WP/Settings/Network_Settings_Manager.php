<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings;

final class Network_Settings_Manager extends Abstract_Settings_Manager {
	/**
	 * @inheritDoc
	 */
	protected function retrieve_option( string $option_name ) {
		return get_network_option( null, $option_name, null );
	}

	/**
	 * @inheritDoc
	 */
	protected function persist_option( string $option_name, array $data ): bool {
		return update_network_option( null, $option_name, $data );
	}

	protected function remove_option( string $option_name ): bool {
		return delete_network_option( null, $option_name );
	}

	protected function get_setting_label(): string {
		return 'Network setting';
	}
}
