<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_get_property_profile( $property_id = null ) {
	$id = $property_id ?? pw_get_current_property_id();
	if ( ! $id ) {
		return [];
	}

	$keys = [
		'legal_name'         => '_pw_legal_name',
		'star_rating'        => '_pw_star_rating',
		'currency'           => '_pw_currency',
		'check_in_time'      => '_pw_check_in_time',
		'check_out_time'     => '_pw_check_out_time',
		'year_established'   => '_pw_year_established',
		'total_rooms'        => '_pw_total_rooms',
		'address_line_1'     => '_pw_address_line_1',
		'address_line_2'     => '_pw_address_line_2',
		'city'               => '_pw_city',
		'state'              => '_pw_state',
		'postal_code'        => '_pw_postal_code',
		'country'            => '_pw_country',
		'country_code'       => '_pw_country_code',
		'lat'                => '_pw_lat',
		'lng'                => '_pw_lng',
		'google_place_id'    => '_pw_google_place_id',
		'timezone'           => '_pw_timezone',
		'social_facebook'    => '_pw_social_facebook',
		'social_instagram'   => '_pw_social_instagram',
		'social_twitter'     => '_pw_social_twitter',
		'social_tripadvisor' => '_pw_social_tripadvisor',
		'social_linkedin'    => '_pw_social_linkedin',
		'social_youtube'     => '_pw_social_youtube',
	];

	$profile = [];
	foreach ( $keys as $label => $meta_key ) {
		$profile[ $label ] = get_post_meta( (int) $id, $meta_key, true );
	}

	$profile['contacts'] = [];

	return $profile;
}

function pw_get_all_properties() {
	$ids = get_posts(array(
		'post_type' => 'pw_property',
		'post_status' => 'any',
		'numberposts' => -1,
		'fields' => 'ids',
		'orderby' => 'title',
		'order' => 'ASC',
	));

	if (!is_array($ids)) {
		return array();
	}

	$out = array();
	foreach ($ids as $id) {
		$out[] = array(
			'id' => (int) $id,
			'name' => get_the_title($id),
			'slug' => get_post_field('post_name', $id),
		);
	}

	return $out;
}

function pw_property_base() {
	return pw_get_fixed_permalink_base();
}

/**
 * Path segments for the current front request (no leading/trailing slashes).
 *
 * @return string[]
 */
function pw_parse_front_request_segments() {
	global $wp;
	if ( isset( $wp ) && isset( $wp->request ) && is_string( $wp->request ) && $wp->request !== '' ) {
		return explode( '/', trim( $wp->request, '/' ) );
	}
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = $uri ? (string) parse_url( $uri, PHP_URL_PATH ) : '';
	$path = $path ? trim( $path, '/' ) : '';

	return $path !== '' ? explode( '/', $path ) : [];
}

/**
 * @deprecated Optional URL prefix only; use pw_get_fixed_permalink_base().
 */
function pw_get_base_segment_for_property( $property_id ) {
	return pw_get_fixed_permalink_base();
}

/**
 * Property slug used in URLs (post_name).
 *
 * @param int $property_id Property post ID.
 * @return string
 */
function pw_get_property_slug( $property_id ) {
	$property_id = (int) $property_id;
	if ( $property_id <= 0 ) {
		return '';
	}
	$name = get_post_field( 'post_name', $property_id );

	return is_string( $name ) ? sanitize_title( $name ) : '';
}

/**
 * Resolve property post ID from URL slug (cached per request).
 *
 * @param string $slug Sanitized slug (post_name).
 * @return int Post ID or 0.
 */
function pw_resolve_property_slug( $slug ) {
	static $cache = [];
	$slug = sanitize_title( (string) $slug );
	if ( $slug === '' ) {
		return 0;
	}
	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}
	$found = get_posts(
		[
			'post_type'      => 'pw_property',
			'post_status'    => 'publish',
			'name'           => $slug,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);
	$cache[ $slug ] = ! empty( $found ) ? (int) $found[0] : 0;
	return $cache[ $slug ];
}

/**
 * @deprecated Use pw_resolve_property_slug().
 *
 * @param string $slug Sanitized slug segment from URL.
 * @return int|null Property ID or null.
 */
function pw_resolve_property_id_by_slug_segment( $slug ) {
	$id = pw_resolve_property_slug( $slug );
	return $id > 0 ? $id : null;
}

/**
 * @deprecated
 */
function pw_property_exists_with_slug_in_uri( $slug_seg ) {
	return pw_resolve_property_slug( $slug_seg ) > 0;
}

/**
 * @deprecated
 */
function pw_validate_property_base_segment( $property_id, $base_seg ) {
	return true;
}

/**
 * Public URL for a property root (multi mode). No trailing slash.
 *
 * @param int $property_id Property post ID.
 * @return string URL or empty if invalid.
 */
