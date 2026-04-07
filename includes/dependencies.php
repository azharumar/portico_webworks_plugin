<?php
/**
 * Portico Webworks — Required dependency installer.
 *
 * Registers required plugins/themes, checks their status,
 * and provides admin UI + AJAX handlers to install/activate them.
 */

if (!defined('ABSPATH')) {
	exit;
}

define( 'PW_DEP_ACTIVE',        'active' );
define( 'PW_DEP_INSTALLED',     'installed' );
define( 'PW_DEP_NOT_INSTALLED', 'not_installed' );

function pw_can_manage_deps() {
	return current_user_can( 'install_plugins' );
}

function pw_get_dependencies() {
	$plugin_dir = plugin_dir_path(PW_PLUGIN_FILE);

	$deps = array(
		array(
			'name'       => 'GeneratePress',
			'slug'       => 'generatepress',
			'type'       => 'theme',
			'source'     => 'repo',
			'file'       => '',
			'required'   => true,
		),
		array(
			'name'          => 'Portico Webworks Starter Theme',
			'slug'          => 'portico_webworks_theme',
			'type'          => 'theme',
			'source'        => 'github_release',
			'releases_url'  => 'https://github.com/azharumar/portico_webworks_starter_theme/releases',
			'github_asset'  => PW_STARTER_THEME_RELEASE_ZIP,
			'file'          => '',
			'required'      => true,
		),
		array(
			'name'       => 'Activity Log',
			'slug'       => 'aryo-activity-log',
			'type'       => 'plugin',
			'source'     => 'repo',
			'file'       => 'aryo-activity-log/aryo-activity-log.php',
			'required'   => true,
		),
		array(
			'name'       => 'Rank Math SEO',
			'slug'       => 'seo-by-rank-math',
			'type'       => 'plugin',
			'source'     => 'repo',
			'file'       => 'seo-by-rank-math/rank-math.php',
			'required'   => true,
		),
		array(
			'name'       => 'Rank Math SEO PRO',
			'slug'       => 'seo-by-rank-math-pro',
			'type'       => 'plugin',
			'source'     => 'bundled',
			'zip'        => $plugin_dir . 'assets/zips/seo-by-rank-math-pro.zip',
			'file'       => 'seo-by-rank-math-pro/rank-math-pro.php',
			'required'   => true,
		),
		array(
			'name'       => 'GenerateBlocks',
			'slug'       => 'generateblocks',
			'type'       => 'plugin',
			'source'     => 'repo',
			'file'       => 'generateblocks/plugin.php',
			'required'   => true,
		),
		array(
			'name'       => 'GenerateBlocks Pro',
			'slug'       => 'generateblocks-pro',
			'type'       => 'plugin',
			'source'     => 'bundled',
			'zip'        => $plugin_dir . 'assets/zips/generateblocks-pro-2.5.0.zip',
			'file'       => 'generateblocks-pro/plugin.php',
			'required'   => true,
		),
		array(
			'name'       => 'GP Premium',
			'slug'       => 'gp-premium',
			'type'       => 'plugin',
			'source'     => 'bundled',
			'zip'        => $plugin_dir . 'assets/zips/gp-premium-2.5.5.zip',
			'file'       => 'gp-premium/gp-premium.php',
			'required'   => true,
		),
		array(
			'name'       => 'Favicon by RealFaviconGenerator',
			'slug'       => 'favicon-by-realfavicongenerator',
			'type'       => 'plugin',
			'source'     => 'repo',
			'file'       => 'favicon-by-realfavicongenerator/favicon-by-realfavicongenerator.php',
			'required'   => true,
		),
		array(
			'name'       => 'SVG Support',
			'slug'       => 'svg-support',
			'type'       => 'plugin',
			'source'     => 'repo',
			'file'       => 'svg-support/svg-support.php',
			'required'   => true,
		),
	);

	return apply_filters( 'pw_dependencies', $deps );
}

function pw_dep_status($dep) {
	if (!function_exists('is_plugin_active')) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ($dep['type'] === 'theme') {
		$theme = wp_get_theme($dep['slug']);
		if (!$theme->exists()) {
			return PW_DEP_NOT_INSTALLED;
		}
		$active_theme = wp_get_theme();
		if ($active_theme->get_stylesheet() === $dep['slug'] || $active_theme->get_template() === $dep['slug']) {
			return PW_DEP_ACTIVE;
		}
		return PW_DEP_INSTALLED;
	}

	if (!empty($dep['file'])) {
		$installed = file_exists(WP_PLUGIN_DIR . '/' . $dep['file']);
		if (!$installed) {
			return PW_DEP_NOT_INSTALLED;
		}
		if (is_plugin_active($dep['file'])) {
			return PW_DEP_ACTIVE;
		}
		return PW_DEP_INSTALLED;
	}

	return PW_DEP_NOT_INSTALLED;
}

