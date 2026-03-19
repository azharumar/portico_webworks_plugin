<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Shared CPT config
// ---------------------------------------------------------------------------

function pw_cpt_labels( $singular, $plural ) {
	return [
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $plural,
		'add_new_item'       => 'Add New ' . $singular,
		'edit_item'          => 'Edit ' . $singular,
		'new_item'           => 'New ' . $singular,
		'view_item'          => 'View ' . $singular,
		'search_items'       => 'Search ' . $plural,
		'not_found'          => 'No ' . strtolower( $plural ) . ' found',
		'not_found_in_trash' => 'No ' . strtolower( $plural ) . ' found in trash',
		'all_items'          => 'All ' . $plural,
	];
}

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
		'labels'     => pw_cpt_labels( 'Feature', 'Features' ),
		'menu_icon'  => 'dashicons-tag',
	] ) );

	register_post_type( 'pw_room_type', array_merge( $defaults, [
		'labels'     => pw_cpt_labels( 'Room Type', 'Room Types' ),
		'menu_icon'  => 'dashicons-bed',
		'taxonomies' => [ 'pw_bed_type', 'pw_view_type' ],
	] ) );

	register_post_type( 'pw_restaurant', array_merge( $defaults, [
		'labels'     => pw_cpt_labels( 'Restaurant', 'Restaurants' ),
		'menu_icon'  => 'dashicons-food',
		'taxonomies' => [ 'pw_meal_period' ],
	] ) );

	register_post_type( 'pw_spa', array_merge( $defaults, [
		'labels'     => pw_cpt_labels( 'Spa', 'Spas' ),
		'menu_icon'  => 'dashicons-heart',
		'taxonomies' => [ 'pw_treatment_type' ],
	] ) );

	register_post_type( 'pw_meeting_room', array_merge( $defaults, [
		'labels'     => pw_cpt_labels( 'Meeting Room', 'Meeting Rooms' ),
		'menu_icon'  => 'dashicons-groups',
		'taxonomies' => [ 'pw_av_equipment' ],
	] ) );

	register_post_type( 'pw_amenity', array_merge( $defaults, [
		'labels'     => pw_cpt_labels( 'Amenity', 'Amenities' ),
		'menu_icon'  => 'dashicons-star-filled',
	] ) );

	register_post_type( 'pw_policy', array_merge( $defaults, [
		'labels'     => pw_cpt_labels( 'Policy', 'Policies' ),
		'menu_icon'  => 'dashicons-media-text',
	] ) );

	register_post_type( 'pw_faq', array_merge( $defaults, [
		'labels' => [
			'name'               => 'FAQs',
			'singular_name'      => 'FAQ',
			'menu_name'          => 'FAQs',
			'add_new_item'       => 'Add New FAQ',
			'edit_item'          => 'Edit FAQ',
			'new_item'           => 'New FAQ',
			'search_items'       => 'Search FAQs',
			'not_found'          => 'No FAQs found',
			'not_found_in_trash' => 'No FAQs found in trash',
			'all_items'          => 'All FAQs',
		],
		'menu_icon' => 'dashicons-editor-help',
		'supports'  => [ 'title', 'custom-fields' ],
	] ) );

	register_post_type( 'pw_offer', array_merge( $defaults, [
		'labels'    => pw_cpt_labels( 'Offer', 'Offers' ),
		'menu_icon' => 'dashicons-tag',
		'supports'  => [ 'title', 'thumbnail', 'custom-fields' ],
	] ) );

	register_post_type( 'pw_nearby', array_merge( $defaults, [
		'labels'     => pw_cpt_labels( 'Nearby Location', 'Nearby' ),
		'menu_icon'  => 'dashicons-location',
		'taxonomies' => [ 'pw_nearby_type', 'pw_transport_mode' ],
	] ) );

	register_post_type( 'pw_experience', array_merge( $defaults, [
		'labels'     => pw_cpt_labels( 'Experience', 'Experiences' ),
		'menu_icon'  => 'dashicons-star-half',
		'supports'   => [ 'title', 'thumbnail', 'custom-fields' ],
		'taxonomies' => [ 'pw_experience_category' ],
	] ) );

	register_post_type( 'pw_event', array_merge( $defaults, [
		'labels'     => pw_cpt_labels( 'Event', 'Events' ),
		'menu_icon'  => 'dashicons-calendar-alt',
		'supports'   => [ 'title', 'thumbnail', 'custom-fields' ],
		'taxonomies' => [ 'pw_event_type' ],
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

	register_taxonomy( 'pw_feature_group', 'pw_feature', array_merge( $shared, [
		'label' => 'Feature Groups',
	] ) );

	register_taxonomy( 'pw_nearby_type', 'pw_nearby', array_merge( $shared, [
		'label' => 'Location Types',
	] ) );

	register_taxonomy( 'pw_transport_mode', 'pw_nearby', array_merge( $shared, [
		'label' => 'Transport Modes',
	] ) );

	register_taxonomy( 'pw_experience_category', 'pw_experience', array_merge( $shared, [
		'label' => 'Experience Categories',
	] ) );

	register_taxonomy( 'pw_event_type', 'pw_event', array_merge( $shared, [
		'label' => 'Event Types',
	] ) );
}

// ---------------------------------------------------------------------------
// Post meta registration
// ---------------------------------------------------------------------------

function pw_register_child_post_meta() {

	// --- pw_feature ---

	foreach ( [ '_pw_icon', '_pw_short_description' ] as $key ) {
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

	// --- pw_faq ---

	register_post_meta( 'pw_faq', '_pw_answer', [
		'type'         => 'string',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => '',
	] );

	register_post_meta( 'pw_faq', '_pw_connected_to', [
		'type'   => 'array',
		'single' => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [
					'type'       => 'object',
					'properties' => [
						'type' => [ 'type' => 'string' ],
						'id'   => [ 'type' => 'integer' ],
					],
				],
			],
		],
	] );

	// --- pw_property: sustainability ---

	$sus_keys = [
		'_pw_sus_solar_power',
		'_pw_sus_solar_water_heater',
		'_pw_sus_energy_efficient_lighting',
		'_pw_sus_energy_saving_thermostats',
		'_pw_sus_green_building_design',
		'_pw_sus_water_efficient_fixtures',
		'_pw_sus_sewage_treatment_plant',
		'_pw_sus_water_reuse_program',
		'_pw_sus_waste_segregation',
		'_pw_sus_recycling_program',
		'_pw_sus_no_styrofoam',
		'_pw_sus_electronics_disposal',
		'_pw_sus_reusable_water_bottles',
		'_pw_sus_wall_mounted_dispensers',
		'_pw_sus_eco_friendly_toiletries',
		'_pw_sus_towel_reuse_program',
		'_pw_sus_linen_reuse_program',
		'_pw_sus_local_food_sourcing',
		'_pw_sus_organic_food_options',
	];

	foreach ( $sus_keys as $key ) {
		register_post_meta( 'pw_property', $key, [
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => 'unknown',
		] );
	}

	foreach ( [ '_pw_sus_certification_name', '_pw_sus_certification_url' ] as $key ) {
		register_post_meta( 'pw_property', $key, [
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => '',
		] );
	}

	// --- pw_property: accessibility ---

	$acc_keys = [
		'_pw_acc_wheelchair_accessible',
		'_pw_acc_step_free_entrance',
		'_pw_acc_automatic_doors',
		'_pw_acc_accessible_parking',
		'_pw_acc_accessible_path_to_entrance',
		'_pw_acc_accessible_room_available',
		'_pw_acc_grab_bars_bathroom',
		'_pw_acc_roll_in_shower',
		'_pw_acc_adjustable_showerhead',
		'_pw_acc_lowered_closet',
		'_pw_acc_transfer_friendly_bed',
		'_pw_acc_emergency_pull_cords',
		'_pw_acc_reachable_outlets',
		'_pw_acc_elevator',
		'_pw_acc_elevator_audio_cues',
		'_pw_acc_pool_lift',
		'_pw_acc_accessible_restaurant',
		'_pw_acc_visual_fire_alarm',
		'_pw_acc_clear_dietary_labels',
	];

	foreach ( $acc_keys as $key ) {
		register_post_meta( 'pw_property', $key, [
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => 'unknown',
		] );
	}

	// --- pw_property: pools ---

	register_post_meta( 'pw_property', '_pw_pools', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [
					'type'       => 'object',
					'properties' => [
						'name'        => [ 'type' => 'string' ],
						'length_m'    => [ 'type' => 'number' ],
						'width_m'     => [ 'type' => 'number' ],
						'depth_m'     => [ 'type' => 'number' ],
						'is_heated'   => [ 'type' => 'boolean' ],
						'is_kids'     => [ 'type' => 'boolean' ],
						'is_indoor'   => [ 'type' => 'boolean' ],
						'is_infinity' => [ 'type' => 'boolean' ],
					],
				],
			],
		],
	] );

	// --- pw_property: direct booking benefits ---

	register_post_meta( 'pw_property', '_pw_direct_benefits', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [
					'type'       => 'object',
					'properties' => [
						'title'       => [ 'type' => 'string' ],
						'description' => [ 'type' => 'string' ],
						'icon'        => [ 'type' => 'string' ],
					],
				],
			],
		],
	] );

	// --- pw_offer ---

	register_post_meta( 'pw_offer', '_pw_parent_type', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_offer', '_pw_parent_id', [
		'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'default' => 0,
	] );
	register_post_meta( 'pw_offer', '_pw_offer_type', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => 'promotion',
	] );
	register_post_meta( 'pw_offer', '_pw_description', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_offer', '_pw_valid_from', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_offer', '_pw_valid_to', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_offer', '_pw_booking_url', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_offer', '_pw_terms', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_offer', '_pw_is_featured', [
		'type' => 'boolean', 'single' => true, 'show_in_rest' => true, 'default' => false,
	] );

	// --- pw_nearby ---

	register_post_meta( 'pw_nearby', '_pw_property_id', [
		'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'default' => 0,
	] );
	register_post_meta( 'pw_nearby', '_pw_distance_km', [
		'type' => 'number', 'single' => true, 'show_in_rest' => true, 'default' => 0,
	] );
	register_post_meta( 'pw_nearby', '_pw_travel_time_min', [
		'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'default' => 0,
	] );
	register_post_meta( 'pw_nearby', '_pw_place_url', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );

	// --- pw_experience ---

	register_post_meta( 'pw_experience', '_pw_property_id', [
		'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'default' => 0,
	] );
	register_post_meta( 'pw_experience', '_pw_description', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_experience', '_pw_duration_hours', [
		'type' => 'number', 'single' => true, 'show_in_rest' => true, 'default' => 0,
	] );
	register_post_meta( 'pw_experience', '_pw_price_from', [
		'type' => 'number', 'single' => true, 'show_in_rest' => true, 'default' => 0,
	] );
	register_post_meta( 'pw_experience', '_pw_booking_url', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_experience', '_pw_is_complimentary', [
		'type' => 'boolean', 'single' => true, 'show_in_rest' => true, 'default' => false,
	] );
	register_post_meta( 'pw_experience', '_pw_gallery', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [ 'type' => 'integer' ],
			],
		],
	] );

	// --- pw_event ---

	register_post_meta( 'pw_event', '_pw_property_id', [
		'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'default' => 0,
	] );
	register_post_meta( 'pw_event', '_pw_venue_id', [
		'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'default' => 0,
	] );
	register_post_meta( 'pw_event', '_pw_description', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_event', '_pw_start_datetime', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_event', '_pw_end_datetime', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_event', '_pw_capacity', [
		'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'default' => 0,
	] );
	register_post_meta( 'pw_event', '_pw_price_from', [
		'type' => 'number', 'single' => true, 'show_in_rest' => true, 'default' => 0,
	] );
	register_post_meta( 'pw_event', '_pw_booking_url', [
		'type' => 'string', 'single' => true, 'show_in_rest' => true, 'default' => '',
	] );
	register_post_meta( 'pw_event', '_pw_is_recurring', [
		'type' => 'boolean', 'single' => true, 'show_in_rest' => true, 'default' => false,
	] );
	register_post_meta( 'pw_event', '_pw_gallery', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [ 'type' => 'integer' ],
			],
		],
	] );
}

add_action( 'init', 'pw_register_child_taxonomies' );
add_action( 'init', 'pw_register_child_post_types' );
add_action( 'init', 'pw_register_child_post_meta' );

// ---------------------------------------------------------------------------
// Admin menu cleanup — remove "All {CPT}" submenu items
// ---------------------------------------------------------------------------

function pw_remove_cpt_submenus() {
	$cpts = [
		'pw_property',
		'pw_feature',
		'pw_room_type',
		'pw_restaurant',
		'pw_spa',
		'pw_meeting_room',
		'pw_amenity',
		'pw_policy',
		'pw_faq',
		'pw_offer',
		'pw_nearby',
		'pw_experience',
		'pw_event',
	];

	foreach ( $cpts as $cpt ) {
		remove_submenu_page(
			'portico-webworks',
			'edit.php?post_type=' . $cpt
		);
	}
}

add_action( 'admin_menu', 'pw_remove_cpt_submenus', 999 );
