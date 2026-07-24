<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\ChatKit;

use WPE\AITK\Core\DTO\Array_Converter;
use WPE\AITK\Core\DTO\Contracts\Array_Constructible_Interface;
use WPE\AITK\WP\Ajax\Data\Base_Ajax_Response_Data;

/**
 * ChatKit Settings Value Object.
 *
 * Provides strict typing and serialization for ChatKit configuration settings.
 * Extends Base_Ajax_Response_Data to be used directly in AJAX responses.
 */
final class ChatKit_Settings extends Base_Ajax_Response_Data implements Array_Constructible_Interface {
	/**
	 * The ChatKit API key.
	 *
	 * @var string
	 */
	private string $api_key;

	/**
	 * Whether ChatKit is enabled.
	 *
	 * @var bool
	 */
	private bool $enabled;

	/**
	 * Whether the floating action button (FAB) is enabled.
	 *
	 * @var bool
	 */
	private bool $enable_fab;

	/**
	 * The ChatKit workflow ID.
	 *
	 * @var string
	 */
	private string $workflow_id;

	/**
	 * Constructor.
	 *
	 * @param string $api_key     The ChatKit API key.
	 * @param bool   $enabled     Whether ChatKit is enabled.
	 * @param bool   $enable_fab  Whether the FAB is enabled.
	 * @param string $workflow_id The ChatKit workflow ID.
	 */
	public function __construct(
		string $api_key = '',
		bool $enabled = false,
		bool $enable_fab = false,
		string $workflow_id = ''
	) {
		$this->api_key     = $api_key;
		$this->enabled     = $enabled;
		$this->enable_fab  = $enable_fab;
		$this->workflow_id = $workflow_id;
	}

	/**
	 * Create a ChatKit_Settings instance from an array.
	 *
	 * @param array<string, scalar> $data The settings data.
	 */
	public static function from_array( array $data ): self {
		return new self(
			Array_Converter::safe_string( $data, 'wpe_chatkit_api_key' ),
			Array_Converter::safe_bool( $data, 'wpe_chatkit_enabled' ),
			Array_Converter::safe_bool( $data, 'wpe_chatkit_enable_fab' ),
			Array_Converter::safe_string( $data, 'wpe_chatkit_workflow_id' )
		);
	}

	/**
	 * Create a ChatKit_Settings instance from POST data with sanitization.
	 */
	public static function from_post_data(): self {
		$data = [
			'wpe_chatkit_api_key'     => filter_input(
				INPUT_POST,
				'wpe_chatkit_api_key',
				FILTER_SANITIZE_FULL_SPECIAL_CHARS
			),
			'wpe_chatkit_enabled'     => filter_input(
				INPUT_POST,
				'wpe_chatkit_enabled',
				FILTER_SANITIZE_FULL_SPECIAL_CHARS
			),
			'wpe_chatkit_enable_fab'  => filter_input(
				INPUT_POST,
				'wpe_chatkit_enable_fab',
				FILTER_SANITIZE_FULL_SPECIAL_CHARS
			),
			'wpe_chatkit_workflow_id' => filter_input(
				INPUT_POST,
				'wpe_chatkit_workflow_id',
				FILTER_SANITIZE_FULL_SPECIAL_CHARS
			),
		];

		// Convert '1' string to boolean true, everything else to false.
		$data['wpe_chatkit_enabled']    = '1' === $data['wpe_chatkit_enabled'];
		$data['wpe_chatkit_enable_fab'] = '1' === $data['wpe_chatkit_enable_fab'];

		return self::from_array( $data );
	}

	/**
	 * Load settings from WordPress options.
	 */
	public static function from_options(): self {
		$data = get_option( 'wpe_chatkit_settings', [] );

		// Support legacy individual options for backward compatibility.
		if ( empty( $data ) ) {
			$data = [
				'wpe_chatkit_api_key'     => get_option( 'wpe_chatkit_api_key', '' ),
				'wpe_chatkit_enabled'     => get_option( 'wpe_chatkit_enabled', false ),
				'wpe_chatkit_enable_fab'  => get_option( 'wpe_chatkit_enable_fab', false ),
				'wpe_chatkit_workflow_id' => get_option( 'wpe_chatkit_workflow_id', '' ),
			];
		}

		return self::from_array( $data );
	}

	/**
	 * Get the API key.
	 */
	public function get_api_key(): string {
		return $this->api_key;
	}

	/**
	 * Get the enabled status.
	 */
	public function is_enabled(): bool {
		return $this->enabled;
	}

	/**
	 * Get the FAB enabled status.
	 */
	public function is_fab_enabled(): bool {
		return $this->enable_fab;
	}

	/**
	 * Get the workflow ID.
	 */
	public function get_workflow_id(): string {
		return $this->workflow_id;
	}

	/**
	 * Convert the settings to an array.
	 *
	 * @return array<string, bool|string>
	 */
	public function to_array(): array {
		return [
			'wpe_chatkit_api_key'     => $this->api_key,
			'wpe_chatkit_enabled'     => $this->enabled,
			'wpe_chatkit_enable_fab'  => $this->enable_fab,
			'wpe_chatkit_workflow_id' => $this->workflow_id,
		];
	}

	/**
	 * Save settings to WordPress options.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function save(): bool {
		return update_option( 'wpe_chatkit_settings', $this->to_array() );
	}
}
