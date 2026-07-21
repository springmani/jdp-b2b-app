<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package jdpower
 */

get_header();
?>

	<main id="primary" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', get_post_type() );

			/* ?>
			<div class="container">
				<div class="pagination">
					<?php
						the_post_navigation(
							array(
								'prev_text' => '<span class="btn nav-subtitle">' . esc_html__( 'Previous', 'jdpower' ) . '</span>',
								'next_text' => '<span class="btn nav-subtitle">' . esc_html__( 'Next', 'jdpower' ) . '</span>',
							)
						);
					?>
				</div>
			</div>
			<?php */

		endwhile;
		?>

	</main>

<?php
get_footer();