<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Post;

use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;
use WPE\AITK\WP\Post\Contracts\Post_Visibility_Interface;

final class Post_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			Post_Visibility::class => static fn (): Post_Visibility_Interface => new Post_Visibility(),
		];
	}
}
