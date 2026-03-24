<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_admin_page_slug() {
	return 'portico-webworks';
}

function pw_admin_settings_url() {
	return admin_url('admin.php?page=' . rawurlencode(pw_admin_page_slug()) . '&tab=settings');
}

function pw_admin_permalinks_url() {
	return admin_url( 'admin.php?page=' . rawurlencode( pw_admin_page_slug() ) . '&tab=permalinks' );
}

function pw_admin_update_url() {
	return admin_url( 'admin.php?page=' . rawurlencode( pw_admin_page_slug() ) . '&tab=update' );
}

/**
 * Order: General, Permalinks, other registered tabs (e.g. Data, Dependencies), Update, About.
 */
function pw_order_admin_tabs( $tabs ) {
	if ( ! is_array( $tabs ) || $tabs === [] ) {
		return $tabs;
	}
	$settings_key   = 'settings';
	$permalinks_key = 'permalinks';
	$update_key     = 'update';
	$about_key      = 'about';
	$general        = [];
	$permalinks     = [];
	$update         = [];
	$about          = [];
	if ( isset( $tabs[ $settings_key ] ) ) {
		$general[ $settings_key ] = $tabs[ $settings_key ];
		unset( $tabs[ $settings_key ] );
	}
	if ( isset( $tabs[ $permalinks_key ] ) ) {
		$permalinks[ $permalinks_key ] = $tabs[ $permalinks_key ];
		unset( $tabs[ $permalinks_key ] );
	}
	if ( isset( $tabs[ $update_key ] ) ) {
		$update[ $update_key ] = $tabs[ $update_key ];
		unset( $tabs[ $update_key ] );
	}
	if ( isset( $tabs[ $about_key ] ) ) {
		$about[ $about_key ] = $tabs[ $about_key ];
		unset( $tabs[ $about_key ] );
	}
	return array_merge( $general, $permalinks, $tabs, $update, $about );
}

add_filter('plugin_action_links_' . plugin_basename(PW_PLUGIN_FILE), function ($links) {
	if (!current_user_can('manage_options')) {
		return $links;
	}
	$settings = '<a href="' . esc_url(pw_admin_settings_url()) . '">' . esc_html('Settings') . '</a>';
	array_unshift($links, $settings);
	return $links;
});

add_filter('install_plugin_complete_actions', function ($install_actions, $api, $plugin_file) {
	if (plugin_basename(PW_PLUGIN_FILE) !== $plugin_file || !current_user_can('manage_options')) {
		return $install_actions;
	}
	$install_actions['pw_plugin_settings'] = '<a href="' . esc_url(pw_admin_settings_url()) . '" target="_parent">' . esc_html('Plugin settings') . '</a>';
	return $install_actions;
}, 10, 3);

add_action('admin_notices', function () {
	if (!current_user_can('manage_options') || !get_transient('pw_activation_settings_notice')) {
		return;
	}
	delete_transient('pw_activation_settings_notice');
	$url = pw_admin_settings_url();
	echo '<div class="notice notice-success is-dismissible"><p>';
	echo esc_html('Portico Webworks Hotel Website Manager activated.') . ' ';
	echo '<a href="' . esc_url($url) . '">' . esc_html('Open plugin settings') . '</a>';
	echo '</p></div>';
});

function pw_add_menu_divider( $label, $slug_suffix ) {
	add_submenu_page(
		pw_admin_page_slug(),
		'',
		'<span class="pw-menu-divider">' . esc_html( $label ) . '</span>',
		'manage_options',
		'#pw-divider-' . $slug_suffix,
		'__return_false'
	);
}

function pw_logo_url() {
	return plugins_url('logo.svg', PW_PLUGIN_FILE);
}

add_action('admin_menu', function () {
	add_menu_page(
		'Portico Webworks',
		'Portico Webworks',
		'manage_options',
		pw_admin_page_slug(),
		'pw_render_root_page',
		'dashicons-building',
		58
	);

	add_submenu_page(
		pw_admin_page_slug(),
		'Portico Webworks',
		'Settings',
		'manage_options',
		pw_admin_page_slug(),
		'pw_render_root_page'
	);
}, 10);

