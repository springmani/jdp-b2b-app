<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package jdpower
 */

get_header();
?>

	<main id="primary" class="site-main site-main--search-page">
		<div class="post-filters search-page-layout">
			<div class="container">
				<?php if ( have_posts() ) : ?>

					<header class="post-filters__header search-page-layout__intro">
						<h1 class="post-filters__title">
							<?php
							/* translators: %s: search query. */
							printf( esc_html__( 'Search Results for: %s', 'jdpower' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
							?>
						</h1>
					</header>

					<div class="post-filters post-filters--view-list post-filters--cols-4 search-page-results">
						<div class="post-filters__grid">
							<?php
							while ( have_posts() ) :
								the_post();
								get_template_part( 'template-parts/content', 'search' );
							endwhile;
							?>
						</div>
					</div>

					<?php the_posts_navigation(); ?>

				<?php else : ?>

					<?php get_template_part( 'template-parts/content', 'none' ); ?>

				<?php endif; ?>
			</div>
		</div>
	</main>

<?php
get_footer();
