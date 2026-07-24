<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings;

final class Settings_Manager extends Abstract_Settings_Manager {
	/**
	 * @inheritDoc
	 */
	protected function retrieve_option( string $option_name ) {
		return get_option( $option_name, null );
	}

	/**
	 * @inheritDoc
	 */
	protected function persist_option( string $option_name, array $data ): bool {
		return update_option( $option_name, $data );
	}

	protected function remove_option( string $option_name ): bool {
		return delete_option( $option_name );
	}

	protected function get_setting_label(): string {
		return 'Setting';
	}
}
