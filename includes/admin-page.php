<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_admin_page_slug() {
	return 'portico-webworks';
}

function pw_add_menu_divider( $label, $slug_suffix ) {
	add_submenu_page(
		pw_admin_page_slug(),
		'',
		'<span class="pw-menu-divider">' . esc_html( $label ) . '</span>',
		'manage_options',
		'#pw-divider-' . $slug_suffix,
		'__return_false'
	);
}

function pw_logo_url() {
	return plugins_url('logo.svg', PW_PLUGIN_FILE);
}

add_action('admin_menu', function () {
	add_menu_page(
		'Portico Webworks',
		'Portico Webworks',
		'manage_options',
		pw_admin_page_slug(),
		'pw_render_root_page',
		'dashicons-building',
		58
	);

	add_submenu_page(
		pw_admin_page_slug(),
		'Portico Webworks',
		'Settings',
		'manage_options',
		pw_admin_page_slug(),
		'pw_render_root_page'
	);
}, 10);

add_action('admin_menu', function () {
	pw_add_menu_divider( 'Properties', 'properties' );

	add_submenu_page(
		pw_admin_page_slug(),
		'All Properties',
		'All Properties',
		'manage_options',
		'edit.php?post_type=pw_property'
	);

	add_submenu_page(
		pw_admin_page_slug(),
		'Add New Property',
		'Add New Property',
		'manage_options',
		'post-new.php?post_type=pw_property'
	);

	pw_add_menu_divider( 'Property Content', 'property-content' );

	add_submenu_page( pw_admin_page_slug(), 'Room Types',    'Room Types',    'manage_options', 'edit.php?post_type=pw_room_type' );
	add_submenu_page( pw_admin_page_slug(), 'Features',      'Features',      'manage_options', 'edit.php?post_type=pw_feature' );
	add_submenu_page( pw_admin_page_slug(), 'Restaurants',   'Restaurants',   'manage_options', 'edit.php?post_type=pw_restaurant' );
	add_submenu_page( pw_admin_page_slug(), 'Spas',          'Spas',          'manage_options', 'edit.php?post_type=pw_spa' );
	add_submenu_page( pw_admin_page_slug(), 'Meeting Rooms', 'Meeting Rooms', 'manage_options', 'edit.php?post_type=pw_meeting_room' );
	add_submenu_page( pw_admin_page_slug(), 'Amenities',     'Amenities',     'manage_options', 'edit.php?post_type=pw_amenity' );
	add_submenu_page( pw_admin_page_slug(), 'Policies',      'Policies',      'manage_options', 'edit.php?post_type=pw_policy' );

	pw_add_menu_divider( 'Marketing', 'marketing' );

	add_submenu_page( pw_admin_page_slug(), 'Offers',      'Offers',      'manage_options', 'edit.php?post_type=pw_offer' );
	add_submenu_page( pw_admin_page_slug(), 'Experiences', 'Experiences', 'manage_options', 'edit.php?post_type=pw_experience' );
	add_submenu_page( pw_admin_page_slug(), 'Events',      'Events',      'manage_options', 'edit.php?post_type=pw_event' );
	add_submenu_page( pw_admin_page_slug(), 'Nearby',      'Nearby',      'manage_options', 'edit.php?post_type=pw_nearby' );
	add_submenu_page( pw_admin_page_slug(), 'FAQs',        'FAQs',        'manage_options', 'edit.php?post_type=pw_faq' );
}, 30);

function pw_title() {
	return 'Portico Webworks Hotel Website Manager';
}

function pw_version() {
	return defined('PW_VERSION') ? PW_VERSION : '';
}

function pw_sanitize_property_base($value) {
	$value = is_string($value) ? trim($value) : '';
	$value = trim($value, '/');
	$value = sanitize_title($value);
	return $value !== '' ? $value : 'properties';
}

function pw_get_setting($key, $default = '') {
	$opts = get_option('pw_settings', []);
	if (is_array($opts) && array_key_exists($key, $opts)) {
		return $opts[$key];
	}
	return get_option($key, $default);
}

add_filter('cmb2_override_option_get_pw_settings', function ($value, $default) {
	$opts = get_option('pw_settings', []);
	if (is_array($opts) && !empty($opts)) {
		return $opts;
	}
	return [
		'pw_property_mode'    => get_option('pw_property_mode', 'single'),
		'pw_property_base'    => get_option('pw_property_base', 'properties'),
		'pw_default_template' => get_option('pw_default_template', ''),
	];
}, 10, 2);

