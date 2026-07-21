<?php

declare(strict_types=1);

namespace WPE\AITK\WP\GraphQL\Contracts;

use WPE\AITK\Core\DTO\Contracts\Array_Constructible_Interface;

/**
 * Marker interface for typed GraphQL response DTOs.
 *
 * All response data objects returned from GraphQL operations must implement this.
 */
interface GraphQL_Response_Interface extends Array_Constructible_Interface {
}