add_action('admin_menu', function () {
	pw_add_menu_divider( 'Properties', 'properties' );

	add_submenu_page(
		pw_admin_page_slug(),
		'All Properties',
		'All Properties',
		'manage_options',
		'edit.php?post_type=pw_property'
	);

	add_submenu_page(
		pw_admin_page_slug(),
		'Add New Property',
		'Add New Property',
		'manage_options',
		'post-new.php?post_type=pw_property'
	);

	pw_add_menu_divider( 'Property Content', 'property-content' );

	add_submenu_page( pw_admin_page_slug(), 'Room Types',    'Room Types',    'manage_options', 'edit.php?post_type=pw_room_type' );
	add_submenu_page( pw_admin_page_slug(), 'Features',      'Features',      'manage_options', 'edit.php?post_type=pw_feature' );
	add_submenu_page( pw_admin_page_slug(), 'Restaurants',   'Restaurants',   'manage_options', 'edit.php?post_type=pw_restaurant' );
	add_submenu_page( pw_admin_page_slug(), 'Spas',          'Spas',          'manage_options', 'edit.php?post_type=pw_spa' );
	add_submenu_page( pw_admin_page_slug(), 'Meeting Rooms', 'Meeting Rooms', 'manage_options', 'edit.php?post_type=pw_meeting_room' );
	add_submenu_page( pw_admin_page_slug(), 'Contacts',      'Contacts',      'manage_options', 'edit.php?post_type=pw_contact' );
	add_submenu_page( pw_admin_page_slug(), 'Amenities',     'Amenities',     'manage_options', 'edit.php?post_type=pw_amenity' );
	add_submenu_page( pw_admin_page_slug(), 'Policies',      'Policies',      'manage_options', 'edit.php?post_type=pw_policy' );

	pw_add_menu_divider( 'Marketing', 'marketing' );

	add_submenu_page( pw_admin_page_slug(), 'Offers',      'Offers',      'manage_options', 'edit.php?post_type=pw_offer' );
	add_submenu_page( pw_admin_page_slug(), 'Experiences', 'Experiences', 'manage_options', 'edit.php?post_type=pw_experience' );
	add_submenu_page( pw_admin_page_slug(), 'Events',      'Events',      'manage_options', 'edit.php?post_type=pw_event' );
	add_submenu_page( pw_admin_page_slug(), 'Nearby',      'Nearby',      'manage_options', 'edit.php?post_type=pw_nearby' );
	add_submenu_page( pw_admin_page_slug(), 'FAQs',        'FAQs',        'manage_options', 'edit.php?post_type=pw_faq' );
}, 30);

function pw_title() {
	return 'Portico Webworks Hotel Website Manager';
}

function pw_version() {
	return defined('PW_VERSION') ? PW_VERSION : '';
}

add_action(
	'admin_notices',
	static function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( get_transient( 'pw_settings_mode_changed_notice' ) ) {
			delete_transient( 'pw_settings_mode_changed_notice' );
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Property mode was updated. Rewrite rules were flushed. Update internal links if URLs changed.', 'portico-webworks' ) . '</p></div>';
		}
		$err = get_transient( 'pw_settings_section_base_error' );
		if ( $err ) {
			delete_transient( 'pw_settings_section_base_error' );
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( (string) $err ) . '</p></div>';
		}
	}
);

