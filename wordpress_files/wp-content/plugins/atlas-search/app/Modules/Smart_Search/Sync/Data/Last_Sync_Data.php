<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Sync\Data;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\WP\Settings\Contracts\Setting_Interface;

/**
 * Last Sync Data Transfer Object.
 *
 * Represents the status and timestamp of the most recent sync operation.
 */
final class Last_Sync_Data implements Setting_Interface {
	/**
	 * Whether the last sync was successful.
	 *
	 * @var bool
	 */
	private bool $success;

	/**
	 * ISO 8601 formatted date of the last sync.
	 *
	 * @var string
	 */
	private string $date;

	/**
	 * Constructor.
	 *
	 * @param bool   $success Whether the last sync was successful.
	 * @param string $date    ISO 8601 formatted date of the last sync.
	 */
	public function __construct( bool $success = false, string $date = '' ) {
		$this->success = $success;
		$this->date    = $date;
	}

	/**
	 * Create a successful sync record.
	 *
	 * @param string $date ISO 8601 formatted date.
	 */
	public static function create_success( string $date ): self {
		return new self( true, $date );
	}

	/**
	 * Create a failed sync record.
	 *
	 * @param string $date ISO 8601 formatted date.
	 */
	public static function create_failure( string $date ): self {
		return new self( false, $date );
	}

	/**
	 * Create instance from array.
	 *
	 * @param array<string, bool|string> $data Array with 'success' and 'date' keys.
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_bool( $data, 'success', false ),
			Array_Converter::safe_string( $data, 'date', '' )
		);
	}

	/**
	 * Convert to array.
	 *
	 * @return array<string, bool|string>
	 */
	public function to_array(): array {
		return [
			'date'    => $this->date,
			'success' => $this->success,
		];
	}

	/**
	 * Check if sync was successful.
	 */
	public function is_successful(): bool {
		return $this->success;
	}

	/**
	 * Get sync date.
	 *
	 * @return string ISO 8601 formatted date.
	 */
	public function get_date(): string {
		return $this->date;
	}
}
