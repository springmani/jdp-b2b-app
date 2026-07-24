<?php

declare(strict_types=1);

namespace WPE\AITK\WP\GraphQL;

/**
 * Base class for GraphQL read operations.
 *
 * Queries typically have no variables (override variables() if needed).
 */
abstract class Abstract_GraphQL_Query extends Abstract_GraphQL_Operation {
}
