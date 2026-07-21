<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package jdpower
 */

if ( ! function_exists( 'jdpower_acf_flag_on' ) ) :
	/**
	 * True when an ACF true/false (or similar) is enabled.
	 * Unset/null counts as on so existing blocks stay animated when the field is new.
	 *
	 * @param mixed $value Raw field value.
	 */
	function jdpower_acf_flag_on( $value ) {
		return ! in_array( $value, array( false, 0, '0' ), true );
	}
endif;

if ( ! function_exists( 'jdpower_sanitize_html_id_attribute' ) ) :
	/**
	 * Sanitize a value for use as an HTML `id` (fragment-friendly: letters, digits, hyphen, underscore).
	 *
	 * @param mixed $value Raw field value.
	 * @return string Non-empty id or '' if nothing valid remains.
	 */
	function jdpower_sanitize_html_id_attribute( $value ) {
		$value = is_string( $value ) ? trim( $value ) : '';
		if ( '' === $value ) {
			return '';
		}
		$id = preg_replace( '/[^a-zA-Z0-9_-]/', '', $value );
		return is_string( $id ) ? $id : '';
	}
endif;

if ( ! function_exists( 'jdpower_inline_html_allowed_tags' ) ) :
	/**
	 * wp_kses allowlist for press release headers and card headings (`template-parts/content-press_release.php`, etc.).
	 * sup, sub, span (class), small, br, wbr by default.
	 *
	 * @param string[] $omit_tags Tag names (lowercase) to drop from the allowlist, e.g. `array( 'br' )` for card body copy.
	 * @return array<string, array<string, bool>> Allowed HTML for wp_kses.
	 */
	function jdpower_inline_html_allowed_tags( $omit_tags = array() ) {
		$tags = array(
			'sup'   => array(),
			'sub'   => array(),
			'span'  => array(
				'class' => true,
			),
			'small' => array(),
			'br'    => array(),
			'wbr'   => array(),
		);
		if ( ! empty( $omit_tags ) ) {
			foreach ( array_map( 'strtolower', (array) $omit_tags ) as $tag ) {
				unset( $tags[ $tag ] );
			}
		}
		return $tags;
	}
endif;

if ( ! function_exists( 'jdpower_sanitize_inline_html' ) ) :
	/**
	 * Sanitize inline HTML via {@see jdpower_inline_html_allowed_tags()}.
	 *
	 * @param mixed    $value      Raw string; non-strings become ''.
	 * @param bool     $multiline  When true, newlines become `<br />` unless `br` is in `$omit_tags` (then newlines / `<br>` become spaces).
	 * @param string[] $omit_tags  Tags to exclude from the allowlist (e.g. `array( 'br' )` for insights/post-filter card copy).
	 * @return string Safe HTML fragment (may be empty).
	 */
	function jdpower_sanitize_inline_html( $value, $multiline = false, $omit_tags = array() ) {
		if ( ! is_string( $value ) ) {
			return '';
		}
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}
		$omit_tags = array_map( 'strtolower', (array) $omit_tags );
		$strip_br  = in_array( 'br', $omit_tags, true );

		if ( $multiline && $strip_br ) {
			$value = preg_replace( '/<br\s*\/?>/i', ' ', $value );
			$value = preg_replace( '/\R+/u', ' ', $value );
			$value = preg_replace( '/\s+/u', ' ', $value );
		} elseif ( $multiline ) {
			$value = nl2br( $value, true );
		}

		return wp_kses( $value, jdpower_inline_html_allowed_tags( $omit_tags ) );
	}
endif;

if ( ! function_exists( 'jdpower_decode_text_for_display' ) ) :
	/**
	 * Decode HTML entities in plain text (e.g. imported taxonomy names stored as `Banking &amp; Payments`).
	 *
	 * @param mixed $value Raw string.
	 * @return string Decoded plain text, or '' for non-strings.
	 */
	function jdpower_decode_text_for_display( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return is_string( $value ) ? $value : '';
		}
		$prev    = '';
		$decoded = $value;
		while ( $decoded !== $prev ) {
			$prev    = $decoded;
			$decoded = html_entity_decode( $prev, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}
		return $decoded;
	}
endif;

if ( ! function_exists( 'jdpower_post_date_fields_empty' ) ) :
	/**
	 * Empty date field pair for cards and singles.
	 *
	 * @return array{date_display: string, date_iso: string}
	 */
	function jdpower_post_date_fields_empty() {
		return array(
			'date_display' => '',
			'date_iso'     => '',
		);
	}
endif;

