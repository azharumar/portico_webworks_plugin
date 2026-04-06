<?php
/**
 * Plugin Name: Portico Webworks Hotel Website Manager
 * Description: Installs and activates the Portico Webworks theme and plugin dependencies.
 * Version: 0.9.0
 * Requires at least: 6.9.4
 * Requires PHP: 8.3
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 * License: Proprietary (All Rights Reserved) - Unauthorized use by other companies or for any purposes is prohibited without written permission.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PW_PLUGIN_FILE', __FILE__ );
define( 'PW_PLUGIN_DIR', plugin_dir_path( PW_PLUGIN_FILE ) );
define( 'PW_VERSION', '0.9.0' );

function pw_plugin_activation() {
	set_transient( 'pw_activation_settings_notice', 1, 300 );
}

register_activation_hook( PW_PLUGIN_FILE, 'pw_plugin_activation' );

require_once PW_PLUGIN_DIR . 'includes/admin-page.php';
require_once PW_PLUGIN_DIR . 'includes/admin-shell-assets.php';
require_once PW_PLUGIN_DIR . 'includes/dependencies.php';
