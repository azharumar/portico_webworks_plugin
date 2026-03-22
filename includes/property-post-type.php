<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_register_property_post_type() {
	$is_multi = pw_get_setting( 'pw_property_mode', 'single' ) === 'multi';

	register_post_type( 'pw_property', [
		'labels' => [
			'name'               => 'Properties',
			'singular_name'      => 'Property',
			'menu_name'          => 'Properties',
			'add_new_item'       => 'Add New Property',
			'edit_item'          => 'Edit Property',
			'new_item'           => 'New Property',
			'view_item'          => 'View Property',
			'search_items'       => 'Search Properties',
			'not_found'          => 'No properties found',
			'not_found_in_trash' => 'No properties found in trash',
			'all_items'          => 'All Properties',
		],

		// mode-dependent: `public` true so REST exposes permalink_template / slug UI; front queries only when multi.
		// `rewrite` false: permastruct `pw_register_property_permastruct` supplies URL shape; avoids Core duplicate rules.
		'public'             => true,
		'publicly_queryable' => $is_multi,
		'show_in_nav_menus'  => $is_multi,
		'rewrite'            => false,
		'query_var'          => $is_multi ? 'pw_property' : false,

		// always on
		'show_ui'            => true,
		'show_in_menu'       => pw_admin_page_slug(),
		'show_in_admin_bar'  => true,
		'show_in_rest'       => true,
		'rest_base'          => 'pw-properties',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_icon'          => 'dashicons-building',
		'menu_position'      => 25,

		'supports'           => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'slug' ],
		'taxonomies'         => [ 'pw_property_type' ],

		'can_export'         => true,
		'delete_with_user'   => false,
		'capability_type'    => 'post',
		'map_meta_cap'       => true,
	] );
}

function pw_register_property_post_meta() {
	$string_keys = [
		'_pw_legal_name',
		'_pw_address_line_1',
		'_pw_address_line_2',
		'_pw_city',
		'_pw_state',
		'_pw_postal_code',
		'_pw_country',
		'_pw_country_code',
		'_pw_social_facebook',
		'_pw_social_instagram',
		'_pw_social_twitter',
		'_pw_social_tripadvisor',
		'_pw_social_linkedin',
		'_pw_social_youtube',
		'_pw_google_place_id',
		'_pw_timezone',
		'_pw_meta_title',
		'_pw_meta_description',
	];

	foreach ( $string_keys as $key ) {
		register_post_meta( 'pw_property', $key, [
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => '',
		] );
	}

	register_post_meta( 'pw_property', '_pw_star_rating', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	register_post_meta( 'pw_property', '_pw_lat', [
		'type'         => 'number',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	register_post_meta( 'pw_property', '_pw_lng', [
		'type'         => 'number',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	register_post_meta( 'pw_property', '_pw_currency', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 'USD',
	] );

	register_post_meta( 'pw_property', '_pw_check_in_time', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => '',
	] );

	register_post_meta( 'pw_property', '_pw_check_out_time', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => '',
	] );

	register_post_meta( 'pw_property', '_pw_year_established', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	register_post_meta( 'pw_property', '_pw_total_rooms', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	register_post_meta( 'pw_property', '_pw_og_image', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	register_post_meta( 'pw_property', '_pw_enabled_sections', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => [],
	] );
}

// Override viewable so builders (GenerateBlocks) discover pw_property even when publicly_queryable is false.
add_filter('is_post_type_viewable', function ($is_viewable, $post_type) {
	if ('pw_property' === $post_type->name) {
		return true;
	}
	return $is_viewable;
}, 10, 2);

function pw_register_page_property_scope_meta() {
	register_post_meta(
		'page',
		'_pw_property_id',
		[
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'default'           => 0,
			'sanitize_callback' => static function ( $value ) {
				return (int) $value;
			},
		]
	);
}

function pw_register_property_permastruct(): void {
	add_permastruct(
		'pw_property',
		'/%postname%',
		[
			'with_front' => false,
			'ep_mask'    => EP_NONE,
			'paged'      => false,
			'feed'       => false,
			'forpage'    => false,
			'walk_dirs'  => false,
		]
	);
}

add_action( 'init', 'pw_register_property_post_type', 10 );
add_action( 'init', 'pw_register_property_permastruct', 12 );
add_action( 'init', 'pw_register_property_post_meta' );
add_action( 'init', 'pw_register_page_property_scope_meta', 11 );

add_filter( 'post_type_link', 'pw_property_post_type_link', 15, 2 );

/**
 * @param string  $post_link Permalink for the post.
 * @param WP_Post $post      Post object.
 * @return string
 */
function pw_property_post_type_link( $post_link, $post ) {
	if ( ! $post instanceof WP_Post || $post->post_type !== 'pw_property' ) {
		return $post_link;
	}
	$name = $post->post_name;
	if ( ! is_string( $name ) || $name === '' ) {
		return untrailingslashit( $post_link );
	}

	return untrailingslashit( home_url( '/' . sanitize_title( $name ) ) );
}

add_filter( 'get_sample_permalink', 'pw_property_get_sample_permalink', 15, 5 );

/**
 * @param array   $permalink [ template, post_name ].
 * @param int     $post_id   Post ID.
 * @param ?string $title     Title override.
 * @param ?string $name      Name override.
 * @param WP_Post $post      Post object.
 * @return array
 */
function pw_property_get_sample_permalink( $permalink, $post_id, $title, $name, $post ) {
	if ( ! $post instanceof WP_Post || $post->post_type !== 'pw_property' ) {
		return $permalink;
	}
	if ( ! is_array( $permalink ) || ! isset( $permalink[0] ) ) {
		return $permalink;
	}
	$permalink[0] = untrailingslashit( home_url( '/%postname%' ) );
	return $permalink;
}

