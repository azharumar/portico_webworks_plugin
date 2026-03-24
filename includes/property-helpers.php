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

	// TODO: verify scope_cpt and scope_id are correct for this context
	$profile['contacts'] = pw_resolve_contact( 'property', 0, (int) $id );

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
 * REST collection route segment for a post type (e.g. wp/v2/{base}/id).
 */
function pw_get_cpt_rest_base( string $cpt ): string {
	$pto = get_post_type_object( $cpt );
	if ( ! $pto ) {
		return '';
	}
	return ! empty( $pto->rest_base ) ? (string) $pto->rest_base : $cpt;
}

/**
 * When ?pw_property_id_preview= is set, adjust REST link (and template) for outlet permalink preview in the editor.
 *
 * @param WP_REST_Response $response Response.
 * @param WP_Post          $post     Post.
 * @param WP_REST_Request  $request  Request.
 * @return WP_REST_Response
 */
function pw_rest_outlet_permalink_preview( $response, $post, $request ) {
	if ( ! $response instanceof WP_REST_Response || ! $post instanceof WP_Post || ! $request instanceof WP_REST_Request ) {
		return $response;
	}
	$preview_pid = (int) $request->get_param( 'pw_property_id_preview' );
	if ( $preview_pid <= 0 ) {
		return $response;
	}

	$property = get_post( $preview_pid );
	if (
		! $property instanceof WP_Post ||
		$property->post_type !== 'pw_property' ||
		$property->post_status !== 'publish'
	) {
		return $response;
	}

	$singular = pw_get_section_base( $post->post_type, 'singular' );
	if ( $singular === '' ) {
		return $response;
	}

	$mode = pw_get_setting( 'pw_property_mode', 'single' );
	$slug = sanitize_title( (string) $post->post_name );
	if ( $slug === '' ) {
		return $response;
	}

	$slug_tag = '%' . $post->post_type . '%';
	if ( $mode === 'single' ) {
		$url      = untrailingslashit( home_url( '/' . $singular . '/' . $slug ) );
		$template = untrailingslashit( home_url( '/' . $singular . '/' . $slug_tag ) );
	} else {
		$prop_seg = sanitize_title( (string) $property->post_name );
		if ( $prop_seg === '' ) {
			return $response;
		}
		$url      = untrailingslashit( home_url( '/' . $prop_seg . '/' . $singular . '/' . $slug ) );
		$template = untrailingslashit( home_url( '/' . $prop_seg . '/' . $singular . '/' . $slug_tag ) );
	}

	$data = $response->get_data();
	if ( ! is_array( $data ) ) {
		return $response;
	}
	$data['link']               = $url;
	$data['permalink_template'] = $template;
	$response->set_data( $data );
	return $response;
}

add_action(
	'init',
	static function () {
		foreach ( pw_url_section_cpts() as $cpt ) {
			add_filter( "rest_prepare_{$cpt}", 'pw_rest_outlet_permalink_preview', 10, 3 );
		}
	},
	20
);

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
	$feature_ids = get_post_meta( (int) $room_type_id, '_pw_features', true );
	if ( empty( $feature_ids ) || ! is_array( $feature_ids ) ) return [];

	return get_posts( [
		'post_type'      => 'pw_feature',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'post__in'       => $feature_ids,
		'orderby'        => 'post__in',
	] );
}

function pw_get_experiences_for( $post_type, $post_id ) {
	$post_id = (int) $post_id;
	$experiences = get_posts( [
		'post_type'      => 'pw_experience',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	] );

	return array_values( array_filter( $experiences, function( $exp ) use ( $post_type, $post_id ) {
		if ( $post_type === 'pw_property' && $post_id > 0 ) {
			$scope = (int) get_post_meta( $exp->ID, '_pw_property_id', true );
			if ( $scope === $post_id ) {
				return true;
			}
		}
		$connections = get_post_meta( $exp->ID, '_pw_connected_to', true );
		if ( empty( $connections ) || ! is_array( $connections ) ) {
			return false;
		}
		foreach ( $connections as $c ) {
			if ( isset( $c['type'], $c['id'] ) &&
				$c['type'] === $post_type &&
				(int) $c['id'] === $post_id ) {
				return true;
			}
		}
		return false;
	} ) );
}

function pw_get_faqs_for( $post_type, $post_id ) {
	$post_id = (int) $post_id;
	$faqs    = get_posts( [
		'post_type'      => 'pw_faq',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	] );

	return array_values( array_filter( $faqs, function( $faq ) use ( $post_type, $post_id ) {
		if ( $post_type === 'pw_property' && $post_id > 0 ) {
			$scope = (int) get_post_meta( $faq->ID, '_pw_property_id', true );
			if ( $scope === $post_id ) {
				return true;
			}
		}
		$connections = get_post_meta( $faq->ID, '_pw_connected_to', true );
		if ( empty( $connections ) || ! is_array( $connections ) ) {
			return false;
		}
		foreach ( $connections as $connection ) {
			if (
				isset( $connection['type'], $connection['id'] ) &&
				$connection['type'] === $post_type &&
				(int) $connection['id'] === $post_id
			) {
				return true;
			}
		}
		return false;
	} ) );
}

