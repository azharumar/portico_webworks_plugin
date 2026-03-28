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