function pw_all_deps_satisfied() {
	foreach (pw_get_dependencies() as $dep) {
		if (pw_dep_status($dep) !== PW_DEP_ACTIVE) {
			return false;
		}
	}
	return true;
}

/**
 * Normalize a version string for version_compare (strip leading "v", trim).
 */
function pw_dep_normalize_version( $version ) {
	$version = is_string( $version ) ? trim( $version ) : '';
	if ( $version === '' ) {
		return '';
	}
	return ltrim( strtolower( $version ), 'v' );
}

/**
 * Installed theme or plugin version string, or '' if unknown.
 */
function pw_dep_installed_version( $dep ) {
	if ( $dep['type'] === 'theme' ) {
		$theme = wp_get_theme( $dep['slug'] );
		if ( ! $theme->exists() ) {
			return '';
		}
		return (string) $theme->get( 'Version' );
	}
	if ( ! empty( $dep['file'] ) ) {
		$path = WP_PLUGIN_DIR . '/' . $dep['file'];
		if ( ! file_exists( $path ) ) {
			return '';
		}
		$data = get_plugin_data( $path, false, false );
		return isset( $data['Version'] ) ? (string) $data['Version'] : '';
	}
	return '';
}

/**
 * Remote / latest version (WordPress.org, GitHub tag, or false if unknown). Not used for bundled.
 *
 * @return string|false
 */
function pw_dep_remote_version( $dep ) {
	static $cache = array();
	$key = wp_json_encode(
		array(
			'slug'   => $dep['slug'] ?? '',
			'source' => $dep['source'] ?? '',
			'type'   => $dep['type'] ?? '',
			'url'    => $dep['releases_url'] ?? '',
			'asset'  => $dep['github_asset'] ?? '',
		)
	);
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}
	$transient_key = 'pw_dep_remote_v_' . md5( $key );
	$cached = get_transient( $transient_key );
	if ( $cached !== false && is_string( $cached ) && $cached !== '' ) {
		$cache[ $key ] = $cached;
		return $cached;
	}
	$out = false;
	if ( $dep['source'] === 'repo' && $dep['type'] === 'plugin' ) {
		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}
		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $dep['slug'],
				'fields' => array( 'sections' => false ),
			)
		);
		if ( ! is_wp_error( $api ) && ! empty( $api->version ) ) {
			$out = (string) $api->version;
		}
	} elseif ( $dep['source'] === 'repo' && $dep['type'] === 'theme' ) {
		if ( ! function_exists( 'themes_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/theme-install.php';
		}
		$api = themes_api(
			'theme_information',
			array(
				'slug'   => $dep['slug'],
				'fields' => array( 'sections' => false ),
			)
		);
		if ( ! is_wp_error( $api ) && ! empty( $api->version ) ) {
			$out = (string) $api->version;
		}
	} elseif ( $dep['source'] === 'github_release' && $dep['type'] === 'theme' ) {
		$releases_url = isset( $dep['releases_url'] ) ? trim( (string) $dep['releases_url'] ) : '';
		$asset        = isset( $dep['github_asset'] ) ? trim( (string) $dep['github_asset'] ) : '';
		if ( $releases_url !== '' && $asset !== '' && function_exists( 'pw_github_get_latest_release_zip_by_asset' ) ) {
			$pkg = pw_github_get_latest_release_zip_by_asset( $releases_url, $asset );
			if ( ! is_wp_error( $pkg ) && ! empty( $pkg['tag_name'] ) ) {
				$out = (string) $pkg['tag_name'];
			}
		}
	}
	$cache[ $key ] = $out;
	if ( is_string( $out ) && $out !== '' ) {
		set_transient( $transient_key, $out, 15 * MINUTE_IN_SECONDS );
	}
	return $out;
}

/**
 * Clear cached remote version after an update so the Dependencies table refreshes.
 */
function pw_dep_invalidate_remote_version_cache( $dep ) {
	$key = wp_json_encode(
		array(
			'slug'   => $dep['slug'] ?? '',
			'source' => $dep['source'] ?? '',
			'type'   => $dep['type'] ?? '',
			'url'    => $dep['releases_url'] ?? '',
			'asset'  => $dep['github_asset'] ?? '',
		)
	);
	delete_transient( 'pw_dep_remote_v_' . md5( $key ) );
}