function pw_get_operating_hours( $post_id ) {
	$days  = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];
	$hours = [];
	foreach ( $days as $day ) {
		$hours[ $day ] = get_post_meta( (int) $post_id, '_pw_hours_' . $day, true ) ?: [
			'is_closed' => false,
			'sessions'  => [],
		];
	}
	return $hours;
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

add_filter( 'render_block', 'pw_replace_property_currency_token', 999, 1 );
add_filter( 'the_content', 'pw_replace_property_currency_token', 999, 1 );
add_filter( 'widget_block_content', 'pw_replace_property_currency_token', 999, 1 );

/**
 * Replace Portico URL tokens in block HTML: {{pw_section_url:cpt}}, {{pw_current_section_listing_url}}, {{pw_home_url}}.
 *
 * @param string $html  Rendered block HTML.
 * @param array  $block Parsed block.
 * @return string
 */
function pw_resolve_section_url_tokens( string $html, array $block ): string {
	unset( $block );

	$needs_work =
		strpos( $html, '{{pw_section_url:' ) !== false
		|| strpos( $html, '{{pw_current_section_listing_url}}' ) !== false
		|| strpos( $html, '{{pw_home_url}}' ) !== false;
	if ( ! $needs_work ) {
		return $html;
	}

	if ( strpos( $html, '{{pw_section_url:' ) !== false ) {
		$property_id = pw_get_current_property_id();
		foreach ( pw_url_section_cpts() as $cpt ) {
			$token = '{{pw_section_url:' . $cpt . '}}';
			if ( strpos( $html, $token ) !== false ) {
				$url  = pw_get_section_listing_url( $property_id, $cpt );
				$html = str_replace( $token, esc_url( $url ), $html );
			}
		}
	}

	if ( strpos( $html, '{{pw_current_section_listing_url}}' ) !== false ) {
		$property_id = (int) pw_get_current_property_id();
		$pt            = get_post_type();
		if ( ! is_string( $pt ) || $pt === '' || ! in_array( $pt, pw_url_section_cpts(), true ) ) {
			$qo = get_queried_object();
			$pt = ( $qo instanceof WP_Post_Type && isset( $qo->name ) ) ? (string) $qo->name : '';
		}
		$replace = '';
		if ( $property_id > 0 && $pt !== '' && in_array( $pt, pw_url_section_cpts(), true ) ) {
			$replace = esc_url( pw_get_section_listing_url( $property_id, $pt ) );
		}
		$html = str_replace( '{{pw_current_section_listing_url}}', $replace, $html );
	}

	if ( strpos( $html, '{{pw_home_url}}' ) !== false ) {
		$html = str_replace( '{{pw_home_url}}', esc_url( untrailingslashit( home_url( '/' ) ) ), $html );
	}

	return $html;
}

add_filter( 'render_block', 'pw_resolve_section_url_tokens', 10, 2 );

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

/**
 * Property-scoped GB queries: sort by _pw_display_order; policies only when _pw_active is set (CMB2 checkbox).
 */
function pw_filter_generateblocks_query_display_order_and_policy_active( $query_args, $attributes ) {
	if ( ! is_array( $query_args ) || ! is_array( $attributes ) ) {
		return $query_args;
	}
	if ( ! pw_gb_query_should_scope_to_property( $attributes ) ) {
		return $query_args;
	}

	$pt = $query_args['post_type'] ?? '';
	if ( is_array( $pt ) ) {
		$pt = count( $pt ) === 1 ? (string) reset( $pt ) : '';
	} else {
		$pt = (string) $pt;
	}
	if ( $pt === '' ) {
		return $query_args;
	}

	$order_post_types = array(
		'pw_room_type',
		'pw_policy',
		'pw_faq',
		'pw_amenity',
		'pw_offer',
		'pw_nearby',
		'pw_experience',
	);
	if ( in_array( $pt, $order_post_types, true ) ) {
		$query_args['meta_key'] = '_pw_display_order';
		$query_args['orderby']  = array(
			'meta_value_num' => 'ASC',
			'title'          => 'ASC',
		);
	}

	if ( $pt === 'pw_policy' ) {
		$active_clause = array(
			'relation' => 'OR',
			array(
				'key'     => '_pw_active',
				'value'   => 'on',
				'compare' => '=',
			),
			array(
				'key'     => '_pw_active',
				'value'   => '1',
				'compare' => '=',
			),
		);
		$existing = isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] ) ? $query_args['meta_query'] : array();
		$merged   = array( 'relation' => 'AND' );
		if ( ! empty( $existing ) ) {
			if ( isset( $existing['relation'] ) ) {
				$merged[] = $existing;
			} else {
				foreach ( $existing as $piece ) {
					if ( is_array( $piece ) && isset( $piece['key'] ) ) {
						$merged[] = $piece;
					}
				}
			}
		}
		$merged[]                 = $active_clause;
		$query_args['meta_query'] = $merged;
	}

	return $query_args;
}

