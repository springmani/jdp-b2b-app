<?php
/**
 * Relabel core Post type to "News" (admin + front-end labels from get_post_type_object()).
 * Unregister core category and post_tag from post (News); use Insight taxonomies only.
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string       $post_type Post type slug.
 * @param WP_Post_Type $wp_post_type Post type object.
 */
function jdpower_rename_post_labels_to_news( $post_type, $wp_post_type ) {
	if ( 'post' !== $post_type || ! isset( $wp_post_type->labels ) ) {
		return;
	}

	$labels = $wp_post_type->labels;

	$labels->name                     = __( 'News', 'jdpower' );
	$labels->singular_name              = __( 'News', 'jdpower' );
	$labels->menu_name                  = __( 'News', 'jdpower' );
	$labels->name_admin_bar             = __( 'News', 'jdpower' );
	$labels->add_new                    = __( 'Add New', 'jdpower' );
	$labels->add_new_item               = __( 'Add New News', 'jdpower' );
	$labels->edit_item                  = __( 'Edit News', 'jdpower' );
	$labels->new_item                   = __( 'New News', 'jdpower' );
	$labels->view_item                  = __( 'View News', 'jdpower' );
	$labels->view_items               = __( 'View News', 'jdpower' );
	$labels->all_items                  = __( 'All News', 'jdpower' );
	$labels->search_items               = __( 'Search News', 'jdpower' );
	$labels->not_found                  = __( 'No news found', 'jdpower' );
	$labels->not_found_in_trash         = __( 'No news found in Trash', 'jdpower' );
	$labels->parent_item_colon          = __( 'Parent News:', 'jdpower' );
	$labels->archives                   = __( 'News Archives', 'jdpower' );
	$labels->attributes               = __( 'News Attributes', 'jdpower' );
	$labels->insert_into_item          = __( 'Insert into news', 'jdpower' );
	$labels->uploaded_to_this_item     = __( 'Uploaded to this news item', 'jdpower' );
	$labels->filter_items_list         = __( 'Filter news list', 'jdpower' );
	$labels->filter_by_date            = __( 'Filter news by date', 'jdpower' );
	$labels->items_list_navigation     = __( 'News list navigation', 'jdpower' );
	$labels->items_list                = __( 'News list', 'jdpower' );
	$labels->item_published            = __( 'News published.', 'jdpower' );
	$labels->item_published_privately  = __( 'News published privately.', 'jdpower' );
	$labels->item_reverted_to_draft    = __( 'News reverted to draft.', 'jdpower' );
	$labels->item_scheduled            = __( 'News scheduled.', 'jdpower' );
	$labels->item_updated              = __( 'News updated.', 'jdpower' );
	$labels->item_link                 = __( 'News Link', 'jdpower' );
	$labels->item_link_description     = __( 'A link to a news item.', 'jdpower' );
}
add_action( 'registered_post_type', 'jdpower_rename_post_labels_to_news', 10, 2 );

/**
 * Remove default Categories and Tags from the post (News) editor; topics/industries remain via ACF taxonomies.
 */
function jdpower_unregister_default_taxonomies_from_post() {
	unregister_taxonomy_for_object_type( 'category', 'post' );
	unregister_taxonomy_for_object_type( 'post_tag', 'post' );
}
add_action( 'init', 'jdpower_unregister_default_taxonomies_from_post', 11 );

/**
 * Post type label for insight card layouts (grid, featured insights, related insights).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function jdpower_insight_card_post_type_label( $post_id ) {
	$post_id   = (int) $post_id;
	$post_type = $post_id > 0 ? get_post_type( $post_id ) : '';
	if ( ! is_string( $post_type ) || '' === $post_type ) {
		return '';
	}

	if ( 'resource' === $post_type ) {
		return __( 'News', 'jdpower' );
	}

	$pto = get_post_type_object( $post_type );
	if ( $pto && ! empty( $pto->labels->singular_name ) ) {
		return (string) $pto->labels->singular_name;
	}

	return '';
}