if ( ! function_exists( 'jdpower_post_date_fields_from_timestamp' ) ) :
	/**
	 * Format a Unix timestamp for display, or empty when not displayable (epoch / legacy import).
	 *
	 * @param int $timestamp Unix timestamp.
	 * @return array{date_display: string, date_iso: string}
	 */
	function jdpower_post_date_fields_from_timestamp( $timestamp ) {
		$timestamp = (int) $timestamp;
		if ( $timestamp <= 0 || (int) wp_date( 'Y', $timestamp ) <= 1970 ) {
			return jdpower_post_date_fields_empty();
		}

		return array(
			'date_display' => date_i18n( 'j F Y', $timestamp ),
			'date_iso'     => wp_date( 'c', $timestamp ),
		);
	}
endif;

if ( ! function_exists( 'jdpower_post_card_date_fields' ) ) :
	/**
	 * Published date fields for cards and singles (`date_display`, `date_iso`).
	 *
	 * Omits legacy import placeholders (Unix epoch / 1 January 1970).
	 *
	 * @param int $post_id Post ID.
	 * @return array{date_display: string, date_iso: string}
	 */
	function jdpower_post_card_date_fields( $post_id ) {
		$post_id = (int) $post_id;
		if ( $post_id <= 0 ) {
			return jdpower_post_date_fields_empty();
		}

		$post_date = get_post_field( 'post_date', $post_id );
		if ( is_string( $post_date ) && str_starts_with( $post_date, '1970-01-01' ) ) {
			return jdpower_post_date_fields_empty();
		}

		$ts = function_exists( 'get_post_timestamp' ) ? get_post_timestamp( $post_id ) : false;
		if ( false === $ts ) {
			$ts = (int) get_post_time( 'U', false, $post_id );
		}

		return jdpower_post_date_fields_from_timestamp( $ts );
	}
endif;

if ( ! function_exists( 'jdpower_post_modified_date_fields' ) ) :
	/**
	 * Modified date fields for singles when publish and modified differ.
	 *
	 * @param int $post_id Post ID.
	 * @return array{date_display: string, date_iso: string}
	 */
	function jdpower_post_modified_date_fields( $post_id ) {
		$post_id = (int) $post_id;
		if ( $post_id <= 0 ) {
			return jdpower_post_date_fields_empty();
		}

		$post_modified = get_post_field( 'post_modified', $post_id );
		if ( is_string( $post_modified ) && str_starts_with( $post_modified, '1970-01-01' ) ) {
			return jdpower_post_date_fields_empty();
		}

		return jdpower_post_date_fields_from_timestamp( (int) get_post_modified_time( 'U', false, $post_id ) );
	}
endif;

