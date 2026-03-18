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
	register_post_meta( 'pw_property', '_pw_legal_name', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_brand_name', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_slug', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_address_line_1', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_address_line_2', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_city', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_country', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_lat', [
		'type'         => 'number',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_lng', [
		'type'         => 'number',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_phone', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_email', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_star_rating', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'pw_property', '_pw_social_links', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [ 'type' => 'string' ],
			],
		],
	] );

	register_post_meta( 'pw_property', '_pw_default_template', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
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

