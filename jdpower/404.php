<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package jdpower
 */

get_header();

$show_hero       = function_exists( 'jdpower_404_options_hero_has_content' ) && jdpower_404_options_hero_has_content();
$show_below_copy = function_exists( 'jdpower_404_options_below_hero_has_content' ) && jdpower_404_options_below_hero_has_content();
$show_default    = ! $show_hero && ! $show_below_copy;

$below_hero_html = '';
if ( $show_below_copy && function_exists( 'get_field' ) ) {
	$below_hero_html = get_field( 'error_404_below_hero_copy', 'option' );
}
?>

	<main id="primary" class="site-main">

		<?php if ( $show_hero && function_exists( 'get_field' ) ) : ?>
			<?php
			$hero_image              = get_field( 'error_404_hero_image', 'option' );
			$hero_background_image   = get_field( 'error_404_hero_background_image', 'option' );
			$hero_background_position = get_field( 'error_404_hero_background_position', 'option' );
			$hero_background_overlay  = get_field( 'error_404_hero_background_overlay', 'option' );
			$pre_heading             = get_field( 'error_404_hero_pre_heading', 'option' );
			$heading                 = get_field( 'error_404_hero_heading', 'option' );
			$copy                    = get_field( 'error_404_hero_copy', 'option' );
			$cta                     = get_field( 'error_404_hero_cta', 'option' );

			$pre_heading = is_string( $pre_heading ) ? trim( $pre_heading ) : '';
			$heading     = is_string( $heading ) ? trim( $heading ) : '';
			$copy        = is_string( $copy ) ? trim( $copy ) : '';

			$has_image             = is_array( $hero_image ) && ! empty( $hero_image['ID'] );
			$image_id              = $has_image ? (int) $hero_image['ID'] : 0;
			$has_background_image  = is_array( $hero_background_image ) && ! empty( $hero_background_image['ID'] );
			$bg_image_id           = $has_background_image ? (int) $hero_background_image['ID'] : 0;

			if ( $has_background_image ) {
				if ( empty( $hero_background_position ) || ! in_array( $hero_background_position, array( 'center', 'top', 'bottom' ), true ) ) {
					$hero_background_position = 'center';
				}
				if ( empty( $hero_background_overlay ) || ! in_array( $hero_background_overlay, array( 'dark', 'medium', 'light' ), true ) ) {
					$hero_background_overlay = 'dark';
				}
			}

			$cta_url    = is_array( $cta ) ? ( $cta['url'] ?? '' ) : '';
			$cta_title  = is_array( $cta ) ? trim( (string) ( $cta['title'] ?? '' ) ) : '';
			$cta_target = is_array( $cta ) ? ( $cta['target'] ?? '' ) : '';

			$target_attr = '';
			$rel_attr    = '';
			if ( '_blank' === $cta_target ) {
				$target_attr = ' target="_blank"';
				$rel_attr    = ' rel="noopener noreferrer"';
			}

			$hero_classes = 'hero-block hero-block--404 bg-light hero-block--valign-center';
			if ( ! $has_background_image ) {
				$hero_classes .= ' hero-block--tone-light';
			}
			if ( $has_background_image ) {
				$hero_classes .= ' hero-block--has-bg-image';
				if ( 'light' === $hero_background_overlay ) {
					$hero_classes .= ' text-dark';
				} else {
					$hero_classes .= ' text-light';
				}
			}
			if ( $has_image ) {
				$hero_classes .= ' hero-block--two-columns hero-block--column-image hero-block--column-image-valign-center';
			}

			$pre_markup = '';
			if ( '' !== $pre_heading ) {
				$pre_markup = sprintf(
					'<p class="hero-block__pre preheading">%s</p>',
					wp_kses_post( $pre_heading )
				);
			}

			$heading_markup = '';
			if ( '' !== $heading ) {
				$heading_markup = sprintf(
					'<h1 class="hero-block__heading medium">%s</h1>',
					wp_kses_post( $heading )
				);
			}

			$copy_markup = '';
			if ( '' !== $copy ) {
				$copy_markup = sprintf(
					'<div class="hero-block__copy">%s</div>',
					wp_kses_post( wpautop( $copy ) )
				);
			}

			$cta_markup = '';
			if ( $cta_url && $cta_title ) {
				$cta_markup = sprintf(
					'<a class="btn btn-small hero-block__btn" href="%s"%s>%s</a>',
					esc_url( $cta_url ),
					$target_attr . $rel_attr,
					esc_html( $cta_title )
				);
			}
			?>
			<section class="<?php echo esc_attr( $hero_classes ); ?>">
				<?php if ( $has_background_image ) : ?>
					<div class="hero-block__backdrop">
						<?php
						echo wp_get_attachment_image(
							$bg_image_id,
							'full',
							false,
							array(
								'class'         => 'hero-block__bg-img hero-block__bg-img--' . sanitize_html_class( $hero_background_position ),
								'loading'       => 'eager',
								'fetchpriority' => 'high',
								'decoding'      => 'async',
							)
						);
						?>
						<div class="hero-block__overlay hero-block__overlay--<?php echo esc_attr( $hero_background_overlay ); ?>" aria-hidden="true"></div>
					</div>
				<?php endif; ?>
				<div class="container">
					<?php if ( $has_image ) : ?>
						<div class="row g-0 hero-block__row--column-image hero-block__row--column-image--center-pre align-items-lg-center">
							<div class="hero-block__column-image-stack">
								<?php if ( '' !== $pre_heading ) : ?>
									<div class="hero-block__col-image-pre hero-content">
										<?php echo $pre_markup; ?>
									</div>
								<?php endif; ?>
								<div class="hero-block__col-image-body hero-content">
									<?php echo $heading_markup; ?>
									<?php echo $copy_markup; ?>
									<?php echo $cta_markup; ?>
								</div>
							</div>
							<div class="hero-block__media hero-block__media-col">
								<div class="hero-block__figure">
									<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'hero-block__img' ) ); ?>
								</div>
							</div>
						</div>
					<?php else : ?>
						<div class="row hero-block__single-row">
							<div class="col-12 hero-content">
								<?php echo $pre_markup; ?>
								<?php echo $heading_markup; ?>
								<?php echo $copy_markup; ?>
								<?php echo $cta_markup; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $show_below_copy ) : ?>
			<section class="error-404__below-hero">
				<div class="container error-404__below-hero-inner">
					<div class="error-404__below-hero-copy">
						<?php echo wp_kses_post( $below_hero_html ); ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $show_default ) : ?>
			<section class="error-404 not-found">
				<div class="container">
					<header class="page-header">
						<h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'jdpower' ); ?></h1>
					</header>

					<div class="page-content">
						<p><?php esc_html_e( 'It looks like nothing was found at this location.', 'jdpower' ); ?></p>
					</div>
				</div>
			</section>
		<?php endif; ?>

	</main>

<?php
get_footer();