function pw_get_property_url( $property_id ) {
	$property_id = (int) $property_id;
	if ( $property_id <= 0 || get_post_type( $property_id ) !== 'pw_property' ) {
		return '';
	}
	if ( pw_get_setting( 'pw_property_mode', 'single' ) !== 'multi' ) {
		return '';
	}
	$slug = pw_get_property_slug( $property_id );
	if ( $slug === '' ) {
		return '';
	}
	return untrailingslashit( home_url( '/' . $slug ) );
}

/**
 * Section listing URL for a property. No trailing slash.
 *
 * @param int    $property_id Property post ID.
 * @param string $cpt         Section CPT.
 * @return string
 */
function pw_get_section_listing_url( $property_id, $cpt ) {
	if ( ! in_array( $cpt, pw_url_section_cpts(), true ) ) {
		return '';
	}
	$pl = pw_get_section_base( $cpt, 'plural' );
	if ( $pl === '' ) {
		return '';
	}
	if ( pw_get_setting( 'pw_property_mode', 'single' ) === 'single' ) {
		return untrailingslashit( home_url( '/' . $pl ) );
	}
	$root = pw_get_property_url( (int) $property_id );
	if ( $root === '' ) {
		return '';
	}
	return untrailingslashit( $root . '/' . $pl );
}

/**
 * Outlet singular public URL. No trailing slash.
 *
 * @param int $outlet_post_id Outlet post ID.
 * @return string
 */
function pw_get_outlet_url( $outlet_post_id ) {
	$outlet_post_id = (int) $outlet_post_id;
	$pt             = get_post_type( $outlet_post_id );
	if ( ! $pt || ! in_array( $pt, pw_url_section_cpts(), true ) ) {
		return '';
	}
	$prop = (int) get_post_meta( $outlet_post_id, '_pw_property_id', true );
	if ( $prop <= 0 ) {
		return '';
	}
	$sing = pw_get_section_base( $pt, 'singular' );
	$name = get_post_field( 'post_name', $outlet_post_id );
	if ( $sing === '' || ! is_string( $name ) || $name === '' ) {
		return '';
	}
	if ( pw_get_setting( 'pw_property_mode', 'single' ) === 'single' ) {
		return untrailingslashit( home_url( '/' . $sing . '/' . sanitize_title( $name ) ) );
	}
	$root = pw_get_property_url( $prop );
	if ( $root === '' ) {
		return '';
	}
	return untrailingslashit( $root . '/' . $sing . '/' . sanitize_title( $name ) );
}

/**
 * @deprecated Routing uses query vars; kept for code that still calls this.
 */
function pw_resolve_property_id_from_url() {
	global $wp;
	if ( pw_get_setting( 'pw_property_mode', 'single' ) !== 'multi' ) {
		return null;
	}
	$qv = ( is_object( $wp ) && isset( $wp->query_vars ) && is_array( $wp->query_vars ) ) ? $wp->query_vars : [];
	if ( empty( $qv['pw_property_slug'] ) ) {
		return null;
	}
	$slug = sanitize_title( (string) $qv['pw_property_slug'] );
	$id   = pw_resolve_property_slug( $slug );
	return $id > 0 ? $id : null;
}

/**
 * @deprecated
 */
function pw_request_matches_property_url_path() {
	return false;
}

/**
 * Shared mutable state for block-scoped property context.
 */
function pw_block_property_state() {
	static $state = null;
	if ( $state === null ) {
		$state = (object) [ 'id' => null, 'active' => false ];
	}
	return $state;
}

/**
 * @param int|null $id Property ID or null to clear.
 */
function pw_set_block_property_id( $id ) {
	$s = pw_block_property_state();
	if ( $id === null ) {
		$s->active = false;
		$s->id     = null;
		return;
	}
	$s->active = true;
	$s->id     = (int) $id;
}

function pw_clear_block_property_id(): void {
	pw_set_block_property_id( null );
}

function pw_get_current_property_id() {
	$s = pw_block_property_state();
	if ( $s->active && $s->id !== null && (int) $s->id > 0 ) {
		return (int) $s->id;
	}

	$slug = get_query_var( 'pw_property_slug', '' );
	if ( is_string( $slug ) && $slug !== '' ) {
		$rid = pw_resolve_property_slug( $slug );
		if ( $rid > 0 ) {
			return $rid;
		}
	}

	if ( is_singular( 'pw_property' ) ) {
		$qid = (int) get_queried_object_id();
		return $qid > 0 ? $qid : 0;
	}

	$def = (int) pw_get_setting( 'pw_default_property_id', 0 );
	if ( $def > 0 ) {
		return $def;
	}

	return 0;
}

/**
 * Whether a section listing URL is enabled for this property (empty meta = all enabled).
 *
 * @param int    $property_id Property post ID.
 * @param string $cpt         Section CPT.
 */
