<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Shared CPT config
// ---------------------------------------------------------------------------

function pw_child_cpt_defaults() {
	return [
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => 'portico-webworks',
		'show_in_rest'       => true,
		'show_in_nav_menus'  => false,
		'show_in_admin_bar'  => false,
		'has_archive'        => false,
		'hierarchical'       => false,
		'rewrite'            => false,
		'query_var'          => false,
		'supports'           => [ 'title', 'custom-fields' ],
		'capability_type'    => 'post',
		'map_meta_cap'       => true,
	];
}

// ---------------------------------------------------------------------------
// CPT registration
// ---------------------------------------------------------------------------

function pw_register_child_post_types() {
	$defaults = pw_child_cpt_defaults();

	register_post_type( 'pw_feature', array_merge( $defaults, [
		'label'      => 'Features',
		'menu_icon'  => 'dashicons-tag',
	] ) );

	register_post_type( 'pw_room_type', array_merge( $defaults, [
		'label'      => 'Room Types',
		'menu_icon'  => 'dashicons-bed',
		'taxonomies' => [ 'pw_bed_type', 'pw_view_type' ],
	] ) );

	register_post_type( 'pw_restaurant', array_merge( $defaults, [
		'label'      => 'Restaurants',
		'menu_icon'  => 'dashicons-food',
		'taxonomies' => [ 'pw_meal_period' ],
	] ) );

	register_post_type( 'pw_spa', array_merge( $defaults, [
		'label'      => 'Spas',
		'menu_icon'  => 'dashicons-heart',
		'taxonomies' => [ 'pw_treatment_type' ],
	] ) );

	register_post_type( 'pw_meeting_room', array_merge( $defaults, [
		'label'      => 'Meeting Rooms',
		'menu_icon'  => 'dashicons-groups',
		'taxonomies' => [ 'pw_av_equipment' ],
	] ) );

	register_post_type( 'pw_amenity', array_merge( $defaults, [
		'label'      => 'Amenities',
		'menu_icon'  => 'dashicons-star-filled',
	] ) );

	register_post_type( 'pw_policy', array_merge( $defaults, [
		'label'      => 'Policies',
		'menu_icon'  => 'dashicons-media-text',
	] ) );
}

// ---------------------------------------------------------------------------
// Taxonomy registration
// ---------------------------------------------------------------------------

function pw_register_child_taxonomies() {
	$shared = [
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => false,
	];

	register_taxonomy( 'pw_bed_type', 'pw_room_type', array_merge( $shared, [
		'label' => 'Bed Types',
	] ) );

	register_taxonomy( 'pw_view_type', 'pw_room_type', array_merge( $shared, [
		'label' => 'View Types',
	] ) );

	register_taxonomy( 'pw_meal_period', 'pw_restaurant', array_merge( $shared, [
		'label' => 'Meal Periods',
	] ) );

	register_taxonomy( 'pw_treatment_type', 'pw_spa', array_merge( $shared, [
		'label' => 'Treatment Types',
	] ) );

	register_taxonomy( 'pw_av_equipment', 'pw_meeting_room', array_merge( $shared, [
		'label' => 'AV Equipment',
	] ) );
}

// ---------------------------------------------------------------------------
// Post meta registration
// ---------------------------------------------------------------------------

