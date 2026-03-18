<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_property_meta_key() {
	return '_pw_property_profile';
}

function pw_property_profile_defaults() {
	return array(
		'property_name' => '',
		'property_short_name' => '',
		'abbreviation' => '',
		'legal_name' => '',
		'tax_id' => '',
		'address_line_1' => '',
		'address_line_2' => '',
		'city' => '',
		'state' => '',
		'postal_code' => '',
		'phone' => '',
		'mobile' => '',
		'whatsapp' => '',
		'email' => '',
		'latitude' => '',
		'longitude' => '',
		'instagram' => '',
		'facebook' => '',
		'youtube' => '',
		'linkedin' => '',
		'tripadvisor' => '',
		'twitter' => '',
		'google_business' => '',
	);
}

function pw_get_property_profile($property_id = null) {
	$defaults = pw_property_profile_defaults();

	if (empty($property_id) || !is_numeric($property_id)) {
		$property_id = pw_get_current_property_id();
	}

	if (is_wp_error($property_id) || empty($property_id)) {
		return $defaults;
	}

	$val = get_post_meta((int) $property_id, pw_property_meta_key(), true);
	if (!is_array($val)) {
		$val = array();
	}

	return array_merge($defaults, $val);
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
	$base = get_option('pw_property_base', 'properties');
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

	$mode = get_option('pw_property_mode', 'single');

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

add_action('template_redirect', function () {
	if (is_admin() || wp_doing_ajax()) {
		return;
	}

	$mode = get_option('pw_property_mode', 'single');
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

