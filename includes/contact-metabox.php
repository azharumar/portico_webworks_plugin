<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string, string>
 */
function pw_contact_scope_cpt_options() {
	return [
		'property'     => 'Property',
		'restaurant'   => 'Restaurant',
		'spa'          => 'Spa',
		'meeting_room' => 'Meeting room',
		'experience'   => 'Experience',
		'all'          => 'All',
	];
}

/**
 * Maps `_pw_scope_cpt` option slug to WP post type for outlet picker (empty if N/A).
 */
function pw_contact_scope_cpt_to_post_type( $scope_cpt ) {
	$map = [
		'restaurant'   => 'pw_restaurant',
		'spa'          => 'pw_spa',
		'meeting_room' => 'pw_meeting_room',
		'experience'   => 'pw_experience',
	];
	return $map[ $scope_cpt ] ?? '';
}

function pw_register_pw_contact_post_meta() {
	$string_meta = [
		'_pw_label'     => '',
		'_pw_phone'     => '',
		'_pw_mobile'    => '',
		'_pw_whatsapp'  => '',
		'_pw_email'     => '',
		'_pw_scope_cpt' => 'property',
	];
	foreach ( $string_meta as $key => $default ) {
		register_post_meta(
			'pw_contact',
			$key,
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => $default,
			]
		);
	}

	register_post_meta(
		'pw_contact',
		'_pw_property_id',
		[
			'type'         => 'integer',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => 0,
		]
	);

	register_post_meta(
		'pw_contact',
		'_pw_scope_id',
		[
			'type'         => 'integer',
			'single'       => true,
			'show_in_rest' => true,
			'default'      => 0,
		]
	);
}

add_action( 'init', 'pw_register_pw_contact_post_meta', 11 );

add_action( 'cmb2_admin_init', 'pw_register_contact_cmb2_metabox' );

function pw_register_contact_cmb2_metabox() {
	$scope_opts    = pw_contact_scope_cpt_options();
	$scope_whitelist = pw_sanitize_select_whitelist( $scope_opts );

	$cmb = new_cmb2_box(
		[
			'id'           => 'pw_contact_metabox',
			'title'        => 'Contact details',
			'object_types' => [ 'pw_contact' ],
			'context'      => 'normal',
			'priority'     => 'high',
		]
	);

	$cmb->add_field(
		[
			'name'             => 'Property',
			'id'               => '_pw_property_id',
			'type'             => 'select',
			'options'          => 'pw_property_options',
			'sanitization_cb'  => 'absint',
			'attributes'       => [
				'required' => 'required',
			],
		]
	);

	$cmb->add_field(
		[
			'name' => 'Label',
			'id'   => '_pw_label',
			'type' => 'text',
			'desc' => 'e.g. Reservations, Spa booking desk',
		]
	);

	$cmb->add_field( [ 'name' => 'Phone', 'id' => '_pw_phone', 'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Mobile', 'id' => '_pw_mobile', 'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'WhatsApp', 'id' => '_pw_whatsapp', 'type' => 'text_small' ] );
	$cmb->add_field( [ 'name' => 'Email', 'id' => '_pw_email', 'type' => 'text_email' ] );

	$cmb->add_field(
		[
			'name'             => 'Scope (CPT group)',
			'id'               => '_pw_scope_cpt',
			'type'             => 'select',
			'options'          => $scope_opts,
			'default'          => 'property',
			'sanitization_cb'  => $scope_whitelist,
			'attributes'       => [
				'data-pw-contact-scope-cpt' => '1',
			],
		]
	);

	$cmb->add_field(
		[
			'name'             => 'Outlet (optional)',
			'desc'             => 'Leave as group-level (none) to apply to all outlets of this type under the property. Pick one for an outlet-specific override.',
			'id'               => '_pw_scope_id',
			'type'             => 'select',
			'options'          => [ '' => '— None (group level) —' ],
			'sanitization_cb'  => 'absint',
			'attributes'       => [
				'data-pw-contact-scope-id' => '1',
			],
		]
	);
}

/**
 * Clear outlet when scope is property or all; validate property required on save.
 *
 * @param int $object_id Contact post ID.
 */
function pw_contact_normalize_meta_on_save( $object_id ) {
	if ( get_post_type( $object_id ) !== 'pw_contact' ) {
		return;
	}
	$scope = get_post_meta( $object_id, '_pw_scope_cpt', true );
	$scope = is_string( $scope ) ? sanitize_key( $scope ) : 'property';
	if ( ! pw_contact_is_valid_scope_cpt( $scope ) ) {
		$scope = 'property';
		update_post_meta( $object_id, '_pw_scope_cpt', $scope );
	}
	if ( $scope === 'property' || $scope === 'all' ) {
		update_post_meta( $object_id, '_pw_scope_id', 0 );
	}
	$pid = (int) get_post_meta( $object_id, '_pw_property_id', true );
	if ( $pid <= 0 ) {
		return;
	}
	$outlet_id = (int) get_post_meta( $object_id, '_pw_scope_id', true );
	if ( $outlet_id <= 0 ) {
		return;
	}
	$pt = pw_contact_scope_cpt_to_post_type( $scope );
	if ( $pt === '' ) {
		update_post_meta( $object_id, '_pw_scope_id', 0 );
		return;
	}
	$opid = (int) get_post_meta( $outlet_id, '_pw_property_id', true );
	if ( $opid !== $pid || get_post_type( $outlet_id ) !== $pt || get_post_status( $outlet_id ) !== 'publish' ) {
		update_post_meta( $object_id, '_pw_scope_id', 0 );
	}
}

add_action( 'save_post_pw_contact', 'pw_contact_normalize_meta_on_save', 30 );

add_action(
	'admin_enqueue_scripts',
	static function ( $hook_suffix ) {
		if ( ! defined( 'PW_PLUGIN_FILE' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'pw_contact' ) {
			return;
		}
		wp_enqueue_script(
			'pw-admin-contact-scope',
			plugins_url( 'assets/admin-contact-scope.js', PW_PLUGIN_FILE ),
			[ 'jquery', 'wp-api-fetch' ],
			defined( 'PW_VERSION' ) ? PW_VERSION : '1',
			true
		);
		wp_localize_script(
			'pw-admin-contact-scope',
			'pwContactScope',
			[
				'outletMap' => [
					'restaurant'   => 'pw_restaurant',
					'spa'          => 'pw_spa',
					'meeting_room' => 'pw_meeting_room',
					'experience'   => 'pw_experience',
				],
			]
		);
	},
	25
);
