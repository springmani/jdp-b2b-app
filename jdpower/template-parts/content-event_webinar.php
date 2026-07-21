<?php
/**
 * Event & Webinar single (event_webinar).
 *
 * Full-width row: post type, title, date. Below: two columns (image, body) | event details, or a single column when no details. Related insights follow (full-width, same as other singles).
 * Mobile: event details above image and main column content.
 *
 * @package jdpower
 */

$has_event_details = function_exists( 'jdpower_event_webinar_has_event_details' ) && jdpower_event_webinar_has_event_details();
$has_thumbnail     = has_post_thumbnail();
$time_line         = $has_event_details && function_exists( 'jdpower_event_webinar_datetime_display_line' )
	? jdpower_event_webinar_datetime_display_line()
	: '';
$format_line       = $has_event_details && function_exists( 'jdpower_event_webinar_format_display_line' )
	? jdpower_event_webinar_format_display_line()
	: '';

$cta     = function_exists( 'get_field' ) ? get_field( 'webinar_registration_cta' ) : null;
$cta_url = is_array( $cta ) && ! empty( $cta['url'] ) ? (string) $cta['url'] : '';
$cta_lbl = is_array( $cta ) && is_string( $cta['title'] ) && '' !== trim( $cta['title'] )
	? trim( $cta['title'] )
	: __( 'Sign up now', 'jdpower' );
$cta_target = is_array( $cta ) && ! empty( $cta['target'] ) && '_blank' === (string) $cta['target'] ? '_blank' : '';

$format_value = function_exists( 'get_field' ) ? get_field( 'webinar_event_format' ) : '';
$format_value = is_string( $format_value ) && '' !== $format_value ? $format_value : 'online';

$event_details_label = function_exists( 'jdpower_pll__' )
	? jdpower_pll__( 'Event details' )
	: __( 'Event details', 'jdpower' );

