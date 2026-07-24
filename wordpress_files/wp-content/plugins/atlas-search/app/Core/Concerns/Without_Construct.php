<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Concerns;

trait Without_Construct {
	protected function __construct() {
		// Prevent direct instantiation.
	}
}
