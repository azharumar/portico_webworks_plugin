<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'pw_admin_tabs',
	static function ( $tabs ) {
		$tabs['permalinks'] = 'Permalinks';
		return $tabs;
	},
	15
);

add_action( 'admin_post_pw_save_permalinks', 'pw_handle_save_permalinks' );

function pw_handle_save_permalinks() {
	if (
		! isset( $_POST['pw_permalinks_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pw_permalinks_nonce'] ) ), 'pw_save_permalinks' ) ||
		! current_user_can( 'manage_options' )
	) {
		wp_die( 'Unauthorised' );
	}

	$existing = pw_get_merged_pw_settings();
	$fixed    = pw_sanitize_property_base( wp_unslash( $_POST['pw_permalink_base_fixed'] ?? '' ) );

	$settings                        = $existing;
	$settings['pw_permalink_base_fixed'] = $fixed;
	$settings['pw_property_base']        = $fixed;

	if ( ! pw_validate_new_settings_reserved_conflicts( $settings ) ) {
		wp_safe_redirect( add_query_arg( 'pw_permalinks_error', '1', pw_admin_permalinks_url() ) );
		exit;
	}

	update_option( 'pw_settings', $settings );

	$need_flush = ( (string) ( $existing['pw_property_base'] ?? '' ) !== (string) $fixed );
	if ( $need_flush ) {
		set_transient( 'pw_flush_rewrites', 1, 120 );
	}

	wp_safe_redirect(
		add_query_arg(
			'pw_permalinks_saved',
			'1',
			pw_admin_permalinks_url()
		)
	);
	exit;
}

add_action( 'pw_render_tab_permalinks', 'pw_render_permalinks_tab' );

function pw_render_permalinks_tab() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['pw_permalinks_saved'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Permalink settings saved.', 'portico-webworks' ) . '</p></div>';
	}
	if ( isset( $_GET['pw_permalinks_error'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Could not save: reserved slug conflicts with existing content.', 'portico-webworks' ) . '</p></div>';
	}

	$fixed            = pw_get_fixed_permalink_base();
	$permalink_reading = admin_url( 'options-permalink.php' );

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">' . esc_html__( 'Permalinks', 'portico-webworks' ) . '</div></div>';
	echo '<div class="pw-card-body">';

	echo '<p class="description">' . esc_html__( 'Optional path prefix before property slugs in multi-property mode (leave empty for property at site root). Site-wide structure:', 'portico-webworks' ) . ' ';
	echo '<a href="' . esc_url( $permalink_reading ) . '">' . esc_html__( 'Settings → Permalinks', 'portico-webworks' ) . '</a>.</p>';

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="pw_save_permalinks" />';
	wp_nonce_field( 'pw_save_permalinks', 'pw_permalinks_nonce' );

	echo '<table class="form-table" role="presentation"><tbody>';
	echo '<tr><th scope="row"><label for="pw_permalink_base_fixed">' . esc_html__( 'URL prefix', 'portico-webworks' ) . '</label></th><td>';
	echo '<input type="text" class="regular-text" id="pw_permalink_base_fixed" name="pw_permalink_base_fixed" value="' . esc_attr( $fixed ) . '" placeholder="' . esc_attr__( 'empty or e.g. hotels', 'portico-webworks' ) . '" />';
	echo '<p class="description">' . esc_html__( 'Sanitized like a slug. Empty means /your-property-slug/ with no leading segment.', 'portico-webworks' ) . '</p>';
	echo '</td></tr>';
	echo '</tbody></table>';

	submit_button( __( 'Save Permalinks', 'portico-webworks' ) );
	echo '</form>';

	echo '</div></div>';
}
