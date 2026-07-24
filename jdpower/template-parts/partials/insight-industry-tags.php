<?php
/**
 * Industry tag links for singular insight posts.
 *
 * @package jdpower
 *
 * @var array{terms: WP_Term[]} $args
 */

$terms = isset( $args['terms'] ) && is_array( $args['terms'] ) ? $args['terms'] : array();
if ( empty( $terms ) ) {
	return;
}
?>
<aside class="insight-industry-tags" aria-label="<?php esc_attr_e( 'Industries', 'jdpower' ); ?>">
	<div class="container">
		<ul class="insight-industry-tags__list">
			<?php foreach ( $terms as $term ) : ?>
				<?php
				if ( ! ( $term instanceof WP_Term ) || '' === trim( (string) $term->name ) ) {
					continue;
				}
				$label = function_exists( 'jdpower_decode_text_for_display' )
					? jdpower_decode_text_for_display( (string) $term->name )
					: (string) $term->name;
				$url   = function_exists( 'jdpower_insight_center_industry_filter_url' )
					? jdpower_insight_center_industry_filter_url( (string) $term->slug )
					: '';
				if ( '' === $url ) {
					continue;
				}
				?>
				<li class="insight-industry-tags__item">
					<a class="insight-industry-tags__tag" href="<?php echo esc_url( $url ); ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</aside>
