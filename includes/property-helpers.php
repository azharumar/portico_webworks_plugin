<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_get_property_profile( $property_id = null ) {
	$id = $property_id ?? pw_get_current_property_id();
	if ( ! $id ) return [];

	$keys = [
		'legal_name'         => '_pw_legal_name',
		'star_rating'        => '_pw_star_rating',
		'currency'           => '_pw_currency',
		'check_in_time'      => '_pw_check_in_time',
		'check_out_time'     => '_pw_check_out_time',
		'year_established'   => '_pw_year_established',
		'total_rooms'        => '_pw_total_rooms',
		'address_line_1'     => '_pw_address_line_1',
		'address_line_2'     => '_pw_address_line_2',
		'city'               => '_pw_city',
		'state'              => '_pw_state',
		'postal_code'        => '_pw_postal_code',
		'country'            => '_pw_country',
		'country_code'       => '_pw_country_code',
		'contacts'           => '_pw_contacts',
		'lat'                => '_pw_lat',
		'lng'                => '_pw_lng',
		'google_place_id'    => '_pw_google_place_id',
		'timezone'           => '_pw_timezone',
		'social_facebook'    => '_pw_social_facebook',
		'social_instagram'   => '_pw_social_instagram',
		'social_twitter'     => '_pw_social_twitter',
		'social_tripadvisor' => '_pw_social_tripadvisor',
		'social_linkedin'    => '_pw_social_linkedin',
		'social_youtube'     => '_pw_social_youtube',
	];

	$profile = [];
	foreach ( $keys as $label => $meta_key ) {
		$profile[ $label ] = get_post_meta( (int) $id, $meta_key, true );
	}

	return $profile;
}

function pw_get_all_properties() {
	$ids = get_posts(array(
		'post_type' => 'pw_property',
		'post_status' => 'any',
		'numberposts' => -1,
		'fields' => 'ids',
		'orderby' => 'title',
		'order' => 'ASC',
	));

	if (!is_array($ids)) {
		return array();
	}

	$out = array();
	foreach ($ids as $id) {
		$out[] = array(
			'id' => (int) $id,
			'name' => get_the_title($id),
			'slug' => get_post_field('post_name', $id),
		);
	}

	return $out;
}

function pw_property_base() {
	$base = pw_get_setting('pw_property_base', 'properties');
	$base = is_string($base) ? trim($base) : 'properties';
	$base = trim($base, '/');
	if ($base === '') {
		$base = 'properties';
	}

	return sanitize_title($base);
}

function pw_resolve_property_id_from_url() {
	global $wp;

	$request = '';
	if (isset($wp) && isset($wp->request) && is_string($wp->request)) {
		$request = $wp->request;
	} else {
		$uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
		$path = $uri ? parse_url($uri, PHP_URL_PATH) : '';
		$request = $path ? trim($path, '/') : '';
	}

	$base = pw_property_base();
	if ($base === '') {
		return null;
	}

	$segments = $request !== '' ? explode('/', trim($request, '/')) : array();
	if (count($segments) < 2) {
		return null;
	}

	if ($segments[0] !== $base) {
		return null;
	}

	$slug = sanitize_title($segments[1]);
	if ($slug === '') {
		return null;
	}

	$props = get_posts(array(
		'post_type' => 'pw_property',
		'post_status' => 'publish',
		'name' => $slug,
		'numberposts' => 1,
		'fields' => 'ids',
	));

	if (!empty($props) && is_array($props)) {
		return (int) $props[0];
	}

	return null;
}

function pw_get_current_property_id() {
	static $resolved = null;

	if ($resolved !== null) {
		return $resolved;
	}

	$mode = pw_get_setting('pw_property_mode', 'single');

	if ($mode === 'multi') {
		$from_url = pw_resolve_property_id_from_url();
		if (!empty($from_url)) {
			$resolved = (int) $from_url;
			return $resolved;
		}
	}

	$all = get_posts(array(
		'post_type' => 'pw_property',
		'post_status' => 'publish',
		'numberposts' => 2,
		'fields' => 'ids',
	));

	$cnt = is_array($all) ? count($all) : 0;
	if ($cnt >= 1 && !empty($all[0])) {
		if ($mode === 'single' || $cnt === 1) {
			$resolved = (int) $all[0];
			return $resolved;
		}
	}

	if ($cnt === 0) {
		$resolved = new WP_Error('pw_property_missing', 'No properties found.');
		return $resolved;
	}

	$resolved = new WP_Error('pw_property_not_found', 'Property not found.');
	return $resolved;
}

function pw_get_current_property_profile() {
	return pw_get_property_profile(null);
}

function pw_get_child_posts( $cpt, $property_id ) {
	return get_posts( [
		'post_type'      => $cpt,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => [
			[
				'key'   => '_pw_property_id',
				'value' => (int) $property_id,
			],
		],
	] );
}

function pw_get_room_features( $room_type_id ) {
	$feature_ids = get_post_meta( (int) $room_type_id, '_pw_features', true );
	if ( empty( $feature_ids ) || ! is_array( $feature_ids ) ) return [];

	return get_posts( [
		'post_type'      => 'pw_feature',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'post__in'       => $feature_ids,
		'orderby'        => 'post__in',
	] );
}

function pw_get_experiences_for( $post_type, $post_id ) {
	$experiences = get_posts( [
		'post_type'      => 'pw_experience',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	] );

	return array_filter( $experiences, function( $exp ) use ( $post_type, $post_id ) {
		$connections = get_post_meta( $exp->ID, '_pw_connected_to', true );
		if ( empty( $connections ) || ! is_array( $connections ) ) return false;
		foreach ( $connections as $c ) {
			if ( isset( $c['type'], $c['id'] ) &&
				$c['type'] === $post_type &&
				(int) $c['id'] === (int) $post_id ) {
				return true;
			}
		}
		return false;
	} );
}

function pw_get_faqs_for( $post_type, $post_id ) {
	$faqs = get_posts( [
		'post_type'      => 'pw_faq',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	] );

	return array_filter( $faqs, function( $faq ) use ( $post_type, $post_id ) {
		$connections = get_post_meta( $faq->ID, '_pw_connected_to', true );
		if ( empty( $connections ) || ! is_array( $connections ) ) return false;
		foreach ( $connections as $connection ) {
			if (
				isset( $connection['type'], $connection['id'] ) &&
				$connection['type'] === $post_type &&
				(int) $connection['id'] === (int) $post_id
			) {
				return true;
			}
		}
		return false;
	} );
}

function pw_get_operating_hours( $post_id ) {
	$days  = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];
	$hours = [];
	foreach ( $days as $day ) {
		$hours[ $day ] = get_post_meta( (int) $post_id, '_pw_hours_' . $day, true ) ?: [
			'is_closed' => false,
			'sessions'  => [],
		];
	}
	return $hours;
}

function pw_get_property_currency( $property_id = null ) {
	$id = $property_id ?? pw_get_current_property_id();
	return get_post_meta( (int) $id, '_pw_currency', true ) ?: 'USD';
}

add_action('template_redirect', function () {
	if (is_admin() || wp_doing_ajax()) {
		return;
	}

	$mode = pw_get_setting('pw_property_mode', 'single');
	if ($mode === 'single') {
		return;
	}

	$property_id = pw_get_current_property_id();
	if (!is_wp_error($property_id)) {
		return;
	}

	global $wp_query;
	if (isset($wp_query) && is_object($wp_query)) {
		$wp_query->set_404();
	}

	wp_die('', '', array(
		'response' => 404,
	));
});

