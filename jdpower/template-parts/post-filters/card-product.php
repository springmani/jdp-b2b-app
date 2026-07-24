<?php
/**
 * Product card for Product Finder grid.
 *
 * @package jdpower
 */

$post_id = get_the_ID();
$card    = jdpower_post_filters_product_card_data( $post_id );

?>
<article <?php post_class( 'post-filters__card post-filters__card--product' ); ?>>
	<a class="post-filters__card-link" href="<?php echo esc_url( get_permalink() ); ?>">
		<div class="post-filters__card-media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large', array( 'class' => 'post-filters__card-img' ) ); ?>
			<?php else : ?>
				<span class="post-filters__card-placeholder" aria-hidden="true"></span>
			<?php endif; ?>
		</div>
		<div class="post-filters__card-body">
			<?php if ( is_string( $card['segment_label'] ) && '' !== $card['segment_label'] ) : ?>
				<p class="post-filters__card-type"><?php echo esc_html( $card['segment_label'] ); ?></p>
			<?php endif; ?>
			<h2 class="small-min post-filters__card-title"><span class="post-filters__card-title-text"><?php echo $card['heading']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- jdpower_sanitize_inline_html() in card data ?></span></h2>
			<?php if ( is_string( $card['copy_html'] ) && '' !== trim( wp_strip_all_tags( $card['copy_html'] ) ) ) : ?>
				<div class="post-filters__card-excerpt"><?php echo $card['copy_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
		</div>
	</a>
</article>
