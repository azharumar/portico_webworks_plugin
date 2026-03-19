<?php
/**
 * Plugin Name: Portico Webworks Hotel Website Manager
 * Description: Portico Webworks plugin.
 * Version: 0.7.2
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 * License: Proprietary (All Rights Reserved) - Unauthorized use by other companies or for any purposes is prohibited without written permission.
 */

if (!defined('ABSPATH')) {
	exit;
}

define('PW_PLUGIN_FILE', __FILE__);
define('PW_VERSION', '0.7.2');

function pw_apply_install_defaults() {
	if (get_option('pw_install_defaults_applied', 0)) {
		return;
	}

	// Settings -> Media: "Organize my uploads into month- and year-based folders" (checked => uploads_use_yearmonth_folders = 1)
	update_option('uploads_use_yearmonth_folders', 0);

	update_option('pw_install_defaults_applied', 1);
	update_option( 'pw_seed_policy_types', 1 );
}

register_activation_hook(PW_PLUGIN_FILE, 'pw_apply_install_defaults');

add_action( 'init', 'pw_migrate_default_template', 5 );

function pw_migrate_default_template() {
	if ( get_option( 'pw_default_template_migrated', 0 ) ) {
		return;
	}
	if ( ! function_exists( 'pw_get_setting' ) ) {
		return;
	}
	$plugin_val = pw_get_setting( 'pw_default_template', '' );
	if ( $plugin_val !== '' ) {
		update_option( 'pw_default_template_migrated', 1 );
		return;
	}
	$props = get_posts( [
		'post_type'      => 'pw_property',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	] );
	$val = '';
	foreach ( $props as $id ) {
		$v = get_post_meta( $id, '_pw_default_template', true );
		if ( is_string( $v ) && trim( $v ) !== '' ) {
			$val = trim( $v );
			break;
		}
	}
	if ( $val !== '' ) {
		$opts = get_option( 'pw_settings', [] );
		$opts = is_array( $opts ) ? $opts : [];
		$opts['pw_default_template'] = $val;
		update_option( 'pw_settings', $opts );
		update_option( 'pw_default_template', $val );
	}
	update_option( 'pw_default_template_migrated', 1 );
}

add_action( 'init', function() {
	if ( ! get_option( 'pw_seed_policy_types', 0 ) ) {
		return;
	}
	delete_option( 'pw_seed_policy_types' );
	$policy_types = [ 'Check-in', 'Check-out', 'Cancellation', 'Pet', 'Child', 'Payment', 'Smoking' ];
	foreach ( $policy_types as $term ) {
		if ( ! term_exists( $term, 'pw_policy_type' ) ) {
			wp_insert_term( $term, 'pw_policy_type' );
		}
	}
}, 999 );

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/cmb2/cmb2/init.php';
add_filter( 'cmb2_menus', '__return_empty_array' );
require_once __DIR__ . '/includes/cmb2-rrule-field.php';

require_once __DIR__ . '/includes/admin-page.php';
require_once __DIR__ . '/includes/currency-data.php';
require_once __DIR__ . '/includes/property-post-type.php';
require_once __DIR__ . '/includes/child-post-types.php';
require_once __DIR__ . '/includes/child-post-type-metaboxes.php';
require_once __DIR__ . '/includes/import-export.php';
require_once __DIR__ . '/includes/property-helpers.php';
require_once __DIR__ . '/includes/backward-compat.php';
require_once __DIR__ . '/includes/property-profile.php';
require_once __DIR__ . '/includes/admin-assets.php';
require_once __DIR__ . '/includes/seo-compatibility.php';
require_once __DIR__ . '/includes/dependencies.php';