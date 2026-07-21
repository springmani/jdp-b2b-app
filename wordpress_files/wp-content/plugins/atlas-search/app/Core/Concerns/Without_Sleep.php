<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Concerns;

use LogicException;

trait Without_Sleep {
	/**
	 * @return never
	 *
	 * @throws \LogicException Thrown when attempting to serialize the class.
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
	 */
	final public function __sleep() {
		throw new LogicException(sprintf(
			"Serialization of ' % s' is not allowed.",
			static::class
		));
	}
}
