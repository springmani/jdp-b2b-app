<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Exceptions;

use RuntimeException;

/**
 * Exception thrown when a property has an unsupported type.
 *
 * This exception is raised when a class property uses a type that is not supported
 * by the reflection parsers, such as:
 * - Object types (classes, interfaces)
 * - Multidimensional arrays
 * - Resource types
 * - Mixed types
 * - Callable types
 *
 * Only scalar types (int, string, float, bool) are supported.
 */
final class Unsupported_Property_Type_Exception extends RuntimeException {
}
