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
				'pw_section_cpt',
				'pw_outlet_slug',
				'pw_bare_singular',
				'pw_static_page_slug',
			] as $v
		) {
			$vars[] = $v;
		}
		return $vars;
	}
);

add_action(
	'init',
	static function () {
		add_rewrite_tag( '%pw_property_slug%', '([^/]+)', 'pw_property_slug=' );
	},
	5
);

add_action( 'init', 'pw_register_all_rewrite_rules', 10 );

add_filter( 'post_type_link', 'pw_outlet_post_type_link', 10, 2 );

/**
 * Replace %pw_property_slug% in outlet permalinks (multi-property mode).
 *
 * @param string  $post_link Permalink with optional unresolved tag.
 * @param WP_Post $post      Post object.
 * @return string
 */
function pw_outlet_post_type_link( $post_link, $post ) {
	if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, pw_url_section_cpts(), true ) ) {
		return $post_link;
	}

	$mode = pw_get_setting( 'pw_property_mode', 'single' );
	if ( $mode === 'single' ) {
		$out = str_replace( [ '%pw_property_slug%', '%pw_property_slug%/' ], [ '', '' ], $post_link );
		$out = preg_replace( '#([^:])//+#', '$1/', $out );
		return untrailingslashit( is_string( $out ) ? $out : $post_link );
	}

	$property_id = (int) get_post_meta( $post->ID, '_pw_property_id', true );
	if ( $property_id <= 0 ) {
		return untrailingslashit( $post_link );
	}
	$property = get_post( $property_id );
	if ( ! $property instanceof WP_Post || $property->post_type !== 'pw_property' || $property->post_status !== 'publish' ) {
		return untrailingslashit( $post_link );
	}
	$slug = $property->post_name;
	if ( ! is_string( $slug ) || $slug === '' ) {
		return untrailingslashit( $post_link );
	}
	$out = str_replace( '%pw_property_slug%', sanitize_title( $slug ), $post_link );
	return untrailingslashit( $out );
}

add_filter( 'get_sample_permalink', 'pw_outlet_get_sample_permalink', 10, 5 );

/**
 * Sample permalink for block editor (outlet CPTs).
 *
 * @param array   $permalink [ template, post_name ].
 * @param int     $post_id   Post ID.
 * @param ?string $title     Title override.
 * @param ?string $name      Name override.
 * @param WP_Post $post      Post object.
 * @return array
 */
function pw_outlet_get_sample_permalink( $permalink, $post_id, $title, $name, $post ) {
	if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, pw_url_section_cpts(), true ) ) {
		return $permalink;
	}
	if ( ! is_array( $permalink ) || ! isset( $permalink[0] ) ) {
		return $permalink;
	}

	$singular = pw_get_section_base( $post->post_type, 'singular' );
	if ( $singular === '' ) {
		return $permalink;
	}

	$mode = pw_get_setting( 'pw_property_mode', 'single' );
	$slug_tag = '%' . $post->post_type . '%';
	if ( $mode === 'single' ) {
		$permalink[0] = untrailingslashit( home_url( '/' . $singular . '/' . $slug_tag ) );
		return $permalink;
	}

	$property_id = (int) get_post_meta( $post_id, '_pw_property_id', true );
	if ( $property_id <= 0 ) {
		$placeholder = '__property__';
	} else {
		$prop = get_post( $property_id );
		$placeholder = ( $prop instanceof WP_Post && $prop->post_status === 'publish' && is_string( $prop->post_name ) && $prop->post_name !== '' )
			? sanitize_title( $prop->post_name )
			: '__property__';
	}
	$permalink[0] = untrailingslashit( home_url( '/' . $placeholder . '/' . $singular . '/' . $slug_tag ) );
	return $permalink;
}

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
 *
 * Rules use position 'top' so they are matched before permastruct-generated rules from
 * add_permastruct() (outlet CPTs). The front controller reads pw_section_cpt and
 * pw_outlet_slug and resolves the post. Permastructs exist for get_permalink() / REST
 * only; they do not replace this routing for incoming requests.
 */
function pw_register_all_rewrite_rules() {
	$bases       = pw_get_section_bases();
	$mode        = pw_get_setting( 'pw_property_mode', 'single' );
	$child_bases = [];
	foreach ( pw_url_section_cpts() as $cpt ) {
		if ( isset( $bases[ $cpt ] ) ) {
			$child_bases[ $cpt ] = $bases[ $cpt ];
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
		add_rewrite_rule( '^([^/]+)/?$', 'index.php?pw_static_page_slug=$matches[1]', 'bottom' );
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
		foreach ( array_reverse( $child_bases, true ) as $cpt => $pair ) {
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
		add_rewrite_rule(
			'^([^/]+)/?$',
			'index.php?pw_property_slug=$matches[1]',
			'top'
		);
		$rules_ver = defined( 'PW_VERSION' ) ? (string) PW_VERSION : '0';
		if ( get_option( 'pw_rewrite_rules_version', '' ) !== $rules_ver ) {
			update_option( 'pw_rewrite_rules_version', $rules_ver );
			set_transient( 'pw_flush_rewrites', 1, 120 );
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
		return;
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
		global $wp_query;
		$wp_query->is_archive           = true;
		$wp_query->is_post_type_archive = true;
		$wp_query->queried_object       = get_post_type_object( $cpt );
		$wp_query->queried_object_id    = 0;
		pw_url_virtual_archive( $cpt );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$debug_pid = pw_get_current_property_id();
			if ( $debug_pid === 0 ) {
				error_log( 'PW: section archive for ' . $cpt . ' has no property context. GB scope filter will return no results.' );
			}
		}
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

	if ( pw_get_setting( 'pw_property_mode', 'single' ) !== 'multi' ) {
		return;
	}
	$prop_slug = (string) get_query_var( 'pw_property_slug', '' );
	if ( $prop_slug === '' ) {
		return;
	}
	if ( (string) get_query_var( 'pw_section_cpt', '' ) !== '' || (string) get_query_var( 'pw_static_page_slug', '' ) !== '' || (string) get_query_var( 'pw_outlet_slug', '' ) !== '' ) {
		return;
	}
	if ( (int) get_query_var( 'pw_bare_singular', 0 ) === 1 ) {
		return;
	}
	$slug   = sanitize_title( $prop_slug );
	$prop_id = pw_resolve_property_slug( $slug );
	if ( $prop_id <= 0 ) {
		return;
	}
	$post = get_post( $prop_id );
	if ( ! $post instanceof WP_Post || $post->post_status !== 'publish' || $post->post_type !== 'pw_property' ) {
		return;
	}
	global $wp_query;
	$GLOBALS['post'] = $post;
	$wp_query->init();
	$wp_query->queried_object    = $post;
	$wp_query->queried_object_id = $prop_id;
	$wp_query->is_single         = true;
	$wp_query->is_singular       = true;
	$wp_query->is_404            = false;
	$wp_query->posts             = [ $post ];
	$wp_query->post              = $post;
	$wp_query->post_count        = 1;
	$wp_query->found_posts       = 1;
	$wp_query->is_page           = false;
	$wp_query->is_home           = false;
	$wp_query->is_archive        = false;
	$wp_query->max_num_pages     = 1;
	$wp_query->current_post      = -1;
	setup_postdata( $post );
	status_header( 200 );
	nocache_headers();
	set_query_var( 'pw_property_slug', $slug );
	add_filter(
		'template_include',
		static function ( $template ) {
			$p = locate_template( [ 'single-pw_property.php', 'single.php', 'singular.php', 'index.php' ] );
			return $p ? $p : $template;
		},
		10
	);
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
