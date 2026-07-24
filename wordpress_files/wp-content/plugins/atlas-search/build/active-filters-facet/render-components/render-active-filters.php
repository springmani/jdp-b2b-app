<?php

use const AtlasSearch\Blocks\WordPress\RANGE_SLIDER_PREFIX;

?>

<div
		data-wp-interactive="wpengine-smart-search-facets__store"
	<?php echo wp_kses_data( $wrapper_attributes ); ?>
	<?php echo wp_kses_data( $context ); ?>
		class="active-filters-block"
		data-wp-bind--hidden="state.filters.length === 0">
	<div class="active-filters-header">
		<h4  data-wp-bind--hidden="!context.attributes.hasLabel"><?php echo esc_html( $attributes['label'] ); ?></h4>
		<div class="header-actions">
			<button
					class="hide-filters"
					data-wp-on--click="actions.hideAllFilters">
				<span class="dashicons dashicons-filter"></span>
				<span id="hide-all-filters-btn-text"><?php echo esc_html( $filters_hidden_text ); ?></span>
			</button>
			<button
					class="clear-all"
					data-wp-on--click="actions.clearAllFilters">
				<?php echo esc_html__( 'Clear All', 'wpengine-smart-search' ); ?>
			</button>
		</div>
	</div>

	<ul class="active-filters-list">
		<?php

		foreach ( $filters as $filter_name => $filter ) :
			?>
			<li class="active-filter">
				<span class="filter-name"><?php echo esc_html( $filter['label'] ?? '' ); ?>:</span>
				<div class="filter-values">
							<?php
							$values = is_array( $filter['value'] ) ? $filter['value'] : [ $filter['value'] ];

							if ( 0 === strpos( $filter_name, RANGE_SLIDER_PREFIX ) && 2 === count( $values ) ) {
								$values = [ implode( '-', $values ) ];
							}

							foreach ( $values as $value ) :
								?>
						<span class="filter-value">
									<?php echo esc_html( $value ); ?>
							<button
									class="remove-filter"
									data-wp-on--click="actions.removeFilter"
									data-filter-name='
								<?php
									echo wp_json_encode(
										[
											'name'  => $filter_name,
											'value' => $value,
										]
									);
								?>
													'
									aria-label="<?php esc_attr_e( 'Remove filter', 'wpengine-smart-search' ); ?>">
								&times;
							</button>
						</span>
								<?php
							endforeach;

							?>
				</div>
			</li>
				<?php
		endforeach;

		?>
	</ul>
</div>
