<?php
/**
 * Plugin Name: Portico Webworks Hotel Website Manager
 * Description: Portico Webworks plugin.
 * Version: 0.3.4
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 * License: Proprietary (All Rights Reserved) - Unauthorized use by other companies or for any purposes is prohibited without written permission.
 */

if (!defined('ABSPATH')) {
	exit;
}

define('PW_PLUGIN_FILE', __FILE__);
define('PW_VERSION', '0.3.4');

function pw_apply_install_defaults() {
	if (get_option('pw_install_defaults_applied', 0)) {
		return;
	}

	// Settings -> Reading: "Discourage search engines from indexing this site" (checked => blog_public = 0)
	update_option('blog_public', 0);

	// Settings -> Media: "Organize my uploads into month- and year-based folders" (checked => uploads_use_yearmonth_folders = 1)
	update_option('uploads_use_yearmonth_folders', 0);

	update_option('pw_install_defaults_applied', 1);
}

register_activation_hook(PW_PLUGIN_FILE, 'pw_apply_install_defaults');

function pw_pre_option_blog_public($value) {
	// Only affect front-end; keep WP admin/DB settings consistent.
	if (function_exists('is_admin') && is_admin()) {
		return $value;
	}

	$site_mode = get_option('pw_site_mode', 'development');
	$target_blog_public = $site_mode === 'production' ? 1 : 0;
	return (int) $target_blog_public;
}

add_filter('pre_option_blog_public', 'pw_pre_option_blog_public', 99);

require_once __DIR__ . '/includes/admin-page.php';
require_once __DIR__ . '/includes/property-post-type.php';
require_once __DIR__ . '/includes/property-helpers.php';
require_once __DIR__ . '/includes/property-profile.php';
require_once __DIR__ . '/includes/admin-assets.php';
require_once __DIR__ . '/includes/dependencies.php';