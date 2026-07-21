<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Search_Item;

use WPE\AITK\Modules\Smart_Search\Custom_Search_Results\Custom_Search_Results_Subscriber;
use WPE\AITK\Modules\Smart_Search\Promotions\Promotions_Subscriber;
use WPE\AITK\WP\Post\Contracts\Post_Visibility_Interface;
use WP_Post;

/**
 * Removes post references from promotions and custom search results
 * when a post is deleted or its status changes to non-public.
 */
final class Search_Item_Cleanup_Handler {
	// todo: wrap in provider of some kind.
	private const OPTION_NAMES = [
		Promotions_Subscriber::OPTION,
		Custom_Search_Results_Subscriber::OPTION,
	];

	private Search_Item_Remover $remover;

	private Post_Visibility_Interface $post_visibility;

	public function __construct( Search_Item_Remover $remover, Post_Visibility_Interface $post_visibility ) {
		$this->remover         = $remover;
		$this->post_visibility = $post_visibility;
	}

	/**
	 * Handle post deletion by removing the post from all search settings.
	 *
	 * @param int $post_id The deleted post ID.
	 */
	public function on_post_deleted( int $post_id ): void {
		$this->remove_post_from_settings( $post_id );
	}

	/**
	 * Handle post status transitions by removing non-public posts from search settings.
	 *
	 * @param string   $new_status New post status.
	 * @param string   $old_status Old post status.
	 * @param \WP_Post $post       Post object.
	 */
	// phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
	public function on_post_status_changed( string $new_status, string $old_status, WP_Post $post ): void {
		if ( $this->post_visibility->is_public( $post ) ) {
			return;
		}

		$this->remove_post_from_settings( $post->ID );
	}

	/**
	 * Remove a post ID from all search-related option arrays.
	 */
	private function remove_post_from_settings( int $post_id ): void {
		foreach ( self::OPTION_NAMES as $option_name ) {
			$data = get_option( $option_name );

			if ( ! is_array( $data ) ) {
				continue;
			}

			$filtered = $this->remover->remove_from_data( $data, $post_id );

			if ( $filtered === $data ) {
				continue;
			}

			update_option( $option_name, $filtered );
		}
	}
}
