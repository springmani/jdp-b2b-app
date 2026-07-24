<?php
/**
 * Insight card for filter grid.
 *
 * @package jdpower
 */

$post_id = get_the_ID();
$card    = jdpower_post_filters_insight_card_data( $post_id );
if ( ! $card ) {
	return;
}

$type = function_exists( 'jdpower_insight_card_post_type_label' )
	? jdpower_insight_card_post_type_label( $post_id )
	: '';

$title_size = get_query_var( 'jdpower_pf_card_heading_class', 'xxsmall' );
$title_size = in_array( $title_size, array( 'xxsmall', 'xsmall', 'small', 'medium', 'large' ), true ) ? $title_size : 'xxsmall';

$segment_label = isset( $card['segment_label'] ) && is_string( $card['segment_label'] )
	? trim( $card['segment_label'] )
	: '';
?>
<article <?php post_class( 'post-filters__card post-filters__card--insight' ); ?>>
	<a class="post-filters__card-link" href="<?php echo esc_url( $card['url'] ); ?>">
		<div class="post-filters__card-media">
			<?php if ( ! empty( $card['image_id'] ) ) : ?>
				<?php echo wp_get_attachment_image( (int) $card['image_id'], 'large', false, array( 'class' => 'post-filters__card-img' ) ); ?>
			<?php else : ?>
				<span class="post-filters__card-placeholder" aria-hidden="true">
					<?php if ( '' !== $type ) : ?>
						<span class="post-filters__card-placeholder-label"><?php echo esc_html( $type ); ?></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
			<?php if ( '' !== $segment_label ) : ?>
				<span class="post-filters__card-segment"><?php echo esc_html( $segment_label ); ?></span>
			<?php endif; ?>
		</div>
		<div class="post-filters__card-body">
			<?php if ( '' !== $type ) : ?>
				<p class="post-filters__card-type"><?php echo esc_html( $type ); ?></p>
			<?php endif; ?>
			<h2 class="<?php echo esc_attr( $title_size . ' post-filters__card-title' ); ?>"><span class="post-filters__card-title-text"><?php echo $card['heading']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- jdpower_sanitize_inline_html() in card data ?></span></h2>
			<?php if ( is_string( $card['copy_html'] ) && '' !== trim( wp_strip_all_tags( $card['copy_html'] ) ) ) : ?>
				<div class="post-filters__card-excerpt"><?php echo $card['copy_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
			<?php
			$date_display = isset( $card['date_display'] ) && is_string( $card['date_display'] ) ? trim( $card['date_display'] ) : '';
			$date_iso     = isset( $card['date_iso'] ) && is_string( $card['date_iso'] ) ? trim( $card['date_iso'] ) : '';
			if ( '' !== $date_display ) :
				?>
				<time class="post-filters__card-date"<?php echo '' !== $date_iso ? ' datetime="' . esc_attr( $date_iso ) . '"' : ''; ?>><?php echo esc_html( $date_display ); ?></time>
			<?php endif; ?>
		</div>
	</a>
</article>
