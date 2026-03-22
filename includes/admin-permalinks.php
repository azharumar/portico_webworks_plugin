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

	$settings = $existing;

	$new_bases  = pw_default_section_bases();
	$post_bases = isset( $_POST['pw_section_bases'] ) && is_array( $_POST['pw_section_bases'] ) ? wp_unslash( $_POST['pw_section_bases'] ) : [];
	foreach ( $new_bases as $cpt => $def ) {
		if ( ! isset( $post_bases[ $cpt ] ) || ! is_array( $post_bases[ $cpt ] ) ) {
			continue;
		}
		$p = sanitize_title( (string) ( $post_bases[ $cpt ]['plural'] ?? '' ) );
		$s = sanitize_title( (string) ( $post_bases[ $cpt ]['singular'] ?? '' ) );
		if ( $p === '' || $s === '' ) {
			set_transient( 'pw_settings_section_base_error', __( 'Plural and singular bases cannot be empty.', 'portico-webworks' ), 120 );
			wp_safe_redirect( wp_get_referer() ?: pw_admin_permalinks_url() );
			exit;
		}
		if ( $p === $s ) {
			set_transient( 'pw_settings_section_base_error', __( 'Plural and singular bases must differ for each section.', 'portico-webworks' ), 120 );
			wp_safe_redirect( wp_get_referer() ?: pw_admin_permalinks_url() );
			exit;
		}
		$new_bases[ $cpt ] = [ 'plural' => $p, 'singular' => $s ];
	}
	$settings['pw_section_bases'] = $new_bases;

	if ( ! pw_validate_new_settings_reserved_conflicts( $settings ) ) {
		wp_safe_redirect( add_query_arg( 'pw_permalinks_error', '1', pw_admin_permalinks_url() ) );
		exit;
	}

	$need_flush = wp_json_encode( $existing['pw_section_bases'] ?? [] ) !== wp_json_encode( $settings['pw_section_bases'] );

	update_option( 'pw_settings', $settings );

	if ( pw_installer_section_plural_bases_changed( $existing, $settings ) ) {
		pw_run_page_installer_all_scopes();
	}

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

	$permalink_reading = admin_url( 'options-permalink.php' );
	$sb                = pw_get_section_bases();

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">' . esc_html__( 'Permalinks', 'portico-webworks' ) . '</div></div>';
	echo '<div class="pw-card-body">';

	echo '<p class="description">' . esc_html__( 'URL segments for section listings and outlets. Property URLs use the property slug only (no extra base segment). WordPress permalink structure:', 'portico-webworks' ) . ' ';
	echo '<a href="' . esc_url( $permalink_reading ) . '">' . esc_html__( 'Settings → Permalinks', 'portico-webworks' ) . '</a>.</p>';

	echo '<form class="pw-permalinks-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="pw_save_permalinks" />';
	wp_nonce_field( 'pw_save_permalinks', 'pw_permalinks_nonce' );

	echo '<div class="pw-subsection-title">' . esc_html__( 'Section URL bases', 'portico-webworks' ) . '</div>';
	echo '<p class="description">' . esc_html__( 'Plural segment lists outlets; singular prefix is used for single outlet URLs.', 'portico-webworks' ) . '</p>';
	$mode = pw_get_setting( 'pw_property_mode', 'single' );

	echo '<table class="widefat striped pw-section-bases-table" style="margin-top:0.75em;"><thead><tr>';
	echo '<th scope="col">' . esc_html__( 'Section', 'portico-webworks' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Singular', 'portico-webworks' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Plural', 'portico-webworks' ) . '</th>';
	echo '<th scope="col">' . esc_html__( 'Example URL', 'portico-webworks' ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( pw_url_section_cpts() as $cpt ) {
		$pto   = get_post_type_object( $cpt );
		$label = ( $pto && isset( $pto->labels->name ) ) ? $pto->labels->name : $cpt;
		$p     = $sb[ $cpt ]['plural'] ?? '';
		$s     = $sb[ $cpt ]['singular'] ?? '';
		if ( $mode === 'single' ) {
			$example = untrailingslashit( home_url( '/' . $s . '/outlet-slug' ) );
		} else {
			$example = untrailingslashit( home_url( '/property-slug/' . $s . '/outlet-slug' ) );
		}
		echo '<tr>';
		echo '<th scope="row">' . esc_html( $label ) . '</th>';
		echo '<td><input type="text" class="regular-text" name="pw_section_bases[' . esc_attr( $cpt ) . '][singular]" value="' . esc_attr( $s ) . '" /></td>';
		echo '<td><input type="text" class="regular-text" name="pw_section_bases[' . esc_attr( $cpt ) . '][plural]" value="' . esc_attr( $p ) . '" /></td>';
		echo '<td><code>' . esc_html( $example ) . '</code></td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
	echo '<p class="description">' . esc_html__( 'outlet-slug is the post slug. In multi-property mode, property-slug is the property\'s URL slug set on the property edit screen.', 'portico-webworks' ) . '</p>';

	submit_button( __( 'Save Permalinks', 'portico-webworks' ) );
	echo '</form>';

	if ( function_exists( 'pw_render_page_structure_admin_panel' ) ) {
		pw_render_page_structure_admin_panel();
	}

	echo '</div></div>';
}
