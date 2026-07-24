<?php
/**
 * Insight single entry meta: mobile share + published date.
 *
 * @package jdpower
 */
?>
<div class="insight-entry-meta">
	<div class="insight-entry-meta__details">
		<?php jdpower_posted_on(); ?>
	</div>
	<?php
	get_template_part(
		'template-parts/partials/insight-social',
		'share',
		array(
			'placement' => 'inline',
		)
	);
	?>
</div>
