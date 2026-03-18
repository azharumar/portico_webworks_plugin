<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_property_sections() {
	return array(
		'identity' => array('label' => 'Identity'),
		'address' => array('label' => 'Address'),
		'contact' => array('label' => 'Contact'),
		'geo' => array('label' => 'Geo'),
		'social' => array('label' => 'Social'),
	);
}

function pw_property_fields() {
	return array(
		'property_name' => array('section' => 'identity', 'label' => 'Property Name', 'type' => 'text', 'placeholder' => 'e.g. The Grand Pavilion', 'help' => 'Public-facing name shown to guests.'),
		'property_short_name' => array('section' => 'identity', 'label' => 'Property Short Name', 'type' => 'text', 'placeholder' => 'e.g. Grand Pavilion', 'help' => 'Shortened name for tight layouts (headers, nav, etc.).'),
		'abbreviation' => array('section' => 'identity', 'label' => 'Abbreviation', 'type' => 'text', 'placeholder' => 'e.g. TGP', 'help' => 'Internal shorthand (optional).'),
		'legal_name' => array('section' => 'identity', 'label' => 'Legal Name (Company)', 'type' => 'text', 'placeholder' => 'e.g. Grand Pavilion Hospitality Pvt Ltd', 'help' => 'For invoices, contracts, and compliance.'),
		'tax_id' => array('section' => 'identity', 'label' => 'Tax ID', 'type' => 'text', 'placeholder' => 'e.g. GSTIN / VAT / EIN', 'help' => 'Use the format required in your jurisdiction.'),

		'address_line_1' => array('section' => 'address', 'label' => 'Address Line 1', 'type' => 'text', 'placeholder' => 'Street address, building, etc.'),
		'address_line_2' => array('section' => 'address', 'label' => 'Address Line 2', 'type' => 'text', 'placeholder' => 'Area, landmark (optional)'),
		'city' => array('section' => 'address', 'label' => 'City', 'type' => 'text', 'placeholder' => 'e.g. Kochi'),
		'state' => array('section' => 'address', 'label' => 'State', 'type' => 'text', 'placeholder' => 'e.g. Kerala'),
		'postal_code' => array('section' => 'address', 'label' => 'Postal Code', 'type' => 'text', 'placeholder' => 'e.g. 682001'),

		'phone' => array('section' => 'contact', 'label' => 'Phone No.', 'type' => 'tel', 'placeholder' => 'e.g. +91 484 123 4567', 'help' => 'Main front desk / reservations number.'),
		'mobile' => array('section' => 'contact', 'label' => 'Mobile No.', 'type' => 'tel', 'placeholder' => 'e.g. +91 98765 43210', 'help' => 'Optional backup mobile contact.'),
		'whatsapp' => array('section' => 'contact', 'label' => 'WhatsApp No.', 'type' => 'tel', 'placeholder' => 'e.g. +91 98765 43210', 'help' => 'Number used for WhatsApp chats. Include country code.'),
		'email' => array('section' => 'contact', 'label' => 'Email ID', 'type' => 'email', 'placeholder' => 'e.g. reservations@yourhotel.com', 'help' => 'Inbox for guest enquiries and reservations.'),

		'latitude' => array('section' => 'geo', 'label' => 'Latitude', 'type' => 'text', 'placeholder' => 'e.g. 9.9312', 'help' => 'Example: 9.9312'),
		'longitude' => array('section' => 'geo', 'label' => 'Longitude', 'type' => 'text', 'placeholder' => 'e.g. 76.2673', 'help' => 'Example: 76.2673'),

		'instagram' => array('section' => 'social', 'label' => 'Instagram', 'type' => 'url', 'placeholder' => 'https://instagram.com/yourhandle'),
		'facebook' => array('section' => 'social', 'label' => 'Facebook', 'type' => 'url', 'placeholder' => 'https://facebook.com/yourpage'),
		'youtube' => array('section' => 'social', 'label' => 'YouTube', 'type' => 'url', 'placeholder' => 'https://youtube.com/@yourchannel'),
		'linkedin' => array('section' => 'social', 'label' => 'LinkedIn', 'type' => 'url', 'placeholder' => 'https://linkedin.com/company/yourcompany'),
		'tripadvisor' => array('section' => 'social', 'label' => 'Tripadvisor', 'type' => 'url', 'placeholder' => 'https://tripadvisor.com/...'),
		'twitter' => array('section' => 'social', 'label' => 'Twitter (X)', 'type' => 'url', 'placeholder' => 'https://x.com/yourhandle'),
		'google_business' => array('section' => 'social', 'label' => 'Google My Business', 'type' => 'url', 'placeholder' => 'https://g.page/...'),
	);
}

function pw_sanitize_property_profile($input) {
	$defaults = pw_property_profile_defaults();
	if (!is_array($input)) {
		return $defaults;
	}

	$out = $defaults;

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
		'latitude',
		'longitude',
	);

	foreach ($text_fields as $k) {
		if (isset($input[$k])) {
			$out[$k] = sanitize_text_field(wp_unslash($input[$k]));
		}
	}

	if (isset($input['email'])) {
		$out['email'] = sanitize_email(wp_unslash($input['email']));
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
			$out[$k] = esc_url_raw(wp_unslash($input[$k]));
		}
	}

	return $out;
}

function pw_render_property_metabox($post) {
	$profile = pw_get_property_profile($post->ID);
	$sections = pw_property_sections();
	$fields = pw_property_fields();

	wp_nonce_field('pw_save_property_profile', 'pw_property_profile_nonce');

	foreach ($sections as $section_key => $section_meta) {
		echo '<h3>' . esc_html($section_meta['label']) . '</h3>';
		echo '<table class="form-table" role="presentation"><tbody>';

		foreach ($fields as $key => $field) {
			if ($field['section'] !== $section_key) {
				continue;
			}

			$label = $field['label'];
			$type = isset($field['type']) ? $field['type'] : 'text';
			$placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
			$help = isset($field['help']) ? $field['help'] : '';

			$name = 'pw_property_profile[' . $key . ']';
			$val = isset($profile[$key]) ? $profile[$key] : '';

			echo '<tr>';
			echo '<th scope="row">';
			echo '<label for="pw-' . esc_attr($key) . '">' . esc_html($label) . '</label>';
			echo '</th>';
			echo '<td>';
			echo '<input class="regular-text" id="pw-' . esc_attr($key) . '" name="' . esc_attr($name) . '" type="' . esc_attr($type) . '" value="' . esc_attr($val) . '" placeholder="' . esc_attr($placeholder) . '" />';
			if ($help !== '') {
				echo '<p class="description">' . esc_html($help) . '</p>';
			}
			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}
}

function pw_add_property_metabox() {
	add_meta_box(
		'pw_property_profile',
		'Property Profile',
		'pw_render_property_metabox',
		'pw_property',
		'normal',
		'high'
	);
}

add_action('add_meta_boxes', 'pw_add_property_metabox');

function pw_save_property_metabox($post_id) {
	if (!isset($_POST['pw_property_profile_nonce'])) {
		return;
	}

	if (!wp_verify_nonce(wp_unslash($_POST['pw_property_profile_nonce']), 'pw_save_property_profile')) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	if (!isset($_POST['pw_property_profile']) || !is_array($_POST['pw_property_profile'])) {
		return;
	}

	$raw = wp_unslash($_POST['pw_property_profile']);
	$sanitized = pw_sanitize_property_profile($raw);
	update_post_meta((int) $post_id, pw_property_meta_key(), $sanitized);
}

add_action('save_post_pw_property', 'pw_save_property_metabox');

