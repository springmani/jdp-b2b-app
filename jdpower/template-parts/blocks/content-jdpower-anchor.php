<?php
/**
 * Block Name: Page anchor
 *
 * In-page jump target: outputs an empty element with `id` only (no visible content).
 *
 * @package jdpower
 */

$raw = function_exists( 'get_field' ) ? get_field( 'page_anchor_html_id' ) : '';
$id  = function_exists( 'jdpower_sanitize_html_id_attribute' )
	? jdpower_sanitize_html_id_attribute( $raw )
	: '';

if ( ! empty( $is_preview ) ) {
	$label = __( 'ANCHOR LINK', 'jdpower' );
	$note  = __( 'Only shown in the editor. On the site this block is invisible and only sets an HTML id for links like #section-id.', 'jdpower' );
	echo '<div class="jdpower-anchor-block jdpower-anchor-block--preview">';
	echo '<p class="jdpower-anchor-block__preview-title">' . esc_html( $label ) . '</p>';
	if ( '' !== $id ) {
		echo '<p class="jdpower-anchor-block__preview-id"><code>#' . esc_html( $id ) . '</code></p>';
	} else {
		echo '<p class="jdpower-anchor-block__preview-empty">' . esc_html__( 'Add an HTML id in the block sidebar.', 'jdpower' ) . '</p>';
	}
	echo '<p class="jdpower-anchor-block__preview-note">' . esc_html( $note ) . '</p>';
	echo '</div>';
	return;
}

if ( '' === $id ) {
	return;
}

echo '<span id="' . esc_attr( $id ) . '" class="jdpower-anchor-block" aria-hidden="true"></span>';
