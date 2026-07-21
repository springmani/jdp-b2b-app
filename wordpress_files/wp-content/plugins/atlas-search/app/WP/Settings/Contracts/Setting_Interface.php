<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings\Contracts;

use WPE\AITK\Core\DTO\Contracts\Array_Constructible_Interface;
use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;

/**
 * Interface for settings represented as objects that can be serialized to and from arrays.
 */
interface Setting_Interface extends Array_Convertible_Interface, Array_Constructible_Interface {
}
