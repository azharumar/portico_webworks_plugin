<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PW_SUSTAINABILITY_ITEMS_META_KEY', '_pw_sustainability_items' );
define( 'PW_ACCESSIBILITY_ITEMS_META_KEY', '_pw_accessibility_items' );

function pw_sanitize_facet_status( $value ) {
	$allowed = [ 'unknown' => true, 'available' => true, 'not_available' => true ];
	$v       = is_string( $value ) ? $value : 'unknown';
	return isset( $allowed[ $v ] ) ? $v : 'unknown';
}

function pw_get_sustainability_facet_definitions() {
	return [
		[ 'key' => 'solar_power', 'label' => 'Solar power', 'section' => 'Energy' ],
		[ 'key' => 'solar_water_heater', 'label' => 'Solar water heater', 'section' => 'Energy' ],
		[ 'key' => 'energy_efficient_lighting', 'label' => 'Energy-efficient lighting', 'section' => 'Energy' ],
		[ 'key' => 'energy_saving_thermostats', 'label' => 'Energy-saving thermostats', 'section' => 'Energy' ],
		[ 'key' => 'green_building_design', 'label' => 'Green building design', 'section' => 'Energy' ],
		[ 'key' => 'water_efficient_fixtures', 'label' => 'Water-efficient fixtures', 'section' => 'Water' ],
		[ 'key' => 'sewage_treatment_plant', 'label' => 'Sewage treatment plant', 'section' => 'Water' ],
		[ 'key' => 'water_reuse_program', 'label' => 'Water reuse program', 'section' => 'Water' ],
		[ 'key' => 'waste_segregation', 'label' => 'Waste segregation', 'section' => 'Waste reduction' ],
		[ 'key' => 'recycling_program', 'label' => 'Recycling program', 'section' => 'Waste reduction' ],
		[ 'key' => 'no_styrofoam', 'label' => 'No styrofoam', 'section' => 'Waste reduction' ],
		[ 'key' => 'electronics_disposal', 'label' => 'Electronics disposal', 'section' => 'Waste reduction' ],
		[ 'key' => 'reusable_water_bottles', 'label' => 'Reusable water bottles', 'section' => 'Waste reduction' ],
		[ 'key' => 'wall_mounted_dispensers', 'label' => 'Wall-mounted dispensers', 'section' => 'Guest amenities' ],
		[ 'key' => 'eco_friendly_toiletries', 'label' => 'Eco-friendly toiletries', 'section' => 'Guest amenities' ],
		[ 'key' => 'towel_reuse_program', 'label' => 'Towel reuse program', 'section' => 'Guest amenities' ],
		[ 'key' => 'linen_reuse_program', 'label' => 'Linen reuse program', 'section' => 'Guest amenities' ],
		[ 'key' => 'local_food_sourcing', 'label' => 'Local food sourcing', 'section' => 'Sustainable sourcing' ],
		[ 'key' => 'organic_food_options', 'label' => 'Organic food options', 'section' => 'Sustainable sourcing' ],
	];
}

