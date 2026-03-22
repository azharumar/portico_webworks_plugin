<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PW_GITHUB_PLUGIN_RELEASE_ZIP = 'portico_webworks_plugin.zip';

/**
 * Normalize and validate a GitHub releases page URL; empty if invalid.
 */
function pw_sanitize_github_releases_url( $value, $field_args = null, $field = null ) {
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
 * @param array<string,mixed> $data Single release object from GitHub API.
 * @return array{zip_url:string,tag_name:string}|null
 */
function pw_github_zip_from_release_payload( $data ) {
	if ( ! is_array( $data ) ) {
		return null;
	}
	$tag    = isset( $data['tag_name'] ) ? (string) $data['tag_name'] : '';
	$assets = isset( $data['assets'] ) && is_array( $data['assets'] ) ? $data['assets'] : array();
	$want   = strtolower( PW_GITHUB_PLUGIN_RELEASE_ZIP );
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
		return null;
	}
	return array(
		'zip_url'  => $zip_url,
		'tag_name' => $tag,
	);
}

/**
 * @return array|WP_Error
 */
function pw_github_api_get( $url ) {
	return wp_remote_get(
		$url,
		array(
			'timeout' => 20,
			'headers' => array(
				'Accept'     => 'application/vnd.github+json',
				'User-Agent' => 'PorticoWebworks-WordPress-Plugin/' . ( defined( 'PW_VERSION' ) ? PW_VERSION : '0' ),
			),
		)
	);
}

/**
 * Render GitHub Flavored Markdown to HTML (matches GitHub release notes).
 *
 * @param string $markdown    Raw markdown.
 * @param string $releases_url Optional releases URL used to derive `owner/repo` API context.
 * @return string Sanitized HTML (empty string when markdown is empty).
 */
function pw_github_release_body_to_html( $markdown, $releases_url = '' ) {
	$markdown = is_string( $markdown ) ? $markdown : '';
	if ( $markdown === '' ) {
		return '';
	}

	$context = '';
	$ru      = is_string( $releases_url ) ? trim( $releases_url ) : '';
	if ( $ru !== '' ) {
		$repo = pw_parse_github_repo_from_releases_url( $ru );
		if ( is_array( $repo ) ) {
			$context = $repo['owner'] . '/' . $repo['repo'];
		}
	}

	$payload = array(
		'text' => $markdown,
		'mode' => 'gfm',
	);
	if ( $context !== '' ) {
		$payload['context'] = $context;
	}

	$response = wp_remote_post(
		'https://api.github.com/markdown',
		array(
			'timeout' => 25,
			'headers' => array(
				'Accept'       => 'application/vnd.github+json',
				'Content-Type' => 'application/json; charset=utf-8',
				'User-Agent'   => 'PorticoWebworks-WordPress-Plugin/' . ( defined( 'PW_VERSION' ) ? PW_VERSION : '0' ),
			),
			'body'    => wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
		)
	);

	if ( ! is_wp_error( $response ) && (int) wp_remote_retrieve_response_code( $response ) === 200 ) {
		$html = wp_remote_retrieve_body( $response );
		if ( is_string( $html ) && $html !== '' ) {
			return wp_kses_post( $html );
		}
	}

	return wp_kses_post( wpautop( esc_html( $markdown ) ) );
}

/**
 * Latest release GitHub JSON plus optional zip package (same resolution rules as one-click update).
 *
 * @return array{release:array<string,mixed>,package:array{zip_url:string,tag_name:string}|null}|WP_Error
 */
