<?php
/**
 * Block Name: Logins
 *
 * Region-grouped login cards with client-side search and region filter.
 *
 * @package jdpower
 */

$classes = 'logins-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'logins_padding_top' );
$padding_bottom  = get_field( 'logins_padding_bottom' );
$background_tone = get_field( 'logins_background_tone' );

$valid_padding = array( 'default', 'small', 'medium', 'large', 'none' );
$classes      .= ' pt-' . ( ! empty( $padding_top ) && in_array( $padding_top, $valid_padding, true ) ? $padding_top : 'default' );
$classes      .= ' pb-' . ( ! empty( $padding_bottom ) && in_array( $padding_bottom, $valid_padding, true ) ? $padding_bottom : 'default' );

if ( empty( $background_tone ) || ! in_array( $background_tone, array( 'dark', 'medium', 'light' ), true ) ) {
	$background_tone = 'light';
}
$classes .= ' bg-' . $background_tone;

$search_placeholder = get_field( 'logins_search_placeholder' );
if ( ! is_string( $search_placeholder ) || '' === trim( $search_placeholder ) ) {
	$search_placeholder = __( 'Search Logins', 'jdpower' );
}

$region_rows = get_field( 'logins_regions' );
if ( ! is_array( $region_rows ) ) {
	$region_rows = array();
}

$sections       = array();
$filter_regions = array();

foreach ( $region_rows as $region_row ) {
	if ( ! is_array( $region_row ) ) {
		continue;
	}

	$region_term = $region_row['logins_region'] ?? null;
	if ( ! $region_term instanceof WP_Term ) {
		if ( is_numeric( $region_term ) ) {
			$region_term = get_term( (int) $region_term, 'product_region' );
		} elseif ( is_array( $region_term ) && ! empty( $region_term['term_id'] ) ) {
			$region_term = get_term( (int) $region_term['term_id'], 'product_region' );
		}
	}

	if ( ! $region_term instanceof WP_Term || is_wp_error( $region_term ) ) {
		continue;
	}

	$item_rows = isset( $region_row['logins_items'] ) && is_array( $region_row['logins_items'] )
		? $region_row['logins_items']
		: array();

	$items = array();
	foreach ( $item_rows as $item_row ) {
		if ( ! is_array( $item_row ) ) {
			continue;
		}

		$name = isset( $item_row['logins_item_name'] ) ? trim( (string) $item_row['logins_item_name'] ) : '';
		if ( '' === $name ) {
			continue;
		}

		$country = isset( $item_row['logins_item_country'] ) ? trim( (string) $item_row['logins_item_country'] ) : '';
		$copy    = isset( $item_row['logins_item_copy'] ) ? trim( (string) $item_row['logins_item_copy'] ) : '';
		$url     = isset( $item_row['logins_item_link'] ) ? trim( (string) $item_row['logins_item_link'] ) : '';
		$image   = isset( $item_row['logins_item_image'] ) && is_array( $item_row['logins_item_image'] )
			? $item_row['logins_item_image']
			: null;

		$search_bits = array_filter( array( $name, $country, $copy, $region_term->name ) );
		$items[]     = array(
			'name'         => $name,
			'country'      => $country,
			'copy'         => $copy,
			'url'          => $url,
			'image'        => $image,
			'search_text'  => function_exists( 'mb_strtolower' )
				? mb_strtolower( implode( ' ', $search_bits ) )
				: strtolower( implode( ' ', $search_bits ) ),
		);
	}

	if ( empty( $items ) ) {
		continue;
	}

	$sections[] = array(
		'term'  => $region_term,
		'items' => $items,
	);

	$filter_regions[ $region_term->slug ] = $region_term;
}

if ( empty( $sections ) ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p>' . esc_html__( 'Add at least one region with login items to display this block.', 'jdpower' ) . '</p>';
	}
	return;
}

$block_dom_id = ! empty( $block['anchor'] )
	? sanitize_title( (string) $block['anchor'] )
	: 'logins-block-' . substr( md5( wp_json_encode( $block ) ), 0, 8 );

$default_region_slug = '';

$longest_region_label = __( 'All regions', 'jdpower' );
foreach ( $filter_regions as $filter_region ) {
	if ( $filter_region instanceof WP_Term && strlen( $filter_region->name ) > strlen( $longest_region_label ) ) {
		$longest_region_label = $filter_region->name;
	}
}

