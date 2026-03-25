<?php
/**
 * Download / locate sample data pack ZIP and load bootstrap (defines dataset installer).
 *
 * @package Portico_Webworks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PW_SAMPLE_DATA_ZIP_FILENAME = 'portico_webworks_plugin-sample-data.zip';

/**
 * Allowed targetOrigin for postMessage from the sample-install iframe (admin scheme/host/port).
 */
function pw_sample_install_allowed_post_message_origin(): string {
	$parsed = wp_parse_url( admin_url() );
	if ( ! is_array( $parsed ) || empty( $parsed['host'] ) ) {
		return '*';
	}
	$scheme = isset( $parsed['scheme'] ) ? $parsed['scheme'] : 'https';
	$host   = $parsed['host'];
	$port   = isset( $parsed['port'] ) ? ':' . (int) $parsed['port'] : '';
	return $scheme . '://' . $host . $port;
}

/**
 * @return true|WP_Error
 */
function pw_ensure_sample_data_pack_loaded( string $zip_url ) {
	static $done = false;
	if ( $done ) {
		return true;
	}

	$local_boot = dirname( PW_PLUGIN_FILE ) . '/sample-data-pack/bootstrap.php';
	if ( is_readable( $local_boot ) ) {
		$dir = dirname( $local_boot );
		$mv  = pw_sample_data_pack_read_validate_manifest( $dir );
		if ( is_wp_error( $mv ) ) {
			return $mv;
		}
		require_once $local_boot;
		$done = true;
		return true;
	}

	$zip_url = trim( $zip_url );
	if ( $zip_url === '' ) {
		return new WP_Error( 'pw_sample_pack_no_url', __( 'No sample data ZIP URL was provided.', 'portico-webworks' ) );
	}

	if ( ! wp_http_validate_url( $zip_url ) || ! preg_match( '#^https://#i', $zip_url ) ) {
		return new WP_Error( 'pw_sample_pack_bad_url', __( 'Sample data URL must be a valid https link.', 'portico-webworks' ) );
	}

	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) ) {
		return new WP_Error( 'pw_sample_pack_uploads', (string) $upload['error'] );
	}

	$slug = pw_sample_data_pack_dir_slug();
	$dest = trailingslashit( $upload['basedir'] ) . 'portico-webworks-sample-data/' . $slug;

	if ( is_dir( $dest ) && is_readable( $dest . '/manifest.json' ) && is_readable( $dest . '/bootstrap.php' ) ) {
		$mv = pw_sample_data_pack_read_validate_manifest( $dest );
		if ( ! is_wp_error( $mv ) ) {
			require_once $dest . '/bootstrap.php';
			$done = true;
			return true;
		}
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';

	wp_mkdir_p( $dest );

	$tmp_zip = download_url( $zip_url, 300 );
	if ( is_wp_error( $tmp_zip ) ) {
		return new WP_Error(
			'pw_sample_pack_download',
			sprintf(
				/* translators: %s: error message */
				__( 'Could not download sample data: %s', 'portico-webworks' ),
				$tmp_zip->get_error_message()
			)
		);
	}

	if ( ! WP_Filesystem() ) {
		@unlink( $tmp_zip );
		return new WP_Error( 'pw_sample_pack_fs', __( 'Could not access the filesystem to extract sample data.', 'portico-webworks' ) );
	}

	global $wp_filesystem;
	$wp_filesystem->delete( $dest, true );
	wp_mkdir_p( $dest );

	$unzipped = unzip_file( $tmp_zip, $dest );
	@unlink( $tmp_zip );

	if ( is_wp_error( $unzipped ) ) {
		return new WP_Error(
			'pw_sample_pack_unzip',
			sprintf(
				/* translators: %s: error message */
				__( 'Could not extract sample data: %s', 'portico-webworks' ),
				$unzipped->get_error_message()
			)
		);
	}

	$mv = pw_sample_data_pack_read_validate_manifest( $dest );
	if ( is_wp_error( $mv ) ) {
		return $mv;
	}

	if ( ! is_readable( $dest . '/bootstrap.php' ) ) {
		return new WP_Error( 'pw_sample_pack_bootstrap', __( 'Sample data archive is missing bootstrap.php.', 'portico-webworks' ) );
	}

	require_once $dest . '/bootstrap.php';
	$done = true;
	return true;
}

