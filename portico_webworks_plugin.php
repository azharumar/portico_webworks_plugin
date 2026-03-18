<?php
/**
 * Plugin Name: Portico Webworks
 * Description: Portico Webworks plugin.
 * Version: 0.1.3
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 */

if (!defined('ABSPATH')) {
	exit;
}

function portico_webworks_logo_url() {
	return plugins_url('logo.svg', __FILE__);
}

function portico_webworks_option_key() {
	return 'portico_webworks_property_profile';
}

function portico_webworks_get_property_profile() {
	$defaults = array(
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

	$val = get_option(portico_webworks_option_key(), array());
	if (!is_array($val)) {
		$val = array();
	}

	return array_merge($defaults, $val);
}

function portico_webworks_sanitize_property_profile($input) {
	if (!is_array($input)) {
		return array();
	}

	$out = array();
	$text_fields = array(
		'property_name',
		'property_short_name',
		'abbreviation',
		'legal_name',
		'tax_id',
		'address_line_1',
		'address_line_2',
		'city',
		'state',
		'postal_code',
		'phone',
		'mobile',
		'whatsapp',
	);

	foreach ($text_fields as $k) {
		$out[$k] = isset($input[$k]) ? sanitize_text_field($input[$k]) : '';
	}

	$out['email'] = isset($input['email']) ? sanitize_email($input['email']) : '';

	$out['latitude'] = isset($input['latitude']) ? sanitize_text_field($input['latitude']) : '';
	$out['longitude'] = isset($input['longitude']) ? sanitize_text_field($input['longitude']) : '';

	$url_fields = array(
		'instagram',
		'facebook',
		'youtube',
		'linkedin',
		'tripadvisor',
		'twitter',
		'google_business',
	);
	foreach ($url_fields as $k) {
		$out[$k] = isset($input[$k]) ? esc_url_raw($input[$k]) : '';
	}

	return $out;
}

function portico_webworks_field_text($args) {
	$profile = portico_webworks_get_property_profile();
	$key = $args['key'];
	$label = $args['label'];
	$type = isset($args['type']) ? $args['type'] : 'text';
	$placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';

	$name = portico_webworks_option_key() . '[' . $key . ']';
	$val = isset($profile[$key]) ? $profile[$key] : '';

	echo '<label for="portico-webworks-' . esc_attr($key) . '" class="screen-reader-text">' . esc_html($label) . '</label>';
	echo '<input class="regular-text" id="portico-webworks-' . esc_attr($key) . '" name="' . esc_attr($name) . '" type="' . esc_attr($type) . '" value="' . esc_attr($val) . '" placeholder="' . esc_attr($placeholder) . '" />';
}

function portico_webworks_field_url($args) {
	portico_webworks_field_text(array_merge($args, array('type' => 'url')));
}

add_action('admin_menu', function () {
	add_menu_page(
		'Portico Webworks',
		'Portico Webworks',
		'manage_options',
		'portico-webworks',
		'portico_webworks_render_root_page',
		'dashicons-building',
		58
	);

	add_submenu_page(
		'portico-webworks',
		'Overview',
		'Overview',
		'manage_options',
		'portico-webworks',
		'portico_webworks_render_root_page'
	);

	add_submenu_page(
		'portico-webworks',
		'Property Profile',
		'Property Profile',
		'manage_options',
		'portico-webworks-property-profile',
		'portico_webworks_render_property_profile_page'
	);

	add_submenu_page(
		'portico-webworks',
		'Settings',
		'Settings',
		'manage_options',
		'portico-webworks-settings',
		'portico_webworks_render_settings_page'
	);
});

add_action('admin_init', function () {
	register_setting(
		'portico_webworks_property_profile',
		portico_webworks_option_key(),
		array('sanitize_callback' => 'portico_webworks_sanitize_property_profile')
	);

	add_settings_section(
		'portico_webworks_property_identity',
		'Identity',
		'__return_null',
		'portico-webworks-property-profile'
	);
	add_settings_field(
		'property_name',
		'Property Name',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_identity',
		array('key' => 'property_name', 'label' => 'Property Name')
	);
	add_settings_field(
		'property_short_name',
		'Property Short Name',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_identity',
		array('key' => 'property_short_name', 'label' => 'Property Short Name')
	);
	add_settings_field(
		'abbreviation',
		'Abbreviation',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_identity',
		array('key' => 'abbreviation', 'label' => 'Abbreviation')
	);
	add_settings_field(
		'legal_name',
		'Legal Name',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_identity',
		array('key' => 'legal_name', 'label' => 'Legal Name')
	);
	add_settings_field(
		'tax_id',
		'Tax ID',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_identity',
		array('key' => 'tax_id', 'label' => 'Tax ID')
	);

	add_settings_section(
		'portico_webworks_property_address',
		'Address',
		'__return_null',
		'portico-webworks-property-profile'
	);
	add_settings_field(
		'address_line_1',
		'Address Line 1',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_address',
		array('key' => 'address_line_1', 'label' => 'Address Line 1')
	);
	add_settings_field(
		'address_line_2',
		'Address Line 2',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_address',
		array('key' => 'address_line_2', 'label' => 'Address Line 2')
	);
	add_settings_field(
		'city',
		'City',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_address',
		array('key' => 'city', 'label' => 'City')
	);
	add_settings_field(
		'state',
		'State',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_address',
		array('key' => 'state', 'label' => 'State')
	);
	add_settings_field(
		'postal_code',
		'Postal Code',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_address',
		array('key' => 'postal_code', 'label' => 'Postal Code')
	);

	add_settings_section(
		'portico_webworks_property_contact',
		'Contact',
		'__return_null',
		'portico-webworks-property-profile'
	);
	add_settings_field(
		'phone',
		'Phone No.',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_contact',
		array('key' => 'phone', 'label' => 'Phone No.')
	);
	add_settings_field(
		'mobile',
		'Mobile No.',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_contact',
		array('key' => 'mobile', 'label' => 'Mobile No.')
	);
	add_settings_field(
		'whatsapp',
		'WhatsApp No.',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_contact',
		array('key' => 'whatsapp', 'label' => 'WhatsApp No.')
	);
	add_settings_field(
		'email',
		'Email ID',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_contact',
		array('key' => 'email', 'label' => 'Email ID', 'type' => 'email')
	);

	add_settings_section(
		'portico_webworks_property_geo',
		'Geo',
		'__return_null',
		'portico-webworks-property-profile'
	);
	add_settings_field(
		'latitude',
		'Latitude',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_geo',
		array('key' => 'latitude', 'label' => 'Latitude', 'placeholder' => 'e.g. 25.2048')
	);
	add_settings_field(
		'longitude',
		'Longitude',
		'portico_webworks_field_text',
		'portico-webworks-property-profile',
		'portico_webworks_property_geo',
		array('key' => 'longitude', 'label' => 'Longitude', 'placeholder' => 'e.g. 55.2708')
	);

	add_settings_section(
		'portico_webworks_property_social',
		'Social',
		'__return_null',
		'portico-webworks-property-profile'
	);
	add_settings_field(
		'instagram',
		'Instagram',
		'portico_webworks_field_url',
		'portico-webworks-property-profile',
		'portico_webworks_property_social',
		array('key' => 'instagram', 'label' => 'Instagram')
	);
	add_settings_field(
		'facebook',
		'Facebook',
		'portico_webworks_field_url',
		'portico-webworks-property-profile',
		'portico_webworks_property_social',
		array('key' => 'facebook', 'label' => 'Facebook')
	);
	add_settings_field(
		'youtube',
		'YouTube',
		'portico_webworks_field_url',
		'portico-webworks-property-profile',
		'portico_webworks_property_social',
		array('key' => 'youtube', 'label' => 'YouTube')
	);
	add_settings_field(
		'linkedin',
		'LinkedIn',
		'portico_webworks_field_url',
		'portico-webworks-property-profile',
		'portico_webworks_property_social',
		array('key' => 'linkedin', 'label' => 'LinkedIn')
	);
	add_settings_field(
		'tripadvisor',
		'Tripadvisor',
		'portico_webworks_field_url',
		'portico-webworks-property-profile',
		'portico_webworks_property_social',
		array('key' => 'tripadvisor', 'label' => 'Tripadvisor')
	);
	add_settings_field(
		'twitter',
		'Twitter (X)',
		'portico_webworks_field_url',
		'portico-webworks-property-profile',
		'portico_webworks_property_social',
		array('key' => 'twitter', 'label' => 'Twitter (X)')
	);
	add_settings_field(
		'google_business',
		'Google My Business',
		'portico_webworks_field_url',
		'portico-webworks-property-profile',
		'portico_webworks_property_social',
		array('key' => 'google_business', 'label' => 'Google My Business')
	);
});

function portico_webworks_render_root_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	echo '<div class="wrap">';
	echo '<h1><img alt="" src="' . esc_url(portico_webworks_logo_url()) . '" style="height: 28px; width: 28px; vertical-align: middle; margin-right: 8px;" />Portico Webworks</h1>';
	echo '<p>Select an option from the submenu.</p>';
	echo '</div>';
}

function portico_webworks_render_property_profile_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	echo '<div class="wrap">';
	echo '<h1><img alt="" src="' . esc_url(portico_webworks_logo_url()) . '" style="height: 28px; width: 28px; vertical-align: middle; margin-right: 8px;" />Property Profile</h1>';
	echo '<form method="post" action="options.php">';
	settings_fields('portico_webworks_property_profile');
	do_settings_sections('portico-webworks-property-profile');
	submit_button('Save');
	echo '</form>';
	echo '</div>';
}

function portico_webworks_render_settings_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	echo '<div class="wrap">';
	echo '<h1><img alt="" src="' . esc_url(portico_webworks_logo_url()) . '" style="height: 28px; width: 28px; vertical-align: middle; margin-right: 8px;" />Settings</h1>';
	echo '<p>Portico Webworks settings will go here.</p>';
	echo '</div>';
}