function pw_get_accessibility_facet_definitions() {
	return [
		[ 'key' => 'wheelchair_accessible', 'label' => 'Wheelchair accessible', 'section' => 'Property access' ],
		[ 'key' => 'step_free_entrance', 'label' => 'Step-free entrance', 'section' => 'Property access' ],
		[ 'key' => 'automatic_doors', 'label' => 'Automatic doors', 'section' => 'Property access' ],
		[ 'key' => 'accessible_parking', 'label' => 'Accessible parking', 'section' => 'Property access' ],
		[ 'key' => 'accessible_path_to_entrance', 'label' => 'Accessible path to entrance', 'section' => 'Property access' ],
		[ 'key' => 'accessible_room_available', 'label' => 'Accessible room available', 'section' => 'Guest rooms' ],
		[ 'key' => 'grab_bars_bathroom', 'label' => 'Grab bars in bathroom', 'section' => 'Guest rooms' ],
		[ 'key' => 'roll_in_shower', 'label' => 'Roll-in shower', 'section' => 'Guest rooms' ],
		[ 'key' => 'adjustable_showerhead', 'label' => 'Adjustable showerhead', 'section' => 'Guest rooms' ],
		[ 'key' => 'lowered_closet', 'label' => 'Lowered closet', 'section' => 'Guest rooms' ],
		[ 'key' => 'transfer_friendly_bed', 'label' => 'Transfer-friendly bed', 'section' => 'Guest rooms' ],
		[ 'key' => 'emergency_pull_cords', 'label' => 'Emergency pull cords', 'section' => 'Guest rooms' ],
		[ 'key' => 'reachable_outlets', 'label' => 'Reachable outlets', 'section' => 'Guest rooms' ],
		[ 'key' => 'elevator', 'label' => 'Elevator', 'section' => 'Facilities' ],
		[ 'key' => 'elevator_audio_cues', 'label' => 'Elevator audio cues', 'section' => 'Facilities' ],
		[ 'key' => 'pool_lift', 'label' => 'Pool lift', 'section' => 'Facilities' ],
		[ 'key' => 'accessible_restaurant', 'label' => 'Accessible restaurant', 'section' => 'Facilities' ],
		[ 'key' => 'visual_fire_alarm', 'label' => 'Visual fire alarm', 'section' => 'Communication' ],
		[ 'key' => 'clear_dietary_labels', 'label' => 'Clear dietary labels', 'section' => 'Communication' ],
	];
}

function pw_normalize_facet_items( $raw, array $definitions ) {
	$by_key = [];
	if ( is_array( $raw ) ) {
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$key = isset( $row['key'] ) ? sanitize_key( (string) $row['key'] ) : '';
			if ( $key === '' ) {
				continue;
			}
			$by_key[ $key ] = [
				'key'    => $key,
				'status' => pw_sanitize_facet_status( $row['status'] ?? 'unknown' ),
				'note'   => isset( $row['note'] ) ? sanitize_text_field( (string) $row['note'] ) : '',
			];
		}
	}

	$out = [];
	foreach ( $definitions as $def ) {
		$k = $def['key'];
		if ( isset( $by_key[ $k ] ) ) {
			$out[] = $by_key[ $k ];
		} else {
			$out[] = [
				'key'    => $k,
				'status' => 'unknown',
				'note'   => '',
			];
		}
	}

	return $out;
}

function pw_normalize_property_facet_meta( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$pairs = [
		PW_SUSTAINABILITY_ITEMS_META_KEY => 'pw_get_sustainability_facet_definitions',
		PW_ACCESSIBILITY_ITEMS_META_KEY  => 'pw_get_accessibility_facet_definitions',
	];

	foreach ( $pairs as $meta_key => $defs_fn ) {
		$raw  = get_post_meta( $post_id, $meta_key, true );
		$norm = pw_normalize_facet_items( $raw, call_user_func( $defs_fn ) );
		update_post_meta( $post_id, $meta_key, $norm );
	}
}

function pw_normalize_property_facet_meta_if_dirty( $post_id, $meta_key ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 || get_post_type( $post_id ) !== 'pw_property' ) {
		return;
	}
	if ( ! in_array( $meta_key, [ PW_SUSTAINABILITY_ITEMS_META_KEY, PW_ACCESSIBILITY_ITEMS_META_KEY ], true ) ) {
		return;
	}

	static $busy = [];

	if ( ! empty( $busy[ $post_id ] ) ) {
		return;
	}
	$busy[ $post_id ] = true;
	pw_normalize_property_facet_meta( $post_id );
	unset( $busy[ $post_id ] );
}

add_action(
	'updated_post_meta',
	static function ( $meta_id, $post_id, $meta_key ) {
		unset( $meta_id );
		pw_normalize_property_facet_meta_if_dirty( $post_id, $meta_key );
	},
	10,
	4
);

add_action(
	'added_post_meta',
	static function ( $meta_id, $post_id, $meta_key ) {
		unset( $meta_id );
		pw_normalize_property_facet_meta_if_dirty( $post_id, $meta_key );
	},
	10,
	4
);
