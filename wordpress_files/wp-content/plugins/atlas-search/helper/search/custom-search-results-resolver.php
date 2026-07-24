<?php

namespace Wpe_Content_Engine\Helper\Search;

use WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Custom_Search_Results_Subscriber;
use WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Data\Custom_Search_Results_Data;

/**
 * Resolves and formats custom search results settings for search queries.
 *
 * Retrieves saved custom search results from WordPress options and formats them
 * as custom results entries for the search backend.
 */
class Custom_Search_Results_Resolver extends Abstract_Search_Option_Resolver {
	/**
	 * @inheritDoc
	 */
	protected function get_option_name(): string {
		return Custom_Search_Results_Subscriber::OPTION;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_error_message_prefix(): string {
		return 'Failed to retrieve custom search results: ';
	}

	/**
	 * @param array $option_value The non-empty option value from WordPress.
	 *
	 * @return array
	 *
	 * @throws \AtlasSearch\Hooks\InvalidIdPrefixError Exception.
	 */
	protected function resolve( array $option_value ): array {
		$custom_search_results = Custom_Search_Results_Data::from_array( $option_value );
		$search_phrases        = $custom_search_results->get_search_phrases();

		if ( empty( $search_phrases ) ) {
			return [];
		}

		$id_prefix = \AtlasSearch\Hooks\filter_id_prefix();

		$results = array_map(
			fn ( $phrase ) => [
				'query'     => $phrase->get_phrase(),
				'documents' => array_map(
					fn ( $item ) => $this->format_document_id( $item->get_post_type(), $item->get_id(), $id_prefix ),
					$phrase->get_search_items()
				),
			],
			$search_phrases
		);

		return array_values( $results );
	}
}