function pw_register_child_post_meta() {

	// --- pw_feature ---

	foreach ( [ '_pw_icon', '_pw_feature_category' ] as $key ) {
		register_post_meta( 'pw_feature', $key, [
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => '',
		] );
	}

	// --- pw_room_type ---

	register_post_meta( 'pw_room_type', '_pw_property_id', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	register_post_meta( 'pw_room_type', '_pw_rate_from', [
		'type'         => 'number',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	foreach ( [ '_pw_max_occupancy', '_pw_size_sqft', '_pw_size_sqm' ] as $key ) {
		register_post_meta( 'pw_room_type', $key, [
			'type'         => 'integer',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => 0,
		] );
	}

	register_post_meta( 'pw_room_type', '_pw_gallery', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [ 'type' => 'integer' ],
			],
		],
	] );

	register_post_meta( 'pw_room_type', '_pw_features', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [ 'type' => 'integer' ],
			],
		],
	] );

	// --- pw_restaurant ---

	register_post_meta( 'pw_restaurant', '_pw_property_id', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	foreach ( [ '_pw_cuisine_type', '_pw_reservation_url', '_pw_menu_url' ] as $key ) {
		register_post_meta( 'pw_restaurant', $key, [
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => '',
		] );
	}

	register_post_meta( 'pw_restaurant', '_pw_seating_capacity', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	register_post_meta( 'pw_restaurant', '_pw_gallery', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [ 'type' => 'integer' ],
			],
		],
	] );

	register_post_meta( 'pw_restaurant', '_pw_operating_hours', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [
					'type'       => 'object',
					'properties' => [
						'day'        => [ 'type' => 'string' ],
						'open_time'  => [ 'type' => 'string' ],
						'close_time' => [ 'type' => 'string' ],
					],
				],
			],
		],
	] );

	// --- pw_spa ---

	register_post_meta( 'pw_spa', '_pw_property_id', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	foreach ( [ '_pw_booking_url', '_pw_menu_url' ] as $key ) {
		register_post_meta( 'pw_spa', $key, [
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => '',
		] );
	}

	register_post_meta( 'pw_spa', '_pw_min_age', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	register_post_meta( 'pw_spa', '_pw_gallery', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [ 'type' => 'integer' ],
			],
		],
	] );

	register_post_meta( 'pw_spa', '_pw_operating_hours', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [
					'type'       => 'object',
					'properties' => [
						'day'        => [ 'type' => 'string' ],
						'open_time'  => [ 'type' => 'string' ],
						'close_time' => [ 'type' => 'string' ],
					],
				],
			],
		],
	] );

	// --- pw_meeting_room ---

	register_post_meta( 'pw_meeting_room', '_pw_property_id', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	foreach ( [
		'_pw_capacity_theatre',
		'_pw_capacity_classroom',
		'_pw_capacity_boardroom',
		'_pw_capacity_ushape',
		'_pw_area_sqft',
		'_pw_area_sqm',
	] as $key ) {
		register_post_meta( 'pw_meeting_room', $key, [
			'type'         => 'integer',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => 0,
		] );
	}

	register_post_meta( 'pw_meeting_room', '_pw_natural_light', [
		'type'         => 'boolean',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => false,
	] );

	register_post_meta( 'pw_meeting_room', '_pw_floor_plan', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	register_post_meta( 'pw_meeting_room', '_pw_gallery', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [ 'type' => 'integer' ],
			],
		],
	] );

	// --- pw_amenity ---

	register_post_meta( 'pw_amenity', '_pw_property_id', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	foreach ( [ '_pw_type', '_pw_icon', '_pw_category', '_pw_description' ] as $key ) {
		register_post_meta( 'pw_amenity', $key, [
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => '',
		] );
	}

	register_post_meta( 'pw_amenity', '_pw_is_complimentary', [
		'type'         => 'boolean',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => false,
	] );

	register_post_meta( 'pw_amenity', '_pw_display_order', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	// --- pw_policy ---

	register_post_meta( 'pw_policy', '_pw_property_id', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	foreach ( [ '_pw_policy_type', '_pw_title', '_pw_content' ] as $key ) {
		register_post_meta( 'pw_policy', $key, [
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => '',
		] );
	}

	register_post_meta( 'pw_policy', '_pw_is_highlighted', [
		'type'         => 'boolean',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => false,
	] );

	register_post_meta( 'pw_policy', '_pw_display_order', [
		'type'         => 'integer',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 0,
	] );

	register_post_meta( 'pw_policy', '_pw_active', [
		'type'         => 'boolean',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => true,
	] );
}

add_action( 'init', 'pw_register_child_taxonomies' );
add_action( 'init', 'pw_register_child_post_types' );
add_action( 'init', 'pw_register_child_post_meta' );