/**
 * Bundled zip is newer on disk than the installed plugin bootstrap file (Portico shipped fresher zips).
 */
function pw_dep_bundled_zip_is_newer( $dep ) {
	if ( $dep['source'] !== 'bundled' || $dep['type'] !== 'plugin' ) {
		return false;
	}
	if ( empty( $dep['zip'] ) || empty( $dep['file'] ) ) {
		return false;
	}
	if ( ! file_exists( $dep['zip'] ) ) {
		return false;
	}
	$path = WP_PLUGIN_DIR . '/' . $dep['file'];
	if ( ! file_exists( $path ) ) {
		return false;
	}
	return (int) filemtime( $dep['zip'] ) > (int) filemtime( $path );
}

/**
 * Whether the dependency should show an Update action (installed or active).
 */
function pw_dep_needs_update( $dep ) {
	$status = pw_dep_status( $dep );
	if ( $status === PW_DEP_NOT_INSTALLED ) {
		return false;
	}
	if ( $dep['source'] === 'bundled' ) {
		$needs = pw_dep_bundled_zip_is_newer( $dep );
		return (bool) apply_filters( 'pw_dep_needs_update', $needs, $dep );
	}
	$local  = pw_dep_installed_version( $dep );
	$remote = pw_dep_remote_version( $dep );
	if ( $remote === false || $remote === '' ) {
		return (bool) apply_filters( 'pw_dep_needs_update', false, $dep );
	}
	if ( $local === '' ) {
		return (bool) apply_filters( 'pw_dep_needs_update', true, $dep );
	}
	$needs = version_compare( pw_dep_normalize_version( $local ), pw_dep_normalize_version( $remote ), '<' );
	return (bool) apply_filters( 'pw_dep_needs_update', $needs, $dep );
}

function pw_dep_can_update( $dep ) {
	if ( $dep['type'] === 'theme' ) {
		return current_user_can( 'update_themes' ) && current_user_can( 'install_themes' );
	}
	return current_user_can( 'update_plugins' ) && current_user_can( 'install_plugins' );
}

// ---------------------------------------------------------------------------
// Admin notices (rendered at the bottom of PW admin pages)
// ---------------------------------------------------------------------------
add_action('pw_admin_notices', function () {
	if (pw_all_deps_satisfied()) {
		return;
	}

	if (!pw_can_manage_deps()) {
		echo '<div class="notice notice-warning"><p>';
		echo '<strong>Portico Webworks</strong> requires additional plugins and a theme to function. ';
		echo 'Please ask a site administrator to complete the setup.';
		echo '</p></div>';
		return;
	}

	$page_url = admin_url('admin.php?page=' . urlencode(pw_admin_page_slug()) . '&tab=dependencies');
	echo '<div class="notice notice-warning"><p>';
	echo '<strong>Portico Webworks</strong> requires additional plugins and a theme. ';
	echo '<a href="' . esc_url($page_url) . '">Install required dependencies</a>.';
	echo '</p></div>';
});

