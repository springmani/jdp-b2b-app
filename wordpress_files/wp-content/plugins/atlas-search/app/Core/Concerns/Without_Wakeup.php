<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Concerns;

use LogicException;

trait Without_Wakeup {
	/**
	 * @return never
	 *
	 * @throws \LogicException Thrown when attempting to unserialize the class.
	 */
	final public function __wakeup(): void {
		throw new LogicException(sprintf(
			"Unserialization of ' % s' is not allowed.",
			static::class
		));
	}
}