add_action(
	'admin_notices',
	static function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$last = get_transient( 'pw_installer_last_run' );
		if ( is_array( $last ) ) {
			delete_transient( 'pw_installer_last_run' );
			$pt    = isset( $last['property_title'] ) ? (string) $last['property_title'] : '';
			$pages = admin_url( 'edit.php?post_type=page' );
			$gpel  = admin_url( 'edit.php?post_type=gp_elements' );
			echo '<div class="notice notice-success is-dismissible"><p>';
			echo esc_html(
				sprintf(
					/* translators: %s: property title */
					__( 'Property "%s" was published. Site structure (pages and section archive elements) was updated where needed.', 'portico-webworks' ),
					$pt !== '' ? $pt : __( '(untitled)', 'portico-webworks' )
				)
			);
			echo ' <a href="' . esc_url( $pages ) . '">' . esc_html__( 'Pages', 'portico-webworks' ) . '</a>';
			if ( post_type_exists( 'gp_elements' ) ) {
				echo ' · <a href="' . esc_url( $gpel ) . '">' . esc_html__( 'Elements', 'portico-webworks' ) . '</a>';
			}
			echo '</p></div>';
		}
		$ran = isset( $_GET['pw_installer_ran'] ) ? sanitize_text_field( wp_unslash( $_GET['pw_installer_ran'] ) ) : '';
		if ( $ran === '1' ) {
			$res = get_transient( 'pw_installer_manual_results' );
			delete_transient( 'pw_installer_manual_results' );
			if ( is_array( $res ) ) {
				$c    = (int) ( $res['created'] ?? 0 );
				$u    = (int) ( $res['updated'] ?? 0 );
				$uc   = (int) ( $res['unchanged'] ?? 0 );
				$cf   = (int) ( $res['conflict'] ?? 0 );
				$msgs = isset( $res['conflict_messages'] ) && is_array( $res['conflict_messages'] ) ? $res['conflict_messages'] : [];
				$class = $cf > 0 ? 'notice-warning' : 'notice-success';
				echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>';
				echo esc_html(
					sprintf(
						/* translators: 1: created count, 2: updated, 3: unchanged, 4: conflicts */
						__( 'Structure installer finished: %1$d created, %2$d updated, %3$d unchanged, %4$d conflicts.', 'portico-webworks' ),
						$c,
						$u,
						$uc,
						$cf
					)
				);
				echo '</p>';
				if ( $msgs !== [] ) {
					echo '<ul style="list-style:disc;margin-left:1.5em;">';
					foreach ( $msgs as $m ) {
						echo '<li>' . esc_html( (string) $m ) . '</li>';
					}
					echo '</ul>';
				}
				echo '</div>';
			}
		}
		$gp_notice = get_transient( 'pw_installer_gp_elements_notice' );
		if ( is_string( $gp_notice ) && $gp_notice !== '' ) {
			delete_transient( 'pw_installer_gp_elements_notice' );
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html( $gp_notice ) . '</p></div>';
		}
	}
);

add_action('admin_notices', function () {
	if (!current_user_can('manage_options')) {
		return;
	}
	if (get_transient('pw_settings_notice_default_property')) {
		delete_transient('pw_settings_notice_default_property');
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html('Default property was cleared because the selected post is not a published property.') . '</p></div>';
	}
	$mode = pw_get_setting('pw_property_mode', 'single');
	if ($mode !== 'single') {
		return;
	}
	$pid = (int) pw_get_setting('pw_default_property_id', 0);
	if ($pid > 0) {
		return;
	}
	$screen = function_exists('get_current_screen') ? get_current_screen() : null;
	if ($screen && $screen->id === 'toplevel_page_' . pw_admin_page_slug()) {
		$url = pw_admin_settings_url();
		echo '<div class="notice notice-warning is-dismissible"><p>';
		echo esc_html('Single-property mode requires a default property. Select one under General settings.');
		echo ' <a href="' . esc_url($url) . '">' . esc_html('Open settings') . '</a>';
		echo '</p></div>';
	}
});

function pw_cmb2_published_property_options( $field = null ) {
	$opts = array();
	foreach ( pw_get_all_properties() as $row ) {
		$id = isset( $row['id'] ) ? (int) $row['id'] : 0;
		if ( $id <= 0 || get_post_status( $id ) !== 'publish' ) {
			continue;
		}
		$opts[ (string) $id ] = isset( $row['name'] ) ? (string) $row['name'] : '#' . $id;
	}
	return $opts;
}