function pw_github_resolve_plugin_release( $releases_url ) {
	$repo = pw_parse_github_repo_from_releases_url( $releases_url );
	if ( ! is_array( $repo ) ) {
		return new WP_Error( 'pw_github_bad_url', __( 'Set a valid GitHub repository URL (e.g. https://github.com/owner/repo/releases).', 'portico-webworks' ) );
	}
	$latest_api = sprintf(
		'https://api.github.com/repos/%s/%s/releases/latest',
		rawurlencode( $repo['owner'] ),
		rawurlencode( $repo['repo'] )
	);
	$response = pw_github_api_get( $latest_api );
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );

	if ( $code === 200 ) {
		$data = json_decode( $body, true );
		if ( ! is_array( $data ) ) {
			return new WP_Error( 'pw_github_json', __( 'Invalid response from GitHub.', 'portico-webworks' ) );
		}
		return array(
			'release' => $data,
			'package' => pw_github_zip_from_release_payload( $data ),
		);
	}

	if ( $code === 404 ) {
		$list_api = sprintf(
			'https://api.github.com/repos/%s/%s/releases?per_page=30',
			rawurlencode( $repo['owner'] ),
			rawurlencode( $repo['repo'] )
		);
		$list_response = pw_github_api_get( $list_api );
		if ( is_wp_error( $list_response ) ) {
			return $list_response;
		}
		$list_code = wp_remote_retrieve_response_code( $list_response );
		$list_body = wp_remote_retrieve_body( $list_response );
		if ( $list_code === 200 ) {
			$list = json_decode( $list_body, true );
			if ( is_array( $list ) ) {
				foreach ( $list as $release ) {
					$pkg = pw_github_zip_from_release_payload( $release );
					if ( $pkg !== null ) {
						return array(
							'release' => $release,
							'package' => $pkg,
						);
					}
				}
			}
		} elseif ( $list_code === 401 || $list_code === 403 ) {
			return new WP_Error(
				'pw_github_api',
				__( 'GitHub API denied access (private repository or rate limit). Use a public repository with published releases.', 'portico-webworks' )
			);
		}
		return new WP_Error(
			'pw_github_no_latest',
			sprintf(
				/* translators: %s: zip filename */
				__( 'GitHub has no usable release with a %s asset. Publish a release on GitHub (tags alone are not enough) and attach the zip, or verify the repository URL. Private repositories need authentication (not supported here).', 'portico-webworks' ),
				PW_GITHUB_PLUGIN_RELEASE_ZIP
			)
		);
	}

	return new WP_Error(
		'pw_github_api',
		sprintf(
			/* translators: %s: HTTP status code */
			__( 'GitHub API error (HTTP %s).', 'portico-webworks' ),
			(string) $code
		)
	);
}

/**
 * @return array{zip_url:string,tag_name:string}|WP_Error
 */
function pw_github_get_latest_release_package( $releases_url ) {
	$r = pw_github_resolve_plugin_release( $releases_url );
	if ( is_wp_error( $r ) ) {
		return $r;
	}
	if ( $r['package'] === null ) {
		return new WP_Error(
			'pw_github_no_zip',
			sprintf(
				/* translators: %s: zip filename */
				__( 'Latest release has no %s asset. Add it via your release workflow.', 'portico-webworks' ),
				PW_GITHUB_PLUGIN_RELEASE_ZIP
			)
		);
	}
	return $r['package'];
}

/**
 * Cached GitHub release summary for the settings screen (version compare + release notes).
 *
 * @return array{ok:true,tag_name:string,name:string,body:string,body_html:string,html_url:string,has_package:bool,is_current:bool,installed:string}|array{ok:false,message:string}
 */
