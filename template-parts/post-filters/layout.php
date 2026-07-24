<?php
/**
 * Insight Center / Product Finder filter layout.
 *
 * @package jdpower
 *
 * Expects set_query_var( 'jdpower_pf_*' ) from the parent template (get_template_part $args
 * extraction is unreliable for nested paths and the name "query").
 */

$config  = get_query_var( 'jdpower_pf_config', null );
$request = get_query_var( 'jdpower_pf_request', null );
$query   = get_query_var( 'jdpower_pf_query', null );

if ( ! is_array( $config ) || ! is_array( $request ) || ! ( $query instanceof WP_Query ) ) {
	return;
}

$cols      = isset( $config['grid_columns'] ) ? (int) $config['grid_columns'] : 4;
$cols_class = 'post-filters--cols-' . min( 4, max( 1, $cols ) );
$context   = isset( $config['context'] ) ? $config['context'] : '';

if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $context ) {
	$page_id = (int) get_option( 'page_for_posts' );
	$pf_title = $page_id ? get_the_title( $page_id ) : __( 'Insight Center', 'jdpower' );
} else {
	$pf_title = get_the_title();
}

$clear_url = jdpower_post_filters_base_url( $config );

$pills               = jdpower_post_filters_active_pills( $config, $request );
$has_active_filters  = jdpower_post_filters_request_has_active_filters( $config, $request );
$pills_list_html     = jdpower_post_filters_render_pills_list_items_html( $pills );

$has_search    = ! empty( $request['s'] );
$sort_choices  = jdpower_post_filters_display_sort_choices( $has_search );
$current_sort  = isset( $request['sort'] ) ? (string) $request['sort'] : ( $has_search ? 'relevancy' : 'newest' );
if ( ! isset( $sort_choices[ $current_sort ] ) ) {
	$current_sort = $has_search ? 'relevancy' : 'newest';
	if ( ! isset( $sort_choices[ $current_sort ] ) ) {
		$current_sort = (string) array_key_first( $sort_choices );
	}
}

$current_page    = max( 1, (int) $request['paged'] );
$per_page        = (int) $config['posts_per_page'];
$found           = (int) $query->found_posts;
$displayed_count = (int) $query->post_count;
$has_more        = $current_page < (int) $query->max_num_pages;

$noun = isset( $config['results_noun'] ) ? $config['results_noun'] : __( 'Insights', 'jdpower' );

$pf_view      = ( isset( $request['view'] ) && 'list' === $request['view'] ) ? 'list' : 'grid';
$view_class   = ( 'list' === $pf_view ) ? ' post-filters--view-list' : '';
$root_classes = trim( $cols_class . $view_class );

$sidebar_filter_groups = array();
if ( ! empty( $config['sidebar_taxonomies'] ) && is_array( $config['sidebar_taxonomies'] ) ) {
	foreach ( $config['sidebar_taxonomies'] as $group ) {
		if ( ! is_array( $group ) || empty( $group['taxonomy'] ) ) {
			continue;
		}
		$term_objects = jdpower_post_filters_get_sidebar_terms( (string) $group['taxonomy'], $config );
		if ( empty( $term_objects ) ) {
			continue;
		}
		$sidebar_filter_groups[] = array(
			'taxonomy' => (string) $group['taxonomy'],
			'label'    => isset( $group['label'] ) ? (string) $group['label'] : '',
			'terms'    => $term_objects,
		);
	}
}

$has_sidebar_filters = ! empty( $sidebar_filter_groups );
$mobile_filters_icon = get_template_directory_uri() . '/images/insights-mobile-filter.svg';

set_query_var( 'jdpower_pf_sidebar_groups', $sidebar_filter_groups );
?>
<div
	class="post-filters <?php echo esc_attr( $root_classes ); ?>"
	data-post-filters
	data-context="<?php echo esc_attr( $context ); ?>"
	data-view="<?php echo esc_attr( $pf_view ); ?>"
	data-paged="<?php echo esc_attr( (string) $current_page ); ?>"
	data-base-url="<?php echo esc_url( jdpower_post_filters_base_url( $config ) ); ?>"
	<?php if ( ! empty( $config['finder_page_id'] ) ) : ?>
		data-finder-page-id="<?php echo esc_attr( (string) (int) $config['finder_page_id'] ); ?>"
	<?php endif; ?>