function pw_handle_settings_save() {
	if (
		!isset($_POST['pw_settings_nonce']) ||
		!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pw_settings_nonce'])), 'pw_save_settings') ||
		!current_user_can('manage_options')
	) {
		return;
	}

	$existing = pw_get_merged_pw_settings();
	$mode     = isset( $_POST['pw_property_mode'] ) && $_POST['pw_property_mode'] === 'multi' ? 'multi' : 'single';

	$settings                     = $existing;
	$settings['pw_property_mode'] = $mode;
	unset( $settings['pw_default_template'] );
	$settings['pw_default_property_id'] = $mode === 'single' ? (int) ( $_POST['pw_default_property_id'] ?? 0 ) : 0;

	if ( $mode === 'single' && $settings['pw_default_property_id'] > 0 ) {
		$pid = $settings['pw_default_property_id'];
		if ( get_post_type( $pid ) !== 'pw_property' || get_post_status( $pid ) !== 'publish' ) {
			$settings['pw_default_property_id'] = 0;
			set_transient( 'pw_settings_notice_default_property', 1, 60 );
		}
	}

	if ( ! pw_validate_new_settings_reserved_conflicts( $settings ) ) {
		// Admin redirect — pw_redirect_with_qs() not required.
		wp_safe_redirect( add_query_arg( 'pw_settings_error', '1', wp_get_referer() ?: pw_admin_settings_url() ) );
		exit;
	}

	$need_flush = ( $mode !== $existing['pw_property_mode'] );

	update_option( 'pw_settings', $settings );

	if ( $mode !== $existing['pw_property_mode'] ) {
		pw_run_page_installer_all_scopes();
	}

	if ( $need_flush ) {
		set_transient( 'pw_flush_rewrites', 1, 120 );
	}
	if ( $mode !== $existing['pw_property_mode'] ) {
		set_transient( 'pw_settings_mode_changed_notice', 1, 120 );
	}

	// Admin redirect — pw_redirect_with_qs() not required.
	wp_safe_redirect(add_query_arg('settings-updated', 'true', wp_get_referer() ?: pw_admin_settings_url()));
	exit;
}

add_action(
	'admin_init',
	static function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! get_transient( 'pw_flush_rewrites' ) ) {
			return;
		}
		delete_transient( 'pw_flush_rewrites' );
		flush_rewrite_rules( false );
	}
);
add_action('admin_post_pw_save_settings', 'pw_handle_settings_save');

/**
 * Site structure tables + installer (Permalinks tab); not inside another form.
 */
