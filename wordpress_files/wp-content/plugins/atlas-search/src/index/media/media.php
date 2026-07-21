<?php

declare(strict_types=1);

namespace AtlasSearch\Index\Media;

use WP_Post;

use function AtlasSearch\Index\filter_wp_object_to_array;

use const AtlasSearch\Hooks\SMART_SEARCH_HOOK_EXTRA_FIELDS;

function get_attachment_source( int $attachment_id, string $format = 'base64' ): ?string {
	switch ( apply_filters( 'wpe_smartsearch\attachment_source_format', $format ) ) {
		case 'base64':
			return get_attachment_as_base64( $attachment_id );
		case 'URL':
		default:
			return get_attachment_as_url( $attachment_id );
	}
}

function get_attachment_as_base64( int $attachment_id ): ?string {
	$file = get_attached_file( $attachment_id );

	if ( ! $file || ! file_exists( $file ) ) {
		return null;
	}

	return 'data:' . wp_check_filetype( $file )['type'] . ';base64,' . base64_encode(
		file_get_contents( $file )
	);
}

function get_attachment_as_url( int $attachment_id ): ?string {
	$attachment_url = wp_get_attachment_url( $attachment_id );

	if ( false === $attachment_url ) {
		return null;
	}

	return $attachment_url;
}

/**
 * Add media data to the post.
 *
 * @param array    $fields The fields.
 * @param \WP_Post $post The post.
 *
 * @return array Modified fields.
 */
function add_media_data_to_post( $fields, $post ): array {
	$extractor    = Media_ID_Extractor::create_default();
	$media_ids    = $extractor->extract_from_post( $post );
	$thumbnail_id = get_post_thumbnail_id( $post->ID );

	if ( $thumbnail_id && ! in_array( $thumbnail_id, $media_ids ) ) {
		$media_ids[] = $thumbnail_id;
	}

	$media_attachments = [];

	foreach ( $media_ids as $media_id ) {
		$media_post = get_post( $media_id );

		if ( ! ( $media_post instanceof WP_Post ) ) {
			continue;
		}

		$media_obj             = filter_wp_object_to_array( $media_post );
		$alt_text              = get_post_meta( $media_id, '_wp_attachment_image_alt', true );
		$media_obj['alt_text'] = false !== $alt_text ? $alt_text : '';
		$media_attachments[]   = $media_obj;
	}

	// Only add the media key if there are media attachments.
	if ( ! empty( $media_attachments ) ) {
		$fields['media'] = $media_attachments;
	}

	return $fields;
}

add_filter( SMART_SEARCH_HOOK_EXTRA_FIELDS, __NAMESPACE__ . '\add_media_data_to_post', 10, 2 );
