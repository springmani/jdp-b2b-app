<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Site_Health\Test;

use WPE\AITK\WP\Plugin\Contracts\Plugin_Name_Resolver_Interface;
use WPE\AITK\WP\Site_Health\Test\Data\Test_Result;

final class Test_Result_Formatter {
	private const BADGE_COLOR_BLUE   = 'blue';
	private const BADGE_COLOR_ORANGE = 'orange';
	private const BADGE_COLOR_RED    = 'red';

	private Plugin_Name_Resolver_Interface $name_resolver;

	public function __construct( Plugin_Name_Resolver_Interface $name_resolver ) {
		$this->name_resolver = $name_resolver;
	}

	/**
	 * @return array{
	 *     label: string,
	 *     status: string,
	 *     badge: array{label: string, color: string},
	 *     description: string,
	 *     actions: string,
	 *     test: string
	 * }
	 */
	public function format( Test_Result $result ): array {
		return [
			'actions'     => $result->get_actions(),
			'badge'       => [
				'color' => $this->get_badge_color( $result ),
				'label' => $this->name_resolver->resolve(),
			],
			'description' => $result->get_description(),
			'label'       => $result->get_label(),
			'status'      => $result->get_status(),
			'test'        => $result->get_test(),
		];
	}

	private function get_badge_color( Test_Result $result ): string {
		switch ( $result->get_status() ) {
			case Test_Result::GOOD:
				return self::BADGE_COLOR_BLUE;
			case Test_Result::RECOMMENDED:
				return self::BADGE_COLOR_ORANGE;
			case Test_Result::CRITICAL:
			default:
				return self::BADGE_COLOR_RED;
		}
	}
}
