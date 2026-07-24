<?php
/**
 * Industry / Segment / Solution routing — Targeted Sync Logic.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * 1. PERSISTENCE (Force the ID into the DB early)
 * ------------------------------------------------------------------------- */

add_filter(
	'acf/update_value',
	function ( $value, $post_id, $field ) {
		if ( ! is_numeric( $post_id ) || (int) $post_id <= 0 ) {
			return $value;
		}

		$name = (string) ( $field['name'] ?? '' );
		if ( ! in_array( $name, array( 'associated_industry', 'associated_segment' ), true ) ) {
			return $value;
		}

		$target_id = jdpower_isp_normalize_to_id( $value );

		if ( $target_id > 0 ) {
			update_post_meta( (int) $post_id, $name, $target_id );
		} else {
			delete_post_meta( (int) $post_id, $name );
		}

		clean_post_cache( $post_id );
		return $value;
	},
	1,
	3
);

/* -------------------------------------------------------------------------
 * 2. ROUTING (The path rules - Targeted, not greedy)
 * ------------------------------------------------------------------------- */

add_filter(
	'query_vars',
	function ( $vars ) {
		return array_merge( $vars, array( 'ind_path', 'seg_path', 'sol_path' ) );
	}
);

/**
 * Register industry hierarchy rewrite rules.
 *
 * @param string $url_prefix Optional language prefix (e.g. "es/").
 * @param string $slug_regex Industry slug alternation pattern.
 * @param string $lang       Polylang language slug for the query, or empty.
 */
function jdpower_isp_register_hierarchy_rewrite_rules( $url_prefix, $slug_regex, $lang = '' ) {
	$lang_query = $lang ? 'lang=' . $lang . '&' : '';
	$prefix     = $url_prefix ? preg_quote( rtrim( $url_prefix, '/' ), '/' ) . '/' : '';

	add_rewrite_rule(
		'^' . $prefix . '(' . $slug_regex . ')/([^/]+)/([^/]+)/?$',
		'index.php?' . $lang_query . 'ind_path=$matches[1]&seg_path=$matches[2]&sol_path=$matches[3]',
		'top'
	);
	add_rewrite_rule(
		'^' . $prefix . '(' . $slug_regex . ')/([^/]+)/?$',
		'index.php?' . $lang_query . 'ind_path=$matches[1]&seg_path=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^' . $prefix . '(' . $slug_regex . ')/?$',
		'index.php?' . $lang_query . 'ind_path=$matches[1]',
		'top'
	);
}

add_action(
	'init',
	function () {
		$lookup_args = array_merge(
			array(
				'post_type'   => 'industries',
				'numberposts' => -1,
				'post_status' => 'publish',
				'fields'      => 'ids',
			),
			jdpower_pll_all_languages_query_arg()
		);

		$industries = get_posts( $lookup_args );

		$slugs = array();
		foreach ( $industries as $industry_id ) {
			$post = get_post( (int) $industry_id );
			if ( $post && '' !== $post->post_name ) {
				$slugs[] = preg_quote( $post->post_name, '/' );
			}
		}

		$slug_regex = ! empty( $slugs ) ? implode( '|', array_unique( $slugs ) ) : '___none___';

		jdpower_isp_register_hierarchy_rewrite_rules( '', $slug_regex );

		if ( ! jdpower_pll_is_active() ) {
			return;
		}

		$default_lang = jdpower_pll_default_language();
		$hide_default = jdpower_pll_hide_default();

		foreach ( pll_languages_list( array( 'fields' => 'slug' ) ) as $lang_slug ) {
			if ( $hide_default && $lang_slug === $default_lang ) {
				continue;
			}

			jdpower_isp_register_hierarchy_rewrite_rules( $lang_slug . '/', $slug_regex, $lang_slug );
		}
	},
	10
);

add_filter(
	'request',
	function ( $query_vars ) {
		if ( is_admin() ) {
			return $query_vars;
		}

		$ind = $query_vars['ind_path'] ?? '';
		$seg = $query_vars['seg_path'] ?? '';
		$sol = $query_vars['sol_path'] ?? '';

		if ( ! $ind ) {
			return $query_vars;
		}

		if ( $sol ) {
			$query_vars['post_type'] = 'solution';
			$query_vars['name']      = $sol;
		} elseif ( $seg ) {
			$query_vars['post_type'] = 'segments';
			$query_vars['name']      = $seg;
		} else {
			$query_vars['post_type'] = 'industries';
			$query_vars['name']      = $ind;
		}

		unset( $query_vars['ind_path'], $query_vars['seg_path'], $query_vars['sol_path'] );

		return jdpower_isp_set_lang_from_hierarchy( $query_vars, $ind, $seg, $sol );
	},
	99
);