// ---------------------------------------------------------------------------
// Render the Dependencies tab content
// ---------------------------------------------------------------------------
add_action('pw_render_tab_dependencies', function () {
	$deps = pw_get_dependencies();
	$can_install = pw_can_manage_deps() && current_user_can('activate_plugins');
	$can_theme   = current_user_can('switch_themes') && current_user_can('install_themes');

	echo '<div class="pw-card" style="max-width:980px">';
	echo '<div class="pw-card-head"><div class="pw-card-title">Required Dependencies</div>';
	if ( $can_install ) {
		echo '<button type="button" id="pw-dep-update-all" class="button" style="font-size:12px;padding:4px 14px;margin-right:6px">Update all</button>';
		echo '<button type="button" id="pw-dep-install-all" class="button button-primary" style="font-size:12px;padding:4px 14px">Install &amp; activate all</button>';
	}
	echo '</div>';
	echo '<div class="pw-card-body" style="padding:0">';
	echo '<table class="widefat" style="border:0;box-shadow:none">';
	echo '<thead><tr><th>Name</th><th>Type</th><th>Source</th><th>Status</th><th style="min-width:200px">Action</th></tr></thead>';
	echo '<tbody>';

	foreach ( $deps as $dep ) {
		$status        = pw_dep_status( $dep );
		$needs_update  = pw_dep_needs_update( $dep );
		$can_update    = pw_dep_can_update( $dep );
		$badge_color   = $status === PW_DEP_ACTIVE ? '#1e8e3e' : ( $status === PW_DEP_INSTALLED ? '#e8a200' : '#b32d15' );
		$badge_label   = $status === PW_DEP_ACTIVE ? __( 'Active', 'portico-webworks' ) : ( $status === PW_DEP_INSTALLED ? __( 'Installed', 'portico-webworks' ) : __( 'Not installed', 'portico-webworks' ) );
		if ( $needs_update && ( $status === PW_DEP_ACTIVE || $status === PW_DEP_INSTALLED ) ) {
			$badge_label .= ' · ' . __( 'update available', 'portico-webworks' );
		}
		if ( $dep['source'] === 'bundled' ) {
			$source_label = 'Bundled ZIP';
		} elseif ( $dep['source'] === 'github_release' ) {
			$source_label = 'GitHub release';
		} else {
			$source_label = 'WordPress.org';
		}

		echo '<tr data-pw-dep="' . esc_attr( $dep['slug'] ) . '">';
		echo '<td><strong>' . esc_html( $dep['name'] ) . '</strong></td>';
		echo '<td>' . esc_html( ucfirst( $dep['type'] ) ) . '</td>';
		echo '<td>' . esc_html( $source_label ) . '</td>';
		echo '<td><span class="pw-dep-badge" style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600;color:#fff;background:' . esc_attr( $badge_color ) . '">' . esc_html( $badge_label ) . '</span></td>';
		echo '<td>';

		if ( $needs_update && $can_update ) {
			echo '<button type="button" class="button pw-dep-action" data-slug="' . esc_attr( $dep['slug'] ) . '" data-action="update">' . esc_html__( 'Update', 'portico-webworks' ) . '</button> ';
		}

		if ( $status === PW_DEP_ACTIVE ) {
			echo '<span class="pw-dep-ready" style="color:#1e8e3e;font-weight:600;font-size:13px">&#10003; ' . esc_html__( 'Ready', 'portico-webworks' ) . '</span>';
		} elseif ( $status === PW_DEP_INSTALLED ) {
			$can = ( $dep['type'] === 'theme' ) ? $can_theme : $can_install;
			if ( $can ) {
				echo '<button type="button" class="button pw-dep-action" data-slug="' . esc_attr( $dep['slug'] ) . '" data-action="activate">' . esc_html__( 'Activate', 'portico-webworks' ) . '</button>';
			} else {
				echo '<em style="color:var(--muted);font-size:12px">' . esc_html__( 'Insufficient permissions', 'portico-webworks' ) . '</em>';
			}
		} else {
			$can = ( $dep['type'] === 'theme' ) ? ( $can_theme && $can_install ) : $can_install;
			if ( $can ) {
				if ( $dep['source'] === 'bundled' && ! empty( $dep['zip'] ) && ! file_exists( $dep['zip'] ) ) {
					echo '<span style="color:#b32d15;font-size:12px;font-weight:600">' . esc_html__( 'ZIP missing', 'portico-webworks' ) . '</span>';
				} else {
					echo '<button type="button" class="button pw-dep-action" data-slug="' . esc_attr( $dep['slug'] ) . '" data-action="install">' . esc_html__( 'Install & activate', 'portico-webworks' ) . '</button>';
				}
			} else {
				echo '<em style="color:var(--muted);font-size:12px">' . esc_html__( 'Insufficient permissions', 'portico-webworks' ) . '</em>';
			}
		}

		echo '</td></tr>';
	}

	echo '</tbody></table>';
	echo '</div></div>';

	wp_nonce_field('portico_dep_action', 'pw_dep_nonce');
});

