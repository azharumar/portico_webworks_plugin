<?php
/**
 * Tiered URL rewrites, query vars, front controller, and redirects.
 *
 * ASSERTION: Do not call add_rewrite_rule() after pw_register_all_rewrite_rules() returns.
 * The static wildcard (Priority 6) must remain last among plugin bottom rules.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'query_vars',
	static function ( $vars ) {
		foreach (
			[
				'pw_property_slug',
				'pw_property_base_segment',
				'pw_base_segment',
				'pw_section_cpt',
				'pw_outlet_slug',
				'pw_bare_singular',
				'pw_static_page_slug',
				'pw_property_listing',
			] as $v
		) {
			$vars[] = $v;
		}
		return $vars;
	}
);

add_action( 'init', 'pw_register_all_rewrite_rules', 10 );

/**
 * Append raw query string and redirect (front-end routing only).
 *
 * @param string $url    Absolute URL without query.
 * @param int    $status HTTP status.
 */
function pw_redirect_with_qs( $url, $status = 301 ) {
	$qs = isset( $_SERVER['QUERY_STRING'] ) ? (string) wp_unslash( $_SERVER['QUERY_STRING'] ) : '';
	$qs = trim( $qs );
	if ( $qs !== '' ) {
		$url .= ( strpos( $url, '?' ) !== false ? '&' : '?' ) . $qs;
	}
	wp_safe_redirect( $url, $status );
	exit;
}

/**
 * Register rewrite rules: Priority 1–3 top (register P3→P2→P1 so P1 is tried first),
 * Priority 4–6 bottom (register P4→P5→P6 so wildcard is last).
 */