function pw_render_page_structure_admin_panel() {
	echo '<div class="pw-subsection-title" style="margin-top:1.25em;">' . esc_html__( 'Site structure', 'portico-webworks' ) . '</div>';
	echo '<p class="description">' . esc_html__( 'Installer-managed static pages (Fact Sheet per property scope) and GeneratePress Elements for section archives. Section listings use Elements, not Pages. Starter block markup is inserted only when an element or page is first created.', 'portico-webworks' ) . '</p>';
	echo '<details class="pw-page-structure"><summary style="cursor:pointer;font-weight:600;margin-top:0.5em;">' . esc_html__( 'Required pages and section archive elements', 'portico-webworks' ) . '</summary>';

	$pages_list_url = admin_url( 'edit.php?post_type=page' );
	$gp_list_url    = admin_url( 'edit.php?post_type=gp_elements' );

	echo '<p class="description" style="margin-top:0.75em;"><strong>' . esc_html__( 'Pages', 'portico-webworks' ) . '</strong></p>';
	echo '<table class="widefat striped pw-page-structure-table" style="margin-top:0.5em;"><thead><tr>';
	echo '<th>' . esc_html__( 'Page', 'portico-webworks' ) . '</th>';
	echo '<th>' . esc_html__( 'Slug', 'portico-webworks' ) . '</th>';
	echo '<th>' . esc_html__( 'Scope', 'portico-webworks' ) . '</th>';
	echo '<th>' . esc_html__( 'Status', 'portico-webworks' ) . '</th>';
	echo '</tr></thead><tbody>';
	$page_rows = pw_get_page_structure_display_rows();
	if ( $page_rows === [] ) {
		echo '<tr><td colspan="4">' . esc_html__( 'No installer-managed pages.', 'portico-webworks' ) . '</td></tr>';
	} else {
		foreach ( $page_rows as $pr ) {
			$slug      = sanitize_title( $pr['slug'] ?? '' );
			$pid_scope = (int) ( $pr['property_id'] ?? 0 );
			$gen       = pw_find_generated_page( $slug, $pid_scope );
			if ( $gen instanceof WP_Post ) {
				$elink  = get_edit_post_link( $gen->ID, 'raw' );
				$status = '<span style="color:#007017;">' . esc_html__( 'Exists', 'portico-webworks' ) . '</span>';
				if ( is_string( $elink ) && $elink !== '' ) {
					$status .= ' <a href="' . esc_url( $elink ) . '">' . esc_html__( 'Edit', 'portico-webworks' ) . '</a>';
				}
			} else {
				$by_path = get_page_by_path( $slug, OBJECT, 'page' );
				if ( $by_path instanceof WP_Post && get_post_meta( $by_path->ID, '_pw_generated', true ) !== '1' ) {
					$elink  = get_edit_post_link( $by_path->ID, 'raw' );
					$status = '<span style="color:#b32d2e;">' . esc_html__( 'Conflict', 'portico-webworks' ) . '</span>';
					if ( is_string( $elink ) && $elink !== '' ) {
						$status .= ' <a href="' . esc_url( $elink ) . '">' . esc_html__( 'View page', 'portico-webworks' ) . '</a>';
					}
				} else {
					$status = '<span style="color:#996800;">' . esc_html__( 'Missing', 'portico-webworks' ) . '</span>';
				}
			}
			$title = isset( $pr['title'] ) ? (string) $pr['title'] : $slug;
			$scope = isset( $pr['property_label'] ) ? (string) $pr['property_label'] : '';
			echo '<tr>';
			echo '<td>' . esc_html( $title ) . '</td>';
			echo '<td><code>' . esc_html( $slug ) . '</code></td>';
			echo '<td>' . esc_html( $scope ) . '</td>';
			echo '<td>' . wp_kses_post( $status ) . '</td>';
			echo '</tr>';
		}
	}
	echo '</tbody></table>';

	$menus_url = admin_url( 'nav-menus.php' );
	echo '<p class="description" style="margin-top:1.25em;"><strong>' . esc_html__( 'GP Elements', 'portico-webworks' ) . '</strong> ';
	echo esc_html__( 'Edit header menus under', 'portico-webworks' );
	echo ' <a href="' . esc_url( $menus_url ) . '">' . esc_html__( 'Appearance → Menus', 'portico-webworks' ) . '</a>';
	echo ' — ' . esc_html__( 'assign links to', 'portico-webworks' );
	echo ' <code>' . esc_html( PW_NAV_MENU_PRIMARY ) . '</code> ';
	echo esc_html__( '(primary) and', 'portico-webworks' );
	echo ' <code>' . esc_html( PW_NAV_MENU_UTILITY ) . '</code> ';
	echo esc_html__( '(utility).', 'portico-webworks' );
	echo '</p>';
	echo '<table class="widefat striped pw-page-structure-table" style="margin-top:0.5em;"><thead><tr>';
	echo '<th>' . esc_html__( 'Element title', 'portico-webworks' ) . '</th>';
	echo '<th>' . esc_html__( 'CPT', 'portico-webworks' ) . '</th>';
	echo '<th>' . esc_html__( 'Type', 'portico-webworks' ) . '</th>';
	echo '<th>' . esc_html__( 'Published', 'portico-webworks' ) . '</th>';
	echo '<th>' . esc_html__( 'Status', 'portico-webworks' ) . '</th>';
	echo '</tr></thead><tbody>';
	$gp_active = post_type_exists( 'gp_elements' );
	foreach ( pw_get_required_elements() as $edef ) {
		$cpt    = (string) ( $edef['cpt'] ?? '' );
		$title  = (string) ( $edef['title'] ?? $cpt );
		$etype  = (string) ( $edef['type'] ?? 'archive' );
		$count     = wp_count_posts( $cpt );
		$published = ( isset( $count->publish ) ? (int) $count->publish : 0 );
		if ( ! $gp_active ) {
			$status = '<span style="color:#996800;">' . esc_html__( 'GP Not Active', 'portico-webworks' ) . '</span>';
		} else {
			$gen = pw_find_generated_element( $cpt, $etype );
			if ( $gen instanceof WP_Post ) {
				$elink  = get_edit_post_link( $gen->ID, 'raw' );
				$status = '<span style="color:#007017;">' . esc_html__( 'Exists', 'portico-webworks' ) . '</span>';
				if ( is_string( $elink ) && $elink !== '' ) {
					$status .= ' <a href="' . esc_url( $elink ) . '">' . esc_html__( 'Edit', 'portico-webworks' ) . '</a>';
				}
			} else {
				$status = '<span style="color:#b32d2e;">' . esc_html__( 'Missing', 'portico-webworks' ) . '</span>';
			}
		}
		$type_label = $etype === 'singular'
			? esc_html__( 'Singular', 'portico-webworks' )
			: esc_html__( 'Archive', 'portico-webworks' );
		echo '<tr>';
		echo '<td>' . esc_html( $title ) . '</td>';
		echo '<td><code>' . esc_html( $cpt ) . '</code></td>';
		echo '<td>' . esc_html( $type_label ) . '</td>';
		echo '<td>' . esc_html( (string) $published ) . '</td>';
		echo '<td>' . wp_kses_post( $status ) . '</td>';
		echo '</tr>';
	}
	if ( $gp_active ) {
		$sh = function_exists( 'pw_find_generated_site_header_element' ) ? pw_find_generated_site_header_element() : null;
		$sh_status = $sh instanceof WP_Post
			? '<span style="color:#007017;">' . esc_html__( 'Exists', 'portico-webworks' ) . '</span>'
			: '<span style="color:#b32d2e;">' . esc_html__( 'Missing', 'portico-webworks' ) . '</span>';
		if ( $sh instanceof WP_Post ) {
			$elink = get_edit_post_link( $sh->ID, 'raw' );
			if ( is_string( $elink ) && $elink !== '' ) {
				$sh_status .= ' <a href="' . esc_url( $elink ) . '">' . esc_html__( 'Edit', 'portico-webworks' ) . '</a>';
			}
		}
		echo '<tr>';
		echo '<td>' . esc_html__( 'Portico Site Header', 'portico-webworks' ) . '</td>';
		echo '<td><code>—</code></td>';
		echo '<td>' . esc_html__( 'Site header', 'portico-webworks' ) . '</td>';
		echo '<td>—</td>';
		echo '<td>' . wp_kses_post( $sh_status ) . '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:1em;">';
	echo '<input type="hidden" name="action" value="pw_run_page_installer" />';
	wp_nonce_field( 'pw_run_page_installer' );
	submit_button( esc_attr__( 'Install Missing Structure', 'portico-webworks' ), 'secondary', 'pw-run-page-installer', false );
	echo '</form>';
	echo '<p class="description">' . esc_html__( 'Creates missing pages and GeneratePress Elements; aligns page slugs with current URL settings. Existing content is not overwritten.', 'portico-webworks' ) . ' ';
	echo '<a href="' . esc_url( $pages_list_url ) . '">' . esc_html__( 'All pages', 'portico-webworks' ) . '</a>';
	if ( $gp_active ) {
		echo ' · <a href="' . esc_url( $gp_list_url ) . '">' . esc_html__( 'All elements', 'portico-webworks' ) . '</a>';
	}
	echo '</p>';
	echo '</details>';
}

function pw_render_root_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$base_tabs = array(
		'settings' => 'General',
		'about'    => 'About',
	);
	$tabs       = apply_filters( 'pw_admin_tabs', $base_tabs );
	$tabs       = pw_order_admin_tabs( $tabs );
	$valid_keys = array_keys( $tabs );

	$tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';
	if (!in_array($tab, $valid_keys, true)) {
		$tab = $valid_keys[0];
	}

	$footer_link = 'https://porticowebworks.com/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=portico_webworks&utm_content=footer';

	echo '<div class="wrap pw-admin" data-pw-property-mode="' . esc_attr( pw_get_setting( 'pw_property_mode', 'single' ) ) . '">';
	echo '<div class="pw-header">';
	echo '<div class="pw-brand">';
	echo '<img class="pw-logo" src="' . esc_url(pw_logo_url()) . '" alt="" />';
	echo '<div class="pw-brand-text">';
	echo '<div class="pw-title">' . esc_html(pw_title()) . '</div>';
	$blog_public = get_option('blog_public', 1);
	$blog_public = is_numeric($blog_public) ? (int) $blog_public : 1;
	$indexing_on = $blog_public === 1;
	$ver = pw_version();
	if ($ver !== '') {
		echo '<div class="pw-version">v' . esc_html($ver) . '</div>';
	}

	$mode_label = $indexing_on ? 'Search engine indexing ON' : 'Search engine indexing OFF';
	$mode_class = $indexing_on ? 'is-production' : 'is-development';
	echo '<div class="pw-mode ' . esc_attr($mode_class) . '">' . esc_html($mode_label) . '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';

	echo '<nav class="pw-tabs" aria-label="Portico Webworks">';
	foreach ($tabs as $key => $label) {
		$url = admin_url('admin.php?page=' . urlencode(pw_admin_page_slug()) . '&tab=' . urlencode($key));
		echo '<a class="pw-tab' . ($tab === $key ? ' is-active' : '') . '" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
	}
	echo '</nav>';

	if ( ! in_array( $tab, array( 'settings', 'about' ), true ) ) {
		do_action('pw_render_tab_' . $tab);
		echo '<div class="pw-footer">';
		echo '<a class="pw-footer-link" href="' . esc_url($footer_link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
		echo '</div>';
		do_action('pw_admin_notices');
		echo '</div>';
		return;
	}

	if ($tab === 'about') {
		echo '<div class="pw-card">';
		echo '<div class="pw-card-head"><div class="pw-card-title">About</div></div>';
		echo '<div class="pw-card-body">';
		$ver = pw_version();
		$ver_text = $ver !== '' ? 'v' . $ver : '';

		echo '<p><strong>Portico Webworks Hotel Website Manager</strong> helps you manage key hotel website profile details inside WordPress.</p>';
		echo '<p>This plugin is deployed on our client hotel websites to enhance WordPress functionality to suit hotel-specific needs.</p>';
		echo '<p><strong>Portico Webworks</strong>: A specialized boutique hotel website agency serving mid-scale, upper mid-scale, and upscale independent properties.</p>';
		echo '<p><strong>Parent company (ZES)</strong>: Zarnik Enterprise Services Private Limited (CIN: U62011KL2024PTC090989, incorporated 16-12-2024).</p>';
		echo '<p><strong>Intellectual Property</strong>: Developed by the Portico Webworks team. This plugin is the intellectual property of Zarnik Enterprise Services Private Limited and is not allowed to be used for any other companies or for any purposes.</p>';
		echo '<p><strong>Connect</strong>: ';
		echo '<a href="' . esc_url('https://porticowebworks.com/') . '" target="_blank" rel="noopener noreferrer">porticowebworks.com</a> | ';
		echo '<a href="' . esc_url('https://www.linkedin.com/company/porticowebworks/') . '" target="_blank" rel="noopener noreferrer">LinkedIn</a> | ';
		echo '<a href="' . esc_url('https://www.facebook.com/porticowebworks') . '" target="_blank" rel="noopener noreferrer">Facebook</a> | ';
		echo '<a href="' . esc_url('https://www.instagram.com/porticowebworks/') . '" target="_blank" rel="noopener noreferrer">Instagram</a>';
		echo '</p>';

		if ($ver_text !== '') {
			echo '<p><strong>Plugin version</strong>: ' . esc_html($ver_text) . '</p>';
		}
		echo '</div>';
		echo '</div>';
		echo '<div class="pw-footer">';
		echo '<a class="pw-footer-link" href="' . esc_url($footer_link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
		echo '</div>';
		do_action('pw_admin_notices');
		echo '</div>';
		return;
	}

	if ($tab === 'settings') {
		$blog_public = get_option('blog_public', 1);
		$blog_public = is_numeric($blog_public) ? (int) $blog_public : 1;
		$indexing_on = $blog_public === 1;
		echo '<div class="pw-card">';
		echo '<div class="pw-card-head"><div class="pw-card-title">General</div></div>';
		echo '<div class="pw-card-body">';
		if ( isset( $_GET['settings-updated'] ) && sanitize_text_field( wp_unslash( (string) $_GET['settings-updated'] ) ) === 'true' ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'portico-webworks' ) . '</p></div>';
		}
		if ( isset( $_GET['pw_settings_error'] ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Could not save settings.', 'portico-webworks' ) . '</p></div>';
		}
		echo '<table class="form-table" role="presentation"><tbody>';
		echo '<tr><th scope="row">Search Engine Indexing</th><td>';
		echo '<strong>' . esc_html($indexing_on ? 'ON' : 'OFF') . '</strong>';
		$reading_url = admin_url('options-reading.php');
		echo '<p class="description">Controlled by WordPress Settings → Reading → "Discourage search engines from indexing this site". ';
		echo '<a href="' . esc_url($reading_url) . '">Open Reading settings</a></p>';
		echo '</td></tr>';
		echo '</tbody></table>';

		$settings_mode          = pw_get_setting('pw_property_mode', 'single');
		$default_property_id    = (int) pw_get_setting('pw_default_property_id', 0);
		$property_select_opts   = pw_cmb2_published_property_options();

		echo '<form class="pw-settings-form" action="' . esc_url(admin_url('admin-post.php')) . '" method="post" id="pw_settings" data-pw-saved-mode="' . esc_attr( $settings_mode ) . '">';
		echo '<input type="hidden" name="action" value="pw_save_settings" />';
		wp_nonce_field('pw_save_settings', 'pw_settings_nonce');
		echo '<table class="form-table" role="presentation"><tbody>';

		echo '<tr><th scope="row">' . esc_html('Property Mode') . '</th><td>';
		echo '<fieldset><label><input type="radio" name="pw_property_mode" value="single" class="pw-property-mode-radio"' . checked($settings_mode, 'single', false) . ' /> ' . esc_html('Single Property') . '</label><br />';
		echo '<label><input type="radio" name="pw_property_mode" value="multi" class="pw-property-mode-radio"' . checked($settings_mode, 'multi', false) . ' /> ' . esc_html('Multi-Property') . '</label></fieldset>';
		echo '</td></tr>';

		echo '<tr class="pw-default-property-row"><th scope="row"><label for="pw_default_property_id">' . esc_html('Default property') . '</label></th><td>';
		echo '<select name="pw_default_property_id" id="pw_default_property_id">';
		echo '<option value="0"' . selected($default_property_id, 0, false) . '>' . esc_html('— Select property —') . '</option>';
		foreach ($property_select_opts as $opt_id => $opt_label) {
			$oid = (int) $opt_id;
			echo '<option value="' . esc_attr((string) $oid) . '"' . selected($default_property_id, $oid, false) . '>' . esc_html($opt_label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . esc_html('Used as the site-wide property context in Single Property mode (required).') . '</p>';
		echo '</td></tr>';

		echo '</tbody></table>';

		echo '<div id="pw-mode-switch-warning-multi" class="notice notice-warning inline" style="display:none;margin:1em 0;"><p>' . esc_html__( 'Switching from multi-property to single-property mode will break all existing property-prefixed URLs. There is no automatic redirect mapping. Ensure this is intentional before saving.', 'portico-webworks' ) . '</p></div>';
		echo '<div id="pw-mode-switch-warning-single" class="notice notice-warning inline" style="display:none;margin:1em 0;"><p>' . esc_html__( 'Switching to multi-property mode will break all existing URLs that do not include a property slug prefix. There is no automatic redirect mapping. Ensure this is intentional before saving.', 'portico-webworks' ) . '</p></div>';

		echo '<p class="description" style="margin-top:1em;">' . esc_html__( 'URL segments, section bases, and listing pages are configured under the Permalinks tab.', 'portico-webworks' ) . ' ';
		echo '<a href="' . esc_url( pw_admin_permalinks_url() ) . '">' . esc_html__( 'Open Permalinks', 'portico-webworks' ) . '</a>';
		if ( current_user_can( 'update_plugins' ) ) {
			echo ' ' . esc_html__( 'Plugin updates from GitHub are under the Update tab.', 'portico-webworks' ) . ' ';
			echo '<a href="' . esc_url( pw_admin_update_url() ) . '">' . esc_html__( 'Open Update', 'portico-webworks' ) . '</a>';
		}
		echo '</p>';

		submit_button( esc_attr__( 'Save General', 'portico-webworks' ), 'primary', 'pw-save-settings' );
		echo '</form>';

		echo '</div>';
		echo '</div>';

		echo '<div class="pw-footer">';
		echo '<a class="pw-footer-link" href="' . esc_url($footer_link) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html(gmdate('Y')) . ' Portico Webworks</a>';
		echo '</div>';
		do_action('pw_admin_notices');
		echo '</div>';
		return;
	}

}

