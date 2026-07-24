<?php
/**
 * Block Name: Hero
 */

$classes = 'hero-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

// Section height / vertical padding.
$hero_size = get_field( 'hero_size' );
if ( empty( $hero_size ) || ! in_array( $hero_size, array( 'large', 'medium', 'small' ), true ) ) {
	$hero_size = 'medium';
}
$classes .= ' hero-block--size-' . $hero_size;

// Headline font scale.
$hero_style_size = get_field( 'hero_style_size' );
if ( empty( $hero_style_size ) || ! in_array( $hero_style_size, array( 'large', 'medium', 'small' ), true ) ) {
	$hero_style_size = 'medium';
}
$hero_h1_classes = array( 'hero-block__heading' );
if ( 'large' === $hero_style_size ) {
	$hero_h1_classes[] = 'large';
} elseif ( 'medium' === $hero_style_size ) {
	$hero_h1_classes[] = 'medium';
}

// Optional max-width on the inner row.
$hero_content_max_width = get_field( 'hero_content_max_width' );
$valid_content_max    = array( 'full', 'large', 'medium', 'small' );
if ( empty( $hero_content_max_width ) || ! in_array( $hero_content_max_width, $valid_content_max, true ) ) {
	$hero_content_max_width = 'full';
}
if ( 'full' !== $hero_content_max_width ) {
	$classes .= ' hero-block--content-max-' . $hero_content_max_width;
}

$background_tone = get_field( 'hero_background_tone' );
if ( empty( $background_tone ) || ! in_array( $background_tone, array( 'dark', 'medium', 'light' ), true ) ) {
	$background_tone = 'light';
}
$classes .= ' bg-' . $background_tone;

// Background image + overlay (optional): `<img>` fills backdrop; overlay is a sibling for gradients (lazy-load + alt via attachment).
$hero_background_image    = get_field( 'hero_background_image' );
$hero_background_position = get_field( 'hero_background_position' );
$hero_background_overlay  = get_field( 'hero_background_overlay' );

$has_background_image = is_array( $hero_background_image ) && ! empty( $hero_background_image['ID'] );

if ( $has_background_image ) {
	$classes .= ' hero-block--has-bg-image';
	if ( empty( $hero_background_position ) || ! in_array( $hero_background_position, array( 'center', 'top', 'bottom' ), true ) ) {
		$hero_background_position = 'center';
	}
	if ( empty( $hero_background_overlay ) || ! in_array( $hero_background_overlay, array( 'dark', 'medium', 'light' ), true ) ) {
		$hero_background_overlay = 'dark';
	}
	if ( ! empty( get_field( 'hero_ken_burns' ) ) ) {
		$classes .= ' hero-block--ken-burns';
	}
}

$background_video = get_field( 'hero_background_video' ); // optional
$has_bleed_bg     = $has_background_image || ( is_array( $background_video ) && ! empty( $background_video['url'] ) );
if ( is_array( $background_video ) && ! empty( $background_video['url'] ) ) {
	$classes .= ' has-video';
}

// Text: solid vs photo/video (tone or explicit light/dark).
if ( ! $has_bleed_bg ) {
	if ( 'dark' === $background_tone ) {
		$classes .= ' hero-block--tone-dark';
	} else {
		$classes .= ' hero-block--tone-light';
	}
} else {
	$hero_font_color = get_field( 'hero_font_color' );
	if ( $hero_font_color === 'light' ) {
		$classes .= ' text-light';
	} elseif ( $hero_font_color === 'dark' ) {
		$classes .= ' text-dark';
	}
}

// Layout: column image OR copy-right (mutually exclusive; copy-right wins if both are set).
$hero_image         = get_field( 'hero_image' );
$hero_copy_right_on = ! empty( get_field( 'hero_copy_right' ) );
$has_column_image_raw = is_array( $hero_image ) && ! empty( $hero_image['ID'] ) && (int) $hero_image['ID'] > 0;
$has_column_image   = $has_column_image_raw && ! $hero_copy_right_on;
$copy_right         = ! $has_column_image && $hero_copy_right_on;
$two_columns        = $has_column_image || $copy_right;

