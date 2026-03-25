<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Sanitization helpers
// ---------------------------------------------------------------------------

function pw_sanitize_notice( $message ) {
	set_transient( 'pw_sanitize_notice', $message, 45 );
}

function pw_sanitize_occupancy( $value, $field_args, $field ) {
	$key = $field->id();
	$occ = isset( $_POST['_pw_max_occupancy'] ) ? (int) wp_unslash( $_POST['_pw_max_occupancy'] ) : 0;
	$adults = isset( $_POST['_pw_max_adults'] ) ? (int) wp_unslash( $_POST['_pw_max_adults'] ) : 0;
	$children = isset( $_POST['_pw_max_children'] ) ? (int) wp_unslash( $_POST['_pw_max_children'] ) : 0;

	if ( $key === '_pw_max_adults' ) {
		$adults = (int) $value;
	} elseif ( $key === '_pw_max_children' ) {
		$children = (int) $value;
	}

	if ( $occ > 0 && ( $adults + $children ) > $occ ) {
		if ( $key === '_pw_max_adults' ) {
			$value = max( 0, $occ - $children );
			pw_sanitize_notice( 'Max adults was clamped to satisfy max adults + max children ≤ max occupancy.' );
		} elseif ( $key === '_pw_max_children' ) {
			$value = max( 0, $occ - $adults );
			pw_sanitize_notice( 'Max children was clamped to satisfy max adults + max children ≤ max occupancy.' );
		}
	}
	return (string) ( (int) $value );
}

function pw_sanitize_url( $value, $field_args, $field ) {
	$raw = is_string( $value ) ? trim( $value ) : '';
	if ( $raw === '' ) {
		return '';
	}
	$sanitized = esc_url_raw( $raw );
	if ( $sanitized === '' && $raw !== '' ) {
		pw_sanitize_notice( 'Invalid URL was cleared: ' . $field->args( 'name' ) );
		return '';
	}
	return $sanitized;
}

function pw_sanitize_date_ymd( $value, $field_args, $field ) {
	$raw = is_string( $value ) ? trim( $value ) : '';
	if ( $raw === '' ) {
		return '';
	}
	$d = DateTime::createFromFormat( 'Y-m-d', $raw );
	if ( ! $d || $d->format( 'Y-m-d' ) !== $raw ) {
		pw_sanitize_notice( 'Invalid date format (expected Y-m-d) was cleared: ' . $field->args( 'name' ) );
		return '';
	}
	return $raw;
}

function pw_sanitize_datetime( $value, $field_args, $field ) {
	$raw = is_string( $value ) ? trim( $value ) : '';
	if ( $raw === '' ) {
		return '';
	}
	$d = DateTime::createFromFormat( 'Y-m-d H:i:s', $raw );
	if ( ! $d || $d->format( 'Y-m-d H:i:s' ) !== $raw ) {
		pw_sanitize_notice( 'Invalid datetime format (expected Y-m-d H:i:s) was cleared: ' . $field->args( 'name' ) );
		return '';
	}
	return $raw;
}

function pw_sanitize_event_datetime( $value, $field_args, $field ) {
	if ( empty( $value ) ) {
		return '';
	}
	$date_format = $field->args( 'date_format' ) ?: 'm/d/Y';
	$time_format = $field->args( 'time_format' ) ?: 'h:i A';
	$full_format = $date_format . ' ' . $time_format;

	if ( is_array( $value ) && isset( $value['date'], $value['time'] ) ) {
		$tzstring = $value['timezone'] ?? wp_timezone_string();
		$tz      = new DateTimeZone( $tzstring );
		$dt      = DateTime::createFromFormat( $full_format, $value['date'] . ' ' . $value['time'], $tz );
		if ( $dt ) {
			return $dt->format( 'Y-m-d H:i:s' );
		}
	}
	if ( is_string( $value ) ) {
		$d = DateTime::createFromFormat( 'Y-m-d H:i:s', trim( $value ) );
		if ( $d && $d->format( 'Y-m-d H:i:s' ) === trim( $value ) ) {
			return trim( $value );
		}
	}
	return '';
}

function pw_sanitize_int_nonneg( $value, $field_args, $field ) {
	$v = (int) $value;
	return (string) max( 0, $v );
}

function pw_sanitize_float_nonneg( $value, $field_args, $field ) {
	$v = (float) $value;
	return (string) max( 0.0, $v );
}

function pw_sanitize_geo_lat( $value, $field_args, $field ) {
	$raw = is_string( $value ) ? trim( $value ) : $value;
	if ( $raw === '' || $raw === null ) {
		return '0';
	}
	$f = max( -90.0, min( 90.0, (float) $raw ) );
	return (string) $f;
}

function pw_sanitize_geo_lng( $value, $field_args, $field ) {
	$raw = is_string( $value ) ? trim( $value ) : $value;
	if ( $raw === '' || $raw === null ) {
		return '0';
	}
	$f = max( -180.0, min( 180.0, (float) $raw ) );
	return (string) $f;
}

/**
 * Normalizes pw_room_type _pw_rates for storage and REST (schema.org Offer rows).
 */
