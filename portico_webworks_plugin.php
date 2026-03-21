<?php
/**
 * Plugin Name: Portico Webworks Hotel Website Manager
 * Description: Portico Webworks plugin.
 * Version: 0.8.5
 * Requires at least: 6.9.4
 * Requires PHP: 8.3
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 * License: Proprietary (All Rights Reserved) - Unauthorized use by other companies or for any purposes is prohibited without written permission.
 */

if (!defined('ABSPATH')) {
	exit;
}

define('PW_PLUGIN_FILE', __FILE__);
define('PW_VERSION', '0.8.5');
define('PW_FACT_SHEET_CONTENT_VERSION', 2);

require_once __DIR__ . '/includes/fact-sheet-page-content.php';

function pw_apply_install_defaults() {
	if (get_option('pw_install_defaults_applied', 0)) {
		return;
	}

	// Settings -> Media: "Organize my uploads into month- and year-based folders" (checked => uploads_use_yearmonth_folders = 1)
	update_option('uploads_use_yearmonth_folders', 0);

	update_option('pw_install_defaults_applied', 1);
	update_option( 'pw_seed_taxonomies', 1 );
}

function pw_ensure_fact_sheet_page() {
	$markup = pw_fact_sheet_page_block_markup();
	$existing = get_posts(
		array(
			'post_type'      => 'page',
			'name'           => 'fact-sheet',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $existing ) ) {
		$page_id = (int) $existing[0];
		$refresh = false;
		$post    = get_post( $page_id );
		if ( $post && is_string( $post->post_content ) ) {
			if ( strpos( $post->post_content, '[pw_fact_sheet]' ) !== false ) {
				$refresh = true;
			}
		}
		if ( (int) get_option( 'pw_fact_sheet_content_version', 0 ) < PW_FACT_SHEET_CONTENT_VERSION ) {
			$refresh = true;
		}
		if ( $refresh ) {
			wp_update_post(
				array(
					'ID'           => $page_id,
					'post_content' => $markup,
				)
			);
		}
		update_option( 'pw_fact_sheet_page_id', $page_id );
		update_option( 'pw_fact_sheet_content_version', PW_FACT_SHEET_CONTENT_VERSION );
		return;
	}

	$page_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Property Fact Sheet',
			'post_name'    => 'fact-sheet',
			'post_content' => $markup,
		),
		true
	);
	if ( ! is_wp_error( $page_id ) && $page_id ) {
		update_option( 'pw_fact_sheet_page_id', (int) $page_id );
		update_option( 'pw_fact_sheet_content_version', PW_FACT_SHEET_CONTENT_VERSION );
	}
}

function pw_maybe_sync_fact_sheet_page() {
	if ( (int) get_option( 'pw_fact_sheet_content_version', 0 ) >= PW_FACT_SHEET_CONTENT_VERSION ) {
		$pid = (int) get_option( 'pw_fact_sheet_page_id', 0 );
		if ( $pid > 0 ) {
			$p = get_post( $pid );
			if ( $p && is_string( $p->post_content ) && strpos( $p->post_content, '[pw_fact_sheet]' ) !== false ) {
				pw_ensure_fact_sheet_page();
			}
		}
		return;
	}
	pw_ensure_fact_sheet_page();
}

add_action( 'plugins_loaded', 'pw_maybe_sync_fact_sheet_page', 30 );

function pw_plugin_activation() {
	pw_apply_install_defaults();
	pw_ensure_fact_sheet_page();
	set_transient('pw_activation_settings_notice', 1, 300);
}

register_activation_hook(PW_PLUGIN_FILE, 'pw_plugin_activation');

add_action( 'init', function() {
	if ( ! get_option( 'pw_seed_taxonomies', 0 ) ) {
		return;
	}
	delete_option( 'pw_seed_taxonomies' );
	pw_seed_taxonomy_terms();
	update_option( 'pw_taxonomy_seed_prompt_status', 'auto_completed' );
}, 999 );

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/cmb2/cmb2/init.php';
add_filter( 'cmb2_menus', '__return_empty_array' );
require_once __DIR__ . '/includes/cmb2-rrule-field.php';

require_once __DIR__ . '/includes/github-plugin-update.php';
require_once __DIR__ . '/includes/admin-page.php';
require_once __DIR__ . '/includes/currency-data.php';
require_once __DIR__ . '/includes/property-post-type.php';
require_once __DIR__ . '/includes/property-facet-definitions.php';
require_once __DIR__ . '/includes/child-post-types.php';
require_once __DIR__ . '/includes/taxonomy-seeds.php';
require_once __DIR__ . '/includes/child-post-type-metaboxes.php';
require_once __DIR__ . '/includes/import-export.php';
require_once __DIR__ . '/includes/sample-data-meta.php';
require_once __DIR__ . '/includes/sample-data.php';
require_once __DIR__ . '/includes/property-helpers.php';
require_once __DIR__ . '/includes/fact-sheet.php';
require_once __DIR__ . '/includes/backward-compat.php';
require_once __DIR__ . '/includes/property-profile.php';
require_once __DIR__ . '/includes/admin-assets.php';
require_once __DIR__ . '/includes/admin-branding.php';
require_once __DIR__ . '/includes/seo-compatibility.php';
require_once __DIR__ . '/includes/dependencies.php';