if ( $two_columns ) {
	$classes .= ' hero-block--two-columns';
}
if ( $copy_right ) {
	$classes .= ' hero-block--copy-right';
}
if ( $has_column_image ) {
	$classes .= ' hero-block--column-image';
}

$hero_vertical_align = get_field( 'hero_vertical_align' );
if ( empty( $hero_vertical_align ) || ! in_array( $hero_vertical_align, array( 'top', 'center', 'bottom' ), true ) ) {
	$hero_vertical_align = 'bottom';
}

// Section + single-column: position the hero block vertically in the section.
$classes .= ' hero-block--valign-' . $hero_vertical_align;

// Copy-right: vertical alignment of the copy column only.
$copy_right_copy_valign = $hero_vertical_align;

// Column image: maps hero_vertical_align to top | center | bottom.
$column_image_valign = 'top';
if ( $has_column_image ) {
	if ( 'center' === $hero_vertical_align ) {
		$column_image_valign = 'center';
	} elseif ( 'bottom' === $hero_vertical_align ) {
		$column_image_valign = 'bottom';
	} else {
		$column_image_valign = 'top';
	}
	$classes .= ' hero-block--column-image-valign-' . $column_image_valign;
}

$hero_pre_heading  = get_field( 'hero_pre_heading' );
$hero_headline     = get_field( 'hero_headline' );
$hero_sub_heading  = get_field( 'hero_sub_heading' );
$hero_copy         = get_field( 'hero_copy' );
$hero_cta         = get_field( 'hero_cta' );

$cta_url    = is_array( $hero_cta ) ? ( $hero_cta['url'] ?? '' ) : '';
$cta_title  = is_array( $hero_cta ) ? ( $hero_cta['title'] ?? '' ) : '';
$cta_target = is_array( $hero_cta ) ? ( $hero_cta['target'] ?? '' ) : '';

$target_attr = '';
$rel_attr    = '';

if ( '_blank' === $cta_target ) {
	$target_attr = ' target="_blank"';
	$rel_attr    = ' rel="noopener noreferrer"';
}

$image_id = $has_column_image ? (int) $hero_image['ID'] : 0;

// HTML fragments for output below.
$hero_pre_markup = '';
if ( ! empty( $hero_pre_heading ) ) {
	$hero_pre_markup = sprintf(
		'<p class="hero-block__pre preheading">%s</p>',
		wp_kses_post( $hero_pre_heading )
	);
}

$hero_headline_markup = '';
if ( ! empty( $hero_headline ) ) {
	$hero_headline_markup = sprintf(
		'<h1 class="%s">%s</h1>',
		esc_attr( implode( ' ', $hero_h1_classes ) ),
		wp_kses_post( $hero_headline )
	);
}

$hero_sub_heading_markup = '';
if ( ! empty( $hero_sub_heading ) ) {
	$hero_sub_heading_markup = sprintf(
		'<h2 class="x-medium hero-block__subheading">%s</h2>',
		wp_kses_post( $hero_sub_heading )
	);
}

$hero_copy_markup = '';
if ( ! empty( $hero_copy ) ) {
	$hero_copy_markup = sprintf(
		'<div class="hero-block__copy">%s</div>',
		wp_kses_post( wpautop( $hero_copy ) )
	);
}

