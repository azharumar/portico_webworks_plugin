<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'pw_admin_tabs',
	function ( $tabs ) {
		$tabs['data'] = 'Data';
		return $tabs;
	},
	20
);

add_action( 'pw_render_tab_data', 'pw_render_data_tab' );

add_action( 'admin_post_pw_reseed_taxonomies', 'pw_handle_reseed_taxonomies' );
add_action( 'admin_post_pw_purge_plugin_data', 'pw_handle_purge_plugin_data' );

function pw_get_plugin_post_types() {
	return [
		'pw_property',
	];
}

function pw_get_plugin_taxonomies() {
	return [
		'pw_property_type',
	];
}

function pw_purge_all_plugin_data() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	foreach ( pw_get_plugin_post_types() as $post_type ) {
		if ( ! post_type_exists( $post_type ) ) {
			continue;
		}
		$ids = get_posts(
			[
				'post_type'              => $post_type,
				'post_status'            => 'any',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'suppress_filters'       => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			]
		);
		foreach ( $ids as $pid ) {
			wp_delete_post( (int) $pid, true );
		}
	}

	foreach ( pw_get_plugin_taxonomies() as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$term_ids = get_terms(
			[
				'taxonomy'               => $taxonomy,
				'hide_empty'             => false,
				'fields'                 => 'ids',
				'update_term_meta_cache' => false,
				'suppress_filter'        => true,
			]
		);
		if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
			continue;
		}
		foreach ( $term_ids as $term_id ) {
			wp_delete_term( (int) $term_id, $taxonomy );
		}
	}

	global $wpdb;
	$wpdb->query( "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL" );
	$wpdb->query( "DELETE tr FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} p ON p.ID = tr.object_id WHERE p.ID IS NULL" );
	$wpdb->query( "DELETE tm FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->terms} t ON t.term_id = tm.term_id WHERE t.term_id IS NULL" );

	delete_option( 'pw_taxonomy_seed_prompt_status' );
	delete_option( 'pw_seed_taxonomies' );
}

function pw_data_accordion_open() {
	echo '<div class="pw-data-accordion">';
}

function pw_data_accordion_close() {
	echo '</div>';
}

function pw_data_accordion_item_begin( $title ) {
	static $pw_acc_index = 0;
	$is_first            = ( 0 === $pw_acc_index );
	$tid                 = 'pw-data-acc-t-' . $pw_acc_index;
	$pid                 = 'pw-data-acc-p-' . $pw_acc_index;
	++$pw_acc_index;

	$classes = 'pw-card pw-accordion-item';
	if ( $is_first ) {
		$classes .= ' is-expanded';
	}
	echo '<div class="' . esc_attr( $classes ) . '">';
	echo '<button type="button" class="pw-card-head pw-accordion-trigger" aria-expanded="' . ( $is_first ? 'true' : 'false' ) . '" aria-controls="' . esc_attr( $pid ) . '" id="' . esc_attr( $tid ) . '">';
	echo '<span class="pw-card-title">' . esc_html( $title ) . '</span>';
	echo '<span class="pw-accordion-chevron" aria-hidden="true"></span>';
	echo '</button>';
	echo '<div class="pw-accordion-panel" id="' . esc_attr( $pid ) . '" role="region" aria-labelledby="' . esc_attr( $tid ) . '"' . ( $is_first ? '' : ' hidden' ) . '>';
	echo '<div class="pw-card-body">';
}

function pw_data_accordion_item_end() {
	echo '</div></div></div>';
}

function pw_render_data_tab() {
	if ( isset( $_GET['pw_taxonomy_reseeded'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Default taxonomy terms were added where they were missing.</p></div>';
	}
	if ( isset( $_GET['pw_plugin_purged'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>All plugin content and taxonomy terms were removed.</p></div>';
	}
	if ( isset( $_GET['pw_purge_denied'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>That action was cancelled: the confirmation phrase did not match.</p></div>';
	}
	if ( isset( $_GET['pw_imported'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Import completed successfully.</p></div>';
	}

	pw_data_accordion_open();
	if ( function_exists( 'pw_render_page_structure_admin_panel' ) ) {
		pw_data_accordion_item_begin( __( 'Site structure', 'portico-webworks' ) );
		pw_render_page_structure_admin_panel();
		pw_data_accordion_item_end();
	}
	if ( function_exists( 'pw_render_import_export_section' ) ) {
		pw_render_import_export_section();
	}

	pw_data_accordion_item_begin( 'Default taxonomy terms' );
	echo '<p>' . esc_html( 'Re-run the default taxonomy term lists (bed types, views, meal periods, etc.): only missing names are created; nothing is renamed or removed.' ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="pw_reseed_taxonomies" />';
	wp_nonce_field( 'pw_reseed_taxonomies' );
	submit_button( 'Reinstall default taxonomy terms', 'secondary', 'submit', false );
	echo '</form>';
	pw_data_accordion_item_end();

	pw_data_accordion_item_begin( 'Remove all plugin data' );
	echo '<p><strong>' . esc_html( 'Remove all plugin data' ) . '</strong> — ' . esc_html( 'Deletes every property, room type, and all other Portico hotel content, all terms in plugin taxonomies, clears orphaned post/term meta rows, and resets the taxonomy seed prompt option. Does not delete normal WordPress posts, pages, categories, or tags.' ) . '</p>';
	echo '<form id="pw-purge-plugin-form" class="pw-purge-plugin-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return pwConfirmPurgePluginData(this);">';
	echo '<input type="hidden" name="action" value="pw_purge_plugin_data" />';
	wp_nonce_field( 'pw_purge_plugin_data' );
	echo '<table class="form-table" role="presentation"><tbody>';
	echo '<tr><th scope="row"><label for="pw-purge-password">' . esc_html( 'Confirmation phrase' ) . '</label></th>';
	echo '<td><input type="password" id="pw-purge-password" name="pw_purge_password" class="regular-text" value="" autocomplete="off" required />';
	echo '<p class="description">' . esc_html( 'Enter porticowebworks.com to confirm this irreversible action.' ) . '</p></td></tr>';
	echo '</tbody></table>';
	submit_button( 'Remove all plugin data', 'primary', 'submit', false, [ 'class' => 'button pw-button-purge-all' ] );
	echo '</form>';
	pw_data_accordion_item_end();

	pw_data_accordion_close();
}

function pw_handle_reseed_taxonomies() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorised' );
	}
	check_admin_referer( 'pw_reseed_taxonomies' );
	pw_seed_taxonomy_terms();
	wp_safe_redirect(
		add_query_arg(
			'pw_taxonomy_reseeded',
			'1',
			admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
		)
	);
	exit;
}

function pw_handle_purge_plugin_data() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorised' );
	}
	check_admin_referer( 'pw_purge_plugin_data' );

	$phrase = isset( $_POST['pw_purge_password'] ) ? sanitize_text_field( wp_unslash( $_POST['pw_purge_password'] ) ) : '';
	if ( ! hash_equals( 'porticowebworks.com', $phrase ) ) {
		wp_safe_redirect(
			add_query_arg(
				'pw_purge_denied',
				'1',
				admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
			)
		);
		exit;
	}

	pw_purge_all_plugin_data();
	wp_safe_redirect(
		add_query_arg(
			'pw_plugin_purged',
			'1',
			admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
		)
	);
	exit;
}
