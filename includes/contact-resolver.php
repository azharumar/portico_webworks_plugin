<?php
/**
 * Contact resolution for Portico Webworks.
 *
 * ## Three-level resolution chain (`pw_resolve_contact`)
 *
 * Given a property ID and a logical context (`scope_cpt` + `scope_id`), the resolver
 * returns **all** `pw_contact` posts at the **first** tier that has at least one match:
 *
 * 1. **Outlet-specific** — `_pw_property_id` matches, `_pw_scope_cpt` matches the requested
 *    `scope_cpt`, and `_pw_scope_id` equals the requested `scope_id` (used only when
 *    `scope_id` > 0).
 * 2. **Group-level** — same property and `scope_cpt`, and `_pw_scope_id` is empty, absent,
 *    or `0` (applies to all outlets of that CPT under the property until overridden).
 * 3. **Property fallback** — `_pw_property_id` matches and `_pw_scope_cpt` is `property`
 *    (hotel-wide contacts when no outlet/group row matched).
 *
 * If every tier returns no posts, the result is an empty array (never `null`).
 *
 * ## Valid `scope_cpt` values
 *
 * Stored on each `pw_contact` and passed into the resolver. Use {@see PW_CONTACT_SCOPE_CPTS}.
 * Values `property` and `all` are not tied to a child CPT; others map to outlet post types
 * (`restaurant` → `pw_restaurant`, etc.).
 *
 * ## `scope_id` semantics
 *
 * - **`0` (or empty meta)** — group-level for the selected `scope_cpt`: one contact row
 *   applies to every outlet of that type under the property unless a row with a specific
 *   `_pw_scope_id` wins at tier 1.
 * - **Positive integer** — outlet-specific: `_pw_scope_id` must be the post ID of a published
 *   outlet (e.g. a `pw_spa` post) that belongs to the same `_pw_property_id`.
 *
 * ## Contract
 *
 * **All** contact reads in PHP (templates, schema output, REST handlers, importers) **must**
 * use `pw_resolve_contact()` / `pw_resolve_primary_contact()`. Do not query `pw_contact`
 * posts directly unless you are maintaining the data model (admin UI, migrations, purge).
 *
 * @package Portico_Webworks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PW_CONTACT_SCOPE_CPTS = [
	'property',
	'restaurant',
	'spa',
	'meeting_room',
	'experience',
	'all',
];

/**
 * @param string $scope_cpt Raw scope slug.
 * @return bool Whether $scope_cpt is allowed for `_pw_scope_cpt` / resolver input.
 */
function pw_contact_is_valid_scope_cpt( $scope_cpt ) {
	return in_array( (string) $scope_cpt, PW_CONTACT_SCOPE_CPTS, true );
}

/**
 * Meta_query fragment: _pw_scope_id is unset, empty, or zero.
 *
 * @return array<int, array<string, mixed>>
 */
function pw_contact_meta_scope_id_is_group_level() {
	return [
		'relation' => 'OR',
		[
			'key'     => '_pw_scope_id',
			'compare' => 'NOT EXISTS',
		],
		[
			'key'     => '_pw_scope_id',
			'value'   => '',
			'compare' => '=',
		],
		[
			'key'     => '_pw_scope_id',
			'value'   => '0',
			'compare' => '=',
		],
		[
			'key'     => '_pw_scope_id',
			'value'   => 0,
			'type'    => 'NUMERIC',
			'compare' => '=',
		],
	];
}

/**
 * @param WP_Post[] $posts
 * @return array<int, array<string, mixed>>
 */
