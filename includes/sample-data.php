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

add_action( 'admin_post_pw_install_sample_data', 'pw_handle_install_sample_data' );
add_action( 'admin_post_pw_remove_sample_data', 'pw_handle_remove_sample_data' );
add_action( 'admin_post_pw_reseed_taxonomies', 'pw_handle_reseed_taxonomies' );
add_action( 'admin_post_pw_purge_plugin_data', 'pw_handle_purge_plugin_data' );

function pw_render_data_tab() {
	pw_strip_sample_flags_from_seed_terms();

	$has_properties = get_posts(
		[
			'post_type'              => 'pw_property',
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'suppress_filters'       => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	if ( isset( $_GET['pw_sample_installed'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Sample data installed successfully.</p></div>';
	}
	if ( isset( $_GET['pw_sample_error'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>Sample data cannot be installed when properties already exist.</p></div>';
	}
	if ( isset( $_GET['pw_sample_fetch_error'] ) ) {
		$uid = get_current_user_id();
		$em  = $uid ? get_transient( 'pw_sample_pack_err_' . $uid ) : false;
		if ( is_string( $em ) && $em !== '' ) {
			delete_transient( 'pw_sample_pack_err_' . $uid );
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $em ) . '</p></div>';
		} else {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Sample data could not be loaded. Check the ZIP URL and try again.', 'portico-webworks' ) . '</p></div>';
		}
	}
	if ( isset( $_GET['pw_sample_removed'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Sample data removed.</p></div>';
	}
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
	pw_render_import_export_section();

	$flagged_posts = pw_count_sample_flagged_posts_only();
	$flagged_terms = pw_count_sample_flagged_terms_only();
	$flagged       = pw_count_sample_flagged_items();

	pw_data_accordion_item_begin( 'Sample content' );
	echo '<p>Install two fictional sample properties — <strong>Meridian Grand Hotel Bengaluru</strong> (business / MICE) and <strong>Azure Bay Beach Resort</strong> (North Goa leisure) — with room types, dining, spas, meeting rooms, property contacts (<code>pw_contact</code>), amenities, policies, FAQs, offers, and related content. Creates every name from the plugin&rsquo;s default taxonomy seed lists (<code>includes/taxonomy-seeds.php</code>) and adds extra demo-only terms where the story needs them. Use on a fresh site for testing or demonstration.</p>';

	if ( ! empty( $has_properties ) ) {
		echo '<p><strong>Sample data can only be installed when no properties exist.</strong> Delete existing properties first if you want to reinstall.</p>';
	} else {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="pw_install_sample_data" />';
		wp_nonce_field( 'pw_install_sample_data' );
		$def_url = function_exists( 'pw_get_default_sample_data_pack_url' ) ? pw_get_default_sample_data_pack_url() : '';
		$saved    = get_option( 'pw_sample_data_pack_url', '' );
		$url_val  = is_string( $saved ) && $saved !== '' ? $saved : $def_url;
		echo '<table class="form-table" role="presentation"><tbody>';
		echo '<tr><th scope="row"><label for="pw_sample_data_pack_url">' . esc_html__( 'Sample data ZIP URL', 'portico-webworks' ) . '</label></th><td>';
		echo '<input type="url" class="large-text code" name="pw_sample_data_pack_url" id="pw_sample_data_pack_url" value="' . esc_attr( $url_val ) . '" />';
		echo '<p class="description">' . esc_html__( 'HTTPS link to portico_webworks_plugin-sample-data.zip from the same release as this plugin version.', 'portico-webworks' ) . '</p>';
		echo '</td></tr></tbody></table>';
		submit_button( __( 'Install sample data', 'portico-webworks' ), 'primary', 'submit', false );
		echo '</form>';
	}

	if ( $flagged > 0 ) {
		echo '<hr style="margin:1.25em 0;" />';
		echo '<p>' . esc_html(
			sprintf(
				1 === $flagged
					? '%1$d item is tagged as sample data (%2$d posts, %3$d terms).'
					: '%1$d items are tagged as sample data (%2$d posts, %3$d terms).',
				$flagged,
				$flagged_posts,
				$flagged_terms
			)
		) . '</p>';
		$items = pw_list_sample_flagged_items();
		echo '<details style="margin-bottom:1em;"><summary>' . esc_html( 'Tagged items' ) . '</summary>';
		echo '<ul style="list-style:disc;margin:0.5em 0 0 1.5em;max-height:16em;overflow:auto;">';
		foreach ( $items['posts'] as $row ) {
			echo '<li>' . esc_html( sprintf( '[%1$s] %2$s (ID %3$d)', $row['type'], $row['title'], $row['id'] ) ) . '</li>';
		}
		foreach ( $items['terms'] as $row ) {
			echo '<li>' . esc_html( sprintf( '[term:%1$s] %2$s (ID %3$d)', $row['taxonomy'], $row['name'], $row['id'] ) ) . '</li>';
		}
		echo '</ul></details>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'' . esc_js( 'Delete all posts, pages, and plugin content tagged as sample data, and remove sample-only taxonomy terms?' ) . '\');">';
		echo '<input type="hidden" name="action" value="pw_remove_sample_data" />';
		wp_nonce_field( 'pw_remove_sample_data' );
		submit_button( 'Remove sample data', 'delete', 'submit', false );
		echo '</form>';
	}

	pw_data_accordion_item_end();

	pw_data_accordion_item_begin( 'Default taxonomy terms' );
	echo '<p>' . esc_html( 'Re-run the default taxonomy term lists (bed types, views, meal periods, etc.): only missing names are created; nothing is renamed or removed.' ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="pw_reseed_taxonomies" />';
	wp_nonce_field( 'pw_reseed_taxonomies' );
	submit_button( 'Reinstall default taxonomy terms', 'secondary', 'submit', false );
	echo '</form>';
	pw_data_accordion_item_end();

	pw_data_accordion_item_begin( 'Remove all plugin data' );
	echo '<p><strong>' . esc_html( 'Remove all plugin data' ) . '</strong> â€” ' . esc_html( 'Deletes every property, room type, and all other Portico hotel content, all terms in plugin taxonomies, clears orphaned post/term meta rows, and resets the taxonomy seed prompt option. Does not delete normal WordPress posts, pages, categories, or tags.' ) . '</p>';
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

function pw_handle_install_sample_data() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorised' );
	}
	check_admin_referer( 'pw_install_sample_data' );

	$existing = get_posts(
		[
			'post_type'              => 'pw_property',
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'suppress_filters'       => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	if ( ! empty( $existing ) ) {
		wp_safe_redirect(
			add_query_arg(
				'pw_sample_error',
				'1',
				admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
			)
		);
		exit;
	}

	$zip_url = isset( $_POST['pw_sample_data_pack_url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['pw_sample_data_pack_url'] ) ) : '';
	if ( $zip_url === '' && function_exists( 'pw_get_default_sample_data_pack_url' ) ) {
		$zip_url = pw_get_default_sample_data_pack_url();
	}

	$res = pw_install_sample_data( $zip_url );
	if ( is_wp_error( $res ) ) {
		$uid = get_current_user_id();
		if ( $uid ) {
			set_transient( 'pw_sample_pack_err_' . $uid, wp_strip_all_tags( $res->get_error_message() ), 120 );
		}
		wp_safe_redirect(
			add_query_arg(
				'pw_sample_fetch_error',
				'1',
				admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
			)
		);
		exit;
	}

	update_option( 'pw_sample_data_pack_url', $zip_url );

	flush_rewrite_rules( false );
	if ( function_exists( 'pw_sync_portico_nav_menus_after_sample_install' ) ) {
		pw_sync_portico_nav_menus_after_sample_install();
	}

	wp_safe_redirect(
		add_query_arg(
			'pw_sample_installed',
			'1',
			admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
		)
	);
	exit;
}

function pw_handle_remove_sample_data() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorised' );
	}
	check_admin_referer( 'pw_remove_sample_data' );

	pw_delete_all_sample_data();

	wp_safe_redirect(
		add_query_arg(
			'pw_sample_removed',
			'1',
			admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
		)
	);
	exit;
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

function pw_sample_wp_insert_post( $postarr, $wp_error = false ) {
	$post_id = wp_insert_post( $postarr, $wp_error );
	if ( ! is_wp_error( $post_id ) && $post_id ) {
		pw_sample_flag_post( (int) $post_id );
	}
	return $post_id;
}

function pw_sample_ensure_term( $name, $taxonomy ) {
	$exists = term_exists( $name, $taxonomy );
	if ( $exists ) {
		return is_array( $exists ) ? (int) $exists['term_id'] : (int) $exists;
	}
	$inserted = wp_insert_term( $name, $taxonomy );
	if ( is_wp_error( $inserted ) ) {
		return 0;
	}
	$tid = (int) $inserted['term_id'];
	if ( ! pw_term_name_is_taxonomy_seed_value( $name, $taxonomy ) ) {
		pw_sample_flag_term( $tid );
	}
	return $tid;
}

function pw_sample_set_operating_hours( $post_id, $sessions ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 || ! is_array( $sessions ) ) {
		return;
	}
	update_post_meta( $post_id, '_pw_operating_hours', array_values( $sessions ) );
}

function pw_sample_spa_treatment_hours( $open, $close ) {
	return [
		[ 'label' => 'Treatments', 'open_time' => $open, 'close_time' => $close ],
	];
}

/**
 * @param string|null $zip_url URL to sample-data ZIP; null uses saved option or default GitHub asset URL.
 * @return true|WP_Error
 */
function pw_install_sample_data( $zip_url = null ) {
	if ( $zip_url === null ) {
		$saved = get_option( 'pw_sample_data_pack_url', '' );
		$zip_url = is_string( $saved ) && $saved !== '' ? $saved : '';
		if ( $zip_url === '' && function_exists( 'pw_get_default_sample_data_pack_url' ) ) {
			$zip_url = pw_get_default_sample_data_pack_url();
		}
	}
	$zip_url = is_string( $zip_url ) ? trim( $zip_url ) : '';

	$loaded = pw_ensure_sample_data_pack_loaded( $zip_url );
	if ( is_wp_error( $loaded ) ) {
		return $loaded;
	}

	pw_strip_sample_flags_from_seed_terms();
	pw_sample_install_lock_open();
	try {
		pw_install_sample_dataset_multi();
	} finally {
		pw_sample_install_lock_close();
	}
	return true;
}
