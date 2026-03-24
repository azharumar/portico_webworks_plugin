<?php
/**
 * Plugin Name: Portico Webworks Hotel Website Manager
 * Description: Portico Webworks plugin.
 * Version: 0.8.34
 * Requires at least: 6.9.4
 * Requires PHP: 8.3
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 * License: Proprietary (All Rights Reserved) - Unauthorized use by other companies or for any purposes is prohibited without written permission.
 */

if (!defined('ABSPATH')) {
	exit;
}

if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	define( 'WP_POST_REVISIONS', true );
}

$pw_fatal_log = __DIR__ . '/includes/pw-fatal-log.php';
if ( is_readable( $pw_fatal_log ) ) {
	require_once $pw_fatal_log;
}

define('PW_PLUGIN_FILE', __FILE__);
define('PW_VERSION', '0.8.34');

function pw_apply_install_defaults() {
	if (get_option('pw_install_defaults_applied', 0)) {
		return;
	}

	// Settings -> Media: "Organize my uploads into month- and year-based folders" (checked => uploads_use_yearmonth_folders = 1)
	update_option('uploads_use_yearmonth_folders', 0);

	// Settings -> General: timezone (Kolkata)
	update_option( 'timezone_string', 'Asia/Kolkata' );

	update_option('pw_install_defaults_applied', 1);
	update_option( 'pw_seed_taxonomies', 1 );
}

function pw_plugin_activation() {
	pw_apply_install_defaults();
	set_transient('pw_activation_settings_notice', 1, 300);
}

register_activation_hook(PW_PLUGIN_FILE, 'pw_plugin_activation');

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

$pw_cmb2_init = plugin_dir_path( __FILE__ ) . 'vendor/cmb2/cmb2/init.php';
if ( ! is_readable( $pw_cmb2_init ) ) {
	add_action(
		'admin_notices',
		static function () {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			echo '<div class="notice notice-error"><p>';
			echo esc_html( 'Portico Webworks: bundled CMB2 is missing (vendor/cmb2). Reinstall the plugin from the release ZIP or run composer install in the plugin folder.' );
			echo '</p></div>';
		}
	);
	return;
}

require_once $pw_cmb2_init;
add_filter( 'cmb2_menus', '__return_empty_array' );
require_once __DIR__ . '/includes/cmb2-rrule-field.php';

require_once __DIR__ . '/includes/github-plugin-update.php';
require_once __DIR__ . '/includes/permalink-config.php';
require_once __DIR__ . '/includes/reserved-slugs.php';
require_once __DIR__ . '/includes/property-helpers.php';
require_once __DIR__ . '/includes/page-installer.php';
require_once __DIR__ . '/includes/admin-page.php';

add_action( 'transition_post_status', 'pw_on_property_published', 10, 3 );
require_once __DIR__ . '/includes/currency-data.php';
require_once __DIR__ . '/includes/property-post-type.php';
require_once __DIR__ . '/includes/property-rewrites.php';
require_once __DIR__ . '/includes/gp-elements-compat.php';
require_once __DIR__ . '/includes/property-facet-definitions.php';
require_once __DIR__ . '/includes/child-post-types.php';
require_once __DIR__ . '/includes/contact-resolver.php';
require_once __DIR__ . '/includes/contact-post-type.php';
require_once __DIR__ . '/includes/admin-list-columns.php';
require_once __DIR__ . '/includes/contact-metabox.php';
require_once __DIR__ . '/includes/taxonomy-seeds.php';
require_once __DIR__ . '/includes/child-post-type-metaboxes.php';
require_once __DIR__ . '/includes/import-export.php';
require_once __DIR__ . '/includes/sample-data-meta.php';
require_once __DIR__ . '/includes/sample-data.php';
require_once __DIR__ . '/includes/admin-permalinks.php';
require_once __DIR__ . '/includes/backward-compat.php';
require_once __DIR__ . '/includes/property-profile.php';
require_once __DIR__ . '/includes/admin-assets.php';
require_once __DIR__ . '/includes/admin-branding.php';
require_once __DIR__ . '/includes/seo-compatibility.php';
require_once __DIR__ . '/includes/dependencies.php';

add_action(
	'init',
	static function () {
		if ( ! get_option( 'pw_seed_taxonomies', 0 ) ) {
			return;
		}
		delete_option( 'pw_seed_taxonomies' );
		pw_seed_taxonomy_terms();
		update_option( 'pw_taxonomy_seed_prompt_status', 'auto_completed' );
	},
	999
);
