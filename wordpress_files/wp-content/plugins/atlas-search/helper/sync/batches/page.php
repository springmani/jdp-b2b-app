<?php

namespace Wpe_Content_Engine\Helper\Sync\Batches;

use WP_Query;
use Wpe_Content_Engine\Helper\Constants\Order;
use Wpe_Content_Engine\Helper\Constants\Order_By;
use Wpe_Content_Engine\Helper\Constants\Post_Status;
use Wpe_Content_Engine\Helper\Constants\Post_Type;
use Wpe_Content_Engine\Helper\Multisite_Network_Sync;
use Wpe_Content_Engine\Helper\Progress_Bar_Info_Trait;

class Page extends Multisite_Network_Sync {
	use Progress_Bar_Info_Trait;

	public function get_total_items(): int {
		return wp_count_posts( Post_Type::PAGE )->publish;
	}

	/**
	 * @param int   $offset Offset.
	 * @param mixed $number Offset.
	 *
	 * @return \WP_Post[]
	 */
	protected function _get_items( $offset, $number ): array {
		$q   = [
			'post_type'           => [ Post_Type::PAGE ],
			'post_status'         => Post_Status::WP_PUBLISH,
			'posts_per_page'      => $number,
			'paged'               => $offset,
			'ignore_sticky_posts' => true,
			'orderby'             => Order_By::MODIFIED,
			'order'               => Order::ASCENDING,
		];
		$qry = new WP_Query( $q );

		return $qry->posts;
	}
}
