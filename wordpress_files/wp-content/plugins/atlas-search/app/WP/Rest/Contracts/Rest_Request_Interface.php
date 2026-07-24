<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Contracts;

use WPE\AITK\Core\DTO\Contracts\Array_Constructible_Interface;

/**
 * REST Request Interface.
 *
 * Marker interface for typed REST API request data objects.
 * All request DTOs hydrated by the dispatch pipeline must implement this.
 */
interface Rest_Request_Interface extends Array_Constructible_Interface {
}
