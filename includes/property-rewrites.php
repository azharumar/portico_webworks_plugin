<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'query_vars',
	static function ( $vars ) {
		$vars[] = 'pw_property_slug';
		$vars[] = 'pw_property_base_segment';
		return $vars;
	}
);

add_action( 'init', 'pw_register_property_public_rewrite_rules', 99 );

/**
 * Sub-path and (when base is dynamic) property-singular rewrites. Fixed base uses CPT rewrite for /base/slug.
 */
function pw_register_property_public_rewrite_rules() {
	if ( pw_get_setting( 'pw_property_mode', 'single' ) !== 'multi' ) {
		return;
	}

	foreach ( pw_get_permalink_subpaths() as $seg => $page_slug ) {
		$page_id = pw_rewrite_resolve_published_page_id_by_slug( $page_slug );
		if ( $page_id <= 0 ) {
			continue;
		}
		$s = preg_quote( $seg, '#' );

		if ( pw_get_permalink_base_source() === 'fixed' ) {
			$fixed = pw_get_fixed_permalink_base();
			$fx    = preg_quote( $fixed, '#' );
			add_rewrite_rule(
				"^{$fx}/([^/]+)/{$s}/?$",
				'index.php?page_id=' . $page_id . '&pw_property_base_segment=' . $fixed . '&pw_property_slug=$matches[1]',
				'top'
			);
		} else {
			add_rewrite_rule(
				"^([^/]+)/([^/]+)/{$s}/?$",
				'index.php?page_id=' . $page_id . '&pw_property_base_segment=$matches[1]&pw_property_slug=$matches[2]',
				'top'
			);
		}
	}

	if ( pw_permalink_uses_dynamic_base() ) {
		add_rewrite_rule(
			'^([^/]+)/([^/]+)/?$',
			'index.php?post_type=pw_property&name=$matches[2]&pw_property_base_segment=$matches[1]',
			'bottom'
		);
	}
}

/**
 * @param string $page_slug Sanitized page post_name (may be a leaf under a hierarchy).
 * @return int Page ID or 0.
 */
function pw_rewrite_resolve_published_page_id_by_slug( $page_slug ) {
	$page_slug = sanitize_title( (string) $page_slug );
	if ( $page_slug === '' ) {
		return 0;
	}
	$page = get_page_by_path( $page_slug, OBJECT, 'page' );
	if ( $page instanceof WP_Post && $page->post_status === 'publish' ) {
		return (int) $page->ID;
	}
	$found = get_posts(
		[
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'name'           => $page_slug,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);

	return ! empty( $found ) ? (int) $found[0] : 0;
}