// ---------------------------------------------------------------------------
// Enqueue dependency tab JS (inline, only on our admin page)
// ---------------------------------------------------------------------------
add_action('admin_footer', function () {
	if (!isset($_GET['page']) || $_GET['page'] !== pw_admin_page_slug()) {
		return;
	}
	if (!isset($_GET['tab']) || $_GET['tab'] !== 'dependencies') {
		return;
	}
	$ajax_url = admin_url('admin-ajax.php');
	?>
	<script>
	(function(){
		var nonce = document.getElementById('pw_dep_nonce') ? document.getElementById('pw_dep_nonce').value : '';

		function labelForAction(action) {
			if (action === 'install') return '<?php echo esc_js( __( 'Installing…', 'portico-webworks' ) ); ?>';
			if (action === 'update') return '<?php echo esc_js( __( 'Updating…', 'portico-webworks' ) ); ?>';
			return '<?php echo esc_js( __( 'Activating…', 'portico-webworks' ) ); ?>';
		}

		function doAction(slug, action, btn) {
			if (btn) {
				btn.disabled = true;
				btn.textContent = labelForAction(action);
			}
			var fd = new FormData();
			fd.append('action', 'portico_dep_action');
			fd.append('_ajax_nonce', nonce);
			fd.append('dep_slug', slug);
			fd.append('dep_action', action);

			return fetch(<?php echo wp_json_encode($ajax_url); ?>, {method:'POST', body:fd, credentials:'same-origin'})
				.then(function(r){
					return r.text().then(function(t){
						try {
							return JSON.parse(t);
						} catch (e) {
							var hint = (t && t.indexOf('<!DOCTYPE') === 0) ? 'Server returned a web page instead of JSON (often a login screen, fatal error, or plugin conflict).' : 'Invalid JSON from server.';
							throw new Error(hint);
						}
					});
				})
				.then(function(data){
					if (data.success && data.data && data.data.reload) {
						location.reload();
						return data;
					}
					var row = document.querySelector('tr[data-pw-dep="'+slug+'"]');
					if (!row) return data;
					var badgeEl = row.querySelector('.pw-dep-badge');
					var actionTd = row.querySelector('td:last-child');
					if (data.success) {
						if (badgeEl) { badgeEl.style.background = '#1e8e3e'; badgeEl.textContent = '<?php echo esc_js( __( 'Active', 'portico-webworks' ) ); ?>'; }
						if (actionTd) {
							var ready = actionTd.querySelector('.pw-dep-ready');
							if (ready) { ready.outerHTML = '<span class="pw-dep-ready" style="color:#1e8e3e;font-weight:600;font-size:13px">&#10003; <?php echo esc_js( __( 'Ready', 'portico-webworks' ) ); ?></span>'; }
							else { actionTd.insertAdjacentHTML('beforeend', '<span class="pw-dep-ready" style="color:#1e8e3e;font-weight:600;font-size:13px">&#10003; <?php echo esc_js( __( 'Ready', 'portico-webworks' ) ); ?></span>'); }
						}
					} else {
						var msg = (data.data && data.data.message) ? data.data.message : 'Failed';
						if (badgeEl) { badgeEl.style.background = '#b32d15'; badgeEl.textContent = '<?php echo esc_js( __( 'Error', 'portico-webworks' ) ); ?>'; }
						if (btn) { btn.disabled = false; btn.textContent = '<?php echo esc_js( __( 'Retry', 'portico-webworks' ) ); ?>'; }
						alert(msg);
					}
					return data;
				})
				.catch(function(err){
					if (btn) { btn.disabled = false; btn.textContent = '<?php echo esc_js( __( 'Retry', 'portico-webworks' ) ); ?>'; }
					alert('Request failed: ' + err.message);
				});
		}

		document.querySelectorAll('.pw-dep-action').forEach(function(btn){
			btn.addEventListener('click', function(){
				doAction(btn.getAttribute('data-slug'), btn.getAttribute('data-action'), btn);
			});
		});

		var bulkInstall = document.getElementById('pw-dep-install-all');
		if (bulkInstall) {
			bulkInstall.addEventListener('click', function(){
				bulkInstall.disabled = true;
				bulkInstall.textContent = '<?php echo esc_js( __( 'Working…', 'portico-webworks' ) ); ?>';
				var rows = document.querySelectorAll('tbody tr[data-pw-dep]');
				var chain = Promise.resolve();
				rows.forEach(function(row){
					var ins = row.querySelector('.pw-dep-action[data-action="install"]');
					var upd = row.querySelector('.pw-dep-action[data-action="update"]');
					var act = row.querySelector('.pw-dep-action[data-action="activate"]');
					var btn = ins || upd || act;
					if (!btn) return;
					chain = chain.then(function(){
						return doAction(btn.getAttribute('data-slug'), btn.getAttribute('data-action'), btn);
					});
				});
				chain.then(function(){
					bulkInstall.textContent = '<?php echo esc_js( __( 'Done', 'portico-webworks' ) ); ?>';
					setTimeout(function(){ location.reload(); }, 600);
				});
			});
		}

		var bulkUpdate = document.getElementById('pw-dep-update-all');
		if (bulkUpdate) {
			bulkUpdate.addEventListener('click', function(){
				bulkUpdate.disabled = true;
				bulkUpdate.textContent = '<?php echo esc_js( __( 'Updating…', 'portico-webworks' ) ); ?>';
				var buttons = Array.from(document.querySelectorAll('.pw-dep-action[data-action="update"]'));
				if (buttons.length === 0) {
					bulkUpdate.disabled = false;
					bulkUpdate.textContent = '<?php echo esc_js( __( 'Update all', 'portico-webworks' ) ); ?>';
					return;
				}
				var chain = Promise.resolve();
				buttons.forEach(function(btn){
					chain = chain.then(function(){
						return doAction(btn.getAttribute('data-slug'), 'update', btn);
					});
				});
				chain.then(function(){
					location.reload();
				}).catch(function(){
					bulkUpdate.textContent = '<?php echo esc_js( __( 'Update all', 'portico-webworks' ) ); ?>';
					bulkUpdate.disabled = false;
				});
			});
		}
	})();
	</script>
	<?php
});

