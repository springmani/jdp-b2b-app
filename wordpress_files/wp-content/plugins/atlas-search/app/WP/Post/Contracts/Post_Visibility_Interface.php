<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Post\Contracts;

use WP_Post;

interface Post_Visibility_Interface {
	public function is_public( WP_Post $post ): bool;
}
