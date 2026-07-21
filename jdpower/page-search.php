<?php
/**
 * Template Name: Search
 * The template for the site search page (?keyword=… loads results here).
 *
 * Assign this template to a Page in the admin and use that page’s URL in the nav
 * (e.g. Content Type “Search icon” with the page permalink). Search URLs use
 * <code>?keyword=your+terms</code> instead of <code>?s=</code>.
 *
 * @package jdpower
 */

get_header();

$search_term = get_query_var( 'keyword' );
if ( ! is_string( $search_term ) ) {
	$search_term = '';
}
$search_term = sanitize_text_field( $search_term );
if ( '' === $search_term ) {
	$search_term = get_search_query();
}

$paged           = max( 1, (int) get_query_var( 'result_paged' ) );
$current_pf_pt   = jdpower_search_page_get_pf_pt();
$current_sort    = jdpower_search_page_get_search_sort();

$search_filters = jdpower_search_page_content_filter_choices();

$display_sort         = jdpower_search_page_display_sort( $search_term, $current_sort );
$display_sort_choices = jdpower_search_page_display_sort_choices( $search_term );

$results_args  = jdpower_search_page_results_query_args( $search_term, $paged, $current_pf_pt, $current_sort );
$results_query = new WP_Query( $results_args );

$has_active_filters = jdpower_search_page_has_active_filters( $search_term, $current_pf_pt );
$search_pills       = jdpower_search_page_active_pills( $search_term, $current_pf_pt, $current_sort );
$pills_list_html    = jdpower_search_page_render_pills_list_items_html( $search_pills );
$clear_url          = jdpower_search_page_clear_url();
?>

	<main id="primary" class="site-main site-main--search-page">
		<div class="post-filters post-filters--view-list post-filters--cols-4 search-page-layout">
			<div class="container">
				<?php
				while ( have_posts() ) :
					the_post();
					?>

					<header class="post-filters__header search-page-layout__intro">
						<h1 class="post-filters__title"><?php the_title(); ?></h1>
						<?php if ( get_the_content() ) : ?>
							<div class="page-intro entry-content search-page-layout__intro-content">
								<?php the_content(); ?>
							</div>
						<?php endif; ?>
					</header>

					<div class="post-filters__search-row">
						<form class="post-filters__search-form search-page-form" method="get" action="<?php echo esc_url( get_permalink() ); ?>" role="search">
							<label class="screen-reader-text" for="search-page-keyword">
								<?php esc_html_e( 'Search for:', 'jdpower' ); ?>
							</label>
							<div class="post-filters__search-field">
								<span class="post-filters__search-icon" aria-hidden="true"></span>
								<input
									type="search"
									id="search-page-keyword"
									class="post-filters__search-input"
									name="keyword"
									value="<?php echo esc_attr( $search_term ); ?>"
									autocomplete="off"
									placeholder="<?php esc_attr_e( 'Search…', 'jdpower' ); ?>"
								/>
							</div>
							<?php if ( '' !== $current_pf_pt ) : ?>
								<input type="hidden" name="pf_pt" value="<?php echo esc_attr( $current_pf_pt ); ?>" />
							<?php endif; ?>
							<?php if ( 'relevancy' !== $display_sort && ( '' !== $search_term || 'date' !== $display_sort ) ) : ?>
								<input type="hidden" name="search_sort" value="<?php echo esc_attr( $display_sort ); ?>" />
							<?php endif; ?>
						</form>
					</div>

					<?php
				endwhile;
				wp_reset_postdata();
				?>

				<div class="post-filters__body row">
					<aside class="post-filters__sidebar col-12" aria-label="<?php esc_attr_e( 'Filters', 'jdpower' ); ?>">
						<details class="post-filters__accordion" open>
							<summary class="post-filters__accordion-summary">
								<span class="post-filters__accordion-title"><?php esc_html_e( 'Content Type', 'jdpower' ); ?></span>
								<span class="post-filters__accordion-toggle" aria-hidden="true"></span>
							</summary>
							<ul class="post-filters__term-list">
								<li class="post-filters__term-item">
									<?php
									$all_url = jdpower_search_page_url( $search_term, '', 1, $current_sort );
									$all_on  = ( '' === $current_pf_pt );
									?>
									<a
										class="post-filters__term-link<?php echo $all_on ? ' is-active' : ''; ?>"
										href="<?php echo esc_url( $all_url ); ?>"
									>
										<span class="post-filters__term-label"><?php esc_html_e( 'All types', 'jdpower' ); ?></span>
									</a>
								</li>
								<?php foreach ( $search_filters as $filter_slug => $filter_label ) : ?>
									<?php
									$next_pt = ( $current_pf_pt === $filter_slug ) ? '' : $filter_slug;
									$href    = jdpower_search_page_url( $search_term, $next_pt, 1, $current_sort );
									$is_on   = ( $current_pf_pt === $filter_slug );
									?>
									<li class="post-filters__term-item">
										<a
											class="post-filters__term-link<?php echo $is_on ? ' is-active' : ''; ?>"
											href="<?php echo esc_url( $href ); ?>"
										>
											<span class="post-filters__term-label"><?php echo esc_html( $filter_label ); ?></span>
											<?php if ( $is_on ) : ?>
												<span class="post-filters__remove" aria-hidden="true">×</span>
											<?php endif; ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</details>
					</aside>

					<div class="post-filters__main col-12">
						<?php if ( $results_query ) : ?>

							<?php
							$found       = (int) $results_query->found_posts;
							$per_page    = (int) $results_query->get( 'posts_per_page' );
							$per_page    = $per_page > 0 ? $per_page : (int) get_option( 'posts_per_page', 10 );
							$range_end   = min( $found, $paged * $per_page );
							$range_start = $found > 0 ? ( ( $paged - 1 ) * $per_page ) + 1 : 0;
							?>
							<div class="post-filters__toolbar search-page-results-toolbar">
								<div class="search-page-results-toolbar__top">
									<div class="post-filters__toolbar-filters"<?php echo $has_active_filters ? '' : ' hidden'; ?>>
										<div class="post-filters__tagged">
											<span class="post-filters__tagged-label"><?php esc_html_e( 'Tagged', 'jdpower' ); ?>:</span>
											<ul class="post-filters__pills">
												<?php
												echo wp_kses(
													$pills_list_html,
													array(
														'li' => array(),
														'a'  => array(
															'class' => true,
															'href'  => true,
														),
														'span' => array(
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
										<form class="search-page-sort-form" method="get" action="<?php echo esc_url( get_permalink() ); ?>">
											<input type="hidden" name="keyword" value="<?php echo esc_attr( $search_term ); ?>" />
											<?php if ( '' !== $current_pf_pt ) : ?>
												<input type="hidden" name="pf_pt" value="<?php echo esc_attr( $current_pf_pt ); ?>" />
											<?php endif; ?>
											<label class="search-page-sort-form__label" for="search-page-sort"><?php esc_html_e( 'Sort by', 'jdpower' ); ?></label>
											<select name="search_sort" id="search-page-sort" class="post-filters__global-select" autocomplete="off" onchange="this.form.submit()">
												<?php foreach ( $display_sort_choices as $sort_key => $sort_label ) : ?>
													<option value="<?php echo esc_attr( $sort_key ); ?>" <?php selected( $display_sort, (string) $sort_key ); ?>>
														<?php echo esc_html( $sort_label ); ?>
													</option>
												<?php endforeach; ?>
											</select>
										</form>
									</div>
								</div>
								<p class="search-page-results-count">
									<?php
									if ( $found < 1 ) {
										esc_html_e( 'No results found.', 'jdpower' );
									} else {
										printf(
											wp_kses(
												/* translators: 1: first result number, 2: last result number, 3: total results. */
												__( 'Showing <strong>%1$d – %2$d</strong> of <strong>%3$d</strong> results', 'jdpower' ),
												array( 'strong' => array() )
											),
											$range_start,
											$range_end,
											$found
										);
									}
									?>
								</p>
							</div>

							<?php if ( $results_query->have_posts() ) : ?>

								<div class="search-page-results">
									<div class="post-filters__grid">
										<?php
										while ( $results_query->have_posts() ) :
											$results_query->the_post();
											get_template_part( 'template-parts/content', 'search' );
										endwhile;
										?>
									</div>
								</div>

								<?php
								$pagination_markup = paginate_links(
									jdpower_search_page_paginate_links_args(
										$results_query->max_num_pages,
										$paged,
										$search_term,
										$current_pf_pt,
										$display_sort
									)
								);
								if ( $pagination_markup ) :
									?>
									<nav class="search-page-pagination" aria-label="<?php esc_attr_e( 'Results pages', 'jdpower' ); ?>">
										<?php echo $pagination_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links() markup. ?>
									</nav>
									<?php
								endif;

								wp_reset_postdata();
							endif;
							?>

						<?php endif; ?>
					</div>
				</div>

			</div>
		</div>
	</main>

<?php
get_footer();
