<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_property_sections() {
	return array(
		'general' => array('label' => 'General'),
		'address' => array('label' => 'Address'),
		'geo'     => array('label' => 'Geo'),
		'social'  => array('label' => 'Social'),
	);
}

function pw_timezone_options() {
	$zones   = DateTimeZone::listIdentifiers();
	$options = array('' => '— Select Timezone —');
	foreach ($zones as $zone) {
		$options[$zone] = $zone;
	}
	return $options;
}

function pw_property_fields() {
	return array_merge(
		[
		'_pw_legal_name'         => ['section' => 'general',  'label' => 'Legal Name',        'type' => 'text',   'placeholder' => 'e.g. Grand Pavilion Hospitality Pvt Ltd',    'help' => 'For invoices, contracts, and compliance.'],
		'_pw_star_rating'        => ['section' => 'general',  'label' => 'Star Rating',       'type' => 'number', 'placeholder' => '1–5',                                        'help' => 'Hotel star classification (1–5).'],
		'_pw_currency'           => ['section' => 'general',  'label' => 'Currency',          'type' => 'select', 'options' => 'pw_currency_options_for_profile',                'help' => 'Default currency for rates and pricing.'],
		'_pw_check_in_time'      => ['section' => 'general',  'label' => 'Check-in Time',       'type' => 'time',   'placeholder' => 'e.g. 14:00'],
		'_pw_check_out_time'     => ['section' => 'general',  'label' => 'Check-out Time',      'type' => 'time',   'placeholder' => 'e.g. 11:00'],
		'_pw_year_established'   => ['section' => 'general',  'label' => 'Year Established',    'type' => 'number', 'placeholder' => 'e.g. 2005'],
		'_pw_total_rooms'        => ['section' => 'general',  'label' => 'Total Rooms',         'type' => 'number', 'placeholder' => 'e.g. 120'],
		],
		[
		'_pw_address_line_1'     => ['section' => 'address',  'label' => 'Address Line 1',   'type' => 'text',   'placeholder' => 'Street address, building, etc.'],
		'_pw_address_line_2'     => ['section' => 'address',  'label' => 'Address Line 2',   'type' => 'text',   'placeholder' => 'Area, landmark (optional)'],
		'_pw_city'               => ['section' => 'address',  'label' => 'City',             'type' => 'text',   'placeholder' => 'e.g. Kochi'],
		'_pw_state'              => ['section' => 'address',  'label' => 'State / Province', 'type' => 'text',   'placeholder' => 'e.g. Kerala'],
		'_pw_postal_code'        => ['section' => 'address',  'label' => 'Postal Code',      'type' => 'text',   'placeholder' => 'e.g. 682001'],
		'_pw_country'            => ['section' => 'address',  'label' => 'Country',          'type' => 'text',   'placeholder' => 'e.g. India',                                'help' => 'Full country name'],
		'_pw_country_code'       => ['section' => 'address',  'label' => 'Country code',     'type' => 'text',   'placeholder' => 'e.g. IN',                                    'help' => 'ISO 3166-1 alpha-2 — used in schema.org markup'],

		'_pw_lat'                => ['section' => 'geo',      'label' => 'Latitude',         'type' => 'text',   'placeholder' => 'e.g. 9.9312'],
		'_pw_lng'                => ['section' => 'geo',      'label' => 'Longitude',        'type' => 'text',   'placeholder' => 'e.g. 76.2673'],
		'_pw_google_place_id'    => ['section' => 'geo',      'label' => 'Google Place ID',  'type' => 'text',   'placeholder' => 'e.g. ChIJN1t_tDeuEmsRUsoyG83frY4',          'help' => 'Used for Google Maps embeds and rich results.'],
		'_pw_timezone'           => ['section' => 'geo',      'label' => 'Timezone',         'type' => 'select', 'options' => 'pw_timezone_options'],

		'_pw_social_facebook'    => ['section' => 'social',   'label' => 'Facebook',         'type' => 'url',    'placeholder' => 'https://facebook.com/yourpage'],
		'_pw_social_instagram'   => ['section' => 'social',   'label' => 'Instagram',        'type' => 'url',    'placeholder' => 'https://instagram.com/yourhandle'],
		'_pw_social_twitter'     => ['section' => 'social',   'label' => 'Twitter / X',      'type' => 'url',    'placeholder' => 'https://twitter.com/yourhandle'],
		'_pw_social_youtube'     => ['section' => 'social',   'label' => 'YouTube',          'type' => 'url',    'placeholder' => 'https://youtube.com/@yourchannel'],
		'_pw_social_linkedin'    => ['section' => 'social',   'label' => 'LinkedIn',         'type' => 'url',    'placeholder' => 'https://linkedin.com/company/yourcompany'],
		'_pw_social_tripadvisor' => ['section' => 'social',   'label' => 'Tripadvisor',      'type' => 'url',    'placeholder' => 'https://tripadvisor.com/...'],
		]
	);
}

function pw_currency_options_for_profile() {
	$options = array('' => '— Select Currency —');
	foreach (pw_get_currency_list() as $code => $data) {
		$options[$code] = $code . ' ' . $data['symbol'] . ' — ' . $data['name'];
	}
	return $options;
}

