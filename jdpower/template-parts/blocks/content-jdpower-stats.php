<?php
/**
 * Block Name: Stats
 */

$classes = 'stats-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'stats_padding_top' );
$padding_bottom  = get_field( 'stats_padding_bottom' );
$background_tone = get_field( 'stats_background_tone' );
$layout              = get_field( 'stats_layout' );
$columns             = get_field( 'stats_columns' );
$alignment           = get_field( 'stats_alignment' );
$intro_align         = get_field( 'stats_intro_align' );
$intro_column_ratio  = get_field( 'stats_intro_column_ratio' );
$heading_max_width   = get_field( 'stats_heading_max_width' );

$valid_intro_column_ratios = array( '50_50', '60_40' );
if ( empty( $intro_column_ratio ) || ! in_array( $intro_column_ratio, $valid_intro_column_ratios, true ) ) {
	$intro_column_ratio = '50_50';
}

// Split layout (intro | stats): always 50/50 from xl; intro column ratio only affects stacked intro (heading | copy).
$split_col_classes = array(
	'intro' => 'col-xl-6',
	'pair'  => 'col-xl-6',
);

$stats_intro_column_classes_row = array(
	'50_50' => array( 'intro' => 'col-lg-6', 'pair' => 'col-lg-6' ),
	'60_40' => array( 'intro' => 'col-lg-7', 'pair' => 'col-lg-5' ),
);
$row_col_classes = $stats_intro_column_classes_row[ $intro_column_ratio ];

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
	$classes .= ' stats-block--tone-dark';
} else {
	$classes .= ' stats-block--tone-light';
}

if ( empty( $layout ) || ! in_array( $layout, array( 'stacked', 'split' ), true ) ) {
	$layout = 'stacked';
}
$classes .= ' stats-block--layout-' . $layout;

if ( empty( $columns ) || ! in_array( (string) $columns, array( '2', '3', '4' ), true ) ) {
	$columns = '4';
}
$classes .= ' stats-block--cols-' . $columns;

if ( empty( $alignment ) || ! in_array( $alignment, array( 'left', 'center' ), true ) ) {
	$alignment = 'center';
}
$classes .= ' stats-block--align-' . $alignment;

$valid_heading_max = array( 'full', 'large', 'medium', 'small' );
if ( empty( $heading_max_width ) || ! in_array( $heading_max_width, $valid_heading_max, true ) ) {
	$heading_max_width = 'full';
}
$classes .= ' stats-block--heading-max-' . $heading_max_width;

$valid_intro_align = array( 'top', 'middle', 'bottom' );
if ( empty( $intro_align ) || ! in_array( $intro_align, $valid_intro_align, true ) ) {
	$intro_align = 'top';
}
$classes .= ' stats-block--intro-align-' . $intro_align;

$intro_align_row_classes = array(
	'top'    => 'align-items-start',
	'middle' => 'align-items-center',
	'bottom' => 'align-items-end',
);
$intro_row_align_class = isset( $intro_align_row_classes[ $intro_align ] ) ? $intro_align_row_classes[ $intro_align ] : 'align-items-start';

$pre_heading = get_field( 'stats_pre_heading' );
$heading     = get_field( 'stats_heading' );
$heading_size = get_field( 'stats_heading_size' );
$copy        = get_field( 'stats_copy' );
$items       = get_field( 'stats_items' );
$value_size  = get_field( 'stats_value_size' );
$stats_animate_raw = get_field( 'stats_animate' );

$valid_heading_sizes = array( 'default', 'large', 'medium', 'small', 'xsmall', 'xxsmall' );
if ( empty( $heading_size ) || ! in_array( $heading_size, $valid_heading_sizes, true ) ) {
	$heading_size = 'default';
}

$heading_classes = array( 'stats-block__heading' );
if ( 'default' !== $heading_size ) {
	$heading_classes[] = $heading_size;
}
$heading_class_attr = esc_attr( implode( ' ', $heading_classes ) );
// Default on (new blocks, legacy blocks, or ACF default). Only explicit off disables animation.
$stats_animate = ! in_array( $stats_animate_raw, array( false, 0, '0' ), true );

if ( ! is_array( $items ) ) {
	$items = array();
}

$valid_stat_symbol_sizes = array( 'large', 'small' );
if ( empty( $value_size ) || ! in_array( $value_size, $valid_stat_symbol_sizes, true ) ) {
	$value_size = 'large';
}
$classes .= ' stats-block--value-size-' . $value_size;

if ( $stats_animate ) {
	$classes .= ' stats-block--animate-stats';
}

$has_copy = is_string( $copy ) && '' !== trim( $copy );
$intro_split = 'stacked' === $layout && $has_copy;