function pw_dep_ob_end_all() {
	while ( ob_get_level() > 0 ) {
		ob_end_clean();
	}
}

/**
 * After install, upgrader skins may print progress; discard so we only validate activation output (e.g. Rank Math).
 */
function pw_dep_ob_restart() {
	pw_dep_ob_end_all();
	ob_start();
}

/**
 * Send JSON error (discard any buffered output first so response is valid JSON).
 */
function pw_dep_send_json_error_clean( $data ) {
	pw_dep_ob_end_all();
	wp_send_json_error( $data );
}

/**
 * Send JSON success only if nothing meaningful was printed (avoids "Unexpected token '<'" in admin JS).
 */
function pw_dep_send_json_success_clean( $data ) {
	$raw = '';
	while ( ob_get_level() > 0 ) {
		$raw .= (string) ob_get_clean();
	}
	$noise = trim( wp_strip_all_tags( $raw ) );
	if ( $noise !== '' ) {
		wp_send_json_error(
			array(
				'message' => __( 'Something printed output while activating (common with some SEO plugins). Use Plugins → Installed Plugins to activate, then return here.', 'portico-webworks' ),
			)
		);
	}
	wp_send_json_success( $data );
}

// ---------------------------------------------------------------------------
// AJAX handler — install / activate / update a single dependency
// ---------------------------------------------------------------------------
add_action( 'wp_ajax_portico_dep_action', function () {
	ob_start();

	if ( ! check_ajax_referer( 'portico_dep_action', false, false ) ) {
		pw_dep_send_json_error_clean( array( 'message' => __( 'Security check failed. Reload this page and try again.', 'portico-webworks' ) ) );
	}

	if ( ! pw_can_manage_deps() || ! current_user_can( 'activate_plugins' ) ) {
		pw_dep_send_json_error_clean( array( 'message' => 'Insufficient permissions.' ) );
	}

	$slug       = isset( $_POST['dep_slug'] ) ? sanitize_key( wp_unslash( $_POST['dep_slug'] ) ) : '';
	$dep_action = isset( $_POST['dep_action'] ) ? sanitize_key( wp_unslash( $_POST['dep_action'] ) ) : '';

	$dep = null;
	foreach ( pw_get_dependencies() as $d ) {
		if ( $d['slug'] === $slug ) {
			$dep = $d;
			break;
		}
	}

	if ( ! $dep ) {
		pw_dep_send_json_error_clean( array( 'message' => 'Unknown dependency.' ) );
	}

	$status = pw_dep_status( $dep );

	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	require_once ABSPATH . 'wp-admin/includes/theme-install.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( $dep_action === 'update' ) {
		if ( ! pw_dep_can_update( $dep ) ) {
			pw_dep_send_json_error_clean( array( 'message' => __( 'You are not allowed to update this item.', 'portico-webworks' ) ) );
		}
		if ( $status === PW_DEP_NOT_INSTALLED ) {
			pw_dep_send_json_error_clean( array( 'message' => __( 'Install it before updating.', 'portico-webworks' ) ) );
		}
		if ( ! pw_dep_needs_update( $dep ) ) {
			pw_dep_send_json_success_clean(
				array(
					'message' => $dep['name'] . ' — ' . __( 'already up to date.', 'portico-webworks' ),
					'reload'  => true,
				)
			);
		}
		$was_active = ( $dep['type'] === 'plugin' && ! empty( $dep['file'] ) && is_plugin_active( $dep['file'] ) );
		if ( $dep['type'] === 'theme' ) {
			$result = pw_upgrade_theme( $dep );
		} else {
			$result = pw_upgrade_plugin( $dep );
		}
		if ( is_wp_error( $result ) ) {
			pw_dep_send_json_error_clean( array( 'message' => $result->get_error_message() ) );
		}
		pw_dep_ob_restart();
		if ( $was_active && ! empty( $dep['file'] ) ) {
			activate_plugin( $dep['file'], '', false, true );
		}
		pw_dep_invalidate_remote_version_cache( $dep );
		pw_dep_send_json_success_clean(
			array(
				'message' => $dep['name'] . ' — ' . __( 'updated.', 'portico-webworks' ),
				'reload'  => true,
			)
		);
	}

	if ( $status === PW_DEP_ACTIVE ) {
		pw_dep_send_json_success_clean( array( 'message' => $dep['name'] . ' is already active.' ) );
	}

	if ( $status === PW_DEP_NOT_INSTALLED ) {
		if ( $dep['type'] === 'theme' ) {
			$result = pw_install_theme( $dep );
		} else {
			$result = pw_install_plugin( $dep );
		}

		if ( is_wp_error( $result ) ) {
			pw_dep_send_json_error_clean( array( 'message' => $result->get_error_message() ) );
		}
		pw_dep_ob_restart();
	}

	if ( $dep['type'] === 'theme' ) {
		if ( ! current_user_can( 'switch_themes' ) ) {
			pw_dep_send_json_error_clean( array( 'message' => 'Insufficient permissions to switch themes.' ) );
		}
		switch_theme( $dep['slug'] );
		pw_dep_send_json_success_clean( array( 'message' => $dep['name'] . ' activated.' ) );
	}

	$activate = activate_plugin( $dep['file'], '', false, false );
	if ( is_wp_error( $activate ) ) {
		pw_dep_send_json_error_clean( array( 'message' => $activate->get_error_message() ) );
	}

	pw_dep_send_json_success_clean( array( 'message' => $dep['name'] . ' activated.' ) );
} );

