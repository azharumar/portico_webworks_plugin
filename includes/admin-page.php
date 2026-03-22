<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_admin_page_slug() {
	return 'portico-webworks';
}

function pw_admin_settings_url() {
	return admin_url('admin.php?page=' . rawurlencode(pw_admin_page_slug()) . '&tab=settings');
}

function pw_admin_permalinks_url() {
	return admin_url( 'admin.php?page=' . rawurlencode( pw_admin_page_slug() ) . '&tab=permalinks' );
}

/**
 * Keep General (settings) first, Permalinks second, About last; other tabs stay in filter order between them.
 */
function pw_order_admin_tabs( $tabs ) {
	if ( ! is_array( $tabs ) || $tabs === [] ) {
		return $tabs;
	}
	$settings_key   = 'settings';
	$permalinks_key = 'permalinks';
	$about_key      = 'about';
	$general        = [];
	$permalinks     = [];
	$about          = [];
	if ( isset( $tabs[ $settings_key ] ) ) {
		$general[ $settings_key ] = $tabs[ $settings_key ];
		unset( $tabs[ $settings_key ] );
	}
	if ( isset( $tabs[ $permalinks_key ] ) ) {
		$permalinks[ $permalinks_key ] = $tabs[ $permalinks_key ];
		unset( $tabs[ $permalinks_key ] );
	}
	if ( isset( $tabs[ $about_key ] ) ) {
		$about[ $about_key ] = $tabs[ $about_key ];
		unset( $tabs[ $about_key ] );
	}
	return array_merge( $general, $permalinks, $tabs, $about );
}

add_filter('plugin_action_links_' . plugin_basename(PW_PLUGIN_FILE), function ($links) {
	if (!current_user_can('manage_options')) {
		return $links;
	}
	$settings = '<a href="' . esc_url(pw_admin_settings_url()) . '">' . esc_html('Settings') . '</a>';
	array_unshift($links, $settings);
	return $links;
});

add_filter('install_plugin_complete_actions', function ($install_actions, $api, $plugin_file) {
	if (plugin_basename(PW_PLUGIN_FILE) !== $plugin_file || !current_user_can('manage_options')) {
		return $install_actions;
	}
	$install_actions['pw_plugin_settings'] = '<a href="' . esc_url(pw_admin_settings_url()) . '" target="_parent">' . esc_html('Plugin settings') . '</a>';
	return $install_actions;
}, 10, 3);

add_action('admin_notices', function () {
	if (!current_user_can('manage_options') || !get_transient('pw_activation_settings_notice')) {
		return;
	}
	delete_transient('pw_activation_settings_notice');
	$url = pw_admin_settings_url();
	echo '<div class="notice notice-success is-dismissible"><p>';
	echo esc_html('Portico Webworks Hotel Website Manager activated.') . ' ';
	echo '<a href="' . esc_url($url) . '">' . esc_html('Open plugin settings') . '</a>';
	echo '</p></div>';
});

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

function pw_sanitize_property_base( $value, $field_args = null, $field = null ) {
	$value = is_string($value) ? trim($value) : '';
	$value = trim($value, '/');
	$value = sanitize_title($value);
	return $value !== '' ? $value : 'properties';
}

/**
 * Full pw_settings array from option with defaults for missing keys.
 *
 * @return array<string, mixed>
 */
function pw_get_merged_pw_settings() {
	$raw = get_option( 'pw_settings', [] );
	$raw = is_array( $raw ) ? $raw : [];
	$defaults = [
		'pw_property_mode'            => 'single',
		'pw_property_base'            => 'properties',
		'pw_default_property_id'      => 0,
		'pw_default_template'         => '',
		'pw_github_releases_url'      => '',
		'pw_permalink_base_source'    => 'fixed',
		'pw_permalink_base_fixed'     => '',
		'pw_permalink_slug_source'    => 'post_name',
		'pw_permalink_subpaths'       => [],
	];
	$out = wp_parse_args( $raw, $defaults );
	if ( empty( $raw['pw_permalink_base_fixed'] ) && ! empty( $out['pw_property_base'] ) ) {
		$out['pw_permalink_base_fixed'] = $out['pw_property_base'];
	}
	if ( empty( $out['pw_permalink_base_fixed'] ) ) {
		$out['pw_permalink_base_fixed'] = 'properties';
	}
	if ( ! is_array( $out['pw_permalink_subpaths'] ) ) {
		$out['pw_permalink_subpaths'] = [];
	}
	$fixed = pw_sanitize_property_base( (string) $out['pw_permalink_base_fixed'] );
	$out['pw_permalink_base_fixed'] = $fixed;
	$out['pw_property_base']       = $fixed;
	return $out;
}