function pw_is_section_enabled( $property_id, $cpt ) {
	$property_id = (int) $property_id;
	if ( $property_id <= 0 || ! in_array( $cpt, pw_url_section_cpts(), true ) ) {
		return false;
	}
	$raw = get_post_meta( $property_id, '_pw_enabled_sections', true );
	if ( $raw === false || $raw === '' || $raw === null ) {
		return true;
	}
	if ( ! is_array( $raw ) ) {
		return true;
	}
	if ( $raw === [] ) {
		return false;
	}
	return in_array( $cpt, $raw, true );
}

function pw_get_current_property_profile() {
	return pw_get_property_profile(null);
}

function pw_get_child_posts( $cpt, $property_id ) {
	return get_posts( [
		'post_type'      => $cpt,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => [
			[
				'key'   => '_pw_property_id',
				'value' => (int) $property_id,
			],
		],
	] );
}

function pw_get_room_features( $room_type_id ) {
	return [];
}

function pw_get_experiences_for( $post_type, $post_id ) {
	return [];
}

function pw_get_faqs_for( $post_type, $post_id ) {
	return [];
}

function pw_get_operating_hours( $post_id ) {
	return [];
}

function pw_get_property_currency( $property_id = null ) {
	$id = $property_id ?? pw_get_current_property_id();
	if ( ! $id ) {
		return 'USD';
	}
	return get_post_meta( (int) $id, '_pw_currency', true ) ?: 'USD';
}

add_shortcode(
	'pw_property_currency',
	static function () {
		return esc_html( pw_get_property_currency() );
	}
);

/**
 * Append property currency in nested GB loops: bracket-free token avoids shortcode/sanitizer stripping.
 */
function pw_replace_property_currency_token( $html ) {
	if ( ! is_string( $html ) ) {
		return $html;
	}
	$cur = esc_html( pw_get_property_currency() );
	if ( strpos( $html, '__PW_PROPERTY_CURRENCY__' ) !== false ) {
		$html = str_replace( '__PW_PROPERTY_CURRENCY__', $cur, $html );
	}
	if ( strpos( $html, '[pw_property_currency]' ) !== false ) {
		$html = str_replace( '[pw_property_currency]', $cur, $html );
	}
	return $html;
}

add_filter( 'the_content', 'pw_replace_property_currency_token', 999, 1 );
add_filter( 'widget_block_content', 'pw_replace_property_currency_token', 999, 1 );

// Presentation token replacements are no longer applied during block rendering.

// Scoping: Query block Additional CSS class "pw-gb-scope-property", or legacy pw_scope_to_property attribute.
function pw_gb_query_should_scope_to_property( array $attributes ) {
	if ( ! empty( $attributes['pw_scope_to_property'] ) ) {
		return true;
	}
	$cn = isset( $attributes['className'] ) ? (string) $attributes['className'] : '';

	return strpos( $cn, 'pw-gb-scope-property' ) !== false;
}

function pw_filter_generateblocks_query_loop_property_scope( $query_args, $attributes ) {
	if ( ! is_array( $attributes ) || ! pw_gb_query_should_scope_to_property( $attributes ) ) {
		return $query_args;
	}

	if ( ! empty( $query_args['pw_property_id'] ) ) {
		pw_set_block_property_id( (int) $query_args['pw_property_id'] );
	}

	$property_id = pw_get_current_property_id();
	if ( $property_id <= 0 ) {
		return $query_args;
	}

	$pt = $query_args['post_type'] ?? '';
	if ( is_array( $pt ) ) {
		$pt = count( $pt ) === 1 ? (string) reset( $pt ) : '';
	} else {
		$pt = (string) $pt;
	}

	// pw_property posts are not filtered by _pw_property_id; scope by post ID on the front end only (admin/REST leave the query unchanged so the block editor does not get an empty loop from a bogus meta clause).
	if ( $pt === 'pw_property' ) {
		$is_editor_or_rest = ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_admin();
		if ( ! $is_editor_or_rest ) {
			$query_args['post__in']       = array( (int) $property_id );
			$query_args['posts_per_page'] = 1;
			$query_args['orderby']        = 'post__in';
			if ( isset( $query_args['meta_query'] ) ) {
				unset( $query_args['meta_query'] );
			}
		}
		return $query_args;
	}

	$clause = array(
		'key'     => '_pw_property_id',
		'value'   => (int) $property_id,
		'type'    => 'NUMERIC',
		'compare' => '=',
	);

	$existing = isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] ) ? $query_args['meta_query'] : array();

	if ( empty( $existing ) ) {
		$query_args['meta_query'] = array( $clause );
	} else {
		$query_args['meta_query'] = array(
			'relation' => 'AND',
			$existing,
			$clause,
		);
	}

	return $query_args;
}

add_filter( 'generateblocks_query_loop_args', 'pw_filter_generateblocks_query_loop_property_scope', 10, 2 );

add_action(
	'loop_end',
	static function () {
		pw_clear_block_property_id();
	},
	10
);

