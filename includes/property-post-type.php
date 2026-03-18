<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_register_property_post_type() {
	register_post_type(
		'pw_property',
		array(
			'labels' => array(
				'name' => 'Properties',
				'singular_name' => 'Property',
				'menu_name' => 'Properties',
				'add_new' => 'Add Property',
				'add_new_item' => 'Add Property',
				'edit_item' => 'Edit Property',
				'new_item' => 'New Property',
				'view_item' => 'View Property',
				'search_items' => 'Search Properties',
				'not_found' => 'No properties found',
				'not_found_in_trash' => 'No properties found in Trash',
				'all_items' => 'All Properties',
			),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_in_admin_bar' => false,
			'supports' => array('title'),
			'capability_type' => 'post',
			'menu_position' => 58,
			'rewrite' => false,
		)
	);
}

add_action('init', 'pw_register_property_post_type');

