<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest\Exceptions;

use LogicException;

/**
 * Duplicate Route Exception.
 *
 * Thrown at registration time when two route configs share the same
 * route pattern and HTTP method combination. This is a developer error,
 * not a runtime REST error.
 */
final class Duplicate_Route_Exception extends LogicException {
}
