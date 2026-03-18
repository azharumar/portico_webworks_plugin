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
	return [
		'_pw_brand_name'         => ['section' => 'identity', 'label' => 'Brand Name',    'type' => 'text',   'placeholder' => 'e.g. The Grand Pavilion', 'help' => 'Public-facing name shown to guests.'],
		'_pw_legal_name'         => ['section' => 'identity', 'label' => 'Legal Name',    'type' => 'text',   'placeholder' => 'e.g. Grand Pavilion Hospitality Pvt Ltd', 'help' => 'For invoices, contracts, and compliance.'],
		'_pw_slug'               => ['section' => 'identity', 'label' => 'Slug',          'type' => 'text',   'placeholder' => 'e.g. grand-pavilion', 'help' => 'Used for URL routing in multi-property mode.'],
		'_pw_star_rating'        => ['section' => 'identity', 'label' => 'Star Rating',   'type' => 'number', 'placeholder' => '1–5', 'help' => 'Hotel star classification (1–5).'],
		'_pw_default_template'   => ['section' => 'identity', 'label' => 'Default Template', 'type' => 'text', 'placeholder' => 'e.g. default', 'help' => 'Template slug for front-end rendering.'],

		'_pw_address_line_1'     => ['section' => 'address',  'label' => 'Address Line 1', 'type' => 'text',  'placeholder' => 'Street address, building, etc.'],
		'_pw_address_line_2'     => ['section' => 'address',  'label' => 'Address Line 2', 'type' => 'text',  'placeholder' => 'Area, landmark (optional)'],
		'_pw_city'               => ['section' => 'address',  'label' => 'City',           'type' => 'text',  'placeholder' => 'e.g. Kochi'],
		'_pw_country'            => ['section' => 'address',  'label' => 'Country',        'type' => 'text',  'placeholder' => 'e.g. IN', 'help' => 'ISO 3166-1 alpha-2 code.'],

		'_pw_phone'              => ['section' => 'contact',  'label' => 'Phone No.',      'type' => 'tel',   'placeholder' => 'e.g. +91 484 123 4567', 'help' => 'Main front desk / reservations number.'],
		'_pw_email'              => ['section' => 'contact',  'label' => 'Email',          'type' => 'email', 'placeholder' => 'e.g. reservations@yourhotel.com', 'help' => 'Inbox for guest enquiries and reservations.'],

		'_pw_lat'                => ['section' => 'geo',      'label' => 'Latitude',       'type' => 'text',  'placeholder' => 'e.g. 9.9312'],
		'_pw_lng'                => ['section' => 'geo',      'label' => 'Longitude',      'type' => 'text',  'placeholder' => 'e.g. 76.2673'],

		'_pw_social_facebook'    => ['section' => 'social',   'label' => 'Facebook',       'type' => 'url',   'placeholder' => 'https://facebook.com/yourpage'],
		'_pw_social_instagram'   => ['section' => 'social',   'label' => 'Instagram',      'type' => 'url',   'placeholder' => 'https://instagram.com/yourhandle'],
		'_pw_social_youtube'     => ['section' => 'social',   'label' => 'YouTube',        'type' => 'url',   'placeholder' => 'https://youtube.com/@yourchannel'],
		'_pw_social_linkedin'    => ['section' => 'social',   'label' => 'LinkedIn',       'type' => 'url',   'placeholder' => 'https://linkedin.com/company/yourcompany'],
		'_pw_social_tripadvisor' => ['section' => 'social',   'label' => 'Tripadvisor',    'type' => 'url',   'placeholder' => 'https://tripadvisor.com/...'],
	];
}

function pw_render_property_metabox($post) {
	$sections = pw_property_sections();
	$fields = pw_property_fields();

	wp_nonce_field('pw_save_property_profile', 'pw_property_profile_nonce');

	foreach ($sections as $section_key => $section_meta) {
		$is_open = $section_key === 'identity';
		echo '<details class="pw-property-profile-section" ' . ($is_open ? 'open' : '') . '>';
		echo '<summary>' . esc_html($section_meta['label']) . '</summary>';
		pw_render_property_profile_section_fields($post->ID, $fields, $section_key);
		echo '</details>';
	}
}

