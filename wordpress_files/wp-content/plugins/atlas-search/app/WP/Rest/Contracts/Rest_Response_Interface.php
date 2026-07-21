<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Contracts;

use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;

/**
 * REST Response Interface.
 *
 * Marker interface for typed REST API response data objects.
 * All response DTOs returned from controller handlers must implement this.
 */
interface Rest_Response_Interface extends Array_Convertible_Interface {
}
