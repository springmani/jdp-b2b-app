<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Container\Exceptions;

use LogicException;
use Psr\Container\ContainerExceptionInterface;

final class Container_Immutable_Exception extends LogicException implements ContainerExceptionInterface {
}