add_filter( 'generateblocks_query_loop_args', 'pw_filter_generateblocks_query_display_order_and_policy_active', 11, 2 );

/**
 * Fact-sheet style queries: limit pw_contact to property-scoped rows when block requests it.
 */
function pw_filter_generateblocks_query_pw_contact_property_scope( $query_args, $attributes ) {
	if ( ! is_array( $query_args ) || ! is_array( $attributes ) ) {
		return $query_args;
	}
	if ( ! pw_gb_query_should_scope_to_property( $attributes ) ) {
		return $query_args;
	}
	$cn = isset( $attributes['className'] ) ? (string) $attributes['className'] : '';
	if ( strpos( $cn, 'pw-gb-contact-filter-property' ) === false ) {
		return $query_args;
	}
	$pt = $query_args['post_type'] ?? '';
	if ( is_array( $pt ) ) {
		$pt = count( $pt ) === 1 ? (string) reset( $pt ) : '';
	} else {
		$pt = (string) $pt;
	}
	if ( $pt !== 'pw_contact' ) {
		return $query_args;
	}
	$scope_clause = [
		'key'     => '_pw_scope_cpt',
		'value'   => 'property',
		'compare' => '=',
	];
	$existing = isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] ) ? $query_args['meta_query'] : [];
	$merged   = [ 'relation' => 'AND' ];
	if ( ! empty( $existing ) ) {
		if ( isset( $existing['relation'] ) ) {
			$merged[] = $existing;
		} else {
			foreach ( $existing as $piece ) {
				if ( is_array( $piece ) && isset( $piece['key'] ) ) {
					$merged[] = $piece;
				}
			}
		}
	}
	$merged[]                 = $scope_clause;
	$query_args['meta_query'] = $merged;
	return $query_args;
}

add_filter( 'generateblocks_query_loop_args', 'pw_filter_generateblocks_query_pw_contact_property_scope', 12, 2 );

/**
 * IANA timezone for interpreting pw_event local datetimes (linked property, else WP timezone).
 */
function pw_event_timezone_for_property( $property_id ) {
	$id = (int) $property_id;
	if ( $id > 0 ) {
		$tz_id = (string) get_post_meta( $id, '_pw_timezone', true );
		if ( $tz_id !== '' ) {
			try {
				return new DateTimeZone( $tz_id );
			} catch ( Exception $e ) {
				// Invalid IANA id — fall back.
			}
		}
	}
	return wp_timezone();
}

/**
 * Interprets stored event wall time (Y-m-d H:i:s) in the property timezone; returns ISO 8601 with offset (schema.org Event startDate/endDate).
 */
function pw_event_local_datetime_to_iso8601( $local_ymd_his, $property_id ) {
	$local_ymd_his = is_string( $local_ymd_his ) ? trim( $local_ymd_his ) : '';
	if ( $local_ymd_his === '' || ! preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $local_ymd_his ) ) {
		return '';
	}
	$tz = pw_event_timezone_for_property( $property_id );
	$dt = DateTime::createFromFormat( 'Y-m-d H:i:s', $local_ymd_his, $tz );
	if ( ! $dt ) {
		return '';
	}
	return $dt->format( 'c' );
}

add_action( 'rest_api_init', function () {
	register_rest_field(
		'pw_event',
		'pw_start_datetime_iso8601',
		[
			'get_callback' => function ( $obj ) {
				$post_id = isset( $obj['id'] ) ? (int) $obj['id'] : 0;
				if ( ! $post_id ) {
					return '';
				}
				$prop = (int) get_post_meta( $post_id, '_pw_property_id', true );
				$raw  = get_post_meta( $post_id, '_pw_start_datetime', true );
				return pw_event_local_datetime_to_iso8601( $raw, $prop );
			},
			'schema'       => [
				'description' => 'startDate: ISO 8601 with offset; _pw_start_datetime interpreted in linked property _pw_timezone.',
				'type'        => 'string',
				'context'     => [ 'view', 'edit', 'embed' ],
				'readonly'    => true,
			],
		]
	);
	register_rest_field(
		'pw_event',
		'pw_end_datetime_iso8601',
		[
			'get_callback' => function ( $obj ) {
				$post_id = isset( $obj['id'] ) ? (int) $obj['id'] : 0;
				if ( ! $post_id ) {
					return '';
				}
				$prop = (int) get_post_meta( $post_id, '_pw_property_id', true );
				$raw  = get_post_meta( $post_id, '_pw_end_datetime', true );
				return pw_event_local_datetime_to_iso8601( $raw, $prop );
			},
			'schema'       => [
				'description' => 'endDate: ISO 8601 with offset; _pw_end_datetime interpreted in linked property _pw_timezone.',
				'type'        => 'string',
				'context'     => [ 'view', 'edit', 'embed' ],
				'readonly'    => true,
			],
		]
	);
} );