if ( $intro_split ) {
	$classes .= ' stats-block--intro-split';
	$classes .= ' stats-block--intro-ratio-' . str_replace( '_', '-', $intro_column_ratio );
}

$has_intro = ( is_string( $pre_heading ) && '' !== trim( $pre_heading ) )
	|| ( is_string( $heading ) && '' !== trim( $heading ) )
	|| $has_copy;

$stats_rows = array();
foreach ( $items as $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	$prefix = isset( $row['prefix'] ) ? trim( (string) $row['prefix'] ) : '';
	$value  = isset( $row['value'] ) ? trim( (string) $row['value'] ) : '';
	$suffix = isset( $row['suffix'] ) ? trim( (string) $row['suffix'] ) : '';
	if ( '' === $suffix && isset( $row['symbol'] ) ) {
		$legacy = trim( (string) $row['symbol'] );
		if ( '' !== $legacy ) {
			$suffix = $legacy;
		}
	}
	$label = isset( $row['label'] ) ? trim( (string) $row['label'] ) : '';

	// Leading numeric token vs trailing characters (e.g. "12.2M" → animate "12.2", show "M" in suffix span).
	$value_figure_numeric = '';
	$value_normalized      = str_replace( array( ',', ' ' ), '', $value );
	if ( preg_match( '/^(?P<num>-?(?:\d+(?:\.\d*)?|\.\d+))(?P<rest>.*)$/u', $value_normalized, $vm ) ) {
		$value_figure_numeric = $vm['num'];
		$value_tail           = trim( $vm['rest'] );
		if ( '' !== $value_tail ) {
			$suffix = trim( $value_tail . ' ' . $suffix );
		}
	}

	$raw_prefix_size = isset( $row['prefix_size'] ) ? (string) $row['prefix_size'] : '';
	$raw_suffix_size = isset( $row['suffix_size'] ) ? (string) $row['suffix_size'] : '';
	if ( 'medium' === $raw_prefix_size ) {
		$raw_prefix_size = 'small';
	}
	if ( 'medium' === $raw_suffix_size ) {
		$raw_suffix_size = 'small';
	}
	$prefix_size = in_array( $raw_prefix_size, $valid_stat_symbol_sizes, true ) ? $raw_prefix_size : 'large';
	$suffix_size = in_array( $raw_suffix_size, $valid_stat_symbol_sizes, true ) ? $raw_suffix_size : 'large';

	if ( 'small' === $value_size ) {
		$prefix_size = 'small';
		$suffix_size = 'small';
	}
	if ( '' === $prefix && '' === $value && '' === $suffix && '' === $label ) {
		continue;
	}
	$stats_rows[] = array(
		'prefix'               => $prefix,
		'prefix_size'          => $prefix_size,
		'value'                => $value,
		'value_figure_numeric' => $value_figure_numeric,
		'suffix'               => $suffix,
		'suffix_size'          => $suffix_size,
		'label'                => $label,
	);
}

$has_stats = count( $stats_rows ) > 0;

if ( ! $has_intro && ! $has_stats ) {
	return;
}

