<?php

if (!defined('ABSPATH')) {
	exit;
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

	$out = portico_webworks_get_property_profile();
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
		if (isset($input[$k])) {
			$out[$k] = sanitize_text_field($input[$k]);
		}
	}

	if (isset($input['email'])) {
		$out['email'] = sanitize_email($input['email']);
	}

	if (isset($input['latitude'])) {
		$out['latitude'] = sanitize_text_field($input['latitude']);
	}
	if (isset($input['longitude'])) {
		$out['longitude'] = sanitize_text_field($input['longitude']);
	}

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
		if (isset($input[$k])) {
			$out[$k] = esc_url_raw($input[$k]);
		}
	}

	return $out;
}

function portico_webworks_property_sections() {
	return array(
		'identity' => array('label' => 'Identity', 'section_id' => 'portico_webworks_property_identity'),
		'address' => array('label' => 'Address', 'section_id' => 'portico_webworks_property_address'),
		'contact' => array('label' => 'Contact', 'section_id' => 'portico_webworks_property_contact'),
		'geo' => array('label' => 'Geo', 'section_id' => 'portico_webworks_property_geo'),
		'social' => array('label' => 'Social', 'section_id' => 'portico_webworks_property_social'),
	);
}

function portico_webworks_field_text($args) {
	$profile = portico_webworks_get_property_profile();
	$key = $args['key'];
	$label = $args['label'];
	$type = isset($args['type']) ? $args['type'] : 'text';
	$placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
	$help = isset($args['help']) ? $args['help'] : '';
	$validate = isset($args['validate']) ? $args['validate'] : '';

	$name = portico_webworks_option_key() . '[' . $key . ']';
	$val = isset($profile[$key]) ? $profile[$key] : '';

	echo '<label for="portico-webworks-' . esc_attr($key) . '" class="screen-reader-text">' . esc_html($label) . '</label>';
	echo '<span class="pw-field">';
	echo '<input class="regular-text" data-pw-label="' . esc_attr($label) . '" id="portico-webworks-' . esc_attr($key) . '" name="' . esc_attr($name) . '" type="' . esc_attr($type) . '" value="' . esc_attr($val) . '" placeholder="' . esc_attr($placeholder) . '" ' . ($validate !== '' ? 'data-pw-validate="' . esc_attr($validate) . '"' : '') . ' />';
	if ($validate === 'url') {
		echo '<span class="pw-valid" aria-hidden="true"></span>';
	}
	echo '</span>';
	if ($help !== '') {
		echo '<p class="description">' . esc_html($help) . '</p>';
	}
}

function portico_webworks_field_url($args) {
	portico_webworks_field_text(array_merge($args, array('type' => 'url')));
}

