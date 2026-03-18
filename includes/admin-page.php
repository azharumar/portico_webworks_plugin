<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_admin_page_slug() {
	return 'portico-webworks';
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
		'Overview',
		'manage_options',
		pw_admin_page_slug(),
		'pw_render_root_page'
	);
}, 10);

add_action('admin_menu', function () {
	$cpt_slugs = [
		'pw_property',
		'pw_feature',
		'pw_room_type',
		'pw_restaurant',
		'pw_spa',
		'pw_meeting_room',
		'pw_amenity',
		'pw_policy',
	];

	foreach ($cpt_slugs as $cpt) {
		remove_submenu_page(pw_admin_page_slug(), 'post-new.php?post_type=' . $cpt);
	}
}, 999);

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

add_action('admin_init', function () {
	register_setting('pw_settings', 'pw_property_mode', array(
		'sanitize_callback' => function ($v) {
			return $v === 'multi' ? 'multi' : 'single';
		},
		'default' => 'single',
	));

	register_setting('pw_settings', 'pw_property_base', array(
		'sanitize_callback' => 'pw_sanitize_property_base',
	));
});

function pw_maybe_flush_property_rewrites($old_value, $value) {
	if ($old_value === $value) {
		return;
	}

	if (!function_exists('flush_rewrite_rules')) {
		return;
	}

	// This runs only when the plugin admin changes the options; flushing is needed
	// so rewrite rules update immediately after switching modes/bases.
	flush_rewrite_rules();
}

add_action('update_option_pw_property_mode', 'pw_maybe_flush_property_rewrites', 10, 2);
add_action('update_option_pw_property_base', 'pw_maybe_flush_property_rewrites', 10, 2);

function pw_render_root_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$base_tabs = array(
		'property' => 'Property Profile',
		'settings' => 'Settings',
		'about' => 'About',
	);
	$tabs = apply_filters('pw_admin_tabs', $base_tabs);
	$valid_keys = array_keys($tabs);

	$tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';
	if (!in_array($tab, $valid_keys, true)) {
		$tab = $valid_keys[0];
	}

	$mode = get_option('pw_property_mode', 'single');
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

	if (!in_array($tab, array('property', 'settings', 'about'), true)) {
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
		echo '<div class="pw-card">';
		echo '<div class="pw-card-head"><div class="pw-card-title">Settings</div></div>';
		echo '<div class="pw-card-body">';
		echo '<form method="post" action="options.php">';
		echo '<input type="hidden" name="_wp_http_referer" value="' . esc_attr(admin_url('admin.php?page=' . urlencode(pw_admin_page_slug()) . '&tab=settings')) . '" />';
		settings_fields('pw_settings');

		$current_mode = esc_attr($mode);
		$blog_public = get_option('blog_public', 1);
		$blog_public = is_numeric($blog_public) ? (int) $blog_public : 1;
		$indexing_on = $blog_public === 1;
		echo '<table class="form-table" role="presentation"><tbody>';
		echo '<tr>';
		echo '<th scope="row">Search Engine Indexing</th>';
		echo '<td>';
		echo '<strong>' . esc_html($indexing_on ? 'ON' : 'OFF') . '</strong>';
		echo '<p class="description">Controlled by WordPress Settings -> Reading -> "Discourage search engines from indexing this site".</p>';
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th scope="row">Property Mode</th>';
		echo '<td>';
		echo '<label style="margin-right:16px"><input type="radio" name="pw_property_mode" value="single"' . checked($current_mode, 'single', false) . ' /> Single Property</label>';
		echo '<label><input type="radio" name="pw_property_mode" value="multi"' . checked($current_mode, 'multi', false) . ' /> Multi-Property</label>';
		echo '<p class="description">Single: one property, no URL routing. Multi: multiple properties resolved by URL path.</p>';
		echo '</td>';
		echo '</tr>';
		echo '</tbody></table>';

		$base_val = esc_attr(get_option('pw_property_base', 'properties'));
		echo '<div id="pw-routing-section"' . ($current_mode !== 'multi' ? ' style="display:none"' : '') . '>';
		echo '<h3>Routing</h3>';
		echo '<table class="form-table" role="presentation"><tbody>';
		echo '<tr>';
		echo '<th scope="row"><label for="pw-property-base">Properties Base Path</label></th>';
		echo '<td>';
		echo '<input class="regular-text" id="pw-property-base" type="text" name="pw_property_base" value="' . $base_val . '" placeholder="properties" />';
		echo '<p class="description">URL prefix for properties, e.g. <code>/{base}/{slug}/...</code></p>';
		echo '</td>';
		echo '</tr>';
		echo '</tbody></table>';
		echo '</div>';

		submit_button('Save Settings');
		echo '</form>';
		echo '</div>';
		echo '</div>';

		echo '<script>';
		echo 'document.querySelectorAll(\'input[name="pw_property_mode"]\').forEach(function(r){';
		echo 'r.addEventListener("change",function(){';
		echo 'document.getElementById("pw-routing-section").style.display=this.value==="multi"?"":"none";';
		echo '});});';
		echo '</script>';

		echo '<div class="pw-footer">';
		echo '<a class="pw-footer-link" href="' . esc_url($footer_link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
		echo '</div>';
		do_action('pw_admin_notices');
		echo '</div>';
		return;
	}

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">Properties</div></div>';
	echo '<div class="pw-card-body">';

	$properties = pw_get_all_properties();
	$add_url = admin_url('post-new.php?post_type=pw_property');
	$hide_add = ($mode === 'single' && !empty($properties));

	if (!$hide_add) {
		echo '<p><a class="button button-primary" href="' . esc_url($add_url) . '">Add Property</a></p>';
	}

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
			$profile = pw_get_property_profile($p['id']);
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
	echo '<a class="pw-footer-link" href="' . esc_url($footer_link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
	echo '</div>';
	do_action('pw_admin_notices');
	echo '</div>';
}