if ( ! function_exists( 'jdpower_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time.
	 */
	function jdpower_posted_on() {
		$post_id   = get_the_ID();
		$published = jdpower_post_card_date_fields( $post_id );

		if ( '' === $published['date_display'] ) {
			return;
		}

		$published_iso     = $published['date_iso'];
		$published_display = $published['date_display'];

		$has_modified = (int) get_the_time( 'U', $post_id ) !== (int) get_the_modified_time( 'U', $post_id );
		$modified     = $has_modified ? jdpower_post_modified_date_fields( $post_id ) : jdpower_post_date_fields_empty();

		if ( $has_modified && '' !== $modified['date_display'] ) {
			$time_string = '<time class="entry-date published" datetime="' . esc_attr( $published_iso ) . '">' . esc_html( $published_display ) . '</time><time class="updated" datetime="' . esc_attr( $modified['date_iso'] ) . '">' . esc_html( $modified['date_display'] ) . '</time>';
		} else {
			$time_string = '<time class="entry-date published updated" datetime="' . esc_attr( $published_iso ) . '">' . esc_html( $published_display ) . '</time>';
		}

		$posted_on = sprintf(
			/* translators: %s: post date. */
			esc_html_x( '%s', 'post date', 'jdpower' ),
			$time_string
		);

		echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
endif;

if ( ! function_exists( 'jdpower_posted_by' ) ) :
	/**
	 * Prints HTML with meta information for the current author.
	 */
	function jdpower_posted_by() {
		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( '%s', 'post author', 'jdpower' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
endif;

if ( ! function_exists( 'jdpower_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories and tags.
	 */
	function jdpower_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'jdpower' ) );
			if ( $categories_list ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'jdpower' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'jdpower' ) );
			if ( $tags_list ) {
				/* translators: 1: list of tags. */
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'jdpower' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}
endif;

if ( ! function_exists( 'jdpower_post_thumbnail' ) ) :
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	function jdpower_post_thumbnail() {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>
			<div class="container">
				<div class="post-thumbnail">
					<?php the_post_thumbnail(); ?>
				</div><!-- .post-thumbnail -->
			</div>
		<?php else : ?>

			<div class="container">
				<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
					<?php
						the_post_thumbnail(
							'post-thumbnail',
							array(
								'alt' => the_title_attribute(
									array(
										'echo' => false,
									)
								),
							)
						);
					?>
				</a>
			</div>

			<?php
		endif; // End is_singular().
	}
endif;

if ( ! function_exists( 'jdpower_the_insight_industry_tags' ) ) :
	/**
	 * Industry tag links for singular insight posts (links to Insight Center filtered by industry).
	 */
	function jdpower_the_insight_industry_tags() {
		if ( ! is_singular() ) {
			return;
		}

		$post_id   = get_the_ID();
		$post_type = get_post_type( $post_id );

		$insight_types = function_exists( 'jdpower_post_filters_insight_post_type_slugs' )
			? jdpower_post_filters_insight_post_type_slugs()
			: array();

		if ( empty( $insight_types ) || ! in_array( $post_type, $insight_types, true ) ) {
			return;
		}

		$terms = get_the_terms( $post_id, 'post_industry' );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return;
		}

		$terms = array_values(
			array_filter(
				$terms,
				static function ( $term ) {
					return $term instanceof WP_Term;
				}
			)
		);
		if ( empty( $terms ) ) {
			return;
		}

		usort(
			$terms,
			static function ( $a, $b ) {
				return strcasecmp( (string) $a->name, (string) $b->name );
			}
		);

		get_template_part(
			'template-parts/partials/insight-industry',
			'tags',
			array(
				'terms' => $terms,
			)
		);
	}
endif;

if ( ! function_exists( 'jdpower_the_insight_single_footer' ) ) :
	/**
	 * Footer area for singular insights: industry tags, then related insights.
	 */
	function jdpower_the_insight_single_footer() {
		jdpower_the_insight_industry_tags();
		jdpower_the_related_insights_section();
		get_template_part( 'template-parts/partials/insight', 'cta-banner' );
	}
endif;

if ( ! function_exists( 'jdpower_the_related_insights_section' ) ) :
	/**
	 * Prints a "Related Insights" block with tiered taxonomy matching (up to four cards).
	 *
	 * Fills slots in order: strictest shared industry/segment/topic match on the same post type,
	 * then broader taxonomy tiers (e.g. industry + topic, then industry only), then repeats tiers
	 * across all insight types, then latest insights. Posts with no taxonomy terms skip to latest.
	 */
	function jdpower_the_related_insights_section() {
		if ( ! is_singular() ) {
			return;
		}

		$post_id   = get_the_ID();
		$post_type = get_post_type( $post_id );

		$insight_types = array();
		if ( function_exists( 'jdpower_post_filters_insight_post_type_slugs' ) ) {
			$insight_types = array_values(
				array_filter(
					array_map( 'sanitize_key', jdpower_post_filters_insight_post_type_slugs() )
				)
			);
		}

		if ( empty( $insight_types ) || ! in_array( $post_type, $insight_types, true ) ) {
			return;
		}

		$terms_by_tax = jdpower_related_insights_assigned_terms( $post_id, $post_type );
		$related_ids  = jdpower_related_insights_collect_ids( $post_id, $post_type, $insight_types, $terms_by_tax, 4 );

		if ( empty( $related_ids ) ) {
			return;
		}

		if ( ! function_exists( 'jdpower_featured_insights_card_from_post' ) || ! function_exists( 'jdpower_featured_insights_render_card' ) ) {
			return;
		}

		$heading = function_exists( 'jdpower_pll__' )
			? jdpower_pll__( 'Related Insights' )
			: __( 'Related Insights', 'jdpower' );

		$n         = count( $related_ids );
		$grid_cols = 4;
		$compact   = true;
		$fi_classes = implode(
			' ',
			array(
				'featured-insights-block',
				'related-insights',
				'featured-insights-block--tone-light',
				'featured-insights-block--layout-grid',
				'featured-insights-block--source-dynamic_by_type',
				'featured-insights-block--intro-ratio-50-50',
				'pt-default',
				'pb-default',
				'bg-light',
			)
		);
		?>
		<footer class="entry-footer">
			<section class="<?php echo esc_attr( $fi_classes ); ?>">
				<div class="container">
					<h2 class="preheading"><?php echo esc_html( $heading ); ?></h2>
					<?php
					$rel_grid = 'featured-insights-block__grid featured-insights-block__grid--cols-' . (int) $grid_cols;
					if ( 1 === $n ) {
						$rel_grid .= ' featured-insights-block__grid--single';
					}
					?>
					<div class="<?php echo esc_attr( $rel_grid ); ?>">
						<?php
						foreach ( $related_ids as $related_post_id ) {
							$card = jdpower_featured_insights_card_from_post( $related_post_id );
							if ( null !== $card && ! empty( $card['url'] ) ) {
								jdpower_featured_insights_render_card( $card, true, $card['url'], $compact );
							}
						}
						?>
					</div>
				</div>
			</section>
		</footer>
		<?php
	}
endif;

if ( ! function_exists( 'jdpower_search_result_card_parts' ) ) :
	/**
	 * Title and summary HTML for Search hub result rows.
	 *
	 * Always uses the post title (not card heading overrides). Summary still uses
	 * card short description / excerpt when available (same copy rules as post-filters cards).
	 *
	 * @param int|null $post_id Post ID; defaults to the current loop post.
	 * @return array{title_html: string, summary_html: string} Safe HTML fragments.
	 */
	function jdpower_search_result_card_parts( $post_id = null ) {
		$post_id = null === $post_id ? (int) get_the_ID() : (int) $post_id;
		if ( $post_id <= 0 ) {
			return array(
				'title_html'   => '',
				'summary_html' => '',
			);
		}

		$title_html = esc_html( get_the_title( $post_id ) );
		$post_type  = get_post_type( $post_id );
		$card_data  = null;

		if ( 'product' === $post_type && function_exists( 'jdpower_post_filters_product_card_data' ) ) {
			$card_data = jdpower_post_filters_product_card_data( $post_id );
		} elseif ( function_exists( 'jdpower_post_filters_insight_card_data' ) ) {
			$card_data = jdpower_post_filters_insight_card_data( $post_id );
		}

		if ( is_array( $card_data ) ) {
			$summary_html = isset( $card_data['copy_html'] ) && is_string( $card_data['copy_html'] ) ? $card_data['copy_html'] : '';

			return array(
				'title_html'   => $title_html,
				'summary_html' => $summary_html,
			);
		}

		$summary_html = '';
		$excerpt      = get_the_excerpt( $post_id );
		if ( is_string( $excerpt ) && '' !== trim( wp_strip_all_tags( $excerpt ) ) ) {
			$summary_html = jdpower_sanitize_inline_html( $excerpt, true, array( 'br' ) );
		}

		return array(
			'title_html'   => $title_html,
			'summary_html' => $summary_html,
		);
	}
endif;

if ( ! function_exists( 'jdpower_404_options_hero_has_content' ) ) :
	/**
	 * Whether Theme Options includes enough 404 hero data to render the hero partial.
	 *
	 * @return bool
	 */
	function jdpower_404_options_hero_has_content() {
		if ( ! function_exists( 'get_field' ) ) {
			return false;
		}
		$o = 'option';

		$heading = get_field( 'error_404_hero_heading', $o );
		if ( is_string( $heading ) && '' !== trim( wp_strip_all_tags( $heading ) ) ) {
			return true;
		}

		$pre = get_field( 'error_404_hero_pre_heading', $o );
		if ( is_string( $pre ) && '' !== trim( $pre ) ) {
			return true;
		}

		$copy = get_field( 'error_404_hero_copy', $o );
		if ( is_string( $copy ) && '' !== trim( wp_strip_all_tags( $copy ) ) ) {
			return true;
		}

		$cta = get_field( 'error_404_hero_cta', $o );
		if ( is_array( $cta ) && ! empty( $cta['url'] ) && '' !== trim( (string) ( $cta['title'] ?? '' ) ) ) {
			return true;
		}

		$hero_image = get_field( 'error_404_hero_image', $o );
		if ( is_array( $hero_image ) && ! empty( $hero_image['ID'] ) ) {
			return true;
		}

		$bg_image = get_field( 'error_404_hero_background_image', $o );
		if ( is_array( $bg_image ) && ! empty( $bg_image['ID'] ) ) {
			return true;
		}

		return false;
	}
endif;

if ( ! function_exists( 'jdpower_404_options_below_hero_has_content' ) ) :
	/**
	 * Whether Theme Options has WYSIWYG content for the area below the 404 hero.
	 *
	 * @return bool
	 */
	function jdpower_404_options_below_hero_has_content() {
		if ( ! function_exists( 'get_field' ) ) {
			return false;
		}
		$html = get_field( 'error_404_below_hero_copy', 'option' );
		return is_string( $html ) && '' !== trim( wp_strip_all_tags( $html ) );
	}
endif;

if ( ! function_exists( 'wp_body_open' ) ) :
	/**
	 * Shim for sites older than 5.2.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12563
	 */
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
endif;
