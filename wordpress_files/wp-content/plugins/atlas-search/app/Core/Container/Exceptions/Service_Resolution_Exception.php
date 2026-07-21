<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

final class Service_Resolution_Exception extends RuntimeException implements ContainerExceptionInterface {
}
