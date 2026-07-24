<?php
/**
 * Block Name: Icon Columns
 *
 * @package jdpower
 */

$classes = 'icon-columns-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'icon_columns_padding_top' );
$padding_bottom  = get_field( 'icon_columns_padding_bottom' );
$background_tone = get_field( 'icon_columns_background_tone' );
$animate_columns = function_exists( 'jdpower_acf_flag_on' ) ? jdpower_acf_flag_on( get_field( 'icon_columns_animate_columns' ) ) : true;
$icon_col_reveal = $animate_columns ? ' jdp-animate-view-fade-in-up' : '';
$block_layout    = get_field( 'icon_columns_block_layout' );
$column_split    = get_field( 'icon_columns_column_split' );
$columns         = get_field( 'icon_columns_columns' );
$pre_heading     = get_field( 'icon_columns_pre_heading' );
$heading         = get_field( 'icon_columns_heading' );
$heading_size    = get_field( 'icon_columns_heading_size' );
$intro_copy      = get_field( 'icon_columns_intro_copy' );

$valid_heading_sizes = array( 'default', 'large', 'medium', 'small', 'xsmall', 'xxsmall' );
if ( empty( $heading_size ) || ! in_array( $heading_size, $valid_heading_sizes, true ) ) {
	$heading_size = 'default';
}

$heading_classes = array( 'icon-columns-block__title' );
if ( 'default' !== $heading_size ) {
	$heading_classes[] = $heading_size;
}
$heading_class_attr = esc_attr( implode( ' ', $heading_classes ) );

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
	$classes .= ' icon-columns-block--tone-dark';
} else {
	$classes .= ' icon-columns-block--tone-light';
}

if ( empty( $block_layout ) || ! in_array( $block_layout, array( 'stacked', 'columns' ), true ) ) {
	$block_layout = 'stacked';
}
$classes .= ' icon-columns-block--layout-' . $block_layout;

if ( 'stacked' === $block_layout ) {
	if ( empty( $columns ) || ! in_array( (string) $columns, array( '2', '3' ), true ) ) {
		$columns = '3';
	}
	$classes .= ' icon-columns-block--cols-' . $columns;
}

$has_pre         = is_string( $pre_heading ) && '' !== trim( $pre_heading );
$has_heading     = is_string( $heading ) && '' !== trim( wp_strip_all_tags( (string) $heading ) );
$has_intro_copy  = is_string( $intro_copy ) && '' !== trim( wp_strip_all_tags( (string) $intro_copy ) );

$items_rows = array();
$items_raw  = get_field( 'icon_columns_items' );
if ( is_array( $items_raw ) ) {
	foreach ( $items_raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$icon  = isset( $row['icon_columns_icon'] ) ? $row['icon_columns_icon'] : null;
		$col_h = isset( $row['icon_columns_column_heading'] ) ? $row['icon_columns_column_heading'] : '';
		$copy  = isset( $row['icon_columns_copy'] ) ? $row['icon_columns_copy'] : '';

		$icon_id      = is_array( $icon ) && ! empty( $icon['ID'] ) ? (int) $icon['ID'] : 0;
		$has_icon     = $icon_id > 0;
		$has_col_head = is_string( $col_h ) && '' !== trim( $col_h );
		$has_copy     = is_string( $copy ) && '' !== trim( wp_strip_all_tags( (string) $copy ) );

		if ( ! $has_icon && ! $has_col_head && ! $has_copy ) {
			continue;
		}

		$items_rows[] = array(
			'icon_id'      => $icon_id,
			'has_icon'     => $has_icon,
			'col_heading'  => is_string( $col_h ) ? trim( $col_h ) : '',
			'has_col_head' => $has_col_head,
			'copy'         => is_string( $copy ) ? $copy : '',
			'has_copy'     => $has_copy,
		);
	}
}

$has_items = count( $items_rows ) > 0;

if ( ! $has_pre && ! $has_heading && ! $has_intro_copy && ! $has_items ) {
	return;
}

$split_modifier = '';
if ( 'columns' === $block_layout ) {
	$valid_splits = array( 'half', 'content_60', 'content_70', 'content_40' );
	if ( empty( $column_split ) || ! in_array( $column_split, $valid_splits, true ) ) {
		$column_split = 'half';
	}
	$split_modifier = 'icon-columns-block__split--' . str_replace( '_', '-', $column_split );
}

