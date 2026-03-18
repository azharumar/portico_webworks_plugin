<?php
/**
 * Plugin Name: Portico Webworks
 * Description: Portico Webworks plugin.
 * Version: 0.1.0
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 */

if (!defined('ABSPATH')) {
	exit;
}

add_action('admin_menu', function () {
	add_menu_page(
		'Portico Webworks',
		'Portico Webworks',
		'manage_options',
		'portico-webworks',
		'portico_webworks_render_root_page',
		'dashicons-admin-generic',
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
	echo '<h1>Portico Webworks</h1>';
	echo '<p>Select an option from the submenu.</p>';
	echo '</div>';
}

function portico_webworks_render_settings_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	echo '<div class="wrap">';
	echo '<h1>Settings</h1>';
	echo '<p>Portico Webworks settings will go here.</p>';
	echo '</div>';
}

