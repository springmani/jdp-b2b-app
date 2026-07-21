<?php

declare(strict_types=1);

namespace WPE\AITK\WP\GraphQL\Exceptions;

use RuntimeException;

/**
 * Base exception for all GraphQL client errors.
 */
abstract class GraphQL_Exception extends RuntimeException {
}
