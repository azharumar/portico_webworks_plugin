<?php
/**
 * Plugin Name: Portico Webworks Hotel Website Manager
 * Description: Portico Webworks plugin.
 * Version: 0.1.8
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 */

if (!defined('ABSPATH')) {
	exit;
}

define('PORTICO_WEBWORKS_PLUGIN_FILE', __FILE__);
define('PORTICO_WEBWORKS_VERSION', '0.1.8');

require_once __DIR__ . '/includes/admin-page.php';
require_once __DIR__ . '/includes/property-profile.php';
require_once __DIR__ . '/includes/admin-assets.php';