add_action('admin_init', function () {
	register_setting(
		'portico_webworks_property_profile',
		portico_webworks_option_key(),
		array('sanitize_callback' => 'portico_webworks_sanitize_property_profile')
	);

	$page = portico_webworks_admin_page_slug();

	add_settings_section('portico_webworks_property_identity', 'Identity', '__return_null', $page);
	add_settings_field('property_name', 'Property Name', 'portico_webworks_field_text', $page, 'portico_webworks_property_identity', array('key' => 'property_name', 'label' => 'Property Name', 'placeholder' => 'e.g. The Grand Pavilion', 'help' => 'Public-facing name shown to guests.'));
	add_settings_field('property_short_name', 'Property Short Name', 'portico_webworks_field_text', $page, 'portico_webworks_property_identity', array('key' => 'property_short_name', 'label' => 'Property Short Name', 'placeholder' => 'e.g. Grand Pavilion', 'help' => 'Shortened name for tight layouts (headers, nav, etc.).'));
	add_settings_field('abbreviation', 'Abbreviation', 'portico_webworks_field_text', $page, 'portico_webworks_property_identity', array('key' => 'abbreviation', 'label' => 'Abbreviation', 'placeholder' => 'e.g. TGP', 'help' => 'Internal shorthand (optional).'));
	add_settings_field('legal_name', 'Legal Name (Company)', 'portico_webworks_field_text', $page, 'portico_webworks_property_identity', array('key' => 'legal_name', 'label' => 'Legal Name (Company)', 'placeholder' => 'e.g. Grand Pavilion Hospitality Pvt Ltd', 'help' => 'For invoices, contracts, and compliance.'));
	add_settings_field('tax_id', 'Tax ID', 'portico_webworks_field_text', $page, 'portico_webworks_property_identity', array('key' => 'tax_id', 'label' => 'Tax ID', 'placeholder' => 'e.g. GSTIN / VAT / EIN', 'help' => 'Use the format required in your jurisdiction.'));

	add_settings_section('portico_webworks_property_address', 'Address', '__return_null', $page);
	add_settings_field('address_line_1', 'Address Line 1', 'portico_webworks_field_text', $page, 'portico_webworks_property_address', array('key' => 'address_line_1', 'label' => 'Address Line 1', 'placeholder' => 'Street address, building, etc.'));
	add_settings_field('address_line_2', 'Address Line 2', 'portico_webworks_field_text', $page, 'portico_webworks_property_address', array('key' => 'address_line_2', 'label' => 'Address Line 2', 'placeholder' => 'Area, landmark (optional)'));
	add_settings_field('city', 'City', 'portico_webworks_field_text', $page, 'portico_webworks_property_address', array('key' => 'city', 'label' => 'City', 'placeholder' => 'e.g. Kochi'));
	add_settings_field('state', 'State', 'portico_webworks_field_text', $page, 'portico_webworks_property_address', array('key' => 'state', 'label' => 'State', 'placeholder' => 'e.g. Kerala'));
	add_settings_field('postal_code', 'Postal Code', 'portico_webworks_field_text', $page, 'portico_webworks_property_address', array('key' => 'postal_code', 'label' => 'Postal Code', 'placeholder' => 'e.g. 682001'));

	add_settings_section('portico_webworks_property_contact', 'Contact', '__return_null', $page);
	add_settings_field('phone', 'Phone No.', 'portico_webworks_field_text', $page, 'portico_webworks_property_contact', array('key' => 'phone', 'label' => 'Phone No.', 'type' => 'tel', 'placeholder' => 'e.g. +91 484 123 4567', 'help' => 'Main front desk / reservations number.'));
	add_settings_field('mobile', 'Mobile No.', 'portico_webworks_field_text', $page, 'portico_webworks_property_contact', array('key' => 'mobile', 'label' => 'Mobile No.', 'type' => 'tel', 'placeholder' => 'e.g. +91 98765 43210', 'help' => 'Optional backup mobile contact.'));
	add_settings_field('whatsapp', 'WhatsApp No.', 'portico_webworks_field_text', $page, 'portico_webworks_property_contact', array('key' => 'whatsapp', 'label' => 'WhatsApp No.', 'type' => 'tel', 'placeholder' => 'e.g. +91 98765 43210', 'help' => 'Number used for WhatsApp chats. Include country code.'));
	add_settings_field('email', 'Email ID', 'portico_webworks_field_text', $page, 'portico_webworks_property_contact', array('key' => 'email', 'label' => 'Email ID', 'type' => 'email', 'placeholder' => 'e.g. reservations@yourhotel.com', 'help' => 'Inbox for guest enquiries and reservations.'));

	add_settings_section('portico_webworks_property_geo', 'Geo', '__return_null', $page);
	add_settings_field('latitude', 'Latitude', 'portico_webworks_field_text', $page, 'portico_webworks_property_geo', array('key' => 'latitude', 'label' => 'Latitude', 'placeholder' => 'e.g. 9.9312', 'help' => 'Example: 9.9312'));
	add_settings_field('longitude', 'Longitude', 'portico_webworks_field_text', $page, 'portico_webworks_property_geo', array('key' => 'longitude', 'label' => 'Longitude', 'placeholder' => 'e.g. 76.2673', 'help' => 'Example: 76.2673'));

	add_settings_section('portico_webworks_property_social', 'Social', '__return_null', $page);
	add_settings_field('instagram', 'Instagram', 'portico_webworks_field_url', $page, 'portico_webworks_property_social', array('key' => 'instagram', 'label' => 'Instagram', 'placeholder' => 'https://instagram.com/yourhandle', 'validate' => 'url'));
	add_settings_field('facebook', 'Facebook', 'portico_webworks_field_url', $page, 'portico_webworks_property_social', array('key' => 'facebook', 'label' => 'Facebook', 'placeholder' => 'https://facebook.com/yourpage', 'validate' => 'url'));
	add_settings_field('youtube', 'YouTube', 'portico_webworks_field_url', $page, 'portico_webworks_property_social', array('key' => 'youtube', 'label' => 'YouTube', 'placeholder' => 'https://youtube.com/@yourchannel', 'validate' => 'url'));
	add_settings_field('linkedin', 'LinkedIn', 'portico_webworks_field_url', $page, 'portico_webworks_property_social', array('key' => 'linkedin', 'label' => 'LinkedIn', 'placeholder' => 'https://linkedin.com/company/yourcompany', 'validate' => 'url'));
	add_settings_field('tripadvisor', 'Tripadvisor', 'portico_webworks_field_url', $page, 'portico_webworks_property_social', array('key' => 'tripadvisor', 'label' => 'Tripadvisor', 'placeholder' => 'https://tripadvisor.com/...', 'validate' => 'url'));
	add_settings_field('twitter', 'Twitter (X)', 'portico_webworks_field_url', $page, 'portico_webworks_property_social', array('key' => 'twitter', 'label' => 'Twitter (X)', 'placeholder' => 'https://x.com/yourhandle', 'validate' => 'url'));
	add_settings_field('google_business', 'Google My Business', 'portico_webworks_field_url', $page, 'portico_webworks_property_social', array('key' => 'google_business', 'label' => 'Google My Business', 'placeholder' => 'https://g.page/...', 'validate' => 'url'));
});

