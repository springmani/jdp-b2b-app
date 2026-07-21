<?php
/**
 * Product region dropdown (Featured Solutions + Related Products).
 *
 * @package jdpower
 *
 * @var array{grid_dom_id?: string} $args Template args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$grid_dom_id = isset( $args['grid_dom_id'] ) && is_string( $args['grid_dom_id'] ) ? trim( $args['grid_dom_id'] ) : '';
if ( '' === $grid_dom_id ) {
	return;
}

$region_terms = get_terms(
	array(
		'taxonomy'   => 'product_region',
		'hide_empty' => false,
	)
);
if ( is_wp_error( $region_terms ) || empty( $region_terms ) ) {
	return;
}

$default_region_slug = function_exists( 'jdpower_product_region_visitor_slug' )
	? jdpower_product_region_visitor_slug()
	: '';

$default_region_label = '';
if ( '' !== $default_region_slug ) {
	$visitor_term = get_term_by( 'slug', $default_region_slug, 'product_region' );
	if ( $visitor_term instanceof WP_Term && ! is_wp_error( $visitor_term ) ) {
		$default_region_label = $visitor_term->name;
	}
}

$all_regions_label = function_exists( 'jdpower_pll__' )
	? jdpower_pll__( 'All regions' )
	: __( 'All regions', 'jdpower' );

$region_label_id   = $grid_dom_id . '-region-label';
$region_list_id    = $grid_dom_id . '-region-list';
$region_trigger_id = $grid_dom_id . '-region-trigger';

$longest_region_label = $all_regions_label;
foreach ( $region_terms as $term ) {
	if ( ! $term instanceof WP_Term ) {
		continue;
	}
	$name = $term->name;
	if ( function_exists( 'mb_strlen' ) ) {
		if ( mb_strlen( $name ) > mb_strlen( $longest_region_label ) ) {
			$longest_region_label = $name;
		}
	} elseif ( strlen( $name ) > strlen( $longest_region_label ) ) {
		$longest_region_label = $name;
	}
}
?>
<div
	class="featured-solutions-block__region-combobox"
	data-featured-solutions-grid="<?php echo esc_attr( $grid_dom_id ); ?>"
>
	<label class="screen-reader-text" id="<?php echo esc_attr( $region_label_id ); ?>" for="<?php echo esc_attr( $region_trigger_id ); ?>">
		<?php esc_html_e( 'Product region', 'jdpower' ); ?>
	</label>
	<select
		class="featured-solutions-block__region-select-hidden"
		id="<?php echo esc_attr( $grid_dom_id . '-region' ); ?>"
		tabindex="-1"
		aria-hidden="true"
	>
		<option value=""<?php selected( '', $default_region_slug ); ?>><?php echo esc_html( $all_regions_label ); ?></option>
		<?php foreach ( $region_terms as $term ) : ?>
			<?php if ( ! $term instanceof WP_Term ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<option value="<?php echo esc_attr( $term->slug ); ?>"<?php selected( $default_region_slug, $term->slug ); ?>>
				<?php echo esc_html( $term->name ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<div class="featured-solutions-block__region-dropdown">
		<div class="featured-solutions-block__region-trigger-wrap">
			<div class="featured-solutions-block__region-trigger featured-solutions-block__region-trigger--sizer" aria-hidden="true">
				<span class="featured-solutions-block__region-icon">
					<img
						src="<?php echo esc_url( get_template_directory_uri() . '/images/region.svg' ); ?>"
						alt=""
						width="16"
						height="22"
						decoding="async"
					/>
				</span>
				<span class="featured-solutions-block__region-value"><?php echo esc_html( $longest_region_label ); ?></span>
				<span class="featured-solutions-block__region-chevron"></span>
			</div>
			<button
				type="button"
				class="featured-solutions-block__region-trigger"
				id="<?php echo esc_attr( $region_trigger_id ); ?>"
				aria-haspopup="listbox"
				aria-expanded="false"
				aria-labelledby="<?php echo esc_attr( $region_label_id ); ?>"
				aria-controls="<?php echo esc_attr( $region_list_id ); ?>"
			>
				<span class="featured-solutions-block__region-icon" aria-hidden="true">
					<img
						src="<?php echo esc_url( get_template_directory_uri() . '/images/region.svg' ); ?>"
						alt=""
						width="16"
						height="22"
						loading="lazy"
						decoding="async"
					/>
				</span>
				<span class="featured-solutions-block__region-value" data-featured-solutions-region-label><?php echo esc_html( $default_region_label ? $default_region_label : $all_regions_label ); ?></span>
				<span class="featured-solutions-block__region-chevron" aria-hidden="true"></span>
			</button>
		</div>
		<div
			class="featured-solutions-block__region-list"
			id="<?php echo esc_attr( $region_list_id ); ?>"
			role="listbox"
			hidden
		>
			<button type="button" class="featured-solutions-block__region-option" role="option" data-value="" aria-selected="<?php echo $default_region_slug ? 'false' : 'true'; ?>">
				<?php echo esc_html( $all_regions_label ); ?>
			</button>
			<?php foreach ( $region_terms as $term ) : ?>
				<?php if ( ! $term instanceof WP_Term ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<button type="button" class="featured-solutions-block__region-option" role="option" data-value="<?php echo esc_attr( $term->slug ); ?>" aria-selected="<?php echo $default_region_slug === $term->slug ? 'true' : 'false'; ?>">
					<?php echo esc_html( $term->name ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	</div>
</div>