/**
 * @return string
 */
function pw_sample_data_pack_dir_slug(): string {
	$v = defined( 'PW_VERSION' ) ? (string) PW_VERSION : '0';
	return sanitize_file_name( strtolower( ltrim( $v, 'v' ) ) );
}

/**
 * @param string $dir Absolute path to extracted pack root.
 * @return true|WP_Error
 */
function pw_sample_data_pack_read_validate_manifest( string $dir ) {
	$path = trailingslashit( $dir ) . 'manifest.json';
	if ( ! is_readable( $path ) ) {
		return new WP_Error( 'pw_sample_pack_manifest', __( 'Sample data pack is missing manifest.json.', 'portico-webworks' ) );
	}
	$raw = file_get_contents( $path );
	if ( ! is_string( $raw ) || $raw === '' ) {
		return new WP_Error( 'pw_sample_pack_manifest', __( 'Sample data manifest could not be read.', 'portico-webworks' ) );
	}
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) || empty( $data['pack_version'] ) ) {
		return new WP_Error( 'pw_sample_pack_manifest', __( 'Sample data manifest is invalid.', 'portico-webworks' ) );
	}
	$pv   = (string) $data['pack_version'];
	$plug = defined( 'PW_VERSION' ) ? (string) PW_VERSION : '';
	if ( ! pw_sample_data_pack_versions_match( $pv, $plug ) ) {
		return new WP_Error(
			'pw_sample_pack_version',
			sprintf(
				/* translators: 1: pack version, 2: plugin version */
				__( 'Sample data pack version (%1$s) does not match the plugin version (%2$s). Use the ZIP from the same release.', 'portico-webworks' ),
				$pv,
				$plug
			)
		);
	}
	return true;
}

/**
 * @param string $a Pack manifest version.
 * @param string $b Plugin PW_VERSION.
 */
function pw_sample_data_pack_versions_match( string $a, string $b ): bool {
	$a = strtolower( trim( ltrim( $a, 'vV' ) ) );
	$b = strtolower( trim( ltrim( $b, 'vV' ) ) );
	return $a !== '' && $a === $b;
}

/**
 * @return string Download URL for the sample-data release asset, or empty if not derivable.
 */
function pw_get_default_sample_data_pack_url(): string {
	$ver = defined( 'PW_VERSION' ) ? (string) PW_VERSION : '';
	if ( $ver === '' ) {
		return '';
	}

	$releases = pw_get_setting( 'pw_github_releases_url', '' );
	$parsed   = is_string( $releases ) ? pw_parse_github_repo_from_releases_url( $releases ) : null;
	if ( is_array( $parsed ) && ! empty( $parsed['owner'] ) && ! empty( $parsed['repo'] ) ) {
		$owner = $parsed['owner'];
		$repo  = $parsed['repo'];
	} elseif ( defined( 'PW_SAMPLE_DATA_GITHUB_OWNER' ) && defined( 'PW_SAMPLE_DATA_GITHUB_REPO' ) ) {
		$owner = (string) PW_SAMPLE_DATA_GITHUB_OWNER;
		$repo  = (string) PW_SAMPLE_DATA_GITHUB_REPO;
	} else {
		return '';
	}

	$tag = 'v' . ltrim( strtolower( $ver ), 'v' );

	return sprintf(
		'https://github.com/%s/%s/releases/download/%s/%s',
		rawurlencode( $owner ),
		rawurlencode( $repo ),
		rawurlencode( $tag ),
		PW_SAMPLE_DATA_ZIP_FILENAME
	);
}