function pw_register_all_rewrite_rules() {
	$bases      = pw_get_section_bases();
	$mode       = pw_get_setting( 'pw_property_mode', 'single' );
	$prefix     = pw_multi_property_url_prefix();
	$with       = $mode === 'multi' && $prefix !== '';
	$pp_quoted  = preg_quote( sanitize_title( (string) pw_get_setting( 'pw_property_plural_base', 'hotels' ) ), '#' );
	$prop_pair  = isset( $bases['pw_property'] ) ? $bases['pw_property'] : [ 'plural' => 'hotels', 'singular' => 'hotel' ];
	$prop_sing_q = preg_quote( $prop_pair['singular'], '#' );

	$child_bases = [];
	foreach ( pw_url_section_cpts() as $cpt ) {
		if ( isset( $bases[ $cpt ] ) ) {
			$child_bases[ $cpt ] = $bases[ $cpt ];
		}
	}
	$p3_bases = [];
	foreach ( array_merge( [ 'pw_property' ], pw_url_section_cpts() ) as $cpt ) {
		if ( isset( $bases[ $cpt ] ) ) {
			$p3_bases[ $cpt ] = $bases[ $cpt ];
		}
	}

	if ( $mode === 'single' ) {
		foreach ( array_reverse( $child_bases, true ) as $cpt => $pair ) {
			$sing = preg_quote( $pair['singular'], '#' );
			$pl   = preg_quote( $pair['plural'], '#' );
			add_rewrite_rule(
				"^{$sing}/([^/]+)/?$",
				'index.php?pw_section_cpt=' . $cpt . '&pw_outlet_slug=$matches[1]',
				'top'
			);
		}
		foreach ( array_reverse( $child_bases, true ) as $cpt => $pair ) {
			$pl = preg_quote( $pair['plural'], '#' );
			add_rewrite_rule(
				"^{$pl}/?$",
				'index.php?pw_section_cpt=' . $cpt,
				'top'
			);
		}
		foreach ( array_reverse( $child_bases, true ) as $cpt => $pair ) {
			$sing = preg_quote( $pair['singular'], '#' );
			add_rewrite_rule(
				"^{$sing}/?$",
				'index.php?pw_section_cpt=' . $cpt . '&pw_bare_singular=1',
				'top'
			);
		}
		add_rewrite_rule(
			"^{$prop_sing_q}/?$",
			'index.php?pw_section_cpt=pw_property&pw_bare_singular=1',
			'top'
		);
		add_rewrite_rule( '^([^/]+)/?$', 'index.php?pw_static_page_slug=$matches[1]', 'bottom' );
	} else {
		$bx = preg_quote( $prefix, '#' );
		if ( $with ) {
			foreach ( array_reverse( $child_bases, true ) as $cpt => $pair ) {
				$sing = preg_quote( $pair['singular'], '#' );
				add_rewrite_rule(
					"^{$bx}/([^/]+)/{$sing}/([^/]+)/?$",
					'index.php?pw_base_segment=' . $prefix . '&pw_property_slug=$matches[1]&pw_section_cpt=' . $cpt . '&pw_outlet_slug=$matches[2]',
					'top'
				);
			}
			foreach ( array_reverse( $child_bases, true ) as $cpt => $pair ) {
				$pl = preg_quote( $pair['plural'], '#' );
				add_rewrite_rule(
					"^{$bx}/([^/]+)/{$pl}/?$",
					'index.php?pw_base_segment=' . $prefix . '&pw_property_slug=$matches[1]&pw_section_cpt=' . $cpt,
					'top'
				);
			}
			foreach ( array_reverse( $p3_bases, true ) as $cpt => $pair ) {
				$sing = preg_quote( $pair['singular'], '#' );
				add_rewrite_rule(
					"^{$bx}/([^/]+)/{$sing}/?$",
					'index.php?pw_base_segment=' . $prefix . '&pw_property_slug=$matches[1]&pw_section_cpt=' . $cpt . '&pw_bare_singular=1',
					'top'
				);
			}
			add_rewrite_rule(
				"^{$prop_sing_q}/?$",
				'index.php?pw_section_cpt=pw_property&pw_bare_singular=1',
				'top'
			);
			add_rewrite_rule(
				"^{$bx}/([^/]+)/?$",
				'index.php?pw_base_segment=' . $prefix . '&pw_property_slug=$matches[1]',
				'bottom'
			);
			if ( (string) pw_get_setting( 'pw_property_archive', '1' ) === '1' ) {
				add_rewrite_rule(
					"^{$bx}/?$",
					'index.php?pw_base_segment=' . $prefix . '&pw_property_listing=1',
					'bottom'
				);
			}
			add_rewrite_rule(
				"^{$bx}/([^/]+)/([^/]+)/?$",
				'index.php?pw_base_segment=' . $prefix . '&pw_property_slug=$matches[1]&pw_static_page_slug=$matches[2]',
				'bottom'
			);
		} else {
			foreach ( array_reverse( $child_bases, true ) as $cpt => $pair ) {
				$sing = preg_quote( $pair['singular'], '#' );
				add_rewrite_rule(
					"^([^/]+)/{$sing}/([^/]+)/?$",
					'index.php?pw_property_slug=$matches[1]&pw_section_cpt=' . $cpt . '&pw_outlet_slug=$matches[2]',
					'top'
				);
			}
			foreach ( array_reverse( $child_bases, true ) as $cpt => $pair ) {
				$pl = preg_quote( $pair['plural'], '#' );
				add_rewrite_rule(
					"^([^/]+)/{$pl}/?$",
					'index.php?pw_property_slug=$matches[1]&pw_section_cpt=' . $cpt,
					'top'
				);
			}
			foreach ( array_reverse( $p3_bases, true ) as $cpt => $pair ) {
				$sing = preg_quote( $pair['singular'], '#' );
				add_rewrite_rule(
					"^([^/]+)/{$sing}/?$",
					'index.php?pw_property_slug=$matches[1]&pw_section_cpt=' . $cpt . '&pw_bare_singular=1',
					'top'
				);
			}
			add_rewrite_rule(
				'^([^/]+)/([^/]+)/?$',
				'index.php?pw_property_slug=$matches[1]&pw_static_page_slug=$matches[2]',
				'bottom'
			);
			if ( (string) pw_get_setting( 'pw_property_archive', '1' ) === '1' ) {
				add_rewrite_rule( "^{$pp_quoted}/?$", 'index.php?pw_property_listing=1', 'bottom' );
			}
			add_rewrite_rule( '^([^/]+)/?$', 'index.php?pw_property_slug=$matches[1]', 'bottom' );
		}
	}

	// ASSERTION: if you are adding any rule after this point, you are breaking the wildcard-last guarantee.

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$rw   = $GLOBALS['wp_rewrite'] ?? null;
		$keys = ( $rw && isset( $rw->rules ) && is_array( $rw->rules ) ) ? array_keys( $rw->rules ) : [];
		if ( $keys !== [] ) {
			$wildcard = '^([^/]+)/?$';
			$pos      = array_search( $wildcard, $keys, true );
			if ( $pos !== false && $pos < 20 && $mode === 'single' ) {
				trigger_error(
					'PW URL: static wildcard is at position ' . (int) $pos . ' — it must be registered last. Check pw_register_all_rewrite_rules().',
					E_USER_WARNING
				);
			}
		}
	}
}