function pw_render_property_metabox($post) {
	$sections = pw_property_sections();
	$fields   = pw_property_fields();

	wp_nonce_field('pw_save_property_profile', 'pw_property_profile_nonce');

	foreach ($sections as $section_key => $section_meta) {
		$is_open = $section_key === 'general';
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

		if ($type === 'select') {
			$options_source = $field['options'] ?? array();
			$options        = is_callable($options_source) ? call_user_func($options_source) : (is_array($options_source) ? $options_source : array());
			echo '<select class="regular-text" id="pw-' . esc_attr($meta_key) . '" name="' . esc_attr($input_name) . '">';
			foreach ($options as $opt_val => $opt_label) {
				echo '<option value="' . esc_attr($opt_val) . '"' . selected($val, $opt_val, false) . '>' . esc_html($opt_label) . '</option>';
			}
			echo '</select>';
		} else {
			echo '<input class="regular-text" id="pw-' . esc_attr($meta_key) . '" name="' . esc_attr($input_name) . '" type="' . esc_attr($type) . '" value="' . esc_attr($val) . '" placeholder="' . esc_attr($placeholder) . '" />';
		}

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

function pw_render_property_profile_general_metabox($post) {
	pw_render_property_profile_section_metabox($post, 'general');
}

function pw_render_property_profile_address_metabox($post) {
	pw_render_property_profile_section_metabox($post, 'address');
}

function pw_render_property_profile_geo_metabox($post) {
	pw_render_property_profile_section_metabox($post, 'geo');
}

function pw_render_property_profile_social_metabox($post) {
	pw_render_property_profile_section_metabox($post, 'social');
}

function pw_add_property_metabox() {
	$sections  = pw_property_sections();
	$callbacks = array(
		'general' => 'pw_render_property_profile_general_metabox',
		'address' => 'pw_render_property_profile_address_metabox',
		'geo'     => 'pw_render_property_profile_geo_metabox',
		'social'  => 'pw_render_property_profile_social_metabox',
	);

	foreach ($sections as $section_key => $section_meta) {
		$callback = isset($callbacks[$section_key]) ? $callbacks[$section_key] : 'pw_render_property_profile_general_metabox';
		add_meta_box(
			'pw_property_profile_' . $section_key,
			$section_meta['label'],
			$callback,
			'pw_property',
			'normal',
			'high'
		);
	}
	add_meta_box(
		'pw_property_enabled_sections',
		__( 'Section pages', 'portico-webworks' ),
		'pw_render_property_enabled_sections_metabox',
		'pw_property',
		'side',
		'default'
	);
}

function pw_render_property_enabled_sections_metabox( $post ) {
	wp_nonce_field( 'pw_save_enabled_sections', 'pw_enabled_sections_nonce' );
	$stored = get_post_meta( $post->ID, '_pw_enabled_sections', true );
	echo '<p class="description">' . esc_html__( 'Unchecked sections hide the listing page URL for this property; outlet URLs still work.', 'portico-webworks' ) . '</p>';
	echo '<fieldset><legend class="screen-reader-text">' . esc_html__( 'Enabled sections', 'portico-webworks' ) . '</legend>';
	foreach ( pw_url_section_cpts() as $cpt ) {
		$pto = get_post_type_object( $cpt );
		$label = $pto && isset( $pto->labels->name ) ? $pto->labels->name : $cpt;
		if ( $stored === false || $stored === '' || $stored === null ) {
			$checked = true;
		} elseif ( is_array( $stored ) && $stored === [] ) {
			$checked = false;
		} else {
			$checked = is_array( $stored ) && in_array( $cpt, $stored, true );
		}
		echo '<label style="display:block;margin:0.35em 0;"><input type="checkbox" name="pw_enabled_sections[]" value="' . esc_attr( $cpt ) . '"' . checked( $checked, true, false ) . ' /> ' . esc_html( $label ) . '</label>';
	}
	echo '</fieldset>';
}

function pw_save_property_enabled_sections( $post_id ) {
	if ( ! isset( $_POST['pw_enabled_sections_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pw_enabled_sections_nonce'] ) ), 'pw_save_enabled_sections' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'pw_property' ) {
		return;
	}
	$allowed = pw_url_section_cpts();
	$picked  = [];
	if ( isset( $_POST['pw_enabled_sections'] ) && is_array( $_POST['pw_enabled_sections'] ) ) {
		foreach ( wp_unslash( $_POST['pw_enabled_sections'] ) as $cpt ) {
			$cpt = sanitize_key( (string) $cpt );
			if ( in_array( $cpt, $allowed, true ) ) {
				$picked[] = $cpt;
			}
		}
	}
	if ( count( $picked ) === count( $allowed ) ) {
		delete_post_meta( $post_id, '_pw_enabled_sections' );
		return;
	}
	update_post_meta( $post_id, '_pw_enabled_sections', array_values( $picked ) );
}

add_action( 'add_meta_boxes', 'pw_add_property_metabox' );
add_action( 'save_post_pw_property', 'pw_save_property_enabled_sections', 5 );

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

add_action(
	'admin_notices',
	static function () {
		if ( ! isset( $_GET['pw_duplicate_url_slug'] ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'pw_property' || $screen->base !== 'post' ) {
			return;
		}
		echo '<div class="notice notice-error"><p>' . esc_html__( 'That URL slug is already used by another published property.', 'portico-webworks' ) . '</p></div>';
	}
);
