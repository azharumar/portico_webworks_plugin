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
		'name' => 'Category',
		'desc' => 'e.g. in-room, bathroom, connectivity',
		'id'   => '_pw_feature_category',
		'type' => 'text',
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

	$cmb->add_field( [ 'name' => 'Rate from',     'id' => '_pw_rate_from',     'type' => 'text_money' ] );
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
			[ 'name' => 'Day',       'id' => 'day',        'type' => 'text_small' ],
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
			[ 'name' => 'Day',       'id' => 'day',        'type' => 'text_small' ],
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
}
