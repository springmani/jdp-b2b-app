<?php
// phpcs:ignoreFile

/**
 * Build hierarchical term tree from flat term list
 */
if ( ! function_exists( 'build_term_tree_select' ) ) {
	function build_term_tree_select( $terms ) {
		$tree    = [];
		$indexed = [];

		// Index all terms by ID.
		foreach ( $terms as $term ) {
			$indexed[ $term->term_id ]           = $term;
			$indexed[ $term->term_id ]->children = [];
		}

		// Build parent-child relationships.
		foreach ( $indexed as $term ) {
			if ( $term->parent === 0 ) {
				$tree[] = $term;
			} else {
				if ( isset( $indexed[ $term->parent ] ) ) {
					$indexed[ $term->parent ]->children[] = $term;
				}
			}
		}

		return $tree;
	}
}

/**
 * Render hierarchical select dropdown tree
 */
if ( ! function_exists( 'render_term_tree_select' ) ) {
	function render_term_tree_select( $tree, $selected_taxonomy, $depth = 0 ) {
		foreach ( $tree as $term ) {
			$indent = str_repeat( '&nbsp;&nbsp;', $depth );
			?>
			<option <?php echo ( $term->slug === $selected_taxonomy ) ? 'selected' : ''; ?>
					value="<?php echo esc_attr( $term->slug ); ?>">
				<?php echo wp_kses_post( $indent ); ?><?php echo esc_html( $term->name ); ?> (<?php echo esc_html( $term->count ); ?>)
			</option>
			<?php
			// Recursively render children.
			if ( ! empty( $term->children ) ) {
				render_term_tree_select( $term->children, $selected_taxonomy, $depth + 1 );
			}
		}
	}
}
?>

<div
		data-wp-bind--hidden="state.filtersHidden"
		data-wp-interactive='wpengine-smart-search-facets__store'
		data-wp-init="actions.initDropdownFacet"
	<?php echo wp_kses_data( $wrapper_attributes ); ?>
	<?php echo wp_kses_data( $context ); ?>
>
	<span
			data-wp-bind--hidden="!context.attributes.hasLabel"
			class="wpengine-smart-search-dropdown-facet__label">
		<?php echo esc_html( $attributes['label'] ); ?>
	</span>

	<div class="wpengine-smart-dropdown-facet__fields">
		<select
				class="wpengine-smart-dropdown-facet__field"
				data-wp-bind--name="context.selected_data_source_mapped"
				data-wp-bind--value="context.value"
				data-wp-on--change="actions.setDropdownValue"
				data-dropdown-name="<?php echo esc_attr( $selected_data_source_mapped ); ?>"
		>
			<option value=""><?php esc_html_e( 'Please select...', 'wpengine-smart-search' ); ?></option>
			<?php
			// Hierarchical rendering for taxonomy with showHierarchy enabled.
			if ( $show_hierarchy && 'taxonomy' === $data_source_type ) :
				$tree = build_term_tree_select( $source_data_records );
				render_term_tree_select( $tree, $selected_taxonomy );
			else :
				// Flat rendering for post types or non-hierarchical display.
				foreach ( $source_data_records as $record ) :
					?>
					<option <?php echo ( $record->slug === $selected_taxonomy ) ? 'selected' : ''; ?>
							value="<?php echo esc_attr( $record->slug ); ?>"><?php echo esc_html( $record->name ); ?>
						(<?php echo esc_html( $record->count ); ?>)
					</option>
					<?php
				endforeach;
			endif;
			?>
		</select>
	</div>

	<?php if ( ! ( $attributes['autoReload'] ?? true ) ) : ?>
		<button
			type="button"
			class="wpengine-smart-dropdown-facet__apply-button wp-element-button"
			data-wp-on--click="actions.applyFilters"
		>
			<?php echo esc_html__( 'Apply Filters', 'wpengine-smart-search' ); ?>
		</button>
	<?php endif; ?>
</div>
