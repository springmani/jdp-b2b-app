<?php
/**
 * Block Name: CTA Button
 */

$items = get_field( 'cta_button_items' );

if ( empty( $items ) || ! is_array( $items ) ) {
	return;
}

$valid_rows = array();

foreach ( $items as $row ) {
	$link = isset( $row['cta_item_link'] ) ? $row['cta_item_link'] : null;
	$url  = is_array( $link ) ? ( $link['url'] ?? '' ) : '';
	$title = is_array( $link ) ? ( $link['title'] ?? '' ) : '';
	if ( $url && $title ) {
		$valid_rows[] = $row;
	}
}

if ( empty( $valid_rows ) ) {
	return;
}

$classes = 'cta-button';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$align = get_field( 'cta_button_align' );
$valid_align = array( 'left', 'center', 'right' );
if ( ! is_string( $align ) || ! in_array( $align, $valid_align, true ) ) {
	$align = 'center';
}

$actions_classes = 'cta-button__actions';
if ( 'left' === $align ) {
	$actions_classes .= ' justify-content-start';
} elseif ( 'right' === $align ) {
	$actions_classes .= ' justify-content-end';
} else {
	$actions_classes .= ' justify-content-center';
}

$allowed_styles = array( 'primary', 'teal', 'mint' );
?>

<div class="<?php echo esc_attr( $classes ); ?>">
	<div class="<?php echo esc_attr( $actions_classes ); ?>">
		<?php
		foreach ( $valid_rows as $row ) {
			$link  = isset( $row['cta_item_link'] ) ? $row['cta_item_link'] : null;
			$style = isset( $row['cta_item_style'] ) ? $row['cta_item_style'] : 'primary';

			// Legacy ACF values (older style keys map to primary).
			$legacy_styles = array( 'button', 'outline', 'dark', 'light', 'arrow' );
			if ( in_array( $style, $legacy_styles, true ) ) {
				$style = 'primary';
			}

			if ( empty( $style ) || ! in_array( $style, $allowed_styles, true ) ) {
				$style = 'primary';
			}

			$url    = is_array( $link ) ? ( $link['url'] ?? '' ) : '';
			$title  = is_array( $link ) ? ( $link['title'] ?? '' ) : '';
			$target = is_array( $link ) ? ( $link['target'] ?? '' ) : '';

			if ( ! $url || ! $title ) {
				continue;
			}

			$target_attr = '';
			$rel_attr    = '';
			if ( '_blank' === $target ) {
				$target_attr = ' target="_blank"';
				$rel_attr    = ' rel="noopener noreferrer"';
			}

			$link_classes = 'btn btn-small cta-button__link';
			if ( 'primary' !== $style ) {
				$link_classes .= ' cta-button__link--' . $style;
			}

			printf(
				'<a class="%s" href="%s"%s%s>%s</a>',
				esc_attr( $link_classes ),
				esc_url( $url ),
				$target_attr,
				$rel_attr,
				esc_html( $title )
			);
		}
		?>
	</div>
</div>
