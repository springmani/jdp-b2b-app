<?php
/**
 * Social share links for singular insight posts.
 *
 * @package jdpower
 *
 * @var array{placement?: string} $args `inline` (mobile meta row) or `sticky` (desktop scroll column).
 */

$placement   = isset( $args['placement'] ) ? sanitize_key( (string) $args['placement'] ) : 'inline';
$share_items = function_exists( 'jdpower_insight_social_share_items' )
	? jdpower_insight_social_share_items()
	: array();

if ( empty( $share_items ) ) {
	return;
}

$nav_class = 'insight-social-share insight-social-share--' . ( 'sticky' === $placement ? 'sticky' : 'inline' );
?>
<nav class="<?php echo esc_attr( $nav_class ); ?>" aria-label="<?php esc_attr_e( 'Share', 'jdpower' ); ?>">
	<ul class="insight-social-share__list">
		<?php foreach ( $share_items as $item ) : ?>
			<?php
			if ( empty( $item['id'] ) || empty( $item['url'] ) || empty( $item['label'] ) ) {
				continue;
			}
			$icon = function_exists( 'jdpower_insight_social_share_icon_svg' )
				? jdpower_insight_social_share_icon_svg( (string) $item['id'] )
				: '';
			if ( '' === $icon ) {
				continue;
			}
			$target = ! empty( $item['target'] ) ? (string) $item['target'] : '';
			$rel    = ! empty( $item['rel'] ) ? (string) $item['rel'] : '';
			?>
			<li class="insight-social-share__item">
				<a
					class="insight-social-share__link insight-social-share__link--<?php echo esc_attr( (string) $item['id'] ); ?>"
					href="<?php echo esc_url( (string) $item['url'] ); ?>"
					<?php if ( '' !== $target ) : ?>
						target="<?php echo esc_attr( $target ); ?>"
					<?php endif; ?>
					<?php if ( '' !== $rel ) : ?>
						rel="<?php echo esc_attr( $rel ); ?>"
					<?php endif; ?>
					aria-label="<?php echo esc_attr( (string) $item['label'] ); ?>"
				>
					<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built in theme helper ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
