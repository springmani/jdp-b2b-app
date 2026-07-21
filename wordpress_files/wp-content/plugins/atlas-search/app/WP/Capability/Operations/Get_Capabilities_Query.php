<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Capability\Operations;

use WPE\AITK\WP\GraphQL\Abstract_GraphQL_Query;

final class Get_Capabilities_Query extends Abstract_GraphQL_Query {
	private const QUERY = '
		query GetCapabilities {
			capabilities
		}
	';

	protected function query(): string {
		return self::QUERY;
	}

	protected function response_path(): string {
		return '';
	}
}