$render_stats_value = static function ( $stat, $value_size, $animate ) {
	$v = trim( (string) $stat['value'] );
	if ( '' === $v ) {
		return;
	}
	$value_size = in_array( $value_size, array( 'large', 'small' ), true ) ? $value_size : 'large';
	$base_class = 'stats-block__value stats-block__value--size-' . $value_size;

	$num_token = isset( $stat['value_figure_numeric'] ) ? (string) $stat['value_figure_numeric'] : '';

	if ( '' !== $num_token && is_numeric( $num_token ) && is_finite( (float) $num_token ) ) {
		if ( $animate ) {
			// Initial HTML = final numeric token (no-JS / SEO). Trailing letters live in suffix; JS resets to 0 before observe.
			printf(
				'<span class="%1$s stats-block__value--count-up" data-target="%2$s" aria-label="%3$s">%4$s</span>',
				esc_attr( $base_class ),
				esc_attr( $num_token ),
				esc_attr( $num_token ),
				esc_html( $num_token )
			);
		} else {
			printf(
				'<span class="%1$s">%2$s</span>',
				esc_attr( $base_class ),
				esc_html( $num_token )
			);
		}
		return;
	}
	printf(
		'<span class="%1$s">%2$s</span>',
		esc_attr( $base_class ),
		esc_html( $v )
	);
};
?>
<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( 'split' === $layout ) : ?>
			<div class="row stats-block__split <?php echo esc_attr( $intro_row_align_class ); ?>">
				<div class="col-12 <?php echo $has_stats ? esc_attr( $split_col_classes['intro'] ) : ''; ?> stats-block__intro stats-block__intro--split">
					<?php if ( is_string( $pre_heading ) && '' !== trim( $pre_heading ) ) : ?>
						<p class="stats-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
					<?php endif; ?>
					<?php if ( is_string( $heading ) && '' !== trim( $heading ) ) : ?>
						<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
					<?php endif; ?>
					<?php if ( $has_copy ) : ?>
						<div class="stats-block__copy">
							<?php echo wp_kses_post( wpautop( $copy ) ); ?>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( $has_stats ) : ?>
					<div class="col-12 <?php echo esc_attr( $split_col_classes['pair'] ); ?> stats-block__stats stats-block__stats--split">
						<div class="stats-block__grid" role="list">
							<?php foreach ( $stats_rows as $stat ) : ?>
								<div class="stats-block__stat" role="listitem">
									<?php if ( '' !== $stat['prefix'] || '' !== $stat['value'] || '' !== $stat['suffix'] ) : ?>
										<p class="stats-block__figure">
											<?php if ( '' !== $stat['prefix'] ) : ?>
												<span class="stats-block__prefix stats-block__prefix--size-<?php echo esc_attr( $stat['prefix_size'] ); ?>"><?php echo esc_html( $stat['prefix'] ); ?></span>
											<?php endif; ?>
											<?php if ( '' !== $stat['value'] ) : ?>
												<?php $render_stats_value( $stat, $value_size, $stats_animate ); ?>
											<?php endif; ?>
											<?php if ( '' !== $stat['suffix'] ) : ?>
												<span class="stats-block__suffix stats-block__suffix--size-<?php echo esc_attr( $stat['suffix_size'] ); ?>"><?php echo esc_html( $stat['suffix'] ); ?></span>
											<?php endif; ?>
										</p>
									<?php endif; ?>
									<?php if ( '' !== $stat['label'] ) : ?>
										<p class="stats-block__label"><?php echo esc_html( $stat['label'] ); ?></p>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<?php if ( $has_intro ) : ?>
				<?php if ( $intro_split ) : ?>
					<div class="row stats-block__intro-row <?php echo esc_attr( $intro_row_align_class ); ?>">
						<div class="col-12 <?php echo esc_attr( $row_col_classes['intro'] ); ?> stats-block__intro stats-block__intro--stacked">
							<?php if ( is_string( $pre_heading ) && '' !== trim( $pre_heading ) ) : ?>
								<p class="stats-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
							<?php endif; ?>
							<?php if ( is_string( $heading ) && '' !== trim( $heading ) ) : ?>
								<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
							<?php endif; ?>
						</div>
						<div class="col-12 <?php echo esc_attr( $row_col_classes['pair'] ); ?> stats-block__copy stats-block__copy--aside">
							<?php echo wp_kses_post( wpautop( $copy ) ); ?>
						</div>
					</div>
				<?php else : ?>
					<div class="stats-block__intro stats-block__intro--stacked stats-block__intro--full">
						<?php if ( is_string( $pre_heading ) && '' !== trim( $pre_heading ) ) : ?>
							<p class="stats-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
						<?php endif; ?>
						<?php if ( is_string( $heading ) && '' !== trim( $heading ) ) : ?>
							<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
						<?php endif; ?>
						<?php if ( $has_copy ) : ?>
							<div class="stats-block__copy">
								<?php echo wp_kses_post( wpautop( $copy ) ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $has_stats ) : ?>
				<div class="stats-block__stats stats-block__stats--stacked<?php echo $has_intro ? ' stats-block__stats--after-intro' : ''; ?>">
					<div class="stats-block__grid" role="list">
						<?php foreach ( $stats_rows as $stat ) : ?>
							<div class="stats-block__stat" role="listitem">
								<?php if ( '' !== $stat['prefix'] || '' !== $stat['value'] || '' !== $stat['suffix'] ) : ?>
									<p class="stats-block__figure">
										<?php if ( '' !== $stat['prefix'] ) : ?>
											<span class="stats-block__prefix stats-block__prefix--size-<?php echo esc_attr( $stat['prefix_size'] ); ?>"><?php echo esc_html( $stat['prefix'] ); ?></span>
										<?php endif; ?>
											<?php if ( '' !== $stat['value'] ) : ?>
												<?php $render_stats_value( $stat, $value_size, $stats_animate ); ?>
											<?php endif; ?>
										<?php if ( '' !== $stat['suffix'] ) : ?>
											<span class="stats-block__suffix stats-block__suffix--size-<?php echo esc_attr( $stat['suffix_size'] ); ?>"><?php echo esc_html( $stat['suffix'] ); ?></span>
										<?php endif; ?>
									</p>
								<?php endif; ?>
								<?php if ( '' !== $stat['label'] ) : ?>
									<p class="stats-block__label"><?php echo esc_html( $stat['label'] ); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>
