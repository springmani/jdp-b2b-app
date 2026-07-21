<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Notice\Contracts;

interface Notice_Renderer_Interface {
	public function render( Notice_Interface $notice ): void;
}