add_action('update_option_pw_settings', function ($old_value, $value, $option) {
	if (!function_exists('flush_rewrite_rules')) {
		return;
	}
	$old_mode = is_array($old_value) ? ($old_value['pw_property_mode'] ?? '') : '';
	$old_base = is_array($old_value) ? ($old_value['pw_property_base'] ?? '') : '';
	$new_mode = is_array($value) ? ($value['pw_property_mode'] ?? '') : '';
	$new_base = is_array($value) ? ($value['pw_property_base'] ?? '') : '';
	if ($old_mode !== $new_mode || $old_base !== $new_base) {
		flush_rewrite_rules();
	}
}, 10, 3);

add_action('cmb2_admin_init', 'pw_register_settings_cmb2');

function pw_register_settings_cmb2() {
	$cmb = new_cmb2_box([
		'id'           => 'pw_settings',
		'title'        => 'Portico Webworks Settings',
		'object_types' => ['options-page'],
		'option_key'   => 'pw_settings',
		'parent_slug'  => pw_admin_page_slug(),
		'menu_title'   => 'Settings',
		'capability'   => 'manage_options',
	]);

	$cmb->add_field([
		'name'            => 'Property Mode',
		'id'              => 'pw_property_mode',
		'type'            => 'radio_inline',
		'options'         => [
			'single' => 'Single Property',
			'multi'  => 'Multi-Property',
		],
		'default'         => 'single',
		'sanitization_cb' => function ($v) { return $v === 'multi' ? 'multi' : 'single'; },
	]);

	$cmb->add_field([
		'name'            => 'Properties Base Path',
		'id'              => 'pw_property_base',
		'type'            => 'text',
		'desc'            => 'URL prefix for properties, e.g. /{base}/{slug}/... Only applies in Multi-Property mode.',
		'default'         => 'properties',
		'attributes'      => ['placeholder' => 'properties'],
		'sanitization_cb' => 'pw_sanitize_property_base',
	]);

	$cmb->add_field([
		'name'            => 'Default Template',
		'id'              => 'pw_default_template',
		'type'            => 'text',
		'desc'            => 'Template slug used for front-end rendering. Applied across all properties.',
		'attributes'      => ['placeholder' => 'e.g. default'],
		'sanitization_cb' => 'sanitize_text_field',
	]);
}

add_action('admin_menu', function () {
	remove_submenu_page(pw_admin_page_slug(), 'pw_settings');
}, 9999);