$content_row_class = 'event-webinar__max';
if ( $has_event_details ) {
	$content_row_class .= ' event-webinar__layout event-webinar__layout--with-sidebar';
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'event-webinar' ); ?>>
	<div class="event-webinar__max event-webinar__intro">
		<header class="entry-header event-webinar__header">
			<?php
			$post_type_obj = get_post_type_object( get_post_type() );
			$type_eyebrow  = '';
			if ( $post_type_obj ) {
				if ( ! empty( $post_type_obj->labels->singular_name ) ) {
					$type_eyebrow = $post_type_obj->labels->singular_name;
				} elseif ( ! empty( $post_type_obj->labels->name ) ) {
					$type_eyebrow = $post_type_obj->labels->name;
				}
			}
			if ( '' !== $type_eyebrow ) :
				?>
				<p class="preheading entry-header__post-type"><?php echo esc_html( $type_eyebrow ); ?></p>
			<?php endif; ?>
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</header>
	</div>

	<div class="event-webinar__max event-webinar__intro">
		<div class="entry-meta">
			<?php jdpower_the_insight_entry_meta(); ?>
		</div>
	</div>

	<?php jdpower_the_insight_single_layout_open(); ?>

	<div class="<?php echo esc_attr( $content_row_class ); ?>">
		<div class="event-webinar__main">
			<?php if ( $has_thumbnail ) : ?>
				<div class="event-webinar__media">
					<?php
					echo wp_get_attachment_image(
						(int) get_post_thumbnail_id(),
						'large',
						false,
						array( 'class' => 'event-webinar__image' )
					);
					?>
				</div>
			<?php endif; ?>

			<div class="entry-content event-webinar__content">
				<?php
				the_content(
					sprintf(
						wp_kses(
							/* translators: %s: post title, screen reader only. */
							__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'jdpower' ),
							array(
								'span' => array(
									'class' => array(),
								),
							)
						),
						wp_kses_post( get_the_title() )
					)
				);
				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'jdpower' ),
						'after'  => '</div>',
					)
				);
				?>
			</div>
		</div>

		<?php if ( $has_event_details ) : ?>
			<aside class="event-webinar__sidebar" aria-label="<?php echo esc_attr( $event_details_label ); ?>">
				<div class="event-webinar__info">
					<p class="event-webinar__info-eyebrow"><?php echo esc_html( $event_details_label ); ?></p>
					<?php if ( '' !== $time_line || '' !== $format_line ) : ?>
						<ul class="event-webinar__info-list" role="list">
							<?php if ( '' !== $time_line ) : ?>
								<li class="event-webinar__info-row">
									<span class="event-webinar__info-icon" aria-hidden="true">
										<svg class="event-webinar__icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none" focusable="false" aria-hidden="true">
											<path d="M16 3C13.4288 3 10.9154 3.76244 8.77759 5.1909C6.63975 6.61935 4.97351 8.64968 3.98957 11.0251C3.00563 13.4006 2.74819 16.0144 3.2498 18.5362C3.75141 21.0579 4.98953 23.3743 6.80762 25.1924C8.6257 27.0105 10.9421 28.2486 13.4638 28.7502C15.9856 29.2518 18.5995 28.9944 20.9749 28.0104C23.3503 27.0265 25.3807 25.3603 26.8091 23.2224C28.2376 21.0846 29 18.5712 29 16C28.9964 12.5533 27.6256 9.24882 25.1884 6.81163C22.7512 4.37445 19.4467 3.00364 16 3ZM16 27C13.8244 27 11.6977 26.3549 9.88873 25.1462C8.07979 23.9375 6.66989 22.2195 5.83733 20.2095C5.00477 18.1995 4.78693 15.9878 5.21137 13.854C5.63581 11.7202 6.68345 9.7602 8.22183 8.22183C9.76021 6.68345 11.7202 5.6358 13.854 5.21136C15.9878 4.78692 18.1995 5.00476 20.2095 5.83733C22.2195 6.66989 23.9375 8.07979 25.1462 9.88873C26.3549 11.6977 27 13.8244 27 16C26.9967 18.9164 25.8367 21.7123 23.7745 23.7745C21.7123 25.8367 18.9164 26.9967 16 27ZM24 16C24 16.2652 23.8946 16.5196 23.7071 16.7071C23.5196 16.8946 23.2652 17 23 17H16C15.7348 17 15.4804 16.8946 15.2929 16.7071C15.1054 16.5196 15 16.2652 15 16V9C15 8.73478 15.1054 8.48043 15.2929 8.29289C15.4804 8.10536 15.7348 8 16 8C16.2652 8 16.5196 8.10536 16.7071 8.29289C16.8946 8.48043 17 8.73478 17 9V15H23C23.2652 15 23.5196 15.1054 23.7071 15.2929C23.8946 15.4804 24 15.7348 24 16Z" fill="currentColor" />
										</svg>
									</span>
									<span class="event-webinar__info-text"><?php echo esc_html( $time_line ); ?></span>
								</li>
							<?php endif; ?>
							<?php if ( '' !== $format_line ) : ?>
								<li class="event-webinar__info-row">
									<span class="event-webinar__info-icon" aria-hidden="true">
										<?php if ( 'online' === $format_value ) : ?>
											<svg class="event-webinar__icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none" focusable="false" aria-hidden="true">
												<path d="M25 28H18.8175C19.8561 27.0727 20.8355 26.0811 21.75 25.0312C25.1812 21.085 27 16.925 27 13C27 10.0826 25.8411 7.28473 23.7782 5.22183C21.7153 3.15893 18.9174 2 16 2C13.0826 2 10.2847 3.15893 8.22183 5.22183C6.15893 7.28473 5 10.0826 5 13C5 16.925 6.81375 21.085 10.25 25.0312C11.1645 26.0811 12.1439 27.0727 13.1825 28H7C6.73478 28 6.48043 28.1054 6.29289 28.2929C6.10536 28.4804 6 28.7348 6 29C6 29.2652 6.10536 29.5196 6.29289 29.7071C6.48043 29.8946 6.73478 30 7 30H25C25.2652 30 25.5196 29.8946 25.7071 29.7071C25.8946 29.5196 26 29.2652 26 29C26 28.7348 25.8946 28.4804 25.7071 28.2929C25.5196 28.1054 25.2652 28 25 28ZM7 13C7 10.6131 7.94821 8.32387 9.63604 6.63604C11.3239 4.94821 13.6131 4 16 4C18.3869 4 20.6761 4.94821 22.364 6.63604C24.0518 8.32387 25 10.6131 25 13C25 20.1537 18.0662 26.125 16 27.75C13.9338 26.125 7 20.1537 7 13ZM21 13C21 12.0111 20.7068 11.0444 20.1573 10.2221C19.6079 9.3999 18.827 8.75904 17.9134 8.3806C16.9998 8.00216 15.9945 7.90315 15.0245 8.09607C14.0546 8.289 13.1637 8.7652 12.4645 9.46447C11.7652 10.1637 11.289 11.0546 11.0961 12.0245C10.9031 12.9945 11.0022 13.9998 11.3806 14.9134C11.759 15.827 12.3999 16.6079 13.2221 17.1573C14.0444 17.7068 15.0111 18 16 18C17.3261 18 18.5979 17.4732 19.5355 16.5355C20.4732 15.5979 21 14.3261 21 13ZM13 13C13 12.4067 13.1759 11.8266 13.5056 11.3333C13.8352 10.8399 14.3038 10.4554 14.8519 10.2284C15.4001 10.0013 16.0033 9.94189 16.5853 10.0576C17.1672 10.1734 17.7018 10.4591 18.1213 10.8787C18.5409 11.2982 18.8266 11.8328 18.9424 12.4147C19.0581 12.9967 18.9987 13.5999 18.7716 14.1481C18.5446 14.6962 18.1601 15.1648 17.6667 15.4944C17.1734 15.8241 16.5933 16 16 16C15.2044 16 14.4413 15.6839 13.8787 15.1213C13.3161 14.5587 13 13.7956 13 13Z" fill="currentColor" />
											</svg>
										<?php else : ?>
											<svg class="event-webinar__icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 20 20" fill="none" focusable="false" aria-hidden="true">
												<path d="M10.833 17.5c3.5-4.167 4.167-5.5 4.167-7.5a4.17 4.17 0 0 0-8.333 0c0 2 .667 3.333 4.166 7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
												<circle cx="10" cy="10" r="1.25" fill="currentColor" />
											</svg>
										<?php endif; ?>
									</span>
									<span class="event-webinar__info-text"><?php echo esc_html( $format_line ); ?></span>
								</li>
							<?php endif; ?>
						</ul>
					<?php endif; ?>
					<?php if ( $cta_url ) : ?>
						<div class="event-webinar__info-cta">
							<a class="btn event-webinar__cta-btn" href="<?php echo esc_url( $cta_url ); ?>"
								<?php
								if ( $cta_target ) {
									echo ' target="' . esc_attr( $cta_target ) . '" rel="noopener noreferrer"';
								}
								?>
							>
								<?php echo esc_html( $cta_lbl ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</aside>
		<?php endif; ?>
	</div>

	<?php jdpower_the_insight_single_layout_close(); ?>
	<?php jdpower_the_insight_single_footer(); ?>
</article>
