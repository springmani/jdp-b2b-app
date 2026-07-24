<?php

declare(strict_types=1);

namespace WPE\AITK\WP\GraphQL\Exceptions;

/**
 * Thrown when the server rejects the access token (HTTP 401 or UNAUTHENTICATED GraphQL code).
 */
final class GraphQL_Authentication_Exception extends GraphQL_Exception {
}
