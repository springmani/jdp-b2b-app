<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Concerns;

use LogicException;

trait Without_Clone {
	/**
	 * @return never
	 *
	 * @throws \LogicException Thrown when attempting to clone the class.
	 */
	final public function __clone() {
		throw new LogicException(sprintf(
			"Cloning of %s' is not allowed.",
			static::class
		));
	}
}
