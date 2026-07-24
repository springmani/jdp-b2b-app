<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Container\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

final class Service_Not_Found_Exception extends Exception implements NotFoundExceptionInterface {
}