// ---------------------------------------------------------------------------
// Installer helpers
// ---------------------------------------------------------------------------
function pw_install_plugin($dep) {
	$skin     = new WP_Ajax_Upgrader_Skin();
	$upgrader = new Plugin_Upgrader($skin);

	if ($dep['source'] === 'bundled') {
		if (empty($dep['zip']) || !file_exists($dep['zip'])) {
			return new WP_Error('zip_missing', $dep['name'] . ': bundled ZIP file not found at expected path.');
		}
		$result = $upgrader->install($dep['zip']);
	} else {
		$api = plugins_api('plugin_information', array(
			'slug'   => $dep['slug'],
			'fields' => array('sections' => false),
		));
		if (is_wp_error($api)) {
			return $api;
		}
		$result = $upgrader->install($api->download_link);
	}

	if (is_wp_error($result)) {
		return $result;
	}
	if ($result === false) {
		$errors = $skin->get_errors();
		if (is_wp_error($errors) && $errors->has_errors()) {
			return $errors;
		}
		return new WP_Error('install_failed', 'Installation of ' . $dep['name'] . ' failed.');
	}

	return true;
}

function pw_install_theme($dep) {
	$skin     = new WP_Ajax_Upgrader_Skin();
	$upgrader = new Theme_Upgrader($skin);

	if ($dep['source'] === 'bundled') {
		if (empty($dep['zip']) || !file_exists($dep['zip'])) {
			return new WP_Error('zip_missing', $dep['name'] . ': bundled ZIP file not found at expected path.');
		}
		$result = $upgrader->install($dep['zip']);
	} elseif ( $dep['source'] === 'github_release' ) {
		$releases_url = isset( $dep['releases_url'] ) ? trim( (string) $dep['releases_url'] ) : '';
		$asset        = isset( $dep['github_asset'] ) ? trim( (string) $dep['github_asset'] ) : '';
		if ( $releases_url === '' || $asset === '' ) {
			return new WP_Error( 'pw_dep_github', $dep['name'] . ': missing GitHub releases URL or asset name.' );
		}
		if ( ! function_exists( 'pw_github_get_latest_release_zip_by_asset' ) ) {
			return new WP_Error( 'pw_dep_github', $dep['name'] . ': GitHub release helper not loaded.' );
		}
		$pkg = pw_github_get_latest_release_zip_by_asset( $releases_url, $asset );
		if ( is_wp_error( $pkg ) ) {
			return $pkg;
		}
		$result = $upgrader->install( $pkg['zip_url'] );
	} else {
		$api = themes_api('theme_information', array(
			'slug'   => $dep['slug'],
			'fields' => array('sections' => false),
		));
		if (is_wp_error($api)) {
			return $api;
		}
		$result = $upgrader->install($api->download_link);
	}

	if (is_wp_error($result)) {
		return $result;
	}
	if ($result === false) {
		$errors = $skin->get_errors();
		if (is_wp_error($errors) && $errors->has_errors()) {
			return $errors;
		}
		return new WP_Error('install_failed', 'Installation of ' . $dep['name'] . ' failed.');
	}

	return true;
}

