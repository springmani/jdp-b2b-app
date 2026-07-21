<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Recommendations\Data;

use Throwable;
use WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface;
use WPE\AITK\WP\Debug\Contracts\Logger_Interface;
use WPE\AITK\WP\Nonce\Contracts\Nonce_Interface;

use function AtlasSearch\Hooks\filter_index_id;

/**
 * Data object for analytics script localization.
 */
final class Analytics_Script_Data implements Array_Convertible_Interface {
	private Nonce_Interface $nonce;
	private Logger_Interface $logger;

	public function __construct( Nonce_Interface $nonce, Logger_Interface $logger ) {
		$this->nonce  = $nonce;
		$this->logger = $logger;
	}

	/**
	 * @return array<string, scalar|array>
	 *
	 * @throws \WPE\AITK\WP\Nonce\Exceptions\Nonce_Creation_Exception|\AtlasSearch\Hooks\InvalidIdPrefixError
	 */
	public function to_array(): array {
		$pagination = $this->get_search_pagination();

		return [
			'currentPage'   => $pagination['current_page'],
			'documentID'    => $this->document_id(),
			'nonce'         => $this->nonce->create( 'wp_rest' ),
			'postsPerPage'  => $pagination['posts_per_page'],
			'restUrl'       => esc_url_raw( rest_url() ),
			'searchResults' => $this->search_results(),
			'title'         => get_the_title(),
		];
	}

	/**
	 * @return array<string, int>
	 */
	private function get_search_pagination(): array {
		if ( ! is_search() ) {
			return [
				'current_page'   => 0,
				'posts_per_page' => 0,
			];
		}

		global $wp_query;

		$paged          = (int) $wp_query->get( 'paged' );
		$posts_per_page = (int) $wp_query->get( 'posts_per_page' );

		return [
			'current_page'   => max( 1, $paged ),
			'posts_per_page' => $posts_per_page,
		];
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	private function search_results(): array {
		$search_results = [];

		if ( ! is_search() ) {
			return $search_results;
		}

		try {
			while ( have_posts() ) {
				the_post();
				$search_results[] = [
					'documentID' => $this->document_id(),
					'title'      => get_the_title(),
					'url'        => get_permalink(),
				];
			}

			rewind_posts();
		} catch ( Throwable $e ) {
			$this->logger->error( 'recommendations', 'Search results collection failed: ' . $e->getMessage() );
		}

		return $search_results;
	}

	/**
	 * @throws \AtlasSearch\Hooks\InvalidIdPrefixError
	 */
	private function document_id(): string {
		$post = get_post();

		if ( null !== $post ) {
			return filter_index_id( $post->post_type, $post->ID );
		}

		return '';
	}
}
