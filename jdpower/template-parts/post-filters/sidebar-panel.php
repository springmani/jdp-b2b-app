<?php
/**
 * Sidebar taxonomy filter accordions.
 *
 * @package jdpower
 */

$config                = get_query_var( 'jdpower_pf_config', null );
$request               = get_query_var( 'jdpower_pf_request', null );
$sidebar_filter_groups = get_query_var( 'jdpower_pf_sidebar_groups', array() );

if ( ! is_array( $config ) || ! is_array( $request ) ) {
	return;
}

if ( ! is_array( $sidebar_filter_groups ) ) {
	$sidebar_filter_groups = array();
}

foreach ( $sidebar_filter_groups as $sidebar_group ) :
	if ( ! is_array( $sidebar_group ) ) {
		continue;
	}
	$tax          = isset( $sidebar_group['taxonomy'] ) ? (string) $sidebar_group['taxonomy'] : '';
	$label        = isset( $sidebar_group['label'] ) ? (string) $sidebar_group['label'] : '';
	$term_objects = isset( $sidebar_group['terms'] ) && is_array( $sidebar_group['terms'] ) ? $sidebar_group['terms'] : array();
	if ( '' === $tax || empty( $term_objects ) ) {
		continue;
	}
	?>
	<details class="post-filters__accordion" open>
		<summary class="post-filters__accordion-summary">
			<span class="post-filters__accordion-title"><?php echo esc_html( $label ); ?></span>
			<span class="post-filters__accordion-toggle" aria-hidden="true"></span>
		</summary>
		<ul class="post-filters__term-list">
			<?php foreach ( $term_objects as $term ) : ?>
				<?php
				if ( ! ( $term instanceof WP_Term ) ) {
					continue;
				}
				$r2       = jdpower_post_filters_request_toggle_slug( $request, $tax, $term->slug );
				$rlist    = isset( $request[ $tax ] ) && is_array( $request[ $tax ] ) ? $request[ $tax ] : array();
				$is_on    = in_array( $term->slug, $rlist, true );
				$data_key = $tax;
				$data_val = $term->slug;
				$href     = jdpower_post_filters_build_url( $config, $r2 );
				?>
				<li class="post-filters__term-item">
					<a
						class="post-filters__term-link <?php echo $is_on ? 'is-active' : ''; ?>"
						href="<?php echo esc_url( $href ); ?>"
						data-filter-key="<?php echo esc_attr( $data_key ); ?>"
						data-filter-value="<?php echo esc_attr( $data_val ); ?>"
					>
						<span class="post-filters__term-label"><?php echo esc_html( $term->name ); ?></span>
						<?php if ( $is_on ) : ?>
							<span class="post-filters__remove" aria-hidden="true">×</span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</details>
	<?php
endforeach;
