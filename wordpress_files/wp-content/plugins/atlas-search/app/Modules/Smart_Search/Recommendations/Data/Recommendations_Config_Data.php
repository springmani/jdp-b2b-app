<?php
// phpcs:disable WordPress.NamingConventions.ValidVariableName


declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Recommendations\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\WP\Rest\Contracts\Rest_Request_Interface;
use WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface;
use WPE\AITK\WP\Settings\Contracts\Setting_Interface;

/**
 * Recommendations Configuration Data.
 *
 * Represents the configuration for recommendations feature including analytics tracking settings.
 */
final class Recommendations_Config_Data implements Setting_Interface, Rest_Request_Interface, Rest_Response_Interface {
	/**
	 * Expected configuration version.
	 */
	public const EXPECTED_VERSION = 1;

	/**
	 * @var bool
	 */
	private bool $analyticsTrackingEnabled;

	private int $version;

	public function __construct( bool $analyticsTrackingEnabled = true, int $version = self::EXPECTED_VERSION ) {
		$this->analyticsTrackingEnabled = $analyticsTrackingEnabled;
		$this->version                  = $version;
	}

	/**
	 * Create from array.
	 *
	 * @param array<string, array<string, string|bool|int>> $data Configuration data as array.
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_bool( $data, 'analyticsTrackingEnabled', true ),
			Array_Converter::safe_int( $data, 'version', self::EXPECTED_VERSION )
		);
	}

	public function is_analytics_enabled(): bool {
		return $this->analyticsTrackingEnabled;
	}

	public function get_version(): int {
		return $this->version;
	}

	/**
	 * @inheritDoc
	 */
	public function to_array(): array {
		return [
			'analyticsTrackingEnabled' => $this->analyticsTrackingEnabled,
			'version'                  => $this->version,
		];
	}
}
