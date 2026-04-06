<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pw_admin_page_slug() {
	return 'portico-webworks';
}

function pw_admin_dependencies_url() {
	return admin_url( 'admin.php?page=' . rawurlencode( pw_admin_page_slug() ) . '&tab=dependencies' );
}

function pw_logo_url() {
	return plugins_url( 'logo.svg', PW_PLUGIN_FILE );
}

function pw_title() {
	return 'Portico Webworks';
}

function pw_version() {
	return defined( 'PW_VERSION' ) ? PW_VERSION : '';
}

add_filter(
	'plugin_action_links_' . plugin_basename( PW_PLUGIN_FILE ),
	static function ( $links ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $links;
		}
		$href = '<a href="' . esc_url( pw_admin_dependencies_url() ) . '">' . esc_html__( 'Dependencies', 'portico-webworks' ) . '</a>';
		array_unshift( $links, $href );
		return $links;
	}
);

add_filter(
	'install_plugin_complete_actions',
	static function ( $install_actions, $api, $plugin_file ) {
		if ( plugin_basename( PW_PLUGIN_FILE ) !== $plugin_file || ! current_user_can( 'manage_options' ) ) {
			return $install_actions;
		}
		$install_actions['pw_plugin_settings'] = '<a href="' . esc_url( pw_admin_dependencies_url() ) . '" target="_parent">' . esc_html__( 'Portico Webworks', 'portico-webworks' ) . '</a>';
		return $install_actions;
	},
	10,
	3
);

add_action(
	'admin_notices',
	static function () {
		if ( ! current_user_can( 'manage_options' ) || ! get_transient( 'pw_activation_settings_notice' ) ) {
			return;
		}
		delete_transient( 'pw_activation_settings_notice' );
		echo '<div class="notice notice-success is-dismissible"><p>';
		echo esc_html__( 'Portico Webworks: open Dependencies to install required plugins and theme.', 'portico-webworks' );
		echo ' <a href="' . esc_url( pw_admin_dependencies_url() ) . '">' . esc_html__( 'Open Dependencies', 'portico-webworks' ) . '</a>';
		echo '</p></div>';
	}
);

add_action(
	'admin_menu',
	static function () {
		add_menu_page(
			'Portico Webworks',
			'Portico Webworks',
			'manage_options',
			pw_admin_page_slug(),
			'pw_render_root_page',
			'dashicons-admin-plugins',
			58
		);
		add_submenu_page(
			pw_admin_page_slug(),
			'Portico Webworks',
			'Dependencies',
			'manage_options',
			pw_admin_page_slug(),
			'pw_render_root_page'
		);
	},
	10
);

function pw_order_admin_tabs( $tabs ) {
	if ( ! is_array( $tabs ) || $tabs === [] ) {
		return $tabs;
	}
	$about_key = 'about';
	$about     = [];
	if ( isset( $tabs[ $about_key ] ) ) {
		$about[ $about_key ] = $tabs[ $about_key ];
		unset( $tabs[ $about_key ] );
	}
	return array_merge( $tabs, $about );
}

function pw_render_root_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$base_tabs = array(
		'dependencies' => __( 'Dependencies', 'portico-webworks' ),
		'about'        => __( 'About', 'portico-webworks' ),
	);
	$tabs       = apply_filters( 'pw_admin_tabs', $base_tabs );
	$tabs       = pw_order_admin_tabs( $tabs );
	$valid_keys = array_keys( $tabs );

	$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['tab'] ) ) : '';
	if ( ! in_array( $tab, $valid_keys, true ) ) {
		$tab = 'dependencies';
	}

	$footer_link = 'https://porticowebworks.com/?utm_source=wp-admin&utm_medium=plugin&utm_campaign=portico_webworks&utm_content=footer';

	echo '<div class="wrap pw-admin">';
	echo '<div class="pw-header">';
	echo '<div class="pw-brand">';
	echo '<img class="pw-logo" src="' . esc_url( pw_logo_url() ) . '" alt="" />';
	echo '<div class="pw-brand-text">';
	echo '<div class="pw-title">' . esc_html( pw_title() ) . '</div>';
	$ver = pw_version();
	if ( $ver !== '' ) {
		echo '<div class="pw-version">v' . esc_html( $ver ) . '</div>';
	}
	echo '</div></div></div>';

	echo '<nav class="pw-tabs" aria-label="Portico Webworks">';
	foreach ( $tabs as $key => $label ) {
		$url = admin_url( 'admin.php?page=' . urlencode( pw_admin_page_slug() ) . '&tab=' . urlencode( $key ) );
		echo '<a class="pw-tab' . ( $tab === $key ? ' is-active' : '' ) . '" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
	}
	echo '</nav>';

	if ( $tab === 'about' ) {
		echo '<div class="pw-card">';
		echo '<div class="pw-card-head"><div class="pw-card-title">' . esc_html__( 'About', 'portico-webworks' ) . '</div></div>';
		echo '<div class="pw-card-body">';
		echo '<p>' . esc_html__( 'This plugin installs and activates the standard Portico Webworks theme and plugin stack.', 'portico-webworks' ) . '</p>';
		$ver = pw_version();
		if ( $ver !== '' ) {
			echo '<p><strong>' . esc_html__( 'Version', 'portico-webworks' ) . '</strong>: ' . esc_html( $ver ) . '</p>';
		}
		echo '</div></div>';
		echo '<div class="pw-footer">';
		echo '<a class="pw-footer-link" href="' . esc_url( $footer_link ) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html( gmdate( 'Y' ) ) . ' Portico Webworks</a>';
		echo '</div>';
		do_action( 'pw_admin_notices' );
		echo '</div>';
		return;
	}

	if ( $tab === 'dependencies' ) {
		do_action( 'pw_render_tab_dependencies' );
		echo '<div class="pw-footer">';
		echo '<a class="pw-footer-link" href="' . esc_url( $footer_link ) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html( gmdate( 'Y' ) ) . ' Portico Webworks</a>';
		echo '</div>';
		do_action( 'pw_admin_notices' );
		echo '</div>';
		return;
	}

	do_action( 'pw_render_tab_' . $tab );
	echo '<div class="pw-footer">';
	echo '<a class="pw-footer-link" href="' . esc_url( $footer_link ) . '" target="_blank" rel="noopener noreferrer">© ' . esc_html( gmdate( 'Y' ) ) . ' Portico Webworks</a>';
	echo '</div>';
	do_action( 'pw_admin_notices' );
	echo '</div>';
}