/* -------------------------------------------------------------------------
 * 3. PERMALINKS (The hierarchical builder)
 * ------------------------------------------------------------------------- */

add_filter(
	'post_type_link',
	function ( $post_link, $post, $leavename = false, $sample = false ) {
		if ( ! in_array( $post->post_type, array( 'industries', 'segments', 'solution' ), true ) ) {
			return $post_link;
		}

		$use_placeholder = ( $leavename || $sample );
		$slug            = $use_placeholder ? '%postname%' : $post->post_name;
		if ( ! $slug && ! $use_placeholder ) {
			return $post_link;
		}

		$get_rel_id = function ( $pid, $field_name ) {
			$id = (int) get_post_meta( $pid, $field_name, true );
			if ( $id > 0 ) {
				return $id;
			}
			if ( isset( $_POST['acf'] ) ) {
				$keys = array(
					'associated_industry' => 'field_70fe01a1b0501',
					'associated_segment'  => 'field_70fe01a1b0502',
				);
				$val  = $_POST['acf'][ $keys[ $field_name ] ] ?? $_POST['acf'][ $field_name ] ?? 0;
				return jdpower_isp_normalize_to_id( $val );
			}
			return 0;
		};

		if ( 'industries' === $post->post_type ) {
			return jdpower_prefixed_home_url( $slug );
		}

		if ( 'segments' === $post->post_type ) {
			$ind_id = $get_rel_id( $post->ID, 'associated_industry' );
			$ind_sl = jdpower_get_slug( $ind_id );
			return $ind_sl ? jdpower_prefixed_home_url( "$ind_sl/$slug" ) : jdpower_prefixed_home_url( $slug );
		}

		if ( 'solution' === $post->post_type ) {
			$seg_id = $get_rel_id( $post->ID, 'associated_segment' );
			$seg_sl = jdpower_get_slug( $seg_id );
			$ind_id = $get_rel_id( $seg_id, 'associated_industry' );
			$ind_sl = jdpower_get_slug( $ind_id );

			if ( $ind_sl && $seg_sl ) {
				return jdpower_prefixed_home_url( "$ind_sl/$seg_sl/$slug" );
			}
			if ( $seg_sl ) {
				return jdpower_prefixed_home_url( "$seg_sl/$slug" );
			}
			return jdpower_prefixed_home_url( $slug );
		}

		return $post_link;
	},
	10,
	4
);

/* -------------------------------------------------------------------------
 * 4. UI SYNC (The sidebar fix)
 * ------------------------------------------------------------------------- */

add_action( 'admin_footer-post.php', 'jdpower_isp_editor_sync' );
add_action( 'admin_footer-post-new.php', 'jdpower_isp_editor_sync' );

function jdpower_isp_editor_sync() {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'industries', 'segments', 'solution' ), true ) ) {
		return;
	}

	global $post;
	$pid = (int) ( $post->ID ?? 0 );
	?>
	<script>
	(function() {
		if (typeof wp === 'undefined' || !wp.data) return;
		let wasSaving = false;
		const pid = <?php echo $pid; ?>;

		wp.data.subscribe(function() {
			const editor = wp.data.select('core/editor');
			if (!editor || !editor.isSavingPost) return;

			const saving = editor.isSavingPost();
			const autosaving = editor.isAutosavingPost();

			if (wasSaving && !saving && !autosaving) {
				const currentId = editor.getCurrentPostId();
				const effectiveId = pid || currentId;
				if (effectiveId) {
					const key = 'jd_reload_' + effectiveId;
					if (!sessionStorage.getItem(key)) {
						sessionStorage.setItem(key, '1');
						window.location.reload();
					}
				}
			}
			wasSaving = saving;
		});
	})();
	</script>
	<?php
}

/* -------------------------------------------------------------------------
 * 5. HELPERS
 * ------------------------------------------------------------------------- */

function jdpower_isp_normalize_to_id( $v ) {
	if ( is_numeric( $v ) ) {
		return (int) $v;
	}
	if ( is_array( $v ) ) {
		return (int) ( $v['ID'] ?? $v[0] ?? 0 );
	}
	if ( is_object( $v ) ) {
		return (int) ( $v->ID ?? 0 );
	}
	return 0;
}

function jdpower_get_slug( $id ) {
	if ( ! $id ) {
		return '';
	}
	$p = get_post( $id );
	return ( $p && ! in_array( $p->post_status, array( 'trash', 'auto-draft' ), true ) ) ? $p->post_name : '';
}

add_filter(
	'redirect_canonical',
	function ( $redirect_url, $requested_url ) {
		if ( is_singular( array( 'industries', 'segments', 'solution' ) ) ) {
			return false;
		}
		return $redirect_url;
	},
	10,
	2
);