function pw_sanitize_pw_rates_meta( $value ) {
	if ( ! is_array( $value ) ) {
		return [];
	}
	$allowed_types = [ 'rack', 'seasonal', 'advance', 'package' ];
	$out           = [];
	foreach ( $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$type = isset( $row['rate_type'] ) ? (string) $row['rate_type'] : 'rack';
		if ( ! in_array( $type, $allowed_types, true ) ) {
			$type = 'rack';
		}
		$vf = isset( $row['valid_from'] ) ? trim( (string) $row['valid_from'] ) : '';
		if ( $vf !== '' && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $vf ) ) {
			$vf = '';
		}
		$vt = isset( $row['valid_to'] ) ? trim( (string) $row['valid_to'] ) : '';
		if ( $vt !== '' && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $vt ) ) {
			$vt = '';
		}
		$bf_raw = $row['includes_breakfast'] ?? false;
		$bf     = ( $bf_raw === true || $bf_raw === 'on' || $bf_raw === '1' || $bf_raw === 1 );
		$out[]  = [
			'rate_label'         => isset( $row['rate_label'] ) ? sanitize_text_field( (string) $row['rate_label'] ) : '',
			'rate_type'          => $type,
			'price'              => isset( $row['price'] ) ? max( 0.0, (float) $row['price'] ) : 0.0,
			'valid_from'         => $vf,
			'valid_to'           => $vt,
			'advance_days'       => isset( $row['advance_days'] ) ? max( 0, (int) $row['advance_days'] ) : 0,
			'includes_breakfast' => $bf,
		];
	}
	return $out;
}

function pw_sanitize_select_whitelist( $allowed ) {
	return function ( $value, $field_args, $field ) use ( $allowed ) {
		$v = is_string( $value ) ? $value : '';
		return array_key_exists( $v, $allowed ) ? $v : ( array_key_exists( '', $allowed ) ? '' : array_key_first( $allowed ) );
	};
}

function pw_sanitize_status_enum( $value, $field_args, $field ) {
	$allowed = [ 'unknown' => '', 'available' => '', 'not_available' => '' ];
	$v = is_string( $value ) ? $value : '';
	return array_key_exists( $v, $allowed ) ? $v : 'unknown';
}

function pw_show_if_discount_value( $field ) {
	$post_id = ( is_object( $field ) && method_exists( $field, 'object_id' ) ) ? (int) $field->object_id() : 0;
	if ( ! $post_id ) {
		return true;
	}
	$discount_type = isset( $_POST['_pw_discount_type'] ) ? sanitize_text_field( wp_unslash( $_POST['_pw_discount_type'] ) ) : get_post_meta( $post_id, '_pw_discount_type', true );
	return ! empty( $discount_type ) && $discount_type !== 'value_add';
}

