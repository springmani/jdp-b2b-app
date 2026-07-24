<?php
/**
 * Block Name: Accordion
 *
 * @package jdpower
 */

$classes = 'accordion-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'accordion_padding_top' );
$padding_bottom  = get_field( 'accordion_padding_bottom' );
$background_tone = get_field( 'accordion_background_tone' );
$pre_heading     = get_field( 'accordion_pre_heading' );
$heading         = get_field( 'accordion_heading' );
$items_raw       = get_field( 'accordion_items' );

$valid_padding = array( 'default', 'small', 'medium', 'large', 'none' );
if ( ! empty( $padding_top ) && in_array( $padding_top, $valid_padding, true ) ) {
	$classes .= ' pt-' . $padding_top;
} else {
	$classes .= ' pt-default';
}

if ( ! empty( $padding_bottom ) && in_array( $padding_bottom, $valid_padding, true ) ) {
	$classes .= ' pb-' . $padding_bottom;
} else {
	$classes .= ' pb-default';
}

if ( empty( $background_tone ) || ! in_array( $background_tone, array( 'dark', 'medium', 'light' ), true ) ) {
	$background_tone = 'dark';
}
$classes .= ' bg-' . $background_tone;

if ( 'dark' === $background_tone ) {
	$classes .= ' accordion-block--tone-dark';
} elseif ( 'medium' === $background_tone ) {
	$classes .= ' accordion-block--tone-medium';
} else {
	$classes .= ' accordion-block--tone-light';
}

$items = array();
if ( is_array( $items_raw ) ) {
	foreach ( $items_raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$item_heading = isset( $row['accordion_item_heading'] ) ? $row['accordion_item_heading'] : '';
		$item_intro   = isset( $row['accordion_item_intro'] ) ? $row['accordion_item_intro'] : '';
		$item_body    = isset( $row['accordion_item_body'] ) ? $row['accordion_item_body'] : '';
		$list_cols    = ! empty( $row['accordion_item_list_columns'] );
		$footnote     = isset( $row['accordion_item_footnote'] ) ? $row['accordion_item_footnote'] : '';

		$item_heading = is_string( $item_heading ) ? trim( $item_heading ) : '';
		$item_intro   = is_string( $item_intro ) ? $item_intro : '';
		$item_body    = is_string( $item_body ) ? $item_body : '';
		$footnote     = is_string( $footnote ) ? trim( $footnote ) : '';

		$has_body = '' !== trim( wp_strip_all_tags( $item_body ) );
		if ( '' === $item_heading || ! $has_body ) {
			continue;
		}

		$items[] = array(
			'heading'      => $item_heading,
			'intro'        => $item_intro,
			'body'         => $item_body,
			'list_columns' => $list_cols,
			'footnote'     => $footnote,
		);
	}
}

$has_pre     = is_string( $pre_heading ) && '' !== trim( $pre_heading );
$has_heading = is_string( $heading ) && '' !== trim( wp_strip_all_tags( $heading ) );
$has_items   = count( $items ) > 0;

if ( ! $has_items ) {
	return;
}
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_pre || $has_heading ) : ?>
			<header class="accordion-block__header">
				<?php if ( $has_pre ) : ?>
					<p class="accordion-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
				<?php endif; ?>
				<?php if ( $has_heading ) : ?>
					<h2 class="accordion-block__heading"><?php echo wp_kses_post( $heading ); ?></h2>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<?php if ( $has_items ) : ?>
			<div class="accordion-block__items">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$body_classes = array( 'accordion-block__body' );
					if ( ! empty( $item['list_columns'] ) ) {
						$body_classes[] = 'accordion-block__body--list-columns';
					}
					$has_body = is_string( $item['body'] ) && '' !== trim( wp_strip_all_tags( $item['body'] ) );
					$has_foot = '' !== trim( (string) $item['footnote'] );
					?>
					<details class="accordion-block__item">
						<summary class="accordion-block__summary">
							<span class="accordion-block__summary-main">
								<?php if ( '' !== $item['heading'] ) : ?>
									<h3 class="accordion-block__item-heading"><?php echo esc_html( $item['heading'] ); ?></h3>
								<?php endif; ?>
								<?php if ( is_string( $item['intro'] ) && '' !== trim( $item['intro'] ) ) : ?>
									<span class="accordion-block__item-intro"><?php echo esc_html( trim( $item['intro'] ) ); ?></span>
								<?php endif; ?>
							</span>
							<span class="accordion-block__toggle" aria-hidden="true"></span>
						</summary>
						<div class="accordion-block__panel">
							<?php if ( $has_body ) : ?>
								<div class="<?php echo esc_attr( implode( ' ', $body_classes ) ); ?>">
									<?php echo wp_kses_post( $item['body'] ); ?>
								</div>
							<?php endif; ?>
							<?php if ( $has_foot ) : ?>
								<div class="accordion-block__footnote"><?php echo wp_kses_post( wpautop( trim( $item['footnote'] ) ) ); ?></div>
							<?php endif; ?>
						</div>
					</details>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
