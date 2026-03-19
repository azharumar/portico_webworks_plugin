<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Helper: published pw_property posts as a select options array
// ---------------------------------------------------------------------------

function pw_property_options() {
	$properties = get_posts( [
		'post_type'      => 'pw_property',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	] );

	$options = [ '' => '— Select Property —' ];
	foreach ( $properties as $p ) {
		$options[ $p->ID ] = $p->post_title;
	}
	return $options;
}

// ---------------------------------------------------------------------------
// Offer parent options helper
// ---------------------------------------------------------------------------

function pw_offer_parent_options() {
	$options = [ '' => '— Select —' ];
	$types   = [
		'pw_property'   => 'Property',
		'pw_restaurant' => 'Restaurant',
		'pw_spa'        => 'Spa',
	];
	foreach ( $types as $post_type => $label ) {
		$posts = get_posts( [
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
		foreach ( $posts as $p ) {
			$options[ $p->ID ] = '[' . $label . '] ' . $p->post_title;
		}
	}
	return $options;
}

// ---------------------------------------------------------------------------
// Meeting room options helper
// ---------------------------------------------------------------------------

function pw_meeting_room_options() {
	$options = [ '' => '— No specific venue —' ];
	$rooms   = get_posts( [
		'post_type'      => 'pw_meeting_room',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	] );
	foreach ( $rooms as $r ) {
		$options[ $r->ID ] = $r->post_title;
	}
	return $options;
}

// ---------------------------------------------------------------------------
// FAQ connection options helper
// ---------------------------------------------------------------------------

function pw_faq_connection_options() {
	$options = [ '' => '— Select —' ];
	$types   = [
		'pw_property'     => 'Property',
		'pw_restaurant'   => 'Restaurant',
		'pw_meeting_room' => 'Meeting Room',
		'pw_spa'          => 'Spa',
	];
	foreach ( $types as $post_type => $label ) {
		$posts = get_posts( [
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
		foreach ( $posts as $p ) {
			$options[ $p->ID ] = '[' . $label . '] ' . $p->post_title;
		}
	}
	return $options;
}

// ---------------------------------------------------------------------------
// Register all child CPT meta boxes
// ---------------------------------------------------------------------------

add_action( 'cmb2_admin_init', 'pw_register_child_metaboxes' );

function pw_register_child_metaboxes() {

	// --- pw_feature ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_feature_metabox',
		'title'        => 'Feature Details',
		'object_types' => [ 'pw_feature' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [
		'name' => 'Icon',
		'desc' => 'Paste an SVG string or icon slug',
		'id'   => '_pw_icon',
		'type' => 'textarea_small',
	] );

	$cmb->add_field( [
		'name' => 'Short description',
		'desc' => 'One sentence. Displayed beneath the feature name on the front end.',
		'id'   => '_pw_short_description',
		'type' => 'textarea_small',
	] );

	// --- pw_room_type ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_room_type_metabox',
		'title'        => 'Room Type Details',
		'object_types' => [ 'pw_room_type' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [
		'name'    => 'Property',
		'id'      => '_pw_property_id',
		'type'    => 'select',
		'options' => 'pw_property_options',
	] );

	$cmb->add_field( [ 'name' => 'Rate from', 'desc' => 'Starting rate. Currency is set on the parent property.', 'id' => '_pw_rate_from', 'type' => 'text_money' ] );
	$cmb->add_field( [ 'name' => 'Max occupancy', 'id' => '_pw_max_occupancy', 'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Size (sqft)',   'id' => '_pw_size_sqft',     'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Size (sqm)',    'id' => '_pw_size_sqm',      'type' => 'text_small' ] );

	$cmb->add_field( [
		'name'    => 'Features',
		'desc'    => 'Select all applicable features',
		'id'      => '_pw_features',
		'type'    => 'multicheck',
		'options' => function() {
			$features = get_posts( [
				'post_type'      => 'pw_feature',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			] );
			$options = [];
			foreach ( $features as $f ) {
				$options[ $f->ID ] = $f->post_title;
			}
			return $options;
		},
	] );

	$cmb->add_field( [ 'name' => 'Gallery', 'id' => '_pw_gallery', 'type' => 'file_list' ] );

	// --- pw_restaurant ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_restaurant_metabox',
		'title'        => 'Restaurant Details',
		'object_types' => [ 'pw_restaurant' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property',          'id' => '_pw_property_id',      'type' => 'select',     'options' => 'pw_property_options' ] );
	$cmb->add_field( [ 'name' => 'Cuisine type',       'id' => '_pw_cuisine_type',     'type' => 'text' ] );
	$cmb->add_field( [ 'name' => 'Seating capacity',   'id' => '_pw_seating_capacity', 'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Reservation URL',    'id' => '_pw_reservation_url',  'type' => 'text_url' ] );
	$cmb->add_field( [ 'name' => 'Menu URL',           'id' => '_pw_menu_url',         'type' => 'text_url' ] );

	$cmb->add_field( [
		'name'       => 'Operating hours',
		'id'         => '_pw_operating_hours',
		'type'       => 'group',
		'repeatable' => true,
		'options'    => [ 'group_title' => 'Day {#}', 'add_button' => 'Add Day', 'remove_button' => 'Remove Day' ],
		'fields'     => [
			[
				'name'    => 'Day',
				'id'      => 'day',
				'type'    => 'select',
				'options' => [
					'monday'    => 'Monday',
					'tuesday'   => 'Tuesday',
					'wednesday' => 'Wednesday',
					'thursday'  => 'Thursday',
					'friday'    => 'Friday',
					'saturday'  => 'Saturday',
					'sunday'    => 'Sunday',
				],
			],
			[ 'name' => 'Opens at',  'id' => 'open_time',  'type' => 'text_small' ],
			[ 'name' => 'Closes at', 'id' => 'close_time', 'type' => 'text_small' ],
		],
	] );

	$cmb->add_field( [ 'name' => 'Gallery', 'id' => '_pw_gallery', 'type' => 'file_list' ] );

	// --- pw_spa ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_spa_metabox',
		'title'        => 'Spa Details',
		'object_types' => [ 'pw_spa' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property',    'id' => '_pw_property_id', 'type' => 'select',     'options' => 'pw_property_options' ] );
	$cmb->add_field( [ 'name' => 'Booking URL', 'id' => '_pw_booking_url', 'type' => 'text_url' ] );
	$cmb->add_field( [ 'name' => 'Menu URL',    'id' => '_pw_menu_url',    'type' => 'text_url' ] );
	$cmb->add_field( [ 'name' => 'Minimum age', 'id' => '_pw_min_age',     'type' => 'text_small' ] );

	$cmb->add_field( [
		'name'       => 'Operating hours',
		'id'         => '_pw_operating_hours',
		'type'       => 'group',
		'repeatable' => true,
		'options'    => [ 'group_title' => 'Day {#}', 'add_button' => 'Add Day', 'remove_button' => 'Remove Day' ],
		'fields'     => [
			[
				'name'    => 'Day',
				'id'      => 'day',
				'type'    => 'select',
				'options' => [
					'monday'    => 'Monday',
					'tuesday'   => 'Tuesday',
					'wednesday' => 'Wednesday',
					'thursday'  => 'Thursday',
					'friday'    => 'Friday',
					'saturday'  => 'Saturday',
					'sunday'    => 'Sunday',
				],
			],
			[ 'name' => 'Opens at',  'id' => 'open_time',  'type' => 'text_small' ],
			[ 'name' => 'Closes at', 'id' => 'close_time', 'type' => 'text_small' ],
		],
	] );

	$cmb->add_field( [ 'name' => 'Gallery', 'id' => '_pw_gallery', 'type' => 'file_list' ] );

	// --- pw_meeting_room ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_meeting_room_metabox',
		'title'        => 'Meeting Room Details',
		'object_types' => [ 'pw_meeting_room' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property',             'id' => '_pw_property_id',        'type' => 'select',     'options' => 'pw_property_options' ] );
	$cmb->add_field( [ 'name' => 'Capacity — Theatre',   'id' => '_pw_capacity_theatre',   'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Capacity — Classroom', 'id' => '_pw_capacity_classroom', 'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Capacity — Boardroom', 'id' => '_pw_capacity_boardroom', 'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Capacity — U-Shape',   'id' => '_pw_capacity_ushape',    'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Area (sqft)',           'id' => '_pw_area_sqft',          'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Area (sqm)',            'id' => '_pw_area_sqm',           'type' => 'text_small' ] );

	$cmb->add_field( [
		'name' => 'Natural light',
		'id'   => '_pw_natural_light',
		'type' => 'checkbox',
	] );

	$cmb->add_field( [
		'name' => 'Floor plan',
		'desc' => 'Upload floor plan image',
		'id'   => '_pw_floor_plan',
		'type' => 'file',
	] );

	$cmb->add_field( [ 'name' => 'Gallery', 'id' => '_pw_gallery', 'type' => 'file_list' ] );

	// --- pw_amenity ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_amenity_metabox',
		'title'        => 'Amenity Details',
		'object_types' => [ 'pw_amenity' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property', 'id' => '_pw_property_id', 'type' => 'select', 'options' => 'pw_property_options' ] );

	$cmb->add_field( [
		'name'    => 'Type',
		'id'      => '_pw_type',
		'type'    => 'select',
		'options' => [
			'amenity'  => 'Amenity',
			'service'  => 'Service',
			'facility' => 'Facility',
		],
	] );

	$cmb->add_field( [ 'name' => 'Category',      'id' => '_pw_category',      'type' => 'text' ] );
	$cmb->add_field( [ 'name' => 'Icon',          'id' => '_pw_icon',          'type' => 'textarea_small' ] );
	$cmb->add_field( [ 'name' => 'Description',   'id' => '_pw_description',   'type' => 'textarea_small' ] );
	$cmb->add_field( [ 'name' => 'Display order', 'id' => '_pw_display_order', 'type' => 'text_small' ] );

	$cmb->add_field( [
		'name' => 'Complimentary',
		'id'   => '_pw_is_complimentary',
		'type' => 'checkbox',
	] );

	// --- pw_policy ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_policy_metabox',
		'title'        => 'Policy Details',
		'object_types' => [ 'pw_policy' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property', 'id' => '_pw_property_id', 'type' => 'select', 'options' => 'pw_property_options' ] );

	$cmb->add_field( [
		'name'    => 'Policy type',
		'id'      => '_pw_policy_type',
		'type'    => 'select',
		'options' => [
			'checkin'      => 'Check-in',
			'checkout'     => 'Check-out',
			'cancellation' => 'Cancellation',
			'pet'          => 'Pet',
			'child'        => 'Child',
			'payment'      => 'Payment',
			'smoking'      => 'Smoking',
			'custom'       => 'Custom',
		],
	] );

	$cmb->add_field( [ 'name' => 'Title',         'id' => '_pw_title',          'type' => 'text' ] );
	$cmb->add_field( [ 'name' => 'Content',       'id' => '_pw_content',        'type' => 'textarea' ] );
	$cmb->add_field( [ 'name' => 'Display order', 'id' => '_pw_display_order',  'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Highlighted',   'id' => '_pw_is_highlighted', 'type' => 'checkbox' ] );
	$cmb->add_field( [ 'name' => 'Active',        'id' => '_pw_active',         'type' => 'checkbox' ] );

	// --- pw_faq ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_faq_metabox',
		'title'        => 'FAQ Details',
		'object_types' => [ 'pw_faq' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [
		'name'    => 'Answer',
		'id'      => '_pw_answer',
		'type'    => 'wysiwyg',
		'options' => [ 'textarea_rows' => 5 ],
	] );

	$cmb->add_field( [
		'name'       => 'Connected to',
		'id'         => '_pw_connected_to',
		'type'       => 'group',
		'repeatable' => true,
		'options'    => [
			'group_title'   => 'Connection {#}',
			'add_button'    => 'Add Connection',
			'remove_button' => 'Remove Connection',
		],
		'fields' => [
			[
				'name'    => 'Type',
				'id'      => 'type',
				'type'    => 'select',
				'options' => [
					'pw_property'     => 'Property',
					'pw_restaurant'   => 'Restaurant',
					'pw_meeting_room' => 'Meeting Room',
					'pw_spa'          => 'Spa',
				],
			],
			[
				'name'    => 'Select',
				'id'      => 'id',
				'type'    => 'select',
				'options' => 'pw_faq_connection_options',
			],
		],
	] );

	// --- pw_offer ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_offer_metabox',
		'title'        => 'Offer Details',
		'object_types' => [ 'pw_offer' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [
		'name'    => 'Offer type',
		'id'      => '_pw_offer_type',
		'type'    => 'select',
		'options' => [
			'promotion'              => 'Promotion',
			'package'                => 'Package',
			'direct_booking_benefit' => 'Direct booking benefit',
		],
	] );

	$cmb->add_field( [
		'name'    => 'Attach to',
		'id'      => '_pw_parent_type',
		'type'    => 'select',
		'options' => [
			'pw_property'   => 'Property',
			'pw_restaurant' => 'Restaurant',
			'pw_spa'        => 'Spa',
		],
	] );

	$cmb->add_field( [
		'name'    => 'Select',
		'id'      => '_pw_parent_id',
		'type'    => 'select',
		'options' => 'pw_offer_parent_options',
	] );

	$cmb->add_field( [ 'name' => 'Description', 'id' => '_pw_description', 'type' => 'textarea' ] );
	$cmb->add_field( [ 'name' => 'Valid from',  'id' => '_pw_valid_from',  'type' => 'text_date', 'date_format' => 'Y-m-d' ] );
	$cmb->add_field( [ 'name' => 'Valid to',    'id' => '_pw_valid_to',    'type' => 'text_date', 'date_format' => 'Y-m-d' ] );
	$cmb->add_field( [ 'name' => 'Booking URL', 'id' => '_pw_booking_url', 'type' => 'text_url' ] );
	$cmb->add_field( [ 'name' => 'Terms',       'id' => '_pw_terms',       'type' => 'textarea_small' ] );
	$cmb->add_field( [ 'name' => 'Featured',    'id' => '_pw_is_featured', 'type' => 'checkbox' ] );

	// --- pw_nearby ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_nearby_metabox',
		'title'        => 'Nearby Location Details',
		'object_types' => [ 'pw_nearby' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property',          'id' => '_pw_property_id',     'type' => 'select',     'options'  => 'pw_property_options' ] );
	$cmb->add_field( [ 'name' => 'Distance (km)',      'id' => '_pw_distance_km',     'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Travel time (min)',  'id' => '_pw_travel_time_min', 'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Place URL',          'id' => '_pw_place_url',       'type' => 'text_url',   'desc' => 'Google Maps or website URL' ] );

	// --- pw_experience ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_experience_metabox',
		'title'        => 'Experience Details',
		'object_types' => [ 'pw_experience' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property',       'id' => '_pw_property_id',      'type' => 'select',     'options'  => 'pw_property_options' ] );
	$cmb->add_field( [ 'name' => 'Description',    'id' => '_pw_description',      'type' => 'textarea' ] );
	$cmb->add_field( [ 'name' => 'Duration (hrs)', 'id' => '_pw_duration_hours',   'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Price from',     'id' => '_pw_price_from',       'type' => 'text_money' ] );
	$cmb->add_field( [ 'name' => 'Booking URL',    'id' => '_pw_booking_url',      'type' => 'text_url' ] );
	$cmb->add_field( [ 'name' => 'Complimentary',  'id' => '_pw_is_complimentary', 'type' => 'checkbox' ] );
	$cmb->add_field( [ 'name' => 'Gallery',        'id' => '_pw_gallery',          'type' => 'file_list' ] );

	// --- pw_event ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_event_metabox',
		'title'        => 'Event Details',
		'object_types' => [ 'pw_event' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property',    'id' => '_pw_property_id',    'type' => 'select',                   'options' => 'pw_property_options' ] );
	$cmb->add_field( [ 'name' => 'Venue',       'id' => '_pw_venue_id',       'type' => 'select',                   'options' => 'pw_meeting_room_options' ] );
	$cmb->add_field( [ 'name' => 'Description', 'id' => '_pw_description',    'type' => 'textarea' ] );
	$cmb->add_field( [ 'name' => 'Start',       'id' => '_pw_start_datetime', 'type' => 'text_datetime_timestamp',  'desc' => 'Used for schema.org Event markup' ] );
	$cmb->add_field( [ 'name' => 'End',         'id' => '_pw_end_datetime',   'type' => 'text_datetime_timestamp' ] );
	$cmb->add_field( [ 'name' => 'Capacity',    'id' => '_pw_capacity',       'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Price from',  'id' => '_pw_price_from',     'type' => 'text_money' ] );
	$cmb->add_field( [ 'name' => 'Booking URL', 'id' => '_pw_booking_url',    'type' => 'text_url' ] );
	$cmb->add_field( [ 'name' => 'Recurring',   'id' => '_pw_is_recurring',   'type' => 'checkbox' ] );
	$cmb->add_field( [ 'name' => 'Gallery',     'id' => '_pw_gallery',        'type' => 'file_list' ] );
}

// ---------------------------------------------------------------------------
// pw_property: Sustainability meta box
// ---------------------------------------------------------------------------

add_action( 'cmb2_admin_init', 'pw_register_property_sustainability_metabox' );

function pw_register_property_sustainability_metabox() {
	$status_options = [
		'unknown'       => '— Not specified —',
		'available'     => 'Available',
		'not_available' => 'Not available',
	];

	$cmb = new_cmb2_box( [
		'id'           => 'pw_property_sustainability',
		'title'        => 'Sustainability practices',
		'object_types' => [ 'pw_property' ],
		'context'      => 'normal',
		'priority'     => 'default',
	] );

	$cmb->add_field( [ 'name' => 'Energy', 'type' => 'title', 'id' => '_pw_sus_title_energy' ] );
	$cmb->add_field( [ 'name' => 'Solar power',                'id' => '_pw_sus_solar_power',                'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Solar water heater',         'id' => '_pw_sus_solar_water_heater',         'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Energy-efficient lighting',  'id' => '_pw_sus_energy_efficient_lighting',  'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Energy-saving thermostats',  'id' => '_pw_sus_energy_saving_thermostats',  'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Green building design',      'id' => '_pw_sus_green_building_design',      'type' => 'select', 'options' => $status_options ] );

	$cmb->add_field( [ 'name' => 'Water', 'type' => 'title', 'id' => '_pw_sus_title_water' ] );
	$cmb->add_field( [ 'name' => 'Water-efficient fixtures',   'id' => '_pw_sus_water_efficient_fixtures',   'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Sewage treatment plant',     'id' => '_pw_sus_sewage_treatment_plant',     'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Water reuse program',        'id' => '_pw_sus_water_reuse_program',        'type' => 'select', 'options' => $status_options ] );

	$cmb->add_field( [ 'name' => 'Waste reduction', 'type' => 'title', 'id' => '_pw_sus_title_waste' ] );
	$cmb->add_field( [ 'name' => 'Waste segregation',          'id' => '_pw_sus_waste_segregation',          'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Recycling program',          'id' => '_pw_sus_recycling_program',          'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'No styrofoam',               'id' => '_pw_sus_no_styrofoam',               'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Electronics disposal',       'id' => '_pw_sus_electronics_disposal',       'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Reusable water bottles',     'id' => '_pw_sus_reusable_water_bottles',     'type' => 'select', 'options' => $status_options ] );

	$cmb->add_field( [ 'name' => 'Guest amenities', 'type' => 'title', 'id' => '_pw_sus_title_guest' ] );
	$cmb->add_field( [ 'name' => 'Wall-mounted dispensers',    'id' => '_pw_sus_wall_mounted_dispensers',    'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Eco-friendly toiletries',    'id' => '_pw_sus_eco_friendly_toiletries',    'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Towel reuse program',        'id' => '_pw_sus_towel_reuse_program',        'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Linen reuse program',        'id' => '_pw_sus_linen_reuse_program',        'type' => 'select', 'options' => $status_options ] );

	$cmb->add_field( [ 'name' => 'Sustainable sourcing', 'type' => 'title', 'id' => '_pw_sus_title_sourcing' ] );
	$cmb->add_field( [ 'name' => 'Local food sourcing',        'id' => '_pw_sus_local_food_sourcing',        'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Organic food options',       'id' => '_pw_sus_organic_food_options',       'type' => 'select', 'options' => $status_options ] );

	$cmb->add_field( [ 'name' => 'Certifications', 'type' => 'title', 'id' => '_pw_sus_title_cert' ] );
	$cmb->add_field( [ 'name' => 'Certification name', 'id' => '_pw_sus_certification_name', 'type' => 'text',     'desc' => 'e.g. LEED, Green Key, EarthCheck' ] );
	$cmb->add_field( [ 'name' => 'Certification URL',  'id' => '_pw_sus_certification_url',  'type' => 'text_url' ] );
}

// ---------------------------------------------------------------------------
// pw_property: Accessibility meta box
// ---------------------------------------------------------------------------

add_action( 'cmb2_admin_init', 'pw_register_property_accessibility_metabox' );

function pw_register_property_accessibility_metabox() {
	$status_options = [
		'unknown'       => '— Not specified —',
		'available'     => 'Available',
		'not_available' => 'Not available',
	];

	$cmb = new_cmb2_box( [
		'id'           => 'pw_property_accessibility',
		'title'        => 'Accessibility features',
		'object_types' => [ 'pw_property' ],
		'context'      => 'normal',
		'priority'     => 'default',
	] );

	$cmb->add_field( [ 'name' => 'Property access', 'type' => 'title', 'id' => '_pw_acc_title_access' ] );
	$cmb->add_field( [ 'name' => 'Wheelchair accessible',         'id' => '_pw_acc_wheelchair_accessible',         'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Step-free entrance',            'id' => '_pw_acc_step_free_entrance',            'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Automatic doors',               'id' => '_pw_acc_automatic_doors',               'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Accessible parking',            'id' => '_pw_acc_accessible_parking',            'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Accessible path to entrance',   'id' => '_pw_acc_accessible_path_to_entrance',   'type' => 'select', 'options' => $status_options ] );

	$cmb->add_field( [ 'name' => 'Guest rooms', 'type' => 'title', 'id' => '_pw_acc_title_rooms' ] );
	$cmb->add_field( [ 'name' => 'Accessible room available',  'id' => '_pw_acc_accessible_room_available',  'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Grab bars in bathroom',      'id' => '_pw_acc_grab_bars_bathroom',         'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Roll-in shower',             'id' => '_pw_acc_roll_in_shower',             'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Adjustable showerhead',      'id' => '_pw_acc_adjustable_showerhead',      'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Lowered closet',             'id' => '_pw_acc_lowered_closet',             'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Transfer-friendly bed',      'id' => '_pw_acc_transfer_friendly_bed',      'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Emergency pull cords',       'id' => '_pw_acc_emergency_pull_cords',       'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Reachable outlets',          'id' => '_pw_acc_reachable_outlets',          'type' => 'select', 'options' => $status_options ] );

	$cmb->add_field( [ 'name' => 'Facilities', 'type' => 'title', 'id' => '_pw_acc_title_facilities' ] );
	$cmb->add_field( [ 'name' => 'Elevator',              'id' => '_pw_acc_elevator',              'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Elevator audio cues',   'id' => '_pw_acc_elevator_audio_cues',   'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Pool lift',             'id' => '_pw_acc_pool_lift',             'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Accessible restaurant', 'id' => '_pw_acc_accessible_restaurant', 'type' => 'select', 'options' => $status_options ] );

	$cmb->add_field( [ 'name' => 'Communication', 'type' => 'title', 'id' => '_pw_acc_title_communication' ] );
	$cmb->add_field( [ 'name' => 'Visual fire alarm',     'id' => '_pw_acc_visual_fire_alarm',     'type' => 'select', 'options' => $status_options ] );
	$cmb->add_field( [ 'name' => 'Clear dietary labels',  'id' => '_pw_acc_clear_dietary_labels',  'type' => 'select', 'options' => $status_options ] );
}

// ---------------------------------------------------------------------------
// pw_property: Pools meta box
// ---------------------------------------------------------------------------

add_action( 'cmb2_admin_init', 'pw_register_property_pools_metabox' );

function pw_register_property_pools_metabox() {
	$cmb = new_cmb2_box( [
		'id'           => 'pw_property_pools',
		'title'        => 'Pools',
		'object_types' => [ 'pw_property' ],
		'context'      => 'normal',
		'priority'     => 'default',
	] );

	$cmb->add_field( [
		'name'       => 'Pools',
		'id'         => '_pw_pools',
		'type'       => 'group',
		'repeatable' => true,
		'options'    => [
			'group_title'   => 'Pool {#}',
			'add_button'    => 'Add Pool',
			'remove_button' => 'Remove Pool',
		],
		'fields' => [
			[ 'name' => 'Pool name',    'id' => 'name',        'type' => 'text_small', 'desc' => 'e.g. Main Pool, Kids Pool, Infinity Pool' ],
			[ 'name' => 'Length (m)',   'id' => 'length_m',    'type' => 'text_small' ],
			[ 'name' => 'Width (m)',    'id' => 'width_m',     'type' => 'text_small' ],
			[ 'name' => 'Depth (m)',    'id' => 'depth_m',     'type' => 'text_small' ],
			[ 'name' => 'Heated',       'id' => 'is_heated',   'type' => 'checkbox' ],
			[ 'name' => 'Kids pool',    'id' => 'is_kids',     'type' => 'checkbox' ],
			[ 'name' => 'Indoor',       'id' => 'is_indoor',   'type' => 'checkbox' ],
			[ 'name' => 'Infinity pool','id' => 'is_infinity', 'type' => 'checkbox' ],
		],
	] );
}

// ---------------------------------------------------------------------------
// pw_property: Direct booking benefits meta box
// ---------------------------------------------------------------------------

add_action( 'cmb2_admin_init', 'pw_register_property_direct_benefits_metabox' );

function pw_register_property_direct_benefits_metabox() {
	$cmb = new_cmb2_box( [
		'id'           => 'pw_property_direct_benefits',
		'title'        => 'Direct booking benefits',
		'object_types' => [ 'pw_property' ],
		'context'      => 'normal',
		'priority'     => 'default',
	] );

	$cmb->add_field( [
		'name'       => 'Direct booking benefits',
		'id'         => '_pw_direct_benefits',
		'type'       => 'group',
		'repeatable' => true,
		'options'    => [
			'group_title'   => 'Benefit {#}',
			'add_button'    => 'Add Benefit',
			'remove_button' => 'Remove Benefit',
		],
		'fields' => [
			[ 'name' => 'Title',       'id' => 'title',       'type' => 'text' ],
			[ 'name' => 'Description', 'id' => 'description', 'type' => 'textarea_small' ],
			[ 'name' => 'Icon',        'id' => 'icon',        'type' => 'text_small', 'desc' => 'Icon slug or SVG' ],
		],
	] );
}