add_filter(
	'redirect_canonical',
	static function ( $redirect_url, $requested ) {
		$check = [
			get_query_var( 'pw_property_slug', '' ),
			get_query_var( 'pw_section_cpt', '' ),
			get_query_var( 'pw_static_page_slug', '' ),
			get_query_var( 'pw_outlet_slug', '' ),
			get_query_var( 'pw_bare_singular', '' ),
			get_query_var( 'pw_property_listing', '' ),
		];
		foreach ( $check as $v ) {
			if ( is_string( $v ) && $v !== '' ) {
				return false;
			}
			if ( is_numeric( $v ) && (int) $v !== 0 ) {
				return false;
			}
		}
		return $redirect_url;
	},
	10,
	2
);

add_action( 'template_redirect', 'pw_url_front_controller', 1 );

function pw_url_front_controller() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	if ( (int) get_query_var( 'pw_bare_singular', 0 ) === 1 ) {
		$cpt   = sanitize_key( (string) get_query_var( 'pw_section_cpt', '' ) );
		$bases = pw_get_section_bases();
		if ( $cpt === '' || ! isset( $bases[ $cpt ] ) ) {
			return;
		}
		$pid = (int) pw_get_current_property_id();

		if ( $cpt === 'pw_property' ) {
			$listing_on = (string) pw_get_setting( 'pw_property_archive', '1' ) === '1';
			if ( $listing_on ) {
				pw_redirect_with_qs( pw_get_section_listing_url( $pid, $cpt ), 301 );
			}
			$dest = pw_get_setting( 'pw_property_mode', 'single' ) === 'single'
				? home_url( '/' )
				: pw_get_property_url( $pid );
			if ( $dest === '' ) {
				$dest = home_url( '/' );
			}
			pw_redirect_with_qs( untrailingslashit( $dest ), 301 );
			return;
		}

		if ( $pid > 0 && pw_is_section_enabled( $pid, $cpt ) ) {
			pw_redirect_with_qs( pw_get_section_listing_url( $pid, $cpt ), 301 );
		}
		$dest = pw_get_setting( 'pw_property_mode', 'single' ) === 'single'
			? home_url( '/' )
			: pw_get_property_url( $pid );
		if ( $dest === '' ) {
			$dest = home_url( '/' );
		}
		pw_redirect_with_qs( untrailingslashit( $dest ), 301 );
	}

	$outlet = (string) get_query_var( 'pw_outlet_slug', '' );
	$cpt    = sanitize_key( (string) get_query_var( 'pw_section_cpt', '' ) );
	if ( $cpt !== '' && $outlet !== '' && in_array( $cpt, pw_url_section_cpts(), true ) ) {
		$pid = (int) pw_get_current_property_id();
		if ( $pid <= 0 ) {
			pw_url_set_404();
			return;
		}
		$posts = get_posts(
			[
				'post_type'      => $cpt,
				'post_status'    => 'publish',
				'name'           => sanitize_title( $outlet ),
				'posts_per_page' => 1,
				'meta_query'     => [
					[
						'key'   => '_pw_property_id',
						'value' => $pid,
					],
				],
			]
		);
		if ( empty( $posts ) ) {
			pw_url_set_404();
			return;
		}
		global $post, $wp_query;
		$post = $posts[0];
		setup_postdata( $post );
		$wp_query->queried_object    = $post;
		$wp_query->queried_object_id = (int) $post->ID;
		$wp_query->is_single         = true;
		$wp_query->is_singular       = true;
		$wp_query->is_page           = false;
		$wp_query->is_home           = false;
		$wp_query->is_archive        = false;
		$wp_query->posts             = [ $post ];
		$wp_query->post_count        = 1;
		$wp_query->found_posts       = 1;
		$wp_query->max_num_pages     = 1;
		$wp_query->current_post      = -1;
		status_header( 200 );
		nocache_headers();
		add_filter(
			'template_include',
			static function ( $template ) use ( $cpt ) {
				$p = locate_template( [ "single-{$cpt}.php", 'single.php', 'singular.php', 'index.php' ] );
				return $p ? $p : $template;
			}
		);
		return;
	}

	if ( $cpt !== '' && $outlet === '' && in_array( $cpt, pw_url_section_cpts(), true ) ) {
		$pid = (int) pw_get_current_property_id();
		if ( $pid <= 0 ) {
			pw_url_set_404();
			return;
		}
		if ( ! pw_is_section_enabled( $pid, $cpt ) ) {
			pw_url_set_404();
			return;
		}
		pw_url_virtual_archive( $cpt );
		return;
	}

	$static = (string) get_query_var( 'pw_static_page_slug', '' );
	if ( $static !== '' ) {
		$static  = sanitize_title( $static );
		$pid     = (int) pw_get_current_property_id();
		$scope   = $pid > 0 ? $pid : (int) pw_get_setting( 'pw_default_property_id', 0 );
		$is_multi = pw_get_setting( 'pw_property_mode', 'single' ) === 'multi';
		if ( $is_multi && $scope <= 0 ) {
			pw_url_set_404();
			return;
		}
		$pages = [];
		if ( $scope > 0 ) {
			$pages = get_posts(
				[
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'name'           => $static,
					'posts_per_page' => 1,
					'meta_query'     => [
						[
							'key'   => '_pw_property_id',
							'value' => $scope,
						],
					],
				]
			);
		}
		if ( empty( $pages ) ) {
			$page = get_page_by_path( $static, OBJECT, 'page' );
			if ( ! $page instanceof WP_Post || $page->post_status !== 'publish' ) {
				pw_url_set_404();
				return;
			}
			$pages = [ $page ];
		}
		global $post, $wp_query;
		$post = $pages[0];
		setup_postdata( $post );
		$wp_query->queried_object    = $post;
		$wp_query->queried_object_id = (int) $post->ID;
		$wp_query->is_page           = true;
		$wp_query->is_singular       = true;
		$wp_query->is_single         = false;
		$wp_query->is_home           = false;
		$wp_query->is_archive        = false;
		$wp_query->posts             = [ $post ];
		$wp_query->post_count        = 1;
		$wp_query->found_posts       = 1;
		$wp_query->max_num_pages     = 1;
		$wp_query->current_post      = -1;
		status_header( 200 );
		nocache_headers();
		add_filter(
			'template_include',
			static function ( $template ) {
				$p = locate_template( [ 'page.php', 'singular.php', 'index.php' ] );
				return $p ? $p : $template;
			}
		);
		return;
	}

	if ( (int) get_query_var( 'pw_property_listing', 0 ) === 1 ) {
		if ( (string) pw_get_setting( 'pw_property_archive', '1' ) !== '1' ) {
			pw_url_set_404();
			return;
		}
		pw_url_virtual_archive( 'pw_property' );
	}
}

function pw_url_set_404() {
	global $wp_query;
	if ( isset( $wp_query ) && is_object( $wp_query ) ) {
		$wp_query->set_404();
	}
	status_header( 404 );
	nocache_headers();
}

/**
 * @param string $cpt Post type for virtual archive.
 */
function pw_url_virtual_archive( $cpt ) {
	global $wp_query;
	$wp_query->is_home            = false;
	$wp_query->is_404             = false;
	$wp_query->is_post_type_archive = true;
	$wp_query->is_archive         = true;
	$wp_query->is_singular        = false;
	$wp_query->is_page            = false;
	$wp_query->is_single          = false;
	$wp_query->queried_object     = get_post_type_object( $cpt );
	$wp_query->queried_object_id  = 0;
	$wp_query->set( 'post_type', $cpt );
	$wp_query->posts              = [];
	$wp_query->post_count         = 0;
	$wp_query->found_posts        = 0;
	$wp_query->max_num_pages      = 0;
	status_header( 200 );
	nocache_headers();
	add_filter(
		'template_include',
		static function ( $template ) use ( $cpt ) {
			$p = locate_template( [ "archive-{$cpt}.php", 'archive.php', 'index.php' ] );
			return $p ? $p : $template;
		}
	);
}
