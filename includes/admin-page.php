<?php

if (!defined('ABSPATH')) {
	exit;
}

function portico_webworks_admin_page_slug() {
	return 'portico-webworks';
}

function portico_webworks_logo_url() {
	return plugins_url('logo.svg', PORTICO_WEBWORKS_PLUGIN_FILE);
}

add_action('admin_menu', function () {
	add_menu_page(
		'Portico Webworks',
		'Portico Webworks',
		'manage_options',
		portico_webworks_admin_page_slug(),
		'portico_webworks_render_root_page',
		'dashicons-building',
		58
	);
});

function portico_webworks_title() {
	return 'Portico Webworks Hotel Website Manager';
}

function portico_webworks_version() {
	return defined('PORTICO_WEBWORKS_VERSION') ? PORTICO_WEBWORKS_VERSION : '';
}

function portico_webworks_sanitize_property_base($value) {
	$value = is_string($value) ? trim($value) : '';
	$value = trim($value, '/');
	$value = sanitize_title($value);
	return $value !== '' ? $value : 'properties';
}

add_action('admin_init', function () {
	register_setting(
		'portico_webworks_property_routing',
		'portico_webworks_property_base',
		array('sanitize_callback' => 'portico_webworks_sanitize_property_base')
	);

	add_settings_section(
		'portico_webworks_property_routing_section',
		'Routing',
		'__return_null',
		portico_webworks_admin_page_slug()
	);

	add_settings_field(
		'portico_webworks_property_base',
		'Properties Base Path',
		function () {
			$val = get_option('portico_webworks_property_base', 'properties');
			$val = esc_attr($val);
			echo '<input class="regular-text" type="text" name="portico_webworks_property_base" value="' . $val . '" placeholder="properties" />';
			echo '<p class="description">Used to build/parse URLs like <code>/{portico_webworks_property_base}/{slug}/...</code></p>';
		},
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_routing_section'
	);
});

function portico_webworks_render_root_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$base_tabs = array(
		'property' => 'Property Profile',
		'settings' => 'Settings',
		'about' => 'About',
	);
	$tabs = apply_filters('portico_webworks_admin_tabs', $base_tabs);
	$valid_keys = array_keys($tabs);

	$tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';
	if (!in_array($tab, $valid_keys, true)) {
		$tab = $valid_keys[0];
	}

	echo '<div class="wrap portico-webworks-admin">';
	echo '<div class="pw-header">';
	echo '<div class="pw-brand">';
	echo '<img class="pw-logo" src="' . esc_url(portico_webworks_logo_url()) . '" alt="" />';
	echo '<div class="pw-brand-text">';
	echo '<div class="pw-title">' . esc_html(portico_webworks_title()) . '</div>';
	$ver = portico_webworks_version();
	if ($ver !== '') {
		echo '<div class="pw-version">v' . esc_html($ver) . '</div>';
	}
	echo '</div>';
	echo '</div>';
	echo '</div>';

	echo '<nav class="pw-tabs" aria-label="Portico Webworks">';
	foreach ($tabs as $key => $label) {
		$url = admin_url('admin.php?page=' . urlencode(portico_webworks_admin_page_slug()) . '&tab=' . urlencode($key));
		echo '<a class="pw-tab' . ($tab === $key ? ' is-active' : '') . '" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
	}
	echo '</nav>';

	if (!in_array($tab, array('property', 'settings', 'about'), true)) {
		do_action('portico_webworks_render_tab_' . $tab);
		echo '<div class="pw-footer">';
		$link = 'https://porticowebworks.com/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=portico_webworks&utm_content=footer';
		echo '<a class="pw-footer-link" href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
		echo '</div>';
		echo '</div>';
		return;
	}

	if ($tab === 'about') {
		echo '<div class="pw-card">';
		echo '<div class="pw-card-head"><div class="pw-card-title">About</div></div>';
		echo '<div class="pw-card-body">';
		echo '<p>Portico Webworks Hotel Website Manager helps you manage key hotel website profile details inside WordPress.</p>';
		echo '</div>';
		echo '</div>';
		echo '<div class="pw-footer">';
		$link = 'https://porticowebworks.com/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=portico_webworks&utm_content=footer';
		echo '<a class="pw-footer-link" href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
		echo '</div>';
		echo '</div>';
		return;
	}

	if ($tab === 'settings') {
		echo '<div class="pw-card">';
		echo '<div class="pw-card-head"><div class="pw-card-title">Settings</div></div>';
		echo '<div class="pw-card-body"><p>Portico Webworks settings will go here.</p></div>';
		echo '</div>';
		echo '<div class="pw-footer">';
		$link = 'https://porticowebworks.com/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=portico_webworks&utm_content=footer';
		echo '<a class="pw-footer-link" href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
		echo '</div>';
		echo '</div>';
		return;
	}

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">Properties</div></div>';
	echo '<div class="pw-card-body">';

	$add_url = admin_url('post-new.php?post_type=pw_property');
	echo '<p><a class="button button-primary" href="' . esc_url($add_url) . '">Add Property</a></p>';

	echo '<form method="post" action="options.php">';
	echo '<input type="hidden" name="_wp_http_referer" value="' . esc_attr(admin_url('admin.php?page=' . urlencode(portico_webworks_admin_page_slug()) . '&tab=property')) . '" />';
	settings_fields('portico_webworks_property_routing');
	do_settings_sections(portico_webworks_admin_page_slug());
	submit_button('Save Routing');
	echo '</form>';

	$properties = portico_webworks_get_all_properties();
	echo '<h3 style="margin-top:18px">Property List</h3>';
	echo '<table class="widefat striped" role="presentation">';
	echo '<thead><tr>';
	echo '<th scope="col">Name</th>';
	echo '<th scope="col">Slug</th>';
	echo '<th scope="col">City</th>';
	echo '<th scope="col">Actions</th>';
	echo '</tr></thead>';
	echo '<tbody>';
	if (empty($properties)) {
		echo '<tr><td colspan="4">No properties found. Click "Add Property" to get started.</td></tr>';
	} else {
		foreach ($properties as $p) {
			$profile = portico_webworks_get_property_profile($p['id']);
			$city = isset($profile['city']) ? $profile['city'] : '';
			$edit_url = get_edit_post_link($p['id']);

			echo '<tr>';
			echo '<td>' . esc_html($p['name']) . '</td>';
			echo '<td>' . esc_html($p['slug']) . '</td>';
			echo '<td>' . esc_html($city) . '</td>';
			echo '<td><a href="' . esc_url($edit_url) . '">Edit</a></td>';
			echo '</tr>';
		}
	}
	echo '</tbody>';
	echo '</table>';

	echo '</div></div>';
	echo '<div class="pw-footer">';
	$link = 'https://porticowebworks.com/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=portico_webworks&utm_content=footer';
	echo '<a class="pw-footer-link" href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
	echo '</div>';
	echo '</div>';
}

