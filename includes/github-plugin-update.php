<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PW_GITHUB_PLUGIN_RELEASE_ZIP = 'portico_webworks_plugin.zip';

/**
 * Normalize and validate a GitHub releases page URL; empty if invalid.
 */
function pw_sanitize_github_releases_url( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( $value === '' ) {
		return '';
	}
	$raw = esc_url_raw( $value );
	if ( $raw === '' ) {
		return '';
	}
	$parts = wp_parse_url( $raw );
	if ( ! is_array( $parts ) || empty( $parts['host'] ) || empty( $parts['path'] ) ) {
		return '';
	}
	$host = strtolower( $parts['host'] );
	if ( $host === 'www.github.com' ) {
		$host = 'github.com';
	}
	if ( $host !== 'github.com' ) {
		return '';
	}
	$parsed = pw_parse_github_repo_from_path( $parts['path'] );
	if ( ! is_array( $parsed ) ) {
		return '';
	}
	return 'https://github.com/' . $parsed['owner'] . '/' . $parsed['repo'] . '/releases';
}

/**
 * @return array{owner:string,repo:string}|null
 */
function pw_parse_github_repo_from_path( $path ) {
	if ( ! is_string( $path ) || $path === '' ) {
		return null;
	}
	$path = '/' . trim( $path, '/' );
	if ( ! preg_match( '#^/([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+)(?:/releases(?:/latest)?)?$#', $path, $m ) ) {
		return null;
	}
	return array(
		'owner' => $m[1],
		'repo'  => $m[2],
	);
}

/**
 * @return array{owner:string,repo:string}|null
 */
function pw_parse_github_repo_from_releases_url( $url ) {
	$url = is_string( $url ) ? trim( $url ) : '';
	if ( $url === '' ) {
		return null;
	}
	$parts = wp_parse_url( $url );
	if ( ! is_array( $parts ) || empty( $parts['path'] ) ) {
		return null;
	}
	return pw_parse_github_repo_from_path( $parts['path'] );
}

function pw_github_normalize_version( $v ) {
	$v = is_string( $v ) ? trim( $v ) : '';
	if ( $v === '' ) {
		return '';
	}
	return ltrim( strtolower( $v ), 'v' );
}

/**
 * @return true if equal after normalization
 */
function pw_github_versions_equal( $a, $b ) {
	return pw_github_normalize_version( $a ) === pw_github_normalize_version( $b );
}

/**
 * @return array{zip_url:string,tag_name:string}|WP_Error
 */
function pw_github_get_latest_release_package( $releases_url ) {
	$repo = pw_parse_github_repo_from_releases_url( $releases_url );
	if ( ! is_array( $repo ) ) {
		return new WP_Error( 'pw_github_bad_url', __( 'Set a valid GitHub repository URL (e.g. https://github.com/owner/repo/releases).', 'portico-webworks' ) );
	}
	$api = sprintf(
		'https://api.github.com/repos/%s/%s/releases/latest',
		rawurlencode( $repo['owner'] ),
		rawurlencode( $repo['repo'] )
	);
	$response = wp_remote_get(
		$api,
		array(
			'timeout' => 20,
			'headers' => array(
				'Accept'     => 'application/vnd.github+json',
				'User-Agent' => 'PorticoWebworks-WordPress-Plugin/' . ( defined( 'PW_VERSION' ) ? PW_VERSION : '0' ),
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );
	if ( $code !== 200 ) {
		return new WP_Error(
			'pw_github_api',
			sprintf(
				/* translators: %s: HTTP status code */
				__( 'GitHub API error (HTTP %s).', 'portico-webworks' ),
				(string) $code
			)
		);
	}
	$data = json_decode( $body, true );
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'pw_github_json', __( 'Invalid response from GitHub.', 'portico-webworks' ) );
	}
	$tag = isset( $data['tag_name'] ) ? (string) $data['tag_name'] : '';
	$assets = isset( $data['assets'] ) && is_array( $data['assets'] ) ? $data['assets'] : array();
	$want  = strtolower( PW_GITHUB_PLUGIN_RELEASE_ZIP );
	$zip_url = '';
	foreach ( $assets as $asset ) {
		if ( ! is_array( $asset ) || empty( $asset['name'] ) || empty( $asset['browser_download_url'] ) ) {
			continue;
		}
		if ( strtolower( (string) $asset['name'] ) === $want ) {
			$zip_url = (string) $asset['browser_download_url'];
			break;
		}
	}
	if ( $zip_url === '' ) {
		return new WP_Error(
			'pw_github_no_zip',
			sprintf(
				/* translators: %s: zip filename */
				__( 'Latest release has no %s asset. Add it via your release workflow.', 'portico-webworks' ),
				PW_GITHUB_PLUGIN_RELEASE_ZIP
			)
		);
	}
	return array(
		'zip_url'   => $zip_url,
		'tag_name'  => $tag,
	);
}

