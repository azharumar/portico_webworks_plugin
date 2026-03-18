<?php
/**
 * Plugin Name: Portico Webworks
 * Description: Portico Webworks plugin.
 * Version: 0.1.2
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 */

if (!defined('ABSPATH')) {
	exit;
}

function portico_webworks_logo_url() {
	return plugins_url('logo.svg', __FILE__);
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
		'Settings',
		'Settings',
		'manage_options',
		'portico-webworks-settings',
		'portico_webworks_render_settings_page'
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

function portico_webworks_render_settings_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	echo '<div class="wrap">';
	echo '<h1><img alt="" src="' . esc_url(portico_webworks_logo_url()) . '" style="height: 28px; width: 28px; vertical-align: middle; margin-right: 8px;" />Settings</h1>';
	echo '<p>Portico Webworks settings will go here.</p>';
	echo '</div>';
}