function pw_render_property_profile_section_fields($post_id, $fields, $section_key) {
	echo '<table class="form-table" role="presentation"><tbody>';

	foreach ($fields as $meta_key => $field) {
		if ($field['section'] !== $section_key) {
			continue;
		}

		$label       = $field['label'];
		$type        = $field['type'] ?? 'text';
		$placeholder = $field['placeholder'] ?? '';
		$help        = $field['help'] ?? '';
		$input_name  = ltrim($meta_key, '_');
		$val         = get_post_meta((int) $post_id, $meta_key, true);

		echo '<tr>';
		echo '<th scope="row">';
		echo '<label for="pw-' . esc_attr($meta_key) . '">' . esc_html($label) . '</label>';
		echo '</th>';
		echo '<td>';
		echo '<input class="regular-text" id="pw-' . esc_attr($meta_key) . '" name="' . esc_attr($input_name) . '" type="' . esc_attr($type) . '" value="' . esc_attr($val) . '" placeholder="' . esc_attr($placeholder) . '" />';
		if ($help !== '') {
			echo '<p class="description">' . esc_html($help) . '</p>';
		}
		echo '</td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
}

function pw_render_property_profile_section_metabox($post, $section_key) {
	$fields = pw_property_fields();

	wp_nonce_field('pw_save_property_profile', 'pw_property_profile_nonce');

	pw_render_property_profile_section_fields($post->ID, $fields, $section_key);
}

function pw_render_property_profile_identity_metabox($post) {
	pw_render_property_profile_section_metabox($post, 'identity');
}

function pw_render_property_profile_address_metabox($post) {
	pw_render_property_profile_section_metabox($post, 'address');
}

function pw_render_property_profile_contact_metabox($post) {
	pw_render_property_profile_section_metabox($post, 'contact');
}

function pw_render_property_profile_geo_metabox($post) {
	pw_render_property_profile_section_metabox($post, 'geo');
}

function pw_render_property_profile_social_metabox($post) {
	pw_render_property_profile_section_metabox($post, 'social');
}

function pw_add_property_metabox() {
	$sections = pw_property_sections();
	$callbacks = array(
		'identity' => 'pw_render_property_profile_identity_metabox',
		'address' => 'pw_render_property_profile_address_metabox',
		'contact' => 'pw_render_property_profile_contact_metabox',
		'geo' => 'pw_render_property_profile_geo_metabox',
		'social' => 'pw_render_property_profile_social_metabox',
	);

	foreach ($sections as $section_key => $section_meta) {
		$callback = isset($callbacks[$section_key]) ? $callbacks[$section_key] : 'pw_render_property_profile_identity_metabox';
		add_meta_box(
			'pw_property_profile_' . $section_key,
			$section_meta['label'],
			$callback,
			'pw_property',
			'normal',
			'high'
		);
	}
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

	$fields = pw_property_fields();

	foreach ($fields as $meta_key => $field) {
		$post_key = ltrim($meta_key, '_');

		if (!isset($_POST[$post_key])) {
			continue;
		}

		$raw  = wp_unslash($_POST[$post_key]);
		$type = $field['type'] ?? 'text';

		switch ($type) {
			case 'email':
				$value = sanitize_email($raw);
				break;
			case 'url':
				$value = esc_url_raw($raw);
				break;
			case 'number':
				$value = is_numeric($raw) ? floatval($raw) : 0;
				break;
			default:
				$value = sanitize_text_field($raw);
				break;
		}

		update_post_meta((int) $post_id, $meta_key, $value);
	}
}

add_action('save_post_pw_property', 'pw_save_property_metabox');