function pw_render_root_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$base_tabs = array(
		'settings' => 'General',
		'about'    => 'About',
	);
	$tabs = apply_filters('pw_admin_tabs', $base_tabs);
	$valid_keys = array_keys($tabs);

	$tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';
	if (!in_array($tab, $valid_keys, true)) {
		$tab = $valid_keys[0];
	}

	$mode = pw_get_setting('pw_property_mode', 'single');
	$footer_link = 'https://porticowebworks.com/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=portico_webworks&utm_content=footer';

	echo '<div class="wrap pw-admin">';
	echo '<div class="pw-header">';
	echo '<div class="pw-brand">';
	echo '<img class="pw-logo" src="' . esc_url(pw_logo_url()) . '" alt="" />';
	echo '<div class="pw-brand-text">';
	echo '<div class="pw-title">' . esc_html(pw_title()) . '</div>';
	$blog_public = get_option('blog_public', 1);
	$blog_public = is_numeric($blog_public) ? (int) $blog_public : 1;
	$indexing_on = $blog_public === 1;
	$ver = pw_version();
	if ($ver !== '') {
		echo '<div class="pw-version">v' . esc_html($ver) . '</div>';
	}

	$mode_label = $indexing_on ? 'Search engine indexing ON' : 'Search engine indexing OFF';
	$mode_class = $indexing_on ? 'is-production' : 'is-development';
	echo '<div class="pw-mode ' . esc_attr($mode_class) . '">' . esc_html($mode_label) . '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';

	echo '<nav class="pw-tabs" aria-label="Portico Webworks">';
	foreach ($tabs as $key => $label) {
		$url = admin_url('admin.php?page=' . urlencode(pw_admin_page_slug()) . '&tab=' . urlencode($key));
		echo '<a class="pw-tab' . ($tab === $key ? ' is-active' : '') . '" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
	}
	echo '</nav>';

	if (!in_array($tab, array('settings', 'about'), true)) {
		do_action('pw_render_tab_' . $tab);
		echo '<div class="pw-footer">';
		echo '<a class="pw-footer-link" href="' . esc_url($footer_link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
		echo '</div>';
		do_action('pw_admin_notices');
		echo '</div>';
		return;
	}

	if ($tab === 'about') {
		echo '<div class="pw-card">';
		echo '<div class="pw-card-head"><div class="pw-card-title">About</div></div>';
		echo '<div class="pw-card-body">';
		$ver = pw_version();
		$ver_text = $ver !== '' ? 'v' . $ver : '';

		echo '<p><strong>Portico Webworks Hotel Website Manager</strong> helps you manage key hotel website profile details inside WordPress.</p>';
		echo '<p>This plugin is deployed on our client hotel websites to enhance WordPress functionality to suit hotel-specific needs.</p>';
		echo '<p><strong>Portico Webworks</strong>: A specialized boutique hotel website agency serving mid-scale, upper mid-scale, and upscale independent properties.</p>';
		echo '<p><strong>Parent company (ZES)</strong>: Zarnik Enterprise Services Private Limited (CIN: U62011KL2024PTC090989, incorporated 16-12-2024).</p>';
		echo '<p><strong>Intellectual Property</strong>: Developed by the Portico Webworks team. This plugin is the intellectual property of Zarnik Enterprise Services Private Limited and is not allowed to be used for any other companies or for any purposes.</p>';
		echo '<p><strong>Connect</strong>: ';
		echo '<a href="' . esc_url('https://porticowebworks.com/') . '" target="_blank" rel="noopener noreferrer">porticowebworks.com</a> | ';
		echo '<a href="' . esc_url('https://www.linkedin.com/company/porticowebworks/') . '" target="_blank" rel="noopener noreferrer">LinkedIn</a> | ';
		echo '<a href="' . esc_url('https://www.facebook.com/porticowebworks') . '" target="_blank" rel="noopener noreferrer">Facebook</a> | ';
		echo '<a href="' . esc_url('https://www.instagram.com/porticowebworks/') . '" target="_blank" rel="noopener noreferrer">Instagram</a>';
		echo '</p>';

		if ($ver_text !== '') {
			echo '<p><strong>Plugin version</strong>: ' . esc_html($ver_text) . '</p>';
		}
		echo '</div>';
		echo '</div>';
		echo '<div class="pw-footer">';
		echo '<a class="pw-footer-link" href="' . esc_url($footer_link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
		echo '</div>';
		do_action('pw_admin_notices');
		echo '</div>';
		return;
	}

	if ($tab === 'settings') {
		$blog_public = get_option('blog_public', 1);
		$blog_public = is_numeric($blog_public) ? (int) $blog_public : 1;
		$indexing_on = $blog_public === 1;
		echo '<div class="pw-card">';
		echo '<div class="pw-card-head"><div class="pw-card-title">Settings</div></div>';
		echo '<div class="pw-card-body">';
		echo '<table class="form-table" role="presentation"><tbody>';
		echo '<tr><th scope="row">Search Engine Indexing</th><td>';
		echo '<strong>' . esc_html($indexing_on ? 'ON' : 'OFF') . '</strong>';
		echo '<p class="description">Controlled by WordPress Settings -> Reading -> "Discourage search engines from indexing this site".</p>';
		echo '</td></tr>';
		echo '</tbody></table>';

		$cmb = cmb2_get_metabox('pw_settings', 'pw_settings');
		if ($cmb) {
			echo '<form class="cmb-form" action="' . esc_url(admin_url('admin-post.php')) . '" method="POST" id="pw_settings" enctype="multipart/form-data">';
			echo '<input type="hidden" name="action" value="pw_settings" />';
			$cmb->show_form();
			submit_button(esc_attr__('Save Settings', 'cmb2'), 'primary', 'submit-cmb');
			echo '</form>';
		}

		echo '</div>';
		echo '</div>';

		echo '<div class="pw-footer">';
		echo '<a class="pw-footer-link" href="' . esc_url($footer_link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
		echo '</div>';
		do_action('pw_admin_notices');
		echo '</div>';
		return;
	}

}

