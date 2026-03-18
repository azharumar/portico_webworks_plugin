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