function pw_get_setting($key, $default = '') {
	$opts = pw_get_merged_pw_settings();
	if (array_key_exists($key, $opts)) {
		return $opts[$key];
	}
	return get_option($key, $default);
}

add_action('admin_notices', function () {
	if (!current_user_can('manage_options')) {
		return;
	}
	if (get_transient('pw_settings_notice_default_property')) {
		delete_transient('pw_settings_notice_default_property');
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html('Default property was cleared because the selected post is not a published property.') . '</p></div>';
	}
	$mode = pw_get_setting('pw_property_mode', 'single');
	if ($mode !== 'single') {
		return;
	}
	$pid = (int) pw_get_setting('pw_default_property_id', 0);
	if ($pid > 0) {
		return;
	}
	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	if ($screen && $screen->id === 'toplevel_page_' . pw_admin_page_slug()) {
		$url = pw_admin_settings_url();
		echo '<div class="notice notice-warning is-dismissible"><p>';
		echo esc_html('Single-property mode requires a default property. Select one under General settings.');
		echo ' <a href="' . esc_url($url) . '">' . esc_html('Open settings') . '</a>';
		echo '</p></div>';
	}
});

function pw_cmb2_published_property_options( $field = null ) {
	$opts = array();
	foreach ( pw_get_all_properties() as $row ) {
		$id = isset( $row['id'] ) ? (int) $row['id'] : 0;
		if ( $id <= 0 || get_post_status( $id ) !== 'publish' ) {
			continue;
		}
		$opts[ (string) $id ] = isset( $row['name'] ) ? (string) $row['name'] : '#' . $id;
	}
	return $opts;
}

function pw_handle_settings_save() {
	if (
		!isset($_POST['pw_settings_nonce']) ||
		!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pw_settings_nonce'])), 'pw_save_settings') ||
		!current_user_can('manage_options')
	) {
		return;
	}

	$existing = pw_get_merged_pw_settings();
	$mode     = isset( $_POST['pw_property_mode'] ) && $_POST['pw_property_mode'] === 'multi' ? 'multi' : 'single';

	$settings                          = $existing;
	$settings['pw_property_mode']      = $mode;
	$settings['pw_default_template']   = sanitize_text_field( wp_unslash( $_POST['pw_default_template'] ?? '' ) );
	$settings['pw_github_releases_url'] = pw_sanitize_github_releases_url( wp_unslash( $_POST['pw_github_releases_url'] ?? '' ) );
	$settings['pw_default_property_id'] = $mode === 'single' ? (int) ( $_POST['pw_default_property_id'] ?? 0 ) : 0;

	if ( $mode === 'single' && $settings['pw_default_property_id'] > 0 ) {
		$pid = $settings['pw_default_property_id'];
		if ( get_post_type( $pid ) !== 'pw_property' || get_post_status( $pid ) !== 'publish' ) {
			$settings['pw_default_property_id'] = 0;
			set_transient( 'pw_settings_notice_default_property', 1, 60 );
		}
	}

	update_option( 'pw_settings', $settings );

	$flush = ( $mode !== $existing['pw_property_mode'] );
	if ( $flush ) {
		flush_rewrite_rules();
	}

	wp_safe_redirect(add_query_arg('settings-updated', 'true', wp_get_referer() ?: pw_admin_settings_url()));
	exit;
}
add_action('admin_post_pw_save_settings', 'pw_handle_settings_save');