$region_select_id   = $block_dom_id . '-region';
$region_trigger_id  = $block_dom_id . '-region-trigger';
$region_list_id     = $block_dom_id . '-region-list';
$search_input_id    = $block_dom_id . '-search';
$all_regions_label  = __( 'All regions', 'jdpower' );
?>
<section class="<?php echo esc_attr( $classes ); ?>" data-logins-root>
	<div class="container">
		<div class="logins-block__toolbar">
			<div class="logins-block__filter logins-block__filter--region">
				<div
					class="logins-block__region-combobox"
					data-logins-region-combobox
				>
					<select
						class="logins-block__region-select-hidden"
						id="<?php echo esc_attr( $region_select_id ); ?>"
						tabindex="-1"
						aria-hidden="true"
					>
						<option value=""<?php selected( '', $default_region_slug ); ?>><?php echo esc_html( $all_regions_label ); ?></option>
						<?php foreach ( $filter_regions as $filter_region ) : ?>
							<option value="<?php echo esc_attr( $filter_region->slug ); ?>"<?php selected( $default_region_slug, $filter_region->slug ); ?>>
								<?php echo esc_html( $filter_region->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<div class="logins-block__region-dropdown">
						<div class="logins-block__region-trigger-wrap">
							<div class="logins-block__region-trigger logins-block__region-trigger--sizer" aria-hidden="true">
								<span class="logins-block__region-value"><?php echo esc_html( $longest_region_label ); ?></span>
								<span class="logins-block__region-chevron"></span>
							</div>
							<button
								type="button"
								class="logins-block__region-trigger"
								id="<?php echo esc_attr( $region_trigger_id ); ?>"
								aria-haspopup="listbox"
								aria-expanded="false"
								aria-label="<?php esc_attr_e( 'Select Region', 'jdpower' ); ?>"
								aria-controls="<?php echo esc_attr( $region_list_id ); ?>"
							>
								<span class="logins-block__region-value" data-logins-region-label>
									<?php echo esc_html( $all_regions_label ); ?>
								</span>
								<span class="logins-block__region-chevron" aria-hidden="true"></span>
							</button>
						</div>
						<div
							class="logins-block__region-list"
							id="<?php echo esc_attr( $region_list_id ); ?>"
							role="listbox"
							hidden
						>
							<button type="button" class="logins-block__region-option" role="option" data-value="" aria-selected="<?php echo $default_region_slug ? 'false' : 'true'; ?>">
								<?php echo esc_html( $all_regions_label ); ?>
							</button>
							<?php foreach ( $filter_regions as $filter_region ) : ?>
								<button
									type="button"
									class="logins-block__region-option"
									role="option"
									data-value="<?php echo esc_attr( $filter_region->slug ); ?>"
									aria-selected="<?php echo $default_region_slug === $filter_region->slug ? 'true' : 'false'; ?>"
								>
									<?php echo esc_html( $filter_region->name ); ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>

			<div class="logins-block__filter logins-block__filter--search">
				<div class="logins-block__search-field">
					<span class="logins-block__search-icon" aria-hidden="true"></span>
					<input
						type="search"
						class="logins-block__search-input"
						id="<?php echo esc_attr( $search_input_id ); ?>"
						data-logins-search
						placeholder="<?php echo esc_attr( $search_placeholder ); ?>"
						aria-label="<?php echo esc_attr( $search_placeholder ); ?>"
						autocomplete="off"
					/>
				</div>
			</div>
		</div>

		<div class="logins-block__content" id="<?php echo esc_attr( $block_dom_id ); ?>" data-logins-content>
			<?php foreach ( $sections as $section ) : ?>
				<?php
				$region_term = $section['term'];
				$items       = $section['items'];
				?>
				<section
					class="logins-block__region"
					data-logins-region="<?php echo esc_attr( $region_term->slug ); ?>"
					aria-labelledby="<?php echo esc_attr( $block_dom_id . '-heading-' . $region_term->slug ); ?>"
				>
					<h2 class="logins-block__region-heading" id="<?php echo esc_attr( $block_dom_id . '-heading-' . $region_term->slug ); ?>">
						<?php echo esc_html( $region_term->name ); ?>
					</h2>
					<div class="logins-block__grid">
						<?php foreach ( $items as $item ) : ?>
							<?php
							$has_url   = '' !== $item['url'];
							$tag       = $has_url ? 'a' : 'article';
							$tag_class = 'logins-block__card' . ( $has_url ? ' logins-block__card--link' : ' logins-block__card--static' );
							$attrs     = $has_url
								? ' href="' . esc_url( $item['url'] ) . '" target="_blank" rel="noopener noreferrer"'
								: '';
							?>
							<<?php echo tag_escape( $tag ); ?>
								class="<?php echo esc_attr( $tag_class ); ?>"
								data-logins-item
								data-logins-search="<?php echo esc_attr( $item['search_text'] ); ?>"
								<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?>
							>
								<div class="logins-block__card-inner">
									<?php if ( ! empty( $item['image']['ID'] ) ) : ?>
										<div class="logins-block__card-media">
											<?php
											echo wp_get_attachment_image(
												(int) $item['image']['ID'],
												'medium',
												false,
												array(
													'class'   => 'logins-block__card-image',
													'alt'     => ! empty( $item['image']['alt'] ) ? $item['image']['alt'] : $item['name'],
													'loading' => 'lazy',
													'decoding'=> 'async',
												)
											);
											?>
										</div>
									<?php endif; ?>
									<div class="logins-block__card-body">
										<?php if ( '' !== $item['country'] ) : ?>
											<p class="logins-block__card-country"><?php echo esc_html( $item['country'] ); ?></p>
										<?php endif; ?>
										<h3 class="logins-block__card-name"><?php echo esc_html( $item['name'] ); ?></h3>
										<?php if ( '' !== $item['copy'] ) : ?>
											<p class="logins-block__card-copy"><?php echo esc_html( $item['copy'] ); ?></p>
										<?php endif; ?>
									</div>
								</div>
							</<?php echo tag_escape( $tag ); ?>>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
</section>
