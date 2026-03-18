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

function portico_webworks_render_root_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'property';
	if (!in_array($tab, array('property', 'settings', 'about'), true)) {
		$tab = 'property';
	}

	$sections = portico_webworks_property_sections();
	$sub = isset($_GET['sub']) ? sanitize_key($_GET['sub']) : 'identity';
	if (!isset($sections[$sub])) {
		$sub = 'identity';
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

	$tabs = array(
		'property' => 'Property Profile',
		'settings' => 'Settings',
		'about' => 'About',
	);
	echo '<nav class="pw-tabs" aria-label="Portico Webworks">';
	foreach ($tabs as $key => $label) {
		$url = admin_url('admin.php?page=' . urlencode(portico_webworks_admin_page_slug()) . '&tab=' . urlencode($key));
		echo '<a class="pw-tab' . ($tab === $key ? ' is-active' : '') . '" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
	}
	echo '</nav>';

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
	echo '<div class="pw-card-head"><div class="pw-card-title">Property Profile</div></div>';
	echo '<div class="pw-card-body pw-split">';

	echo '<div class="pw-vnav" aria-label="Property Profile Sections">';
	foreach ($sections as $key => $meta) {
		$url = admin_url('admin.php?page=' . urlencode(portico_webworks_admin_page_slug()) . '&tab=property&sub=' . urlencode($key));
		$is_active = ($sub === $key);
		echo '<a data-pw-sub="' . esc_attr($key) . '" class="' . ($is_active ? 'is-active' : '') . '" href="' . esc_url($url) . '">' . esc_html($meta['label']) . '</a>';
	}
	echo '</div>';

	echo '<div class="pw-vcontent">';
	foreach ($sections as $key => $meta) {
		$is_active = ($sub === $key);
		echo '<div class="pw-section' . ($is_active ? ' is-active' : '') . '" data-pw-panel="' . esc_attr($key) . '">';
		echo '<form method="post" action="options.php">';
		echo '<input type="hidden" name="_wp_http_referer" value="' . esc_attr(admin_url('admin.php?page=' . urlencode(portico_webworks_admin_page_slug()) . '&tab=property&sub=' . urlencode($key))) . '" />';
		settings_fields('portico_webworks_property_profile');
		echo '<table class="form-table" role="presentation"><tbody>';
		do_settings_fields(portico_webworks_admin_page_slug(), $meta['section_id']);
		echo '</tbody></table>';
		submit_button('Save');
		echo '</form>';
		echo '</div>';
	}
	echo '</div>';

	echo '</div></div>';
	echo '<div class="pw-footer">';
	$link = 'https://porticowebworks.com/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=portico_webworks&utm_content=footer';
	echo '<a class="pw-footer-link" href="' . esc_url($link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
	echo '</div>';
	echo '</div>';
}

