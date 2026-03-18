<?php
/**
 * Plugin Name: Portico Webworks Hotel Website Manager
 * Description: Portico Webworks plugin.
 * Version: 0.3.1
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 * License: Proprietary (All Rights Reserved) - Unauthorized use by other companies or for any purposes is prohibited without written permission.
 */

if (!defined('ABSPATH')) {
	exit;
}

define('PW_PLUGIN_FILE', __FILE__);
define('PW_VERSION', '0.3.1');

require_once __DIR__ . '/includes/admin-page.php';
require_once __DIR__ . '/includes/property-post-type.php';
require_once __DIR__ . '/includes/property-helpers.php';
require_once __DIR__ . '/includes/property-profile.php';
require_once __DIR__ . '/includes/admin-assets.php';
require_once __DIR__ . '/includes/dependencies.php';