$hero_cta_markup = '';
if ( $cta_url && $cta_title ) {
	$hero_cta_markup = sprintf(
		'<a class="btn btn-small hero-block__btn" href="%s"%s>%s</a>',
		esc_url( $cta_url ),
		$target_attr . $rel_attr,
		esc_html( $cta_title )
	);
}
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<?php if ( $has_background_image ) : ?>
		<div class="hero-block__backdrop">
			<?php
			echo wp_get_attachment_image(
				(int) $hero_background_image['ID'],
				'full',
				false,
				array(
					'class'           => 'hero-block__bg-img hero-block__bg-img--' . sanitize_html_class( $hero_background_position ),
					'loading'         => 'eager',
					'fetchpriority'   => 'high',
					'decoding'        => 'async',
				)
			);
			?>
			<div class="hero-block__overlay hero-block__overlay--<?php echo esc_attr( $hero_background_overlay ); ?>" aria-hidden="true"></div>
		</div>
	<?php endif; ?>
	<?php if ( is_array( $background_video ) && ! empty( $background_video['url'] ) ) : // Background video. ?>
		<video autoplay muted loop playsinline>
			<source src="<?php echo esc_url( $background_video['url'] ); ?>" type="video/mp4">
		</video>
	<?php endif; ?>

	<div class="container">
		<?php
		// Column image | copy-right | single column.
		if ( $has_column_image ) :
			$ci_center                = ( 'center' === $column_image_valign );
			$ci_bottom                = ( 'bottom' === $column_image_valign );
			$column_image_media_align = $ci_bottom ? ' hero-block__media-col--align-bottom' : '';
			if ( $ci_center ) {
				$column_image_row_align = ' align-items-lg-center';
			} elseif ( $ci_bottom ) {
				$column_image_row_align = ' align-items-lg-stretch';
			} else {
				$column_image_row_align = ' align-items-lg-start';
			}
			?>
			<div class="row g-0 hero-block__row--column-image hero-block__row--column-image--center-pre<?php echo esc_attr( $column_image_row_align ); ?>">
				<div class="hero-block__column-image-stack">
					<?php if ( ! empty( $hero_pre_heading ) ) : ?>
						<div class="hero-block__col-image-pre hero-content">
							<?php echo $hero_pre_markup; ?>
						</div>
					<?php endif; ?>
					<div class="hero-block__col-image-body hero-content">
						<?php echo $hero_headline_markup; ?>
						<?php echo $hero_sub_heading_markup; ?>
						<?php echo $hero_copy_markup; ?>
						<?php echo $hero_cta_markup; ?>
					</div>
				</div>
				<div class="hero-block__media hero-block__media-col<?php echo esc_attr( $column_image_media_align ); ?>">
					<div class="hero-block__figure">
						<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'hero-block__img' ) ); ?>
					</div>
				</div>
			</div>
			<?php
		elseif ( $copy_right ) :
			// Copy-right: headline + copy (7/5), optional CTA row.
			?>
			<div class="row hero-block__copy-right-row">
				<?php if ( ! empty( $hero_pre_heading ) ) : ?>
					<div class="col-12 hero-content hero-block__pre-col">
						<?php echo $hero_pre_markup; ?>
					</div>
				<?php endif; ?>
				<div class="col-12 hero-content hero-block__headline-copy-wrap">
					<div class="row hero-block__headline-copy-row">
						<div class="col-12 col-xl-7 hero-content hero-block__headline-col">
							<?php echo $hero_headline_markup; ?>
							<?php echo $hero_sub_heading_markup; ?>
						</div>
						<div class="col-12 col-xl-5 hero-content hero-block__body hero-block__body--copy-right hero-block__copy-col hero-block__copy-valign--<?php echo esc_attr( $copy_right_copy_valign ); ?>">
							<?php echo $hero_copy_markup; ?>
						</div>
					</div>
				</div>
				<?php if ( $cta_url && $cta_title ) : ?>
					<div class="col-12 hero-content hero-block__cta-col">
						<?php echo $hero_cta_markup; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		else :
			// Single column (no side image / no copy-right).
			?>
			<div class="row hero-block__single-row">
				<div class="col-12 hero-content">
					<?php echo $hero_pre_markup; ?>
					<?php echo $hero_headline_markup; ?>
					<?php echo $hero_sub_heading_markup; ?>
					<?php echo $hero_copy_markup; ?>
					<?php echo $hero_cta_markup; ?>
				</div>
			</div>
			<?php
		endif;
		?>
	</div>
</section>