/**
 * @return true|WP_Error
 */
function pw_github_run_plugin_update_from_zip_url( $zip_url ) {
	if ( ! defined( 'PW_PLUGIN_FILE' ) ) {
		return new WP_Error( 'pw_github_internal', __( 'Plugin bootstrap missing.', 'portico-webworks' ) );
	}
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	$plugin_file = plugin_basename( PW_PLUGIN_FILE );

	$skin = new Automatic_Upgrader_Skin();
	$upgrader = new Plugin_Upgrader( $skin );
	$result = $upgrader->run(
		array(
			'package'           => $zip_url,
			'destination'       => WP_PLUGIN_DIR,
			'clear_destination' => true,
			'clear_working'     => true,
			'hook_extra'        => array(
				'plugin' => $plugin_file,
			),
		)
	);

	if ( is_wp_error( $result ) ) {
		return $result;
	}
	if ( $result === false ) {
		$errors = $skin->get_errors();
		if ( is_wp_error( $errors ) && $errors->has_errors() ) {
			return $errors;
		}
		return new WP_Error( 'pw_github_upgrade_failed', __( 'Plugin update failed.', 'portico-webworks' ) );
	}
	return true;
}

add_action( 'admin_post_pw_github_plugin_update', 'pw_handle_admin_post_github_plugin_update' );

function pw_handle_admin_post_github_plugin_update() {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'pw_github_plugin_update' ) ) {
		wp_die( __( 'Invalid request.', 'portico-webworks' ), '', array( 'response' => 403 ) );
	}
	if ( ! current_user_can( 'update_plugins' ) || ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You are not allowed to update plugins.', 'portico-webworks' ), '', array( 'response' => 403 ) );
	}

	$releases_url = pw_get_setting( 'pw_github_releases_url', '' );
	if ( ! is_string( $releases_url ) || $releases_url === '' ) {
		set_transient( 'pw_github_ud_msg_' . get_current_user_id(), __( 'Save a GitHub releases URL in settings first.', 'portico-webworks' ), 60 );
		wp_safe_redirect( add_query_arg( 'pw_github_upd', 'err', pw_admin_settings_url() ) );
		exit;
	}

	$info = pw_github_get_latest_release_package( $releases_url );
	if ( is_wp_error( $info ) ) {
		set_transient( 'pw_github_ud_msg_' . get_current_user_id(), $info->get_error_message(), 60 );
		wp_safe_redirect( add_query_arg( 'pw_github_upd', 'err', pw_admin_settings_url() ) );
		exit;
	}

	if ( pw_github_versions_equal( $info['tag_name'], PW_VERSION ) ) {
		wp_safe_redirect( add_query_arg( 'pw_github_upd', 'uptodate', pw_admin_settings_url() ) );
		exit;
	}

	$run = pw_github_run_plugin_update_from_zip_url( $info['zip_url'] );
	if ( is_wp_error( $run ) ) {
		set_transient( 'pw_github_ud_msg_' . get_current_user_id(), $run->get_error_message(), 60 );
		wp_safe_redirect( add_query_arg( 'pw_github_upd', 'err', pw_admin_settings_url() ) );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'pw_github_upd', 'ok', pw_admin_settings_url() ) );
	exit;
}

add_action( 'admin_notices', 'pw_github_plugin_update_admin_notices' );

function pw_github_plugin_update_admin_notices() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( empty( $_GET['page'] ) || sanitize_key( wp_unslash( $_GET['page'] ) ) !== pw_admin_page_slug() ) {
		return;
	}
	if ( empty( $_GET['tab'] ) || sanitize_key( wp_unslash( $_GET['tab'] ) ) !== 'settings' ) {
		return;
	}
	if ( empty( $_GET['pw_github_upd'] ) ) {
		return;
	}
	$st = sanitize_key( wp_unslash( $_GET['pw_github_upd'] ) );
	$uid = get_current_user_id();
	$msg = get_transient( 'pw_github_ud_msg_' . $uid );
	if ( $msg !== false ) {
		delete_transient( 'pw_github_ud_msg_' . $uid );
	}
	if ( $st === 'ok' ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Plugin updated from GitHub.', 'portico-webworks' ) . '</p></div>';
		return;
	}
	if ( $st === 'uptodate' ) {
		echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'You already have the latest release.', 'portico-webworks' ) . '</p></div>';
		return;
	}
	if ( $st === 'err' ) {
		$text = is_string( $msg ) && $msg !== '' ? $msg : __( 'Update failed.', 'portico-webworks' );
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $text ) . '</p></div>';
	}
}