function pw_github_get_settings_release_info( $releases_url ) {
	$releases_url = is_string( $releases_url ) ? trim( $releases_url ) : '';
	if ( $releases_url === '' ) {
		return array(
			'ok'      => false,
			'message' => __( 'Save a GitHub releases URL on the Update tab first.', 'portico-webworks' ),
		);
	}
	$cache_key = 'pw_gh_rel_info_' . md5( $releases_url );
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) && isset( $cached['ok'] ) ) {
		if ( ! empty( $cached['ok'] ) && isset( $cached['body'] ) && is_string( $cached['body'] ) && $cached['body'] !== '' ) {
			$has_html = isset( $cached['body_html'] ) && is_string( $cached['body_html'] ) && $cached['body_html'] !== '';
			if ( ! $has_html ) {
				$cached['body_html'] = pw_github_release_body_to_html( $cached['body'], $releases_url );
				set_transient( $cache_key, $cached, 15 * MINUTE_IN_SECONDS );
			}
		}
		return $cached;
	}

	$r = pw_github_resolve_plugin_release( $releases_url );
	if ( is_wp_error( $r ) ) {
		$out = array(
			'ok'      => false,
			'message' => $r->get_error_message(),
		);
		set_transient( $cache_key, $out, 5 * MINUTE_IN_SECONDS );
		return $out;
	}

	$rel      = $r['release'];
	$tag      = isset( $rel['tag_name'] ) ? (string) $rel['tag_name'] : '';
	$name     = isset( $rel['name'] ) ? (string) $rel['name'] : '';
	$body     = isset( $rel['body'] ) ? (string) $rel['body'] : '';
	$html_url = isset( $rel['html_url'] ) ? (string) $rel['html_url'] : '';
	$current  = defined( 'PW_VERSION' ) ? (string) PW_VERSION : '';

	$body_html = $body !== '' ? pw_github_release_body_to_html( $body, $releases_url ) : '';

	$out = array(
		'ok'           => true,
		'tag_name'     => $tag,
		'name'         => $name,
		'body'         => $body,
		'body_html'    => $body_html,
		'html_url'     => $html_url,
		'has_package'  => $r['package'] !== null,
		'is_current'   => $tag !== '' && pw_github_versions_equal( $tag, $current ),
		'installed'    => $current,
	);
	set_transient( $cache_key, $out, 15 * MINUTE_IN_SECONDS );
	return $out;
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
		set_transient( 'pw_github_ud_msg_' . get_current_user_id(), __( 'Save a GitHub releases URL on the Update tab first.', 'portico-webworks' ), 60 );
		wp_safe_redirect( add_query_arg( 'pw_github_upd', 'err', pw_admin_update_url() ) );
		exit;
	}

	$info = pw_github_get_latest_release_package( $releases_url );
	if ( is_wp_error( $info ) ) {
		set_transient( 'pw_github_ud_msg_' . get_current_user_id(), $info->get_error_message(), 60 );
		wp_safe_redirect( add_query_arg( 'pw_github_upd', 'err', pw_admin_update_url() ) );
		exit;
	}

	if ( pw_github_versions_equal( $info['tag_name'], PW_VERSION ) ) {
		wp_safe_redirect( add_query_arg( 'pw_github_upd', 'uptodate', pw_admin_update_url() ) );
		exit;
	}

	$run = pw_github_run_plugin_update_from_zip_url( $info['zip_url'] );
	if ( is_wp_error( $run ) ) {
		set_transient( 'pw_github_ud_msg_' . get_current_user_id(), $run->get_error_message(), 60 );
		wp_safe_redirect( add_query_arg( 'pw_github_upd', 'err', pw_admin_update_url() ) );
		exit;
	}

	delete_transient( 'pw_gh_rel_info_' . md5( $releases_url ) );
	wp_safe_redirect( add_query_arg( 'pw_github_upd', 'ok', pw_admin_update_url() ) );
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
	if ( empty( $_GET['tab'] ) || sanitize_key( wp_unslash( $_GET['tab'] ) ) !== 'update' ) {
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

add_filter(
	'pw_admin_tabs',
	static function ( $tabs ) {
		$tabs['update'] = __( 'Update', 'portico-webworks' );
		return $tabs;
	},
	25
);

add_action( 'admin_post_pw_save_github_settings', 'pw_handle_save_github_settings' );

function pw_handle_save_github_settings() {
	if (
		! isset( $_POST['pw_github_settings_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pw_github_settings_nonce'] ) ), 'pw_save_github_settings' ) ||
		! current_user_can( 'manage_options' )
	) {
		wp_die( __( 'Invalid request.', 'portico-webworks' ), '', array( 'response' => 403 ) );
	}

	$existing = pw_get_merged_pw_settings();
	$settings = $existing;
	$settings['pw_github_releases_url'] = pw_sanitize_github_releases_url( wp_unslash( $_POST['pw_github_releases_url'] ?? '' ) );

	$old_gh = isset( $existing['pw_github_releases_url'] ) ? (string) $existing['pw_github_releases_url'] : '';
	$new_gh = (string) $settings['pw_github_releases_url'];

	update_option( 'pw_settings', $settings );

	if ( $old_gh !== $new_gh ) {
		delete_transient( 'pw_gh_rel_info_' . md5( $old_gh ) );
		delete_transient( 'pw_gh_rel_info_' . md5( $new_gh ) );
	}

	wp_safe_redirect( add_query_arg( 'pw_github_settings_saved', '1', pw_admin_update_url() ) );
	exit;
}

add_action( 'pw_render_tab_update', 'pw_render_github_update_tab' );

