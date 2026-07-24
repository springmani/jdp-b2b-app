<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Post;

use WPE\AITK\WP\Post\Contracts\Post_Visibility_Interface;
use WP_Post;

final class Post_Visibility implements Post_Visibility_Interface {
	public const WP_PUBLISH = 'publish';

	public function is_public( WP_Post $post ): bool {
		return self::WP_PUBLISH === $post->post_status && ! post_password_required( $post );
	}
}
