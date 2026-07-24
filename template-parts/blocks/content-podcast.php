<?php
/**
 * Block Name: Podcast
 *
 * Embeds oEmbed-style iframe markup (e.g. Spotify) plus optional caption.
 * Field names m13_embed / m13_caption match migrated ACF block data.
 *
 * @package jdpower
 */

$classes = 'podcast-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$embed_raw   = function_exists( 'get_field' ) ? get_field( 'm13_embed' ) : '';
$caption_raw = function_exists( 'get_field' ) ? get_field( 'm13_caption' ) : '';

$embed = is_string( $embed_raw ) ? trim( $embed_raw ) : '';

if ( '' === $embed ) {
	if ( ! empty( $is_preview ) ) {
		echo '<div class="' . esc_attr( $classes ) . '"><p class="podcast-block__placeholder">';
		echo esc_html__( 'Add embed code in the block sidebar (Embed field).', 'jdpower' );
		echo '</p></div>';
	}
	return;
}

$embed_safe = wp_kses( $embed, 'podcast_embed' );

if ( '' === trim( $embed_safe ) ) {
	if ( ! empty( $is_preview ) ) {
		echo '<div class="' . esc_attr( $classes ) . '"><p class="podcast-block__placeholder">';
		echo esc_html__( 'Embed HTML was removed by sanitization. Allowed markup uses the podcast_embed rules (iframe, script, object, param, div, etc.).', 'jdpower' );
		echo '</p></div>';
	}
	return;
}

$caption = '';
if ( is_string( $caption_raw ) && '' !== trim( $caption_raw ) ) {
	$caption = wp_kses_post( wpautop( trim( $caption_raw ) ) );
}

$anchor_attr = '';
if ( ! empty( $block['anchor'] ) && is_string( $block['anchor'] ) ) {
	$anchor_attr = ' id="' . esc_attr( $block['anchor'] ) . '"';
}
?>

<section class="<?php echo esc_attr( $classes ); ?>"<?php echo $anchor_attr; ?>>
	<div class="container">
		<div class="podcast-block__inner">
			<div class="podcast-block__embed">
				<?php echo $embed_safe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses podcast_embed allow list (see jdpower_podcast_embed_kses_allowed). ?>
			</div>
			<?php if ( '' !== $caption ) : ?>
				<div class="podcast-block__caption"><?php echo $caption; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post applied. ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>
