<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_register_property_post_type() {
	$mode = get_option('pw_property_mode', 'single');
	$is_multi = $mode === 'multi';

	$property_base = 'properties';
	if (function_exists('pw_property_base')) {
		$property_base = pw_property_base();
	} else {
		$base = get_option('pw_property_base', 'properties');
		$base = is_string($base) ? trim($base) : 'properties';
		$base = trim($base, '/');
		$property_base = sanitize_title($base !== '' ? $base : 'properties');
	}

	register_post_type(
		'pw_property',
		array(
			'labels' => array(
				'name' => 'Properties',
				'singular_name' => 'Property',
				'menu_name' => 'Properties',
				'add_new_item' => 'Add New Property',
				'edit_item' => 'Edit Property',
				'new_item' => 'New Property',
				'view_item' => 'View Property',
				'search_items' => 'Search Properties',
				'not_found' => 'No properties found',
				'not_found_in_trash' => 'No properties found in trash',
				'all_items' => 'All Properties',
			),
		'public'             => true,
		'publicly_queryable' => $is_multi,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_admin_bar'  => $is_multi,
			'show_in_nav_menus'  => $is_multi,
			'show_in_rest'       => true,
			'rest_base'          => 'pw-properties',

			'rewrite'            => $is_multi
				? array('slug' => $property_base, 'with_front' => false)
				: false,
			'query_var'          => $is_multi,
			'has_archive'        => false,
			'hierarchical'       => false,

			'supports'           => [ 'title', 'editor', 'thumbnail', 'revisions', 'custom-fields' ],
			'capability_type' => 'post',
			'menu_position' => 58,
		)
	);
}

function pw_register_property_post_meta() {
	$string_keys = [
		'_pw_legal_name',
		'_pw_brand_name',
		'_pw_slug',
		'_pw_address_line_1',
		'_pw_address_line_2',
		'_pw_city',
		'_pw_country',
		'_pw_phone',
		'_pw_email',
		'_pw_social_facebook',
		'_pw_social_instagram',
		'_pw_social_tripadvisor',
		'_pw_social_linkedin',
		'_pw_social_youtube',
		'_pw_default_template',
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
}

// Override viewable so builders (GenerateBlocks) discover pw_property even when publicly_queryable is false.
add_filter('is_post_type_viewable', function ($is_viewable, $post_type) {
	if ('pw_property' === $post_type->name) {
		return true;
	}
	return $is_viewable;
}, 10, 2);

add_action('init', 'pw_register_property_post_type');
add_action('init', 'pw_register_property_post_meta');

