<?php
/**
 * Block Name: Content Columns
 *
 * @package jdpower
 */

$classes = 'content-columns-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'content_columns_padding_top' );
$padding_bottom  = get_field( 'content_columns_padding_bottom' );
$background_tone = get_field( 'content_columns_background_tone' );
$pre_heading     = get_field( 'content_columns_pre_heading' );
$column_size_raw = get_field( 'content_columns_column_size' );
$cta_style       = get_field( 'content_columns_cta_style' );
$items_raw       = get_field( 'content_columns_items' );

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
	$background_tone = 'light';
}
$classes .= ' bg-' . $background_tone;

if ( 'dark' === $background_tone ) {
	$classes .= ' content-columns-block--tone-dark';
} elseif ( 'medium' === $background_tone ) {
	$classes .= ' content-columns-block--tone-medium';
} else {
	$classes .= ' content-columns-block--tone-light';
}

$column_size = (int) $column_size_raw;
if ( ! in_array( $column_size, array( 2, 3, 4 ), true ) ) {
	$column_size = 3;
}

if ( empty( $cta_style ) || ! in_array( $cta_style, array( 'button', 'arrow' ), true ) ) {
	$cta_style = 'button';
}

$has_pre = is_string( $pre_heading ) && '' !== trim( $pre_heading );

$columns = array();
if ( is_array( $items_raw ) ) {
	foreach ( $items_raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$heading = isset( $row['content_columns_item_heading'] ) ? $row['content_columns_item_heading'] : '';
		$copy    = isset( $row['content_columns_item_copy'] ) ? $row['content_columns_item_copy'] : '';
		$cta     = isset( $row['content_columns_item_cta'] ) ? $row['content_columns_item_cta'] : null;
		$heading = is_string( $heading ) ? $heading : '';
		$copy    = is_string( $copy ) ? $copy : '';
		$has_heading = '' !== trim( wp_strip_all_tags( $heading ) );
		$has_copy    = '' !== trim( wp_strip_all_tags( $copy ) );

		$cta_url    = is_array( $cta ) ? ( $cta['url'] ?? '' ) : '';
		$cta_title  = is_array( $cta ) ? ( $cta['title'] ?? '' ) : '';
		$cta_target = is_array( $cta ) ? ( $cta['target'] ?? '' ) : '';
		$has_cta    = is_string( $cta_url ) && '' !== $cta_url && is_string( $cta_title ) && '' !== trim( $cta_title );

		if ( ! $has_heading && ! $has_copy && ! $has_cta ) {
			continue;
		}

		$columns[] = array(
			'heading'     => $heading,
			'copy'        => $copy,
			'has_heading' => $has_heading,
			'has_copy'    => $has_copy,
			'has_cta'     => $has_cta,
			'cta_url'     => $cta_url,
			'cta_title'   => $cta_title,
			'cta_target'  => $cta_target,
		);
	}
}

$has_items = count( $columns ) > 0;

if ( ! $has_pre && ! $has_items ) {
	return;
}

$row_cols_modifier = 'content-columns-block__row--cols-' . $column_size;
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_pre ) : ?>
			<div class="content-columns-block__intro">
				<p class="content-columns-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( $has_items ) : ?>
			<div class="gx-4 gy-4 content-columns-block__row <?php echo esc_attr( $row_cols_modifier ); ?>">
				<?php foreach ( $columns as $col ) : ?>
					<article class="content-columns-block__column">
						<?php if ( $col['has_heading'] ) : ?>
							<h2 class="content-columns-block__heading small"><?php echo wp_kses_post( $col['heading'] ); ?></h2>
						<?php endif; ?>
						<?php if ( $col['has_copy'] ) : ?>
							<div class="content-columns-block__copy">
								<?php echo wp_kses_post( $col['copy'] ); ?>
							</div>
						<?php endif; ?>
						<?php if ( $col['has_cta'] ) : ?>
							<?php
							$target_attr = '';
							$rel_attr    = '';
							if ( '_blank' === $col['cta_target'] ) {
								$target_attr = ' target="_blank"';
								$rel_attr    = ' rel="noopener noreferrer"';
							}
							?>
							<div class="content-columns-block__cta">
								<?php if ( 'arrow' === $cta_style ) : ?>
									<a class="arrow-link content-columns-block__link" href="<?php echo esc_url( $col['cta_url'] ); ?>"<?php echo $target_attr . $rel_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<?php echo esc_html( $col['cta_title'] ); ?>
									</a>
								<?php else : ?>
									<a class="btn btn-small content-columns-block__btn" href="<?php echo esc_url( $col['cta_url'] ); ?>"<?php echo $target_attr . $rel_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<?php echo esc_html( $col['cta_title'] ); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
