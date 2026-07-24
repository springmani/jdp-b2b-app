<?php

namespace Wpe_Content_Engine\Helper\Search;

use WPE\AITK\Modules\Smart_Search\Promotions\Data\Search_Promotion_Data;
use WPE\AITK\Modules\Smart_Search\Promotions\Promotions_Subscriber;

/**
 * Resolves and formats promotion settings for search queries.
 *
 * Retrieves saved promotions from WordPress options and formats them
 * as document IDs for the search backend.
 */
class Promotions_Resolver extends Abstract_Search_Option_Resolver {
	/**
	 * @inheritDoc
	 */
	protected function get_option_name(): string {
		return Promotions_Subscriber::OPTION;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_error_message_prefix(): string {
		return 'Failed to retrieve promotions: ';
	}

	/**
	 * @param array $option_value The non-empty option value from WordPress.
	 *
	 * @return array
	 *
	 * @throws \AtlasSearch\Hooks\InvalidIdPrefixError Exception.
	 */
	protected function resolve( array $option_value ): array {
		$promotion_settings = Search_Promotion_Data::from_array( $option_value );
		$promoted_items     = $promotion_settings->get_promotions();

		if ( empty( $promoted_items ) ) {
			return [];
		}

		$id_prefix    = \AtlasSearch\Hooks\filter_id_prefix();
		$document_ids = [];

		foreach ( $promoted_items as $item ) {
			$document_ids[] = $this->format_document_id( $item->get_post_type(), $item->get_id(), $id_prefix );
		}

		return $document_ids;
	}
}