/**
 * Overwrite an installed plugin from WordPress.org or bundled zip.
 *
 * @return true|WP_Error
 */
function pw_upgrade_plugin( $dep ) {
	$skin     = new WP_Ajax_Upgrader_Skin();
	$upgrader = new Plugin_Upgrader( $skin );

	if ( $dep['source'] === 'bundled' ) {
		if ( empty( $dep['zip'] ) || ! file_exists( $dep['zip'] ) ) {
			return new WP_Error( 'zip_missing', $dep['name'] . ': bundled ZIP file not found at expected path.' );
		}
		$package = $dep['zip'];
	} elseif ( $dep['source'] === 'repo' ) {
		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $dep['slug'],
				'fields' => array( 'sections' => false ),
			)
		);
		if ( is_wp_error( $api ) ) {
			return $api;
		}
		if ( empty( $api->download_link ) ) {
			return new WP_Error( 'pw_dep_upgrade', $dep['name'] . ': no download package from WordPress.org.' );
		}
		$package = $api->download_link;
	} else {
		return new WP_Error( 'pw_dep_upgrade', $dep['name'] . ': this source cannot be updated from here.' );
	}

	if ( empty( $dep['file'] ) ) {
		return new WP_Error( 'pw_dep_upgrade', $dep['name'] . ': missing plugin file path.' );
	}

	$result = $upgrader->run(
		array(
			'package'           => $package,
			'destination'       => WP_PLUGIN_DIR,
			'clear_destination' => true,
			'clear_working'     => true,
			'hook_extra'        => array(
				'plugin' => $dep['file'],
				'type'   => 'plugin',
				'action' => 'update',
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
		return new WP_Error(
			'pw_dep_upgrade',
			sprintf(
				/* translators: %s: dependency name */
				__( 'Update failed for %s.', 'portico-webworks' ),
				$dep['name']
			)
		);
	}

	return true;
}

/**
 * Overwrite an installed theme from WordPress.org, GitHub release zip, or bundled zip.
 *
 * @return true|WP_Error
 */
function pw_upgrade_theme( $dep ) {
	$skin     = new WP_Ajax_Upgrader_Skin();
	$upgrader = new Theme_Upgrader( $skin );

	if ( $dep['source'] === 'bundled' ) {
		if ( empty( $dep['zip'] ) || ! file_exists( $dep['zip'] ) ) {
			return new WP_Error( 'zip_missing', $dep['name'] . ': bundled ZIP file not found at expected path.' );
		}
		$package = $dep['zip'];
	} elseif ( $dep['source'] === 'github_release' ) {
		$releases_url = isset( $dep['releases_url'] ) ? trim( (string) $dep['releases_url'] ) : '';
		$asset        = isset( $dep['github_asset'] ) ? trim( (string) $dep['github_asset'] ) : '';
		if ( $releases_url === '' || $asset === '' ) {
			return new WP_Error( 'pw_dep_github', $dep['name'] . ': missing GitHub releases URL or asset name.' );
		}
		if ( ! function_exists( 'pw_github_get_latest_release_zip_by_asset' ) ) {
			return new WP_Error( 'pw_dep_github', $dep['name'] . ': GitHub release helper not loaded.' );
		}
		$pkg = pw_github_get_latest_release_zip_by_asset( $releases_url, $asset );
		if ( is_wp_error( $pkg ) ) {
			return $pkg;
		}
		$package = $pkg['zip_url'];
	} elseif ( $dep['source'] === 'repo' ) {
		$api = themes_api(
			'theme_information',
			array(
				'slug'   => $dep['slug'],
				'fields' => array( 'sections' => false ),
			)
		);
		if ( is_wp_error( $api ) ) {
			return $api;
		}
		if ( empty( $api->download_link ) ) {
			return new WP_Error( 'pw_dep_upgrade', $dep['name'] . ': no download package from WordPress.org.' );
		}
		$package = $api->download_link;
	} else {
		return new WP_Error( 'pw_dep_upgrade', $dep['name'] . ': this source cannot be updated from here.' );
	}

	$result = $upgrader->run(
		array(
			'package'           => $package,
			'destination'       => get_theme_root(),
			'clear_destination' => true,
			'clear_working'     => true,
			'hook_extra'        => array(
				'theme'  => $dep['slug'],
				'type'   => 'theme',
				'action' => 'update',
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
		return new WP_Error(
			'pw_dep_upgrade',
			sprintf(
				/* translators: %s: dependency name */
				__( 'Update failed for %s.', 'portico-webworks' ),
				$dep['name']
			)
		);
	}

	return true;
}