function pw_show_if_minimum_stay( $field ) {
	$post_id = ( is_object( $field ) && method_exists( $field, 'object_id' ) ) ? (int) $field->object_id() : 0;
	if ( ! $post_id ) {
		return true;
	}
	$offer_type = isset( $_POST['_pw_offer_type'] ) ? sanitize_text_field( wp_unslash( $_POST['_pw_offer_type'] ) ) : get_post_meta( $post_id, '_pw_offer_type', true );
	return in_array( $offer_type, [ 'promotion', 'package' ], true );
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

function pw_experience_connection_options() {
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
		'name' => 'Content',
		'id'   => '_pw_content',
		'type' => 'textarea',
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
		'desc'    => __( 'Changing the property updates this outlet\'s URL.', 'portico-webworks' ),
		'id'      => '_pw_property_id',
		'type'    => 'select',
		'options' => 'pw_property_options',
	] );

	$cmb->add_field( [ 'name' => 'Rate from',     'desc' => 'Starting rate (summary). Currency is set on the parent property.', 'id' => '_pw_rate_from',     'type' => 'text_money' ] );
	$cmb->add_field( [ 'name' => 'Rate to',       'desc' => 'Upper end of rate range (summary). Use Rates below for multiple schema.org Offers.', 'id' => '_pw_rate_to',       'type' => 'text_money' ] );
	$cmb->add_field( [
		'name'       => 'Rates',
		'id'         => '_pw_rates',
		'type'       => 'group',
		'repeatable' => true,
		'desc'       => 'Repeatable rate plans (rack, seasonal windows, advance purchase, packages). Maps to separate Offer entities in structured data.',
		'options'    => [
			'group_title'   => 'Rate {#}',
			'add_button'    => 'Add rate',
			'remove_button' => 'Remove',
		],
		'fields'     => [
			[ 'name' => 'Label', 'id' => 'rate_label', 'type' => 'text', 'desc' => 'e.g. Best Available, Peak Season, Advance 7-day' ],
			[
				'name'    => 'Type',
				'id'      => 'rate_type',
				'type'    => 'select',
				'options' => [
					'rack'     => 'Rack',
					'seasonal' => 'Seasonal',
					'advance'  => 'Advance purchase',
					'package'  => 'Package (e.g. B&B)',
				],
				'default' => 'rack',
			],
			[ 'name' => 'Price', 'id' => 'price', 'type' => 'text_money' ],
			[ 'name' => 'Valid from', 'id' => 'valid_from', 'type' => 'text_date', 'date_format' => 'Y-m-d', 'sanitization_cb' => 'pw_sanitize_date_ymd' ],
			[ 'name' => 'Valid to', 'id' => 'valid_to', 'type' => 'text_date', 'date_format' => 'Y-m-d', 'sanitization_cb' => 'pw_sanitize_date_ymd' ],
			[ 'name' => 'Advance days', 'id' => 'advance_days', 'type' => 'text_small', 'desc' => 'Min. days before arrival (advance purchase).', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ],
			[ 'name' => 'Includes breakfast', 'id' => 'includes_breakfast', 'type' => 'checkbox' ],
		],
	] );
	$cmb->add_field( [ 'name' => 'Max occupancy', 'id' => '_pw_max_occupancy', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Max adults',    'id' => '_pw_max_adults',    'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_occupancy' ] );
	$cmb->add_field( [ 'name' => 'Max children',  'id' => '_pw_max_children',  'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_occupancy' ] );
	$cmb->add_field( [ 'name' => 'Size (sqft)',   'id' => '_pw_size_sqft',     'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Size (sqm)',    'id' => '_pw_size_sqm',      'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Max extra beds', 'id' => '_pw_max_extra_beds', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );

	$cmb->add_field( [
		'name'    => 'Features',
		'desc'    => 'Select all applicable features',
		'id'      => '_pw_features',
		'type'    => 'multicheck',
		'options' => function ( $field ) {
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
	$cmb->add_field( [ 'name' => 'Display order', 'id' => '_pw_display_order', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [
		'name' => 'Booking URL',
		'id'   => '_pw_booking_url',
		'type' => 'text_url',
		'desc' => 'Deep-link booking engine URL for this room type. Leave blank to use the property default.',
	] );

	// --- pw_restaurant ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_restaurant_metabox',
		'title'        => 'Restaurant Details',
		'object_types' => [ 'pw_restaurant' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property',          'desc' => __( 'Changing the property updates this outlet\'s URL.', 'portico-webworks' ), 'id' => '_pw_property_id',      'type' => 'select',     'options' => 'pw_property_options' ] );
	$cmb->add_field( [ 'name' => 'Location',          'id' => '_pw_location',         'type' => 'text',       'desc' => 'e.g. Rooftop Level, Beach Side, Main Lobby' ] );
	$cmb->add_field( [ 'name' => 'Cuisine type',       'id' => '_pw_cuisine_type',     'type' => 'text' ] );
	$cmb->add_field( [ 'name' => 'Seating capacity',   'id' => '_pw_seating_capacity', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Reservation URL',    'id' => '_pw_reservation_url',  'type' => 'text_url', 'sanitization_cb' => 'pw_sanitize_url' ] );
	$cmb->add_field( [ 'name' => 'Menu URL',           'id' => '_pw_menu_url',         'type' => 'text_url', 'sanitization_cb' => 'pw_sanitize_url' ] );

	$cmb->add_field( [ 'name' => 'Gallery', 'id' => '_pw_gallery', 'type' => 'file_list' ] );

	// --- pw_spa ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_spa_metabox',
		'title'        => 'Spa Details',
		'object_types' => [ 'pw_spa' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property',    'desc' => __( 'Changing the property updates this outlet\'s URL.', 'portico-webworks' ), 'id' => '_pw_property_id', 'type' => 'select',     'options' => 'pw_property_options' ] );
	$cmb->add_field( [ 'name' => 'Booking URL', 'id' => '_pw_booking_url', 'type' => 'text_url', 'sanitization_cb' => 'pw_sanitize_url' ] );
	$cmb->add_field( [ 'name' => 'Menu URL',    'id' => '_pw_menu_url',    'type' => 'text_url', 'sanitization_cb' => 'pw_sanitize_url' ] );
	$cmb->add_field( [ 'name' => 'Minimum age', 'id' => '_pw_min_age',     'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Treatment rooms', 'id' => '_pw_number_of_treatment_rooms', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Gallery', 'id' => '_pw_gallery', 'type' => 'file_list' ] );

	// --- pw_meeting_room ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_meeting_room_metabox',
		'title'        => 'Meeting Room Details',
		'object_types' => [ 'pw_meeting_room' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property',             'desc' => __( 'Changing the property updates this outlet\'s URL.', 'portico-webworks' ), 'id' => '_pw_property_id',        'type' => 'select',     'options' => 'pw_property_options' ] );
	$cmb->add_field( [ 'name' => 'Capacity — Theatre',   'id' => '_pw_capacity_theatre',   'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Capacity — Classroom', 'id' => '_pw_capacity_classroom', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Capacity — Boardroom', 'id' => '_pw_capacity_boardroom', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Capacity — U-Shape',   'id' => '_pw_capacity_ushape',    'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Area (sqft)',           'id' => '_pw_area_sqft',          'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Area (sqm)',            'id' => '_pw_area_sqm',           'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Pre-function area (sqft)', 'id' => '_pw_prefunction_area_sqft', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Pre-function area (sqm)',  'id' => '_pw_prefunction_area_sqm',  'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );

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

	$cmb = new_cmb2_box( [
		'id'           => 'pw_restaurant_operating_hours',
		'title'        => 'Operating hours',
		'object_types' => [ 'pw_restaurant' ],
		'context'      => 'normal',
		'priority'     => 'default',
	] );

	$group_field_restaurant = $cmb->add_field( [
		'id'          => '_pw_operating_hours',
		'type'        => 'group',
		'description' => 'Add one row per meal period or session.',
		'options'     => [
			'group_title'   => 'Session {#}',
			'add_button'    => 'Add session',
			'remove_button' => 'Remove session',
			'sortable'      => true,
		],
	] );

	$cmb->add_group_field( $group_field_restaurant, [
		'name' => 'Label',
		'id'   => 'label',
		'type' => 'text',
		'desc' => 'e.g. Breakfast, Lunch, Dinner',
	] );

	$cmb->add_group_field( $group_field_restaurant, [
		'name' => 'Open time',
		'id'   => 'open_time',
		'type' => 'text_time',
	] );

	$cmb->add_group_field( $group_field_restaurant, [
		'name' => 'Close time',
		'id'   => 'close_time',
		'type' => 'text_time',
	] );

	$cmb = new_cmb2_box( [
		'id'           => 'pw_spa_operating_hours',
		'title'        => 'Operating hours',
		'object_types' => [ 'pw_spa' ],
		'context'      => 'normal',
		'priority'     => 'default',
	] );

	$group_field_spa = $cmb->add_field( [
		'id'          => '_pw_operating_hours',
		'type'        => 'group',
		'description' => 'Add one row per meal period or session.',
		'options'     => [
			'group_title'   => 'Session {#}',
			'add_button'    => 'Add session',
			'remove_button' => 'Remove session',
			'sortable'      => true,
		],
	] );

	$cmb->add_group_field( $group_field_spa, [
		'name' => 'Label',
		'id'   => 'label',
		'type' => 'text',
		'desc' => 'e.g. Breakfast, Lunch, Dinner',
	] );

	$cmb->add_group_field( $group_field_spa, [
		'name' => 'Open time',
		'id'   => 'open_time',
		'type' => 'text_time',
	] );

	$cmb->add_group_field( $group_field_spa, [
		'name' => 'Close time',
		'id'   => 'close_time',
		'type' => 'text_time',
	] );

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
		'name'            => 'Type',
		'id'              => '_pw_type',
		'type'            => 'select',
		'options'         => [
			'amenity'  => 'Amenity',
			'service'  => 'Service',
			'facility' => 'Facility',
		],
		'sanitization_cb' => pw_sanitize_select_whitelist( [ 'amenity' => '', 'service' => '', 'facility' => '' ] ),
	] );

	$cmb->add_field( [ 'name' => 'Category',      'id' => '_pw_category',      'type' => 'text' ] );
	$cmb->add_field( [ 'name' => 'Icon',          'id' => '_pw_icon',          'type' => 'textarea_small' ] );
	$cmb->add_field( [ 'name' => 'Content',       'id' => '_pw_content',       'type' => 'textarea_small' ] );
	$cmb->add_field( [ 'name' => 'Display order', 'id' => '_pw_display_order', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );

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
	$cmb->add_field( [ 'name' => 'Content',       'id' => '_pw_content',        'type' => 'textarea' ] );
	$cmb->add_field( [ 'name' => 'Display order', 'id' => '_pw_display_order',  'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
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
		'name' => 'Property',
		'desc' => 'Scopes this FAQ to a property (e.g. FAQPage per hotel). Use Connected to below for a specific restaurant, spa, or meeting room on that property — both can be set.',
		'id'   => '_pw_property_id',
		'type' => 'select',
		'options' => 'pw_property_options',
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
				'name'            => 'Type',
				'id'              => 'type',
				'type'            => 'select',
				'options'         => [
					'pw_property'     => 'Property',
					'pw_restaurant'   => 'Restaurant',
					'pw_meeting_room' => 'Meeting Room',
					'pw_spa'          => 'Spa',
				],
				'sanitization_cb' => pw_sanitize_select_whitelist( [ 'pw_property' => '', 'pw_restaurant' => '', 'pw_meeting_room' => '', 'pw_spa' => '' ] ),
			],
			[
				'name'    => 'Select',
				'id'      => 'id',
				'type'    => 'select',
				'options' => 'pw_faq_connection_options',
			],
		],
	] );
	$cmb->add_field( [ 'name' => 'Display order', 'id' => '_pw_display_order', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );

	// --- pw_offer ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_offer_metabox',
		'title'        => 'Offer Details',
		'object_types' => [ 'pw_offer' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$offer_type_opts = [ 'promotion' => 'Promotion', 'package' => 'Package', 'direct_booking_benefit' => 'Direct booking benefit' ];
	$cmb->add_field( [
		'name'            => 'Offer type',
		'id'              => '_pw_offer_type',
		'type'            => 'select',
		'options'         => $offer_type_opts,
		'sanitization_cb' => pw_sanitize_select_whitelist( $offer_type_opts ),
	] );

	$cmb->add_field( [
		'name' => 'Property',
		'desc' => __( 'Scopes this offer for queries (multi-property / REST). Use Attach to below for property, restaurant, or spa links — both can be set. Changing the property updates this outlet\'s URL.', 'portico-webworks' ),
		'id'   => '_pw_property_id',
		'type' => 'select',
		'options' => 'pw_property_options',
	] );

	$cmb->add_field( [
		'name'       => 'Attach to',
		'id'         => '_pw_parents',
		'type'       => 'group',
		'repeatable' => true,
		'options'    => [
			'group_title'   => 'Connection {#}',
			'add_button'    => 'Add Connection',
			'remove_button' => 'Remove',
		],
		'fields' => [
			[
				'name'    => 'Type',
				'id'      => 'type',
				'type'    => 'select',
				'options' => [
					'pw_property'   => 'Property',
					'pw_restaurant' => 'Restaurant',
					'pw_spa'        => 'Spa',
				],
			],
			[
				'name'    => 'Select',
				'id'      => 'id',
				'type'    => 'select',
				'options' => 'pw_offer_parent_options',
			],
		],
	] );

	$cmb->add_field( [ 'name' => 'Valid from',  'id' => '_pw_valid_from',  'type' => 'text_date', 'date_format' => 'Y-m-d', 'sanitization_cb' => 'pw_sanitize_date_ymd' ] );
	$cmb->add_field( [ 'name' => 'Valid to',    'id' => '_pw_valid_to',    'type' => 'text_date', 'date_format' => 'Y-m-d', 'sanitization_cb' => 'pw_sanitize_date_ymd' ] );
	$cmb->add_field( [ 'name' => 'Booking URL', 'id' => '_pw_booking_url', 'type' => 'text_url', 'sanitization_cb' => 'pw_sanitize_url' ] );
	$cmb->add_field( [ 'name' => 'Featured',    'id' => '_pw_is_featured', 'type' => 'checkbox' ] );
	$discount_type_opts = [ '' => '— None —', 'percentage' => 'Percentage', 'flat' => 'Flat amount', 'value_add' => 'Value add' ];
	$cmb->add_field( [
		'name'            => 'Discount type',
		'id'              => '_pw_discount_type',
		'type'            => 'select',
		'options'         => $discount_type_opts,
		'sanitization_cb' => pw_sanitize_select_whitelist( $discount_type_opts ),
	] );
	$cmb->add_field( [
		'name'         => 'Discount value',
		'id'           => '_pw_discount_value',
		'type'         => 'text_money',
		'desc'         => 'e.g. 20 for 20% or 500 for ₹500',
		'sanitization_cb' => 'pw_sanitize_float_nonneg',
		'show_on_cb'   => 'pw_show_if_discount_value',
	] );
	$cmb->add_field( [
		'name'         => 'Minimum stay (nights)',
		'id'           => '_pw_minimum_stay_nights',
		'type'         => 'text_small',
		'sanitization_cb' => 'pw_sanitize_int_nonneg',
		'show_on_cb'   => 'pw_show_if_minimum_stay',
	] );
	$cmb->add_field( [
		'name'    => 'Applicable room types',
		'desc'    => 'Leave blank to apply to all room types',
		'id'      => '_pw_room_types',
		'type'    => 'multicheck',
		'options' => function ( $field ) {
			$rooms = get_posts( [
				'post_type'      => 'pw_room_type',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			] );
			$options = [];
			foreach ( $rooms as $r ) {
				$options[ $r->ID ] = $r->post_title;
			}
			return $options;
		},
	] );
	$cmb->add_field( [ 'name' => 'Display order', 'id' => '_pw_display_order', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );

	// --- pw_nearby ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_nearby_metabox',
		'title'        => 'Nearby Location Details',
		'object_types' => [ 'pw_nearby' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property',          'desc' => __( 'Changing the property updates this outlet\'s URL.', 'portico-webworks' ), 'id' => '_pw_property_id',     'type' => 'select',     'options'  => 'pw_property_options' ] );
	$cmb->add_field( [ 'name' => 'Distance (km)',      'id' => '_pw_distance_km',     'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_float_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Travel time (min)',  'id' => '_pw_travel_time_min', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Latitude',            'id' => '_pw_lat',             'type' => 'text_small', 'attributes' => [ 'placeholder' => 'e.g. 25.7907' ], 'sanitization_cb' => 'pw_sanitize_geo_lat' ] );
	$cmb->add_field( [ 'name' => 'Longitude',           'id' => '_pw_lng',             'type' => 'text_small', 'attributes' => [ 'placeholder' => 'e.g. -80.1300' ], 'sanitization_cb' => 'pw_sanitize_geo_lng' ] );
	$cmb->add_field( [ 'name' => 'Place URL',          'id' => '_pw_place_url',       'type' => 'text_url',   'desc' => 'Google Maps or website URL', 'sanitization_cb' => 'pw_sanitize_url' ] );
	$cmb->add_field( [ 'name' => 'Display order', 'id' => '_pw_display_order', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );

	// --- pw_experience ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_experience_metabox',
		'title'        => 'Experience Details',
		'object_types' => [ 'pw_experience' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [
		'name' => 'Property',
		'desc' => __( 'Scopes this experience for property-level archives and `meta_query`. Use Connected to below for specific restaurant/spa links — both can be set. Changing the property updates this outlet\'s URL.', 'portico-webworks' ),
		'id'   => '_pw_property_id',
		'type' => 'select',
		'options' => 'pw_property_options',
	] );

	$cmb->add_field( [
		'name'       => 'Connected to',
		'id'         => '_pw_connected_to',
		'type'       => 'group',
		'repeatable' => true,
		'options'    => [
			'group_title'   => 'Connection {#}',
			'add_button'    => 'Add Connection',
			'remove_button' => 'Remove',
		],
		'fields' => [
			[
				'name'    => 'Type',
				'id'      => 'type',
				'type'    => 'select',
				'options' => [
					'pw_property'   => 'Property',
					'pw_restaurant' => 'Restaurant',
					'pw_spa'        => 'Spa',
				],
			],
			[
				'name'    => 'Select',
				'id'      => 'id',
				'type'    => 'select',
				'options' => 'pw_experience_connection_options',
			],
		],
	] );
	$cmb->add_field( [ 'name' => 'Description',    'id' => '_pw_description',      'type' => 'textarea' ] );
	$cmb->add_field( [ 'name' => 'Duration (hrs)', 'id' => '_pw_duration_hours',   'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_float_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Price from',     'id' => '_pw_price_from',       'type' => 'text_money' ] );
	$cmb->add_field( [ 'name' => 'Booking URL',    'id' => '_pw_booking_url',      'type' => 'text_url', 'sanitization_cb' => 'pw_sanitize_url' ] );
	$cmb->add_field( [ 'name' => 'Complimentary',  'id' => '_pw_is_complimentary', 'type' => 'checkbox' ] );
	$cmb->add_field( [ 'name' => 'Gallery',        'id' => '_pw_gallery',          'type' => 'file_list' ] );
	$cmb->add_field( [ 'name' => 'Display order', 'id' => '_pw_display_order', 'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );

	// --- pw_event ---

	$cmb = new_cmb2_box( [
		'id'           => 'pw_event_metabox',
		'title'        => 'Event Details',
		'object_types' => [ 'pw_event' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cmb->add_field( [ 'name' => 'Property',    'desc' => __( 'Changing the property updates this outlet\'s URL.', 'portico-webworks' ), 'id' => '_pw_property_id',    'type' => 'select',                   'options' => 'pw_property_options' ] );
	$cmb->add_field( [ 'name' => 'Venue',       'id' => '_pw_venue_id',       'type' => 'select',                   'options' => 'pw_meeting_room_options' ] );
	$cmb->add_field( [ 'name' => 'Description', 'id' => '_pw_description',    'type' => 'textarea' ] );
	$cmb->add_field( [
		'name'            => 'Start',
		'id'              => '_pw_start_datetime',
		'type'            => 'text_datetime_timestamp_timezone',
		'desc'            => 'Wall time stored as Y-m-d H:i:s (no offset). For schema.org / ISO 8601, use the linked property’s Timezone (`_pw_timezone`) — `pw_event_local_datetime_to_iso8601()` or REST `pw_start_datetime_iso8601`.',
		'date_format'     => 'Y-m-d',
		'time_format'     => 'H:i:s',
		'sanitization_cb' => 'pw_sanitize_event_datetime',
	] );
	$cmb->add_field( [
		'name'            => 'End',
		'id'              => '_pw_end_datetime',
		'type'            => 'text_datetime_timestamp_timezone',
		'desc'            => 'Same as Start: local wall time; ISO 8601 offset from linked property `_pw_timezone` at render time (`pw_end_datetime_iso8601` in REST).',
		'date_format'     => 'Y-m-d',
		'time_format'     => 'H:i:s',
		'sanitization_cb' => 'pw_sanitize_event_datetime',
	] );
	$cmb->add_field( [ 'name' => 'Capacity',    'id' => '_pw_capacity',       'type' => 'text_small', 'sanitization_cb' => 'pw_sanitize_int_nonneg' ] );
	$cmb->add_field( [ 'name' => 'Price from',  'id' => '_pw_price_from',     'type' => 'text_money' ] );
	$cmb->add_field( [ 'name' => 'Booking URL', 'id' => '_pw_booking_url',    'type' => 'text_url', 'sanitization_cb' => 'pw_sanitize_url' ] );
	$cmb->add_field( [ 'name' => 'Gallery',     'id' => '_pw_gallery',        'type' => 'file_list' ] );
	$cmb->add_field( [
		'name' => 'Recurrence',
		'id'   => '_pw_recurrence_rule',
		'type' => 'pw_rrule',
		'desc' => 'Leave empty for non-recurring events.',
	] );
	$event_status_opts = [ 'EventScheduled' => 'Scheduled', 'EventCancelled' => 'Cancelled', 'EventPostponed' => 'Postponed', 'EventRescheduled' => 'Rescheduled' ];
	$cmb->add_field( [
		'name'            => 'Event status',
		'id'              => '_pw_event_status',
		'type'            => 'select',
		'options'         => $event_status_opts,
		'sanitization_cb' => pw_sanitize_select_whitelist( $event_status_opts ),
	] );
	$attendance_opts = [ 'OfflineEventAttendanceMode' => 'In-person', 'OnlineEventAttendanceMode' => 'Online', 'MixedEventAttendanceMode' => 'Mixed' ];
	$cmb->add_field( [
		'name'            => 'Attendance mode',
		'id'              => '_pw_event_attendance_mode',
		'type'            => 'select',
		'options'         => $attendance_opts,
		'sanitization_cb' => pw_sanitize_select_whitelist( $attendance_opts ),
	] );
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

	$defs         = pw_get_sustainability_facet_definitions();
	$key_options  = [];
	$default_rows = [];
	foreach ( $defs as $d ) {
		$key_options[ $d['key'] ] = $d['label'];
		$default_rows[]           = [
			'key'    => $d['key'],
			'status' => 'unknown',
			'note'   => '',
		];
	}
	$key_sanitize = pw_sanitize_select_whitelist( $key_options );

	$cmb = new_cmb2_box( [
		'id'                 => 'pw_property_sustainability',
		'title'              => 'Sustainability practices',
		'object_types'       => [ 'pw_property' ],
		'context'            => 'normal',
		'priority'           => 'default',
		'mb_callback_args'   => [ '__block_editor_compatible_meta_box' => true ],
	] );

	$cmb->add_field( [
		'name'        => 'Sustainability practices',
		'id'          => PW_SUSTAINABILITY_ITEMS_META_KEY,
		'type'        => 'group',
		'description' => 'One row per practice. Set status and optional content; saving normalizes rows to the canonical list.',
		'repeatable'  => true,
		'default'     => $default_rows,
		'options'     => [
			'group_title'   => 'Practice {#}',
			'add_button'    => 'Add practice',
			'remove_button' => 'Remove',
		],
		'fields'      => [
			[
				'name'            => 'Practice',
				'id'              => 'key',
				'type'            => 'select',
				'options'         => $key_options,
				'sanitization_cb' => $key_sanitize,
			],
			[
				'name'            => 'Status',
				'id'              => 'status',
				'type'            => 'select',
				'options'         => $status_options,
				'sanitization_cb' => 'pw_sanitize_status_enum',
			],
			[
				'name'       => 'Content',
				'id'         => 'note',
				'type'       => 'textarea',
				'attributes' => [
					'rows'  => 5,
					'class' => 'large-text',
				],
			],
		],
	] );
}

// ---------------------------------------------------------------------------
// pw_property: Certifications & Awards meta box
// ---------------------------------------------------------------------------

add_action( 'cmb2_admin_init', 'pw_register_property_certifications_metabox' );

function pw_register_property_certifications_metabox() {
	$cmb = new_cmb2_box( [
		'id'                 => 'pw_property_certifications',
		'title'              => 'Certifications & Awards',
		'object_types'       => [ 'pw_property' ],
		'context'            => 'normal',
		'priority'           => 'default',
		'mb_callback_args'   => [ '__block_editor_compatible_meta_box' => true ],
	] );

	$cmb->add_field( [
		'name'       => 'Certifications & Awards',
		'id'         => '_pw_certifications',
		'type'       => 'group',
		'repeatable' => true,
		'options'    => [
			'group_title'   => 'Certification {#}',
			'add_button'    => 'Add Certification',
			'remove_button' => 'Remove',
		],
		'fields' => [
			[ 'name' => 'Name',   'id' => 'name',   'type' => 'text',       'desc' => 'e.g. Green Key, TripAdvisor CoE, Forbes Travel Guide' ],
			[ 'name' => 'Issuer', 'id' => 'issuer', 'type' => 'text_small', 'desc' => 'Organisation that issued the certification' ],
			[ 'name' => 'Year',   'id' => 'year',   'type' => 'text_small', 'desc' => 'Year awarded or last renewed' ],
			[ 'name' => 'URL',    'id' => 'url',    'type' => 'text_url',   'desc' => 'Link to certificate or listing' ],
		],
	] );
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

	$defs         = pw_get_accessibility_facet_definitions();
	$key_options  = [];
	$default_rows = [];
	foreach ( $defs as $d ) {
		$key_options[ $d['key'] ] = $d['label'];
		$default_rows[]           = [
			'key'    => $d['key'],
			'status' => 'unknown',
			'note'   => '',
		];
	}
	$key_sanitize = pw_sanitize_select_whitelist( $key_options );

	$cmb = new_cmb2_box( [
		'id'                 => 'pw_property_accessibility',
		'title'              => 'Accessibility features',
		'object_types'       => [ 'pw_property' ],
		'context'            => 'normal',
		'priority'           => 'default',
		'mb_callback_args'   => [ '__block_editor_compatible_meta_box' => true ],
	] );

	$cmb->add_field( [
		'name'        => 'Accessibility features',
		'id'          => PW_ACCESSIBILITY_ITEMS_META_KEY,
		'type'        => 'group',
		'description' => 'One row per feature. Set status and optional content; saving normalizes rows to the canonical list.',
		'repeatable'  => true,
		'default'     => $default_rows,
		'options'     => [
			'group_title'   => 'Feature {#}',
			'add_button'    => 'Add feature',
			'remove_button' => 'Remove',
		],
		'fields'      => [
			[
				'name'            => 'Feature',
				'id'              => 'key',
				'type'            => 'select',
				'options'         => $key_options,
				'sanitization_cb' => $key_sanitize,
			],
			[
				'name'            => 'Status',
				'id'              => 'status',
				'type'            => 'select',
				'options'         => $status_options,
				'sanitization_cb' => 'pw_sanitize_status_enum',
			],
			[
				'name'       => 'Content',
				'id'         => 'note',
				'type'       => 'textarea',
				'attributes' => [
					'rows'  => 5,
					'class' => 'large-text',
				],
			],
		],
	] );
}

// ---------------------------------------------------------------------------
// pw_property: Gallery (file list)
// ---------------------------------------------------------------------------

add_action( 'cmb2_admin_init', 'pw_register_property_gallery_metabox' );

function pw_register_property_gallery_metabox() {
	$cmb = new_cmb2_box( [
		'id'                 => 'pw_property_gallery',
		'title'              => 'Property gallery',
		'object_types'       => [ 'pw_property' ],
		'context'            => 'normal',
		'priority'           => 'default',
		'mb_callback_args'   => [ '__block_editor_compatible_meta_box' => true ],
	] );

	$cmb->add_field( [
		'name' => 'Gallery',
		'id'   => '_pw_gallery',
		'type' => 'file_list',
	] );
}

// ---------------------------------------------------------------------------
// pw_property: Pools meta box
// ---------------------------------------------------------------------------

add_action( 'cmb2_admin_init', 'pw_register_property_pools_metabox' );

function pw_register_property_pools_metabox() {
	$cmb = new_cmb2_box( [
		'id'                 => 'pw_property_pools',
		'title'              => 'Pools',
		'object_types'       => [ 'pw_property' ],
		'context'            => 'normal',
		'priority'           => 'default',
		'mb_callback_args'   => [ '__block_editor_compatible_meta_box' => true ],
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
			[
				'name' => 'Photo',
				'id'   => 'attachment_id',
				'type' => 'file',
				'desc' => 'Optional image for this pool.',
			],
			[ 'name' => 'Length (m)',   'id' => 'length_m',    'type' => 'text_small' ],
			[ 'name' => 'Width (m)',    'id' => 'width_m',     'type' => 'text_small' ],
			[ 'name' => 'Depth (m)',    'id' => 'depth_m',     'type' => 'text_small' ],
			[ 'name' => 'Opens at',     'id' => 'open_time',   'type' => 'text_time' ],
			[ 'name' => 'Closes at',    'id' => 'close_time',  'type' => 'text_time' ],
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
		'id'                 => 'pw_property_direct_benefits',
		'title'              => 'Direct booking benefits',
		'object_types'       => [ 'pw_property' ],
		'context'            => 'normal',
		'priority'           => 'default',
		'mb_callback_args'   => [ '__block_editor_compatible_meta_box' => true ],
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

// ---------------------------------------------------------------------------
// pw_property: Announcement bar meta box
// ---------------------------------------------------------------------------

add_action( 'cmb2_admin_init', 'pw_register_property_announcement_metabox' );

function pw_register_property_announcement_metabox() {
	$cmb = new_cmb2_box( [
		'id'               => 'pw_property_announcement_bar',
		'title'            => 'Announcement bar',
		'object_types'     => [ 'pw_property' ],
		'context'          => 'normal',
		'priority'         => 'default',
		'mb_callback_args' => [ '__block_editor_compatible_meta_box' => true ],
	] );

	$cmb->add_field( [
		'name'       => 'Active',
		'id'         => '_pw_announcement_active',
		'type'       => 'checkbox',
		'default'    => false,
	] );

	$cmb->add_field( [
		'name'            => 'Text',
		'id'              => '_pw_announcement_text',
		'type'            => 'textarea',
		'sanitization_cb' => 'wp_kses_post',
	] );

	$cmb->add_field( [
		'name'            => 'Start',
		'id'              => '_pw_announcement_start',
		'type'            => 'text_datetime_timestamp_timezone',
		'desc'            => 'Optional: when set, the bar shows starting at this date/time.',
	] );

	$cmb->add_field( [
		'name'            => 'End',
		'id'              => '_pw_announcement_end',
		'type'            => 'text_datetime_timestamp_timezone',
		'desc'            => 'Optional: when set, the bar hides after this date/time.',
	] );
}

add_action( 'admin_notices', function () {
	$msg = get_transient( 'pw_sanitize_notice' );
	if ( $msg ) {
		delete_transient( 'pw_sanitize_notice' );
		echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
	}
} );

add_action( 'admin_notices', 'pw_outlet_missing_property_notice' );

/**
 * Warn when a section outlet post has no property (multi-property URLs need it).
 */
function pw_outlet_missing_property_notice(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->base !== 'post' || ! in_array( $screen->post_type, pw_url_section_cpts(), true ) ) {
		return;
	}
	global $post;
	if ( ! $post instanceof WP_Post || $post->post_status === 'auto-draft' ) {
		return;
	}
	if ( (int) get_post_meta( $post->ID, '_pw_property_id', true ) > 0 ) {
		return;
	}
	echo '<div class="notice notice-warning"><p>';
	echo esc_html__( 'This post has no property assigned. Its URL will be incorrect until a property is selected in the metabox below.', 'portico-webworks' );
	echo '</p></div>';
}
