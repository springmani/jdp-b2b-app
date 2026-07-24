<?php

namespace Wpe_Content_Engine\Helper\Sync\Batches;

use WP_Query;
use Wpe_Content_Engine\Helper\Constants\Post_Status;
use Wpe_Content_Engine\Helper\Constants\Post_Type;
use Wpe_Content_Engine\Helper\Multisite_Network_Sync;
use Wpe_Content_Engine\Helper\Progress_Bar_Info_Trait;

class Post extends Multisite_Network_Sync {
	use Progress_Bar_Info_Trait;

	public function get_total_items(): int {
		return wp_count_posts( Post_Type::POST )->publish;
	}

	/**
	 * @param int $offset Offset.
	 * @param int $number Number.
	 *
	 * @return \WP_Post[]
	 */
	protected function _get_items( $offset, $number ): array {
		$q   = [
			'post_type'           => [ Post_Type::POST ],
			'post_status'         => Post_Status::WP_PUBLISH,
			'posts_per_page'      => $number,
			'paged'               => $offset,
			'ignore_sticky_posts' => true,
		];
		$qry = new WP_Query( $q );

		return $qry->posts;
	}
}
