<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Exceptions;

use RuntimeException;

/**
 * Exception thrown when a required docblock is missing from a reflection property.
 *
 * This exception is raised when attempting to parse array type information from
 * a property that has no docblock comment. A docblock with a `@var` annotation
 * is required for type extraction.
 */
final class Missing_Docblock_Exception extends RuntimeException {
}
