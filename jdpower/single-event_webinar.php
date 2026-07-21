<?php
/**
 * Single template: Event & Webinar (event_webinar).
 *
 * Layout and max width are defined in `template-parts/content-event_webinar.php` and
 * `sass/components/content/_event-webinar.scss`.
 *
 * @package jdpower
 */

get_header();
?>

<main id="primary" class="site-main site-main--event-webinar">

	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content', 'event_webinar' );
	endwhile;
	?>

</main>

<?php
get_footer();