function pw_contact_posts_to_result_rows( array $posts ) {
	$out = [];
	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$sid = get_post_meta( $post->ID, '_pw_scope_id', true );
		$sid = ( $sid === '' || $sid === null ) ? 0 : absint( $sid );
		$out[] = [
			'id'        => (int) $post->ID,
			'label'     => (string) get_post_meta( $post->ID, '_pw_label', true ),
			'phone'     => (string) get_post_meta( $post->ID, '_pw_phone', true ),
			'mobile'    => (string) get_post_meta( $post->ID, '_pw_mobile', true ),
			'whatsapp'  => (string) get_post_meta( $post->ID, '_pw_whatsapp', true ),
			'email'     => (string) get_post_meta( $post->ID, '_pw_email', true ),
			'scope_cpt' => (string) get_post_meta( $post->ID, '_pw_scope_cpt', true ),
			'scope_id'  => $sid,
		];
	}
	return $out;
}

/**
 * @param string $scope_cpt
 * @param int    $property_id
 * @param int    $scope_id    Must be > 0.
 * @return WP_Post[]
 */
function pw_contact_query_tier_outlet( $scope_cpt, $property_id, $scope_id ) {
	return get_posts(
		[
			'post_type'              => 'pw_contact',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => [
				'relation' => 'AND',
				[
					'key'     => '_pw_property_id',
					'value'   => (int) $property_id,
					'type'    => 'NUMERIC',
					'compare' => '=',
				],
				[
					'key'     => '_pw_scope_cpt',
					'value'   => $scope_cpt,
					'compare' => '=',
				],
				[
					'key'     => '_pw_scope_id',
					'value'   => (int) $scope_id,
					'type'    => 'NUMERIC',
					'compare' => '=',
				],
			],
		]
	);
}

/**
 * @param string $scope_cpt
 * @param int    $property_id
 * @return WP_Post[]
 */
function pw_contact_query_tier_group( $scope_cpt, $property_id ) {
	return get_posts(
		[
			'post_type'              => 'pw_contact',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => [
				'relation' => 'AND',
				[
					'key'     => '_pw_property_id',
					'value'   => (int) $property_id,
					'type'    => 'NUMERIC',
					'compare' => '=',
				],
				[
					'key'     => '_pw_scope_cpt',
					'value'   => $scope_cpt,
					'compare' => '=',
				],
				pw_contact_meta_scope_id_is_group_level(),
			],
		]
	);
}

/**
 * @param int $property_id
 * @return WP_Post[]
 */
function pw_contact_query_tier_property_fallback( $property_id ) {
	return get_posts(
		[
			'post_type'              => 'pw_contact',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => [
				'relation' => 'AND',
				[
					'key'     => '_pw_property_id',
					'value'   => (int) $property_id,
					'type'    => 'NUMERIC',
					'compare' => '=',
				],
				[
					'key'     => '_pw_scope_cpt',
					'value'   => 'property',
					'compare' => '=',
				],
			],
		]
	);
}

/**
 * Resolve contacts for a property and logical scope. Returns [] if nothing matches.
 *
 * @param string $scope_cpt  One of {@see PW_CONTACT_SCOPE_CPTS}.
 * @param int    $scope_id   Outlet post ID, or 0 for non-outlet contexts.
 * @param int    $property_id Property post ID.
 * @return array<int, array{id:int,label:string,phone:string,mobile:string,whatsapp:string,email:string,scope_cpt:string,scope_id:int}>
 */
function pw_resolve_contact( $scope_cpt, $scope_id, $property_id ) {
	$scope_cpt   = sanitize_key( (string) $scope_cpt );
	$scope_id    = (int) $scope_id;
	$property_id = (int) $property_id;

	if ( $property_id <= 0 || ! pw_contact_is_valid_scope_cpt( $scope_cpt ) ) {
		return [];
	}

	if ( $scope_id > 0 ) {
		$posts = pw_contact_query_tier_outlet( $scope_cpt, $property_id, $scope_id );
		if ( $posts !== [] ) {
			return pw_contact_posts_to_result_rows( $posts );
		}
	}

	$posts = pw_contact_query_tier_group( $scope_cpt, $property_id );
	if ( $posts !== [] ) {
		return pw_contact_posts_to_result_rows( $posts );
	}

	$posts = pw_contact_query_tier_property_fallback( $property_id );
	if ( $posts !== [] ) {
		return pw_contact_posts_to_result_rows( $posts );
	}

	return [];
}

