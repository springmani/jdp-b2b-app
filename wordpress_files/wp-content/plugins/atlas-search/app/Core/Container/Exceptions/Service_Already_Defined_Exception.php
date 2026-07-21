<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Container\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

final class Service_Already_Defined_Exception extends Exception implements ContainerExceptionInterface {
}
