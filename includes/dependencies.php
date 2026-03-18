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

function pw_get_dependencies() {
	$plugin_dir = plugin_dir_path(PW_PLUGIN_FILE);

	return array(
		array(
			'name'       => 'GeneratePress',
			'slug'       => 'generatepress',
			'type'       => 'theme',
			'source'     => 'repo',
			'file'       => '',
			'required'   => true,
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
	);
}

function pw_dep_status($dep) {
	if (!function_exists('is_plugin_active')) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ($dep['type'] === 'theme') {
		$theme = wp_get_theme($dep['slug']);
		if (!$theme->exists()) {
			return 'not_installed';
		}
		$active_theme = wp_get_theme();
		if ($active_theme->get_stylesheet() === $dep['slug'] || $active_theme->get_template() === $dep['slug']) {
			return 'active';
		}
		return 'installed';
	}

	if (!empty($dep['file'])) {
		$installed = file_exists(WP_PLUGIN_DIR . '/' . $dep['file']);
		if (!$installed) {
			return 'not_installed';
		}
		if (is_plugin_active($dep['file'])) {
			return 'active';
		}
		return 'installed';
	}

	return 'not_installed';
}

function pw_all_deps_satisfied() {
	foreach (pw_get_dependencies() as $dep) {
		if (pw_dep_status($dep) !== 'active') {
			return false;
		}
	}
	return true;
}

// ---------------------------------------------------------------------------
// Admin notice when dependencies are missing
// ---------------------------------------------------------------------------
add_action('admin_notices', function () {
	if (pw_all_deps_satisfied()) {
		return;
	}

	if (!current_user_can('install_plugins')) {
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
// Register the Dependencies tab in the existing admin page
// ---------------------------------------------------------------------------
add_filter('pw_admin_tabs', function ($tabs) {
	if (!pw_all_deps_satisfied()) {
		return array_merge(array('dependencies' => 'Dependencies'), $tabs);
	}
	$tabs['dependencies'] = 'Dependencies';
	return $tabs;
});

// ---------------------------------------------------------------------------
// Render the Dependencies tab content
// ---------------------------------------------------------------------------
add_action('pw_render_tab_dependencies', function () {
	$deps = pw_get_dependencies();
	$can_install = current_user_can('install_plugins') && current_user_can('activate_plugins');
	$can_theme   = current_user_can('switch_themes') && current_user_can('install_themes');

	echo '<div class="pw-card" style="max-width:980px">';
	echo '<div class="pw-card-head"><div class="pw-card-title">Required Dependencies</div>';
	if ($can_install) {
		echo '<button id="pw-dep-install-all" class="button button-primary" style="font-size:12px;padding:4px 14px">Install &amp; Activate All</button>';
	}
	echo '</div>';
	echo '<div class="pw-card-body" style="padding:0">';
	echo '<table class="widefat" style="border:0;box-shadow:none">';
	echo '<thead><tr><th>Name</th><th>Type</th><th>Source</th><th>Status</th><th style="width:160px">Action</th></tr></thead>';
	echo '<tbody>';

	foreach ($deps as $dep) {
		$status = pw_dep_status($dep);
		$badge_color = $status === 'active' ? '#1e8e3e' : ($status === 'installed' ? '#e8a200' : '#b32d15');
		$badge_label = $status === 'active' ? 'Active' : ($status === 'installed' ? 'Installed' : 'Not Installed');
		$source_label = $dep['source'] === 'bundled' ? 'Bundled ZIP' : 'WordPress.org';

		echo '<tr data-pw-dep="' . esc_attr($dep['slug']) . '">';
		echo '<td><strong>' . esc_html($dep['name']) . '</strong></td>';
		echo '<td>' . esc_html(ucfirst($dep['type'])) . '</td>';
		echo '<td>' . esc_html($source_label) . '</td>';
		echo '<td><span class="pw-dep-badge" style="display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600;color:#fff;background:' . $badge_color . '">' . esc_html($badge_label) . '</span></td>';
		echo '<td>';

		if ($status === 'active') {
			echo '<span style="color:#1e8e3e;font-weight:600;font-size:13px">&#10003; Ready</span>';
		} elseif ($status === 'installed') {
			$can = ($dep['type'] === 'theme') ? $can_theme : $can_install;
			if ($can) {
				echo '<button class="button pw-dep-action" data-slug="' . esc_attr($dep['slug']) . '" data-action="activate">Activate</button>';
			} else {
				echo '<em style="color:var(--muted);font-size:12px">Insufficient permissions</em>';
			}
		} else {
			$can = ($dep['type'] === 'theme') ? ($can_theme && $can_install) : $can_install;
			if ($can) {
				if ($dep['source'] === 'bundled' && !empty($dep['zip']) && !file_exists($dep['zip'])) {
					echo '<span style="color:#b32d15;font-size:12px;font-weight:600">ZIP missing</span>';
				} else {
					echo '<button class="button pw-dep-action" data-slug="' . esc_attr($dep['slug']) . '" data-action="install">Install &amp; Activate</button>';
				}
			} else {
				echo '<em style="color:var(--muted);font-size:12px">Insufficient permissions</em>';
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

		function doAction(slug, action, btn) {
			if (btn) {
				btn.disabled = true;
				btn.textContent = action === 'install' ? 'Installing…' : 'Activating…';
			}
			var fd = new FormData();
			fd.append('action', 'portico_dep_action');
			fd.append('_ajax_nonce', nonce);
			fd.append('dep_slug', slug);
			fd.append('dep_action', action);

			return fetch(<?php echo wp_json_encode($ajax_url); ?>, {method:'POST', body:fd, credentials:'same-origin'})
				.then(function(r){ return r.json(); })
				.then(function(data){
					var row = document.querySelector('tr[data-pw-dep="'+slug+'"]');
					if (!row) return data;
					var badgeEl = row.querySelector('.pw-dep-badge');
					var actionTd = row.querySelector('td:last-child');
					if (data.success) {
						if (badgeEl) { badgeEl.style.background = '#1e8e3e'; badgeEl.textContent = 'Active'; }
						if (actionTd) actionTd.innerHTML = '<span style="color:#1e8e3e;font-weight:600;font-size:13px">&#10003; Ready</span>';
					} else {
						var msg = (data.data && data.data.message) ? data.data.message : 'Failed';
						if (badgeEl) { badgeEl.style.background = '#b32d15'; badgeEl.textContent = 'Error'; }
						if (btn) { btn.disabled = false; btn.textContent = 'Retry'; }
						alert(msg);
					}
					return data;
				})
				.catch(function(err){
					if (btn) { btn.disabled = false; btn.textContent = 'Retry'; }
					alert('Request failed: ' + err.message);
				});
		}

		document.querySelectorAll('.pw-dep-action').forEach(function(btn){
			btn.addEventListener('click', function(){
				doAction(btn.getAttribute('data-slug'), btn.getAttribute('data-action'), btn);
			});
		});

		var bulkBtn = document.getElementById('pw-dep-install-all');
		if (bulkBtn) {
			bulkBtn.addEventListener('click', function(){
				bulkBtn.disabled = true;
				bulkBtn.textContent = 'Working…';
				var buttons = Array.from(document.querySelectorAll('.pw-dep-action'));
				var chain = Promise.resolve();
				buttons.forEach(function(btn){
					chain = chain.then(function(){
						return doAction(btn.getAttribute('data-slug'), btn.getAttribute('data-action'), btn);
					});
				});
				chain.then(function(){
					bulkBtn.textContent = 'Done';
					setTimeout(function(){ location.reload(); }, 800);
				});
			});
		}
	})();
	</script>
	<?php
});

// ---------------------------------------------------------------------------
// AJAX handler — install / activate a single dependency
// ---------------------------------------------------------------------------
add_action('wp_ajax_portico_dep_action', function () {
	check_ajax_referer('portico_dep_action');

	if (!current_user_can('install_plugins') || !current_user_can('activate_plugins')) {
		wp_send_json_error(array('message' => 'Insufficient permissions.'));
	}

	$slug   = isset($_POST['dep_slug']) ? sanitize_key($_POST['dep_slug']) : '';
	$action = isset($_POST['dep_action']) ? sanitize_key($_POST['dep_action']) : '';

	$dep = null;
	foreach (pw_get_dependencies() as $d) {
		if ($d['slug'] === $slug) {
			$dep = $d;
			break;
		}
	}

	if (!$dep) {
		wp_send_json_error(array('message' => 'Unknown dependency.'));
	}

	$status = pw_dep_status($dep);

	if ($status === 'active') {
		wp_send_json_success(array('message' => $dep['name'] . ' is already active.'));
	}

	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	require_once ABSPATH . 'wp-admin/includes/theme-install.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	// Install if not present.
	if ($status === 'not_installed') {
		if ($dep['type'] === 'theme') {
			$result = pw_install_theme($dep);
		} else {
			$result = pw_install_plugin($dep);
		}

		if (is_wp_error($result)) {
			wp_send_json_error(array('message' => $result->get_error_message()));
		}
	}

	// Activate.
	if ($dep['type'] === 'theme') {
		if (!current_user_can('switch_themes')) {
			wp_send_json_error(array('message' => 'Insufficient permissions to switch themes.'));
		}
		switch_theme($dep['slug']);
		wp_send_json_success(array('message' => $dep['name'] . ' activated.'));
	}

	$activate = activate_plugin($dep['file']);
	if (is_wp_error($activate)) {
		wp_send_json_error(array('message' => $activate->get_error_message()));
	}

	wp_send_json_success(array('message' => $dep['name'] . ' activated.'));
});

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