>
	<div class="container">
		<header class="post-filters__header">
			<h1 class="post-filters__title"><?php echo esc_html( $pf_title ); ?></h1>
		</header>

		<div class="post-filters__search-row">
			<form id="post-filters-search-form" class="post-filters__search-form<?php echo JDPOWER_POST_FILTERS_CONTEXT_PRODUCT === $context ? ' post-filters__search-form--product' : ''; ?>" action="<?php echo esc_url( jdpower_post_filters_base_url( $config ) ); ?>" method="get" role="search">
				<label class="screen-reader-text" for="post-filters-s"><?php esc_html_e( 'Search', 'jdpower' ); ?></label>
				<div class="post-filters__search-field">
					<span class="post-filters__search-icon" aria-hidden="true"></span>
					<input
						type="search"
						id="post-filters-s"
						class="post-filters__search-input"
						name="keyword"
						value="<?php echo esc_attr( $request['s'] ); ?>"
						placeholder="<?php echo esc_attr( $config['search_placeholder'] ); ?>"
					/>
				</div>
				<?php if ( JDPOWER_POST_FILTERS_CONTEXT_PRODUCT === $context ) : ?>
					<?php
					$regions = get_terms(
						array(
							'taxonomy'   => 'product_region',
							'hide_empty' => true,
						)
					);
					if ( ! is_wp_error( $regions ) && ! empty( $regions ) ) :
						?>
						<div class="post-filters__global-filter post-filters__global-filter--region">
							<label class="screen-reader-text" for="post-filters-region"><?php echo esc_html( $config['global_filter']['label'] ); ?></label>
							<select name="region" id="post-filters-region" class="post-filters__global-select">
								<option value=""><?php echo esc_html( $config['global_filter']['label'] ); ?></option>
								<?php foreach ( $regions as $r ) : ?>
									<option value="<?php echo esc_attr( $r->slug ); ?>" <?php selected( (string) ( $request['region'] ?? '' ), $r->slug ); ?>>
										<?php echo esc_html( $r->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>
				<?php endif; ?>
				<input type="hidden" name="pf_view" value="<?php echo esc_attr( $pf_view ); ?>" />
				<?php
				// Preserve taxonomy filters in form when submitting search only.
				if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $context ) {
					if ( ! empty( $request['post_industry'] ) ) {
						echo '<input type="hidden" name="post_industry" value="' . esc_attr( implode( ',', $request['post_industry'] ) ) . '" />';
					}
					if ( ! empty( $request['post_topic'] ) ) {
						echo '<input type="hidden" name="post_topic" value="' . esc_attr( implode( ',', $request['post_topic'] ) ) . '" />';
					}
					if ( ! empty( $request['post_segment'] ) ) {
						echo '<input type="hidden" name="post_segment" value="' . esc_attr( implode( ',', $request['post_segment'] ) ) . '" />';
					}
				} else {
					if ( ! empty( $request['product_industry'] ) ) {
						echo '<input type="hidden" name="product_industry" value="' . esc_attr( implode( ',', $request['product_industry'] ) ) . '" />';
					}
					if ( ! empty( $request['product_segment'] ) ) {
						echo '<input type="hidden" name="product_segment" value="' . esc_attr( implode( ',', $request['product_segment'] ) ) . '" />';
					}
				}
				?>
			</form>
		</div>

		<div class="post-filters__body row">
			<aside class="post-filters__sidebar col-12" aria-label="<?php esc_attr_e( 'Filters', 'jdpower' ); ?>">
				<?php if ( $has_sidebar_filters ) : ?>
					<div class="post-filters__sidebar-panel post-filters__sidebar-panel--desktop">
						<?php get_template_part( 'template-parts/post-filters/sidebar', 'panel' ); ?>
					</div>
				<?php endif; ?>
			</aside>

			<div class="post-filters__main col-12">
				<div class="post-filters__toolbar">
					<?php if ( $has_sidebar_filters ) : ?>
						<button
							type="button"
							class="post-filters__mobile-filters-toggle"
							data-post-filters-mobile-toggle
							aria-expanded="false"
							aria-controls="post-filters-sidebar-panel"
						>
							<img
								class="post-filters__mobile-filters-icon"
								src="<?php echo esc_url( $mobile_filters_icon ); ?>"
								width="21"
								height="15"
								alt=""
								aria-hidden="true"
								decoding="async"
							/>
							<span class="post-filters__mobile-filters-label"><?php esc_html_e( 'Filters', 'jdpower' ); ?></span>
						</button>
					<?php endif; ?>
					<div class="post-filters__toolbar-filters"<?php echo $has_active_filters ? '' : ' hidden'; ?>>
						<div class="post-filters__tagged">
							<span class="post-filters__tagged-label"><?php esc_html_e( 'Tagged', 'jdpower' ); ?>:</span>
							<ul class="post-filters__pills">
								<?php
								echo wp_kses(
									$pills_list_html,
									array(
										'li'     => array(),
										'button' => array(
											'type'            => true,
											'class'           => true,
											'data-pill-key'   => true,
											'data-pill-value' => true,
										),
										'span'   => array(
											'class'       => true,
											'aria-hidden' => true,
										),
									)
								);
								?>
							</ul>
						</div>
						<a class="post-filters__clear" href="<?php echo esc_url( $clear_url ); ?>"><?php esc_html_e( 'Clear filters', 'jdpower' ); ?></a>
					</div>
					<div class="post-filters__toolbar-end">
						<div class="post-filters__view-toggle">
							<button
								type="button"
								class="post-filters__view-toggle-btn"
								data-post-filters-view-toggle
								aria-label="<?php echo esc_attr( 'list' === $pf_view ? __( 'Switch to grid layout', 'jdpower' ) : __( 'Switch to list layout', 'jdpower' ) ); ?>"
							>
								<span class="post-filters__view-icon post-filters__view-icon--grid" aria-hidden="true">
									<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" focusable="false">
										<circle cx="5" cy="5" r="2"/>
										<circle cx="15" cy="5" r="2"/>
										<circle cx="5" cy="15" r="2"/>
										<circle cx="15" cy="15" r="2"/>
									</svg>
								</span>
								<span class="post-filters__view-icon post-filters__view-icon--list" aria-hidden="true">
									<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" focusable="false">
										<rect x="2" y="3" width="4" height="2.5" rx="1.25" ry="1.25"/>
										<rect x="7" y="3" width="11" height="2.5" rx="1.25" ry="1.25"/>
										<rect x="2" y="7" width="4" height="2.5" rx="1.25" ry="1.25"/>
										<rect x="7" y="7" width="11" height="2.5" rx="1.25" ry="1.25"/>
										<rect x="2" y="11" width="4" height="2.5" rx="1.25" ry="1.25"/>
										<rect x="7" y="11" width="11" height="2.5" rx="1.25" ry="1.25"/>
										<rect x="2" y="15" width="4" height="2.5" rx="1.25" ry="1.25"/>
										<rect x="7" y="15" width="11" height="2.5" rx="1.25" ry="1.25"/>
									</svg>
								</span>
							</button>
						</div>
						<?php if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $context ) : ?>
						<div class="post-filters__sort">
							<label class="screen-reader-text" for="post-filters-sort-toggle"><?php esc_html_e( 'Sort', 'jdpower' ); ?></label>
							<div class="post-filters__sort-inner post-filters__sort-dropdown">
								<div class="post-filters__sort-sizer" aria-hidden="true">
									<?php foreach ( $sort_choices as $sort_label ) : ?>
										<span class="post-filters__sort-sizer-line"><?php echo esc_html( $sort_label ); ?></span>
									<?php endforeach; ?>
								</div>
								<select
									id="post-filters-sort-native"
									class="post-filters__sort-select screen-reader-text"
									name="pf_sort"
									form="post-filters-search-form"
									tabindex="-1"
									aria-hidden="true"
								>
									<?php foreach ( $sort_choices as $sort_key => $sort_label ) : ?>
										<option value="<?php echo esc_attr( $sort_key ); ?>" <?php selected( $current_sort, (string) $sort_key ); ?>>
											<?php echo esc_html( $sort_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<button
									type="button"
									class="post-filters__sort-toggle"
									id="post-filters-sort-toggle"
									aria-haspopup="listbox"
									aria-expanded="false"
									aria-controls="post-filters-sort-menu"
								>
									<span class="post-filters__sort-toggle-label"><?php echo esc_html( $sort_choices[ $current_sort ] ); ?></span>
									<span class="post-filters__sort-toggle-chevron" aria-hidden="true">
										<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" focusable="false">
											<path d="M2.5 4.5L6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</span>
								</button>
								<ul class="post-filters__sort-menu" id="post-filters-sort-menu" role="listbox" aria-labelledby="post-filters-sort-toggle" hidden>
									<?php foreach ( $sort_choices as $sort_key => $sort_label ) : ?>
										<li
											class="post-filters__sort-option"
											role="option"
											tabindex="-1"
											data-value="<?php echo esc_attr( $sort_key ); ?>"
											<?php echo $current_sort === (string) $sort_key ? 'aria-selected="true"' : 'aria-selected="false"'; ?>
										>
											<?php echo esc_html( $sort_label ); ?>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( $has_sidebar_filters ) : ?>
					<div id="post-filters-sidebar-panel" class="post-filters__sidebar-panel post-filters__sidebar-panel--mobile">
						<?php get_template_part( 'template-parts/post-filters/sidebar', 'panel' ); ?>
					</div>
				<?php endif; ?>

				<div class="post-filters__grid" data-post-filters-results>
					<?php
					if ( $query->have_posts() ) {
						jdpower_post_filters_set_card_heading_query_var( $config );
						while ( $query->have_posts() ) {
							$query->the_post();
							get_template_part( $config['card_part'] );
						}
						wp_reset_postdata();
					} else {
						echo '<p class="post-filters__empty">' . esc_html__( 'No results match your filters.', 'jdpower' ) . '</p>';
					}
					?>
				</div>

				<footer class="post-filters__footer">
					<p class="post-filters__displaying" data-post-filters-displaying>
						<?php
						printf(
							/* translators: 1: number of items currently shown, 2: total matching results, 3: noun e.g. insights */
							esc_html__( 'Displaying %1$d of %2$d %3$s', 'jdpower' ),
							$displayed_count,
							$found,
							esc_html( strtolower( $noun ) )
						);
						?>
					</p>
					<?php if ( $has_more ) : ?>
						<button type="button" class="post-filters__load-more" data-post-filters-load-more>
							<?php
							printf(
								/* translators: %d: number of posts per load */
								esc_html__( 'Load next %d', 'jdpower' ),
								(int) $config['posts_per_page']
							);
							?>
						</button>
					<?php endif; ?>
				</footer>
			</div>
		</div>
	</div>
</div>