?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( 'columns' === $block_layout ) : ?>
			<div class="icon-columns-block__split<?php echo $split_modifier ? ' ' . esc_attr( $split_modifier ) : ''; ?>">
				<div class="icon-columns-block__split-intro">
					<?php if ( $has_pre || $has_heading || $has_intro_copy ) : ?>
						<header class="icon-columns-block__intro icon-columns-block__intro--split">
							<?php if ( $has_pre ) : ?>
								<p class="icon-columns-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
							<?php endif; ?>
							<?php if ( $has_heading ) : ?>
								<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
							<?php endif; ?>
							<?php if ( $has_intro_copy ) : ?>
								<div class="icon-columns-block__intro-copy">
									<?php echo wp_kses_post( $intro_copy ); ?>
								</div>
							<?php endif; ?>
						</header>
					<?php endif; ?>
				</div>
				<div class="icon-columns-block__split-list">
					<?php if ( $has_items ) : ?>
						<?php foreach ( $items_rows as $item ) : ?>
							<div class="icon-columns-block__row-item<?php echo esc_attr( $icon_col_reveal ); ?>">
								<?php if ( $item['has_icon'] ) : ?>
									<div class="icon-columns-block__icon-wrap icon-columns-block__icon-wrap--row">
										<?php
										$path = get_attached_file( $item['icon_id'] );
										$svg  = '';
										if ( $path && is_readable( $path ) && 'svg' === strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
											$svg = file_get_contents( $path );
											$svg = ( false !== $svg ) ? $svg : '';
										}
										if ( $svg ) {
											echo '<span class="icon-columns-block__icon">' . $svg . '</span>';
										} else {
											echo wp_get_attachment_image( $item['icon_id'], 'medium', false, array( 'class' => 'icon-columns-block__icon' ) );
										}
										?>
									</div>
								<?php endif; ?>

								<div class="icon-columns-block__row-text">
									<?php if ( $item['has_col_head'] ) : ?>
										<h2 class="icon-columns-block__column-heading small"><?php echo nl2br( esc_html( $item['col_heading'] ) ); ?></h2>
									<?php endif; ?>
									<?php if ( $item['has_copy'] ) : ?>
										<div class="icon-columns-block__copy">
											<?php echo wp_kses_post( $item['copy'] ); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php else : ?>
			<?php if ( $has_pre || $has_heading || $has_intro_copy ) : ?>
				<header class="icon-columns-block__intro">
					<?php if ( $has_pre ) : ?>
						<p class="icon-columns-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
					<?php endif; ?>
					<?php if ( $has_heading ) : ?>
						<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
					<?php endif; ?>
					<?php if ( $has_intro_copy ) : ?>
						<div class="icon-columns-block__intro-copy">
							<?php echo wp_kses_post( $intro_copy ); ?>
						</div>
					<?php endif; ?>
				</header>
			<?php endif; ?>

			<?php if ( $has_items ) : ?>
				<div class="icon-columns-block__grid">
					<?php foreach ( $items_rows as $item ) : ?>
						<div class="icon-columns-block__column<?php echo esc_attr( $icon_col_reveal ); ?>">
							<?php if ( $item['has_icon'] ) : ?>
								<div class="icon-columns-block__icon-wrap">
									<?php
									$path = get_attached_file( $item['icon_id'] );
									$svg  = '';
									if ( $path && is_readable( $path ) && 'svg' === strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
										$svg = file_get_contents( $path );
										$svg = ( false !== $svg ) ? $svg : '';
									}
									if ( $svg ) {
										echo '<span class="icon-columns-block__icon">' . $svg . '</span>';
									} else {
										echo wp_get_attachment_image( $item['icon_id'], 'medium', false, array( 'class' => 'icon-columns-block__icon' ) );
									}
									?>
								</div>
							<?php endif; ?>

							<?php if ( $item['has_col_head'] ) : ?>
								<h2 class="icon-columns-block__column-heading small"><?php echo nl2br( esc_html( $item['col_heading'] ) ); ?></h2>
							<?php endif; ?>

							<?php if ( $item['has_copy'] ) : ?>
								<div class="icon-columns-block__copy">
									<?php echo wp_kses_post( $item['copy'] ); ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>