function pw_render_github_update_tab() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['pw_github_settings_saved'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Update settings saved.', 'portico-webworks' ) . '</p></div>';
	}

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">' . esc_html__( 'Update from GitHub', 'portico-webworks' ) . '</div></div>';
	echo '<div class="pw-card-body">';

	if ( ! current_user_can( 'update_plugins' ) ) {
		echo '<p class="description">' . esc_html__( 'You need permission to update plugins to use one-click updates from GitHub.', 'portico-webworks' ) . '</p>';
		echo '</div></div>';
		return;
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="pw_save_github_settings" />';
	wp_nonce_field( 'pw_save_github_settings', 'pw_github_settings_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	echo '<tr><th scope="row"><label for="pw_github_releases_url">' . esc_html__( 'GitHub releases URL', 'portico-webworks' ) . '</label></th><td>';
	echo '<input type="text" class="large-text" name="pw_github_releases_url" id="pw_github_releases_url" value="' . esc_attr( (string) pw_get_setting( 'pw_github_releases_url' ) ) . '" placeholder="' . esc_attr( 'https://github.com/owner/repo/releases' ) . '" />';
	echo '<p class="description">' . esc_html__( 'Repository releases page, e.g. https://github.com/owner/repo/releases — latest release must include portico_webworks_plugin.zip.', 'portico-webworks' ) . '</p>';
	echo '</td></tr>';
	echo '</tbody></table>';
	submit_button( esc_attr__( 'Save', 'portico-webworks' ) );
	echo '</form>';

	$gh_url = pw_get_setting( 'pw_github_releases_url', '' );
	if ( is_string( $gh_url ) && $gh_url !== '' ) {
		$gh_info = pw_github_get_settings_release_info( $gh_url );
		if ( empty( $gh_info['ok'] ) ) {
			echo '<p class="description">' . esc_html( isset( $gh_info['message'] ) ? (string) $gh_info['message'] : __( 'Could not load release info from GitHub.', 'portico-webworks' ) ) . '</p>';
		} else {
			$inst = isset( $gh_info['installed'] ) ? trim( (string) $gh_info['installed'] ) : '';
			$tag  = isset( $gh_info['tag_name'] ) ? trim( (string) $gh_info['tag_name'] ) : '';
			echo '<p style="margin-top:0.75em;">';
			echo '<strong>' . esc_html__( 'Installed version', 'portico-webworks' ) . '</strong>: ';
			echo $inst !== '' ? esc_html( 'v' . ltrim( $inst, 'v' ) ) : esc_html__( '(unknown)', 'portico-webworks' );
			echo '<br /><strong>' . esc_html__( 'Latest release on GitHub', 'portico-webworks' ) . '</strong>: ';
			if ( $tag !== '' ) {
				echo esc_html( $tag );
			} else {
				echo esc_html__( '(unknown)', 'portico-webworks' );
			}
			if ( ! empty( $gh_info['html_url'] ) && is_string( $gh_info['html_url'] ) ) {
				echo ' — <a href="' . esc_url( $gh_info['html_url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View on GitHub', 'portico-webworks' ) . '</a>';
			}
			echo '</p>';
			if ( ! empty( $gh_info['is_current'] ) ) {
				echo '<p class="description">' . esc_html__( 'You are running the latest release that this plugin can install from GitHub.', 'portico-webworks' ) . '</p>';
			} else {
				echo '<p class="description">' . esc_html__( 'A newer release is available. Use the button below to update.', 'portico-webworks' ) . '</p>';
			}
			if ( empty( $gh_info['has_package'] ) ) {
				echo '<p class="description" style="color:#b32d2e;">' . esc_html(
					sprintf(
						/* translators: %s: zip filename */
						__( 'This release has no %s asset, so one-click update will not work until the file is attached to the release.', 'portico-webworks' ),
						PW_GITHUB_PLUGIN_RELEASE_ZIP
					)
				) . '</p>';
			}
			$notes = isset( $gh_info['body'] ) ? trim( (string) $gh_info['body'] ) : '';
			if ( $notes !== '' ) {
				$rel_title = isset( $gh_info['name'] ) && trim( (string) $gh_info['name'] ) !== '' ? trim( (string) $gh_info['name'] ) : __( 'Release notes', 'portico-webworks' );
				echo '<p class="description" style="margin-bottom:0.35em;"><strong>' . esc_html( $rel_title ) . '</strong></p>';
				$notes_html = isset( $gh_info['body_html'] ) && is_string( $gh_info['body_html'] ) ? $gh_info['body_html'] : '';
				if ( $notes_html === '' ) {
					$notes_html = pw_github_release_body_to_html( $notes, $gh_url );
				}
				echo '<div class="pw-github-release-notes">' . $notes_html . '</div>';
			}
		}
	} else {
		echo '<p class="description">' . esc_html__( 'Enter a GitHub releases URL and save to enable one-click updates.', 'portico-webworks' ) . '</p>';
	}

	$upd_url = admin_url( 'admin-post.php' );
	echo '<form method="post" action="' . esc_url( $upd_url ) . '" style="margin-top:0.75em;">';
	echo '<input type="hidden" name="action" value="pw_github_plugin_update" />';
	wp_nonce_field( 'pw_github_plugin_update' );
	$btn_attrs = array();
	if ( ! is_string( $gh_url ) || $gh_url === '' ) {
		$btn_attrs['disabled'] = 'disabled';
	}
	submit_button( esc_attr__( 'Update from GitHub', 'portico-webworks' ), 'secondary', 'pw-github-update', false, $btn_attrs );
	echo '</form>';

	echo '</div></div>';
}