/**
 * @param string $scope_cpt
 * @param int    $scope_id
 * @param int    $property_id
 * @return array{id:int,label:string,phone:string,mobile:string,whatsapp:string,email:string,scope_cpt:string,scope_id:int}|null
 */
function pw_resolve_primary_contact( $scope_cpt, $scope_id, $property_id ) {
	$all = pw_resolve_contact( $scope_cpt, $scope_id, $property_id );
	if ( $all === [] ) {
		return null;
	}
	return $all[0];
}

/**
 * REST: GET /wp-json/pw/v1/contacts — resolved contacts (auth: edit_posts).
 */
function pw_register_contact_rest_routes() {
	register_rest_route(
		'pw/v1',
		'/contacts',
		[
			'methods'             => 'GET',
			'permission_callback' => static function () {
				return current_user_can( 'edit_posts' );
			},
			'callback'            => static function ( WP_REST_Request $request ) {
				$property_id = (int) $request->get_param( 'property_id' );
				$scope_cpt   = (string) $request->get_param( 'scope_cpt' );
				$scope_id    = (int) $request->get_param( 'scope_id' );
				if ( $property_id <= 0 || $scope_cpt === '' ) {
					return new WP_Error( 'pw_contact_bad_request', 'property_id and scope_cpt are required.', [ 'status' => 400 ] );
				}
				if ( ! pw_contact_is_valid_scope_cpt( $scope_cpt ) ) {
					return new WP_Error( 'pw_contact_invalid_scope', 'Invalid scope_cpt.', [ 'status' => 400 ] );
				}
				$resolved = pw_resolve_contact( $scope_cpt, $scope_id, $property_id );
				return rest_ensure_response( $resolved );
			},
			'args'                => [
				'property_id' => [
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				],
				'scope_cpt'   => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				],
				'scope_id'    => [
					'required'          => false,
					'type'              => 'integer',
					'default'           => 0,
					'sanitize_callback' => 'absint',
				],
			],
		]
	);

	$outlet_types = [ 'pw_restaurant', 'pw_spa', 'pw_meeting_room', 'pw_experience' ];

	register_rest_route(
		'pw/v1',
		'/contact-scope-posts',
		[
			'methods'             => 'GET',
			'permission_callback' => static function () {
				return current_user_can( 'edit_posts' );
			},
			'callback'            => static function ( WP_REST_Request $request ) use ( $outlet_types ) {
				$property_id = (int) $request->get_param( 'property_id' );
				$post_type   = sanitize_key( (string) $request->get_param( 'post_type' ) );
				if ( $property_id <= 0 || $post_type === '' || ! in_array( $post_type, $outlet_types, true ) ) {
					return new WP_Error( 'pw_contact_scope_bad_request', 'Invalid property_id or post_type.', [ 'status' => 400 ] );
				}
				$posts = get_posts(
					[
						'post_type'              => $post_type,
						'post_status'            => 'publish',
						'posts_per_page'         => -1,
						'orderby'                => 'title',
						'order'                  => 'ASC',
						'no_found_rows'          => true,
						'update_post_meta_cache' => true,
						'update_post_term_cache' => false,
						'meta_query'             => [
							[
								'key'     => '_pw_property_id',
								'value'   => $property_id,
								'type'    => 'NUMERIC',
								'compare' => '=',
							],
						],
					]
				);
				$rows = [];
				foreach ( $posts as $p ) {
					$rows[] = [
						'id'    => (int) $p->ID,
						'title' => (string) get_the_title( $p ),
					];
				}
				return rest_ensure_response( $rows );
			},
			'args'                => [
				'property_id' => [
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				],
				'post_type'   => [
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				],
			],
		]
	);
}

add_action( 'rest_api_init', 'pw_register_contact_rest_routes' );
