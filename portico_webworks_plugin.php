<?php
/**
 * Plugin Name: Portico Webworks Hotel Website Manager
 * Description: Portico Webworks plugin.
 * Version: 0.3.8
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 * License: Proprietary (All Rights Reserved) - Unauthorized use by other companies or for any purposes is prohibited without written permission.
 */

if (!defined('ABSPATH')) {
	exit;
}

define('PW_PLUGIN_FILE', __FILE__);
define('PW_VERSION', '0.3.8');

function pw_apply_install_defaults() {
	if (get_option('pw_install_defaults_applied', 0)) {
		return;
	}

	// Settings -> Media: "Organize my uploads into month- and year-based folders" (checked => uploads_use_yearmonth_folders = 1)
	update_option('uploads_use_yearmonth_folders', 0);

	update_option('pw_install_defaults_applied', 1);
}

register_activation_hook(PW_PLUGIN_FILE, 'pw_apply_install_defaults');

require_once __DIR__ . '/includes/admin-page.php';
require_once __DIR__ . '/includes/property-post-type.php';
require_once __DIR__ . '/includes/property-helpers.php';
require_once __DIR__ . '/includes/property-profile.php';
require_once __DIR__ . '/includes/admin-assets.php';
require_once __DIR__ . '/includes/dependencies.php';