function pw_render_root_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$base_tabs = array(
		'settings' => 'General',
		'about'    => 'About',
	);
	$tabs       = apply_filters( 'pw_admin_tabs', $base_tabs );
	$tabs       = pw_order_admin_tabs( $tabs );
	$valid_keys = array_keys( $tabs );

	$tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';
	if (!in_array($tab, $valid_keys, true)) {
		$tab = $valid_keys[0];
	}

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

	if ( ! in_array( $tab, array( 'settings', 'about' ), true ) ) {
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
		$reading_url = admin_url('options-reading.php');
		echo '<p class="description">Controlled by WordPress Settings → Reading → "Discourage search engines from indexing this site". ';
		echo '<a href="' . esc_url($reading_url) . '">Open Reading settings</a></p>';
		echo '</td></tr>';
		echo '</tbody></table>';

		$settings_mode          = pw_get_setting('pw_property_mode', 'single');
		$default_property_id    = (int) pw_get_setting('pw_default_property_id', 0);
		$property_select_opts   = pw_cmb2_published_property_options();

		echo '<form class="pw-settings-form" action="' . esc_url(admin_url('admin-post.php')) . '" method="post" id="pw_settings">';
		echo '<input type="hidden" name="action" value="pw_save_settings" />';
		wp_nonce_field('pw_save_settings', 'pw_settings_nonce');
		echo '<table class="form-table" role="presentation"><tbody>';

		echo '<tr><th scope="row">' . esc_html('Property Mode') . '</th><td>';
		echo '<fieldset><label><input type="radio" name="pw_property_mode" value="single"' . checked($settings_mode, 'single', false) . ' /> ' . esc_html('Single Property') . '</label><br />';
		echo '<label><input type="radio" name="pw_property_mode" value="multi"' . checked($settings_mode, 'multi', false) . ' /> ' . esc_html('Multi-Property') . '</label></fieldset>';
		echo '</td></tr>';

		echo '<tr class="pw-default-property-row"><th scope="row"><label for="pw_default_property_id">' . esc_html('Default property') . '</label></th><td>';
		echo '<select name="pw_default_property_id" id="pw_default_property_id">';
		echo '<option value="0"' . selected($default_property_id, 0, false) . '>' . esc_html('— Select property —') . '</option>';
		foreach ($property_select_opts as $opt_id => $opt_label) {
			$oid = (int) $opt_id;
			echo '<option value="' . esc_attr((string) $oid) . '"' . selected($default_property_id, $oid, false) . '>' . esc_html($opt_label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html('Used as the site-wide property context in Single Property mode (required).') . '</p>';
		echo '</td></tr>';

		echo '<tr><th scope="row">' . esc_html('Property URLs') . '</th><td>';
		echo '<p class="description">' . esc_html('Multi-property URL base, slug source, and sub-paths (e.g. fact sheet) are configured under the Permalinks tab.') . ' ';
		echo '<a href="' . esc_url( pw_admin_permalinks_url() ) . '">' . esc_html('Open Permalinks') . '</a></p>';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="pw_default_template">' . esc_html('Default Template') . '</label></th><td>';
		echo '<input type="text" class="regular-text" name="pw_default_template" id="pw_default_template" value="' . esc_attr((string) pw_get_setting('pw_default_template')) . '" placeholder="' . esc_attr('e.g. default') . '" />';
		echo '<p class="description">' . esc_html('Template slug used for front-end rendering. Applied across all properties.') . '</p>';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="pw_github_releases_url">' . esc_html('GitHub releases URL') . '</label></th><td>';
		echo '<input type="text" class="large-text" name="pw_github_releases_url" id="pw_github_releases_url" value="' . esc_attr((string) pw_get_setting('pw_github_releases_url')) . '" placeholder="' . esc_attr('https://github.com/owner/repo/releases') . '" />';
		echo '<p class="description">' . esc_html('Repository releases page, e.g. https://github.com/owner/repo/releases — latest release must include portico_webworks_plugin.zip.') . '</p>';
		echo '</td></tr>';

		echo '</tbody></table>';
		submit_button(esc_attr__('Save Settings', 'portico-webworks'), 'primary', 'pw-save-settings');
		echo '</form>';

		if ( current_user_can( 'update_plugins' ) ) {
			$gh_url = pw_get_setting( 'pw_github_releases_url', '' );
			echo '<hr class="pw-settings-divider" />';
			echo '<h2 class="title" style="margin-top:1em;">' . esc_html__( 'Update from GitHub', 'portico-webworks' ) . '</h2>';
			if ( is_string( $gh_url ) && $gh_url !== '' ) {
				echo '<p class="description">' . esc_html__( 'Configured repository:', 'portico-webworks' ) . ' <code>' . esc_html( $gh_url ) . '</code></p>';
			} else {
				echo '<p class="description">' . esc_html__( 'Save a GitHub releases URL above to enable one-click updates.', 'portico-webworks' ) . '</p>';
			}
			$upd_url = admin_url( 'admin-post.php' );
			echo '<form method="post" action="' . esc_url( $upd_url ) . '" style="margin-top:0.75em;">';
			echo '<input type="hidden" name="action" value="pw_github_plugin_update" />';
			wp_nonce_field( 'pw_github_plugin_update' );
			$btn_attrs = array();
			if ( ! is_string( $gh_url ) || $gh_url === '' ) {
				$btn_attrs['disabled'] = 'disabled';
			}
			submit_button( esc_attr__( 'Update from GitHub', 'portico-webworks' ), 'secondary', 'pw-github-update', false, $btn_attrs );
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

