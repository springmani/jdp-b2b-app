<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Test\Data;

use WPE\AITK\WP\Rest\Contracts\Rest_Response_Interface;
use WPE\AITK\WP\Schema\Contracts\Schema_Provider_Interface;

final class Test_Response implements Rest_Response_Interface, Schema_Provider_Interface {
	/**
	 * @var array{label: string, status: string, badge: array{label: string, color: string}, description: string, actions: string, test: string}
	 */
	private array $data;

	/**
	 * @param array{label: string, status: string, badge: array{label: string, color: string}, description: string, actions: string, test: string} $data
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * @inheritDoc
	 */
	public function to_array(): array {
		return $this->data;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_property_schemas(): array {
		return [
			'data' => [
				'properties' => [
					'actions'     => [ 'type' => 'string' ],
					'badge'       => [
						'properties' => [
							'color' => [ 'type' => 'string' ],
							'label' => [ 'type' => 'string' ],
						],
						'type'       => 'object',
					],
					'description' => [ 'type' => 'string' ],
					'label'       => [ 'type' => 'string' ],
					'status'      => [ 'type' => 'string' ],
					'test'        => [ 'type' => 'string' ],
				],
				'type'       => 'object',
			],
		];
	}
}
