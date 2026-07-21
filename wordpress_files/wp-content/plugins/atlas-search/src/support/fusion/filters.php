<?php

namespace AtlasSearch\Support\Fusion;

if ( ! should_load_fusion_support() ) {
	return;
}

function should_load_fusion_support(): bool {
	return is_plugin_active( 'fusion/fusion-core.php' )
		|| ( defined( 'WPE_SMARTSEARCH_FUSION_TEST_FUNCTIONALITY' )
			&& wpe_smartsearch_fusion_test_functionality() );
}

function wpe_smartsearch_fusion_test_functionality(): bool {
	return WPE_SMARTSEARCH_FUSION_TEST_FUNCTIONALITY;
}

add_action(
	'wp_after_insert_post',
	static function () {
		remove_shortcode( 'fsn_component' );
		add_shortcode( 'fsn_component', 'fsn_component_shortcode' );
		set_query_var( 'fusion_wp_after_insert_post_has_ran', true );
	},
	9,
	0
);

add_filter( 'the_content', __NAMESPACE__ . '\handle_fusion_content', 999, 1 );

function handle_fusion_content( $text ) {
	if ( ! get_query_var( 'fusion_wp_after_insert_post_has_ran', false ) ) {
		return $text;
	}

	$fs = new \FusionCoreExtend( '', 'fsn_component', '', '', '', '' );
	remove_shortcode( 'fsn_component' );
	add_shortcode( 'fsn_component', [ $fs, 'extension_shortcode' ] );

	preg_match_all( '/<p[^>]*>(.*?)<\/p>/s', $text, $matches );
	$filtered_text_array = $matches[1] ?? [];
	preg_match_all( '/<div class="fsn-text[^>]*>(.*?)<\/div>/', $text, $matches );
	$filtered_text_array = array_merge( $filtered_text_array, $matches[1] ?? [] );
	$text                = implode( ' ', array_map( 'trim', $filtered_text_array ) );

	return $text;
}
