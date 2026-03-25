<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Internal flag for content created by the sample installer.
 * REST-visible for block editor; auth_callback limits to users who can edit the object.
 */
define( 'PW_IS_SAMPLE_DATA_META_KEY', '_pw_is_sample_data' );

function pw_get_sample_data_post_types_for_meta() {
	return [
		'post',
		'page',
		'pw_property',
		'pw_feature',
		'pw_room_type',
		'pw_restaurant',
		'pw_spa',
		'pw_meeting_room',
		'pw_amenity',
		'pw_policy',
		'pw_faq',
		'pw_offer',
		'pw_nearby',
		'pw_experience',
		'pw_event',
		'pw_contact',
	];
}

function pw_get_sample_data_taxonomies_for_meta() {
	return [
		'pw_bed_type',
		'pw_view_type',
		'pw_meal_period',
		'pw_treatment_type',
		'pw_av_equipment',
		'pw_feature_group',
		'pw_nearby_type',
		'pw_transport_mode',
		'pw_experience_category',
		'pw_event_type',
		'pw_policy_type',
		'pw_event_organiser',
		'pw_property_type',
		'category',
		'post_tag',
	];
}

function pw_register_sample_data_meta() {
	$sanitize = static function ( $value ) {
		return $value ? '1' : '';
	};

	$post_args = [
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
			return current_user_can( 'edit_post', (int) $post_id );
		},
		'sanitize_callback' => $sanitize,
	];

	$term_args = [
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'auth_callback'     => static function ( $allowed, $meta_key, $term_id ) {
			return current_user_can( 'edit_term', (int) $term_id );
		},
		'sanitize_callback' => $sanitize,
	];

	foreach ( pw_get_sample_data_post_types_for_meta() as $post_type ) {
		if ( ! post_type_exists( $post_type ) ) {
			continue;
		}
		register_post_meta( $post_type, PW_IS_SAMPLE_DATA_META_KEY, $post_args );
	}

	foreach ( pw_get_sample_data_taxonomies_for_meta() as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		register_term_meta( $taxonomy, PW_IS_SAMPLE_DATA_META_KEY, $term_args );
	}
}

add_action( 'init', 'pw_register_sample_data_meta', 25 );

function pw_sample_flag_post( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return;
	}
	update_post_meta( $post_id, PW_IS_SAMPLE_DATA_META_KEY, '1' );
}

function pw_sample_flag_term( $term_id ) {
	$term_id = (int) $term_id;
	if ( $term_id <= 0 ) {
		return;
	}
	update_term_meta( $term_id, PW_IS_SAMPLE_DATA_META_KEY, '1' );
}

function pw_sample_install_lock_open() {
	$GLOBALS['pw_sample_install_active'] = true;
}

function pw_sample_install_lock_close() {
	unset( $GLOBALS['pw_sample_install_active'] );
}

function pw_sample_install_is_locked() {
	return ! empty( $GLOBALS['pw_sample_install_active'] );
}

function pw_sample_auto_flag_on_insert( $post_id, $post, $update, $post_before ) {
	if ( ! pw_sample_install_is_locked() || $update ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	pw_sample_flag_post( $post_id );
}

function pw_sample_auto_flag_legacy_save( $post_id, $post, $update ) {
	if ( ! pw_sample_install_is_locked() || $update ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	pw_sample_flag_post( $post_id );
}

add_action(
	'init',
	static function () {
		global $wp_version;
		if ( version_compare( $wp_version, '5.6', '>=' ) ) {
			add_action( 'wp_after_insert_post', 'pw_sample_auto_flag_on_insert', 10, 4 );
		} else {
			add_action( 'save_post', 'pw_sample_auto_flag_legacy_save', 999, 3 );
		}
	},
	0
);

function pw_get_plugin_post_types() {
	return [
		'pw_property',
		'pw_feature',
		'pw_room_type',
		'pw_restaurant',
		'pw_spa',
		'pw_meeting_room',
		'pw_amenity',
		'pw_policy',
		'pw_faq',
		'pw_offer',
		'pw_nearby',
		'pw_experience',
		'pw_event',
		'pw_contact',
	];
}

function pw_get_plugin_taxonomies() {
	return [
		'pw_bed_type',
		'pw_view_type',
		'pw_meal_period',
		'pw_treatment_type',
		'pw_av_equipment',
		'pw_feature_group',
		'pw_nearby_type',
		'pw_transport_mode',
		'pw_experience_category',
		'pw_event_type',
		'pw_policy_type',
		'pw_event_organiser',
		'pw_property_type',
	];
}

/**
 * Post IDs flagged as sample data (postmeta join). Bypasses WP_Query so all post types are included.
 */
function pw_get_sample_flagged_post_ids() {
	global $wpdb;
	$key = PW_IS_SAMPLE_DATA_META_KEY;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- list/delete must match DB, not posts_clauses filters
	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT p.ID FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm ON pm.post_id = p.ID
			WHERE pm.meta_key = %s AND pm.meta_value = %s
			AND p.post_type <> 'revision'",
			$key,
			'1'
		)
	);
	if ( empty( $ids ) ) {
		return [];
	}
	return array_map( 'intval', $ids );
}

/**
 * Term IDs in a taxonomy flagged as sample data (termmeta join).
 */
function pw_get_sample_flagged_term_ids_for_taxonomy( $taxonomy ) {
	global $wpdb;
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return [];
	}
	$key = PW_IS_SAMPLE_DATA_META_KEY;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT tt.term_id FROM {$wpdb->term_taxonomy} AS tt
			INNER JOIN {$wpdb->termmeta} AS tm ON tm.term_id = tt.term_id
			WHERE tt.taxonomy = %s
			AND tm.meta_key = %s AND tm.meta_value = %s",
			$taxonomy,
			$key,
			'1'
		)
	);
	if ( empty( $ids ) ) {
		return [];
	}
	return array_map( 'intval', $ids );
}

function pw_count_sample_flagged_posts_only() {
	return count( pw_get_sample_flagged_post_ids() );
}

function pw_count_sample_flagged_terms_only() {
	$n = 0;
	foreach ( pw_get_sample_data_taxonomies_for_meta() as $taxonomy ) {
		$n += count( pw_get_sample_flagged_term_ids_for_taxonomy( $taxonomy ) );
	}
	return $n;
}

function pw_count_sample_flagged_items() {
	return pw_count_sample_flagged_posts_only() + pw_count_sample_flagged_terms_only();
}

function pw_list_sample_flagged_items() {
	$post_ids = pw_get_sample_flagged_post_ids();
	sort( $post_ids, SORT_NUMERIC );
	$out_posts = [];
	foreach ( $post_ids as $pid ) {
		$p = get_post( $pid );
		if ( ! $p ) {
			continue;
		}
		$out_posts[] = [
			'id'    => (int) $p->ID,
			'title' => get_the_title( $p ),
			'type'  => $p->post_type,
		];
	}

	$out_terms = [];
	foreach ( pw_get_sample_data_taxonomies_for_meta() as $taxonomy ) {
		$term_ids = pw_get_sample_flagged_term_ids_for_taxonomy( $taxonomy );
		sort( $term_ids, SORT_NUMERIC );
		foreach ( $term_ids as $tid ) {
			$t = get_term( $tid, $taxonomy );
			if ( ! $t || is_wp_error( $t ) ) {
				continue;
			}
			$out_terms[] = [
				'id'       => (int) $t->term_id,
				'name'     => $t->name,
				'taxonomy' => $taxonomy,
			];
		}
	}

	return [
		'posts' => $out_posts,
		'terms' => $out_terms,
	];
}

function pw_strip_sample_flags_from_seed_terms() {
	foreach ( pw_get_taxonomy_seed_terms() as $taxonomy => $names ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		foreach ( $names as $name ) {
			$t = get_term_by( 'name', $name, $taxonomy );
			if ( $t && ! is_wp_error( $t ) ) {
				delete_term_meta( $t->term_id, PW_IS_SAMPLE_DATA_META_KEY );
			}
		}
	}
}

function pw_purge_all_plugin_data() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	pw_strip_sample_flags_from_seed_terms();

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

function pw_delete_all_sample_data() {
	$flagged = pw_get_sample_flagged_post_ids();
	$attach  = [];
	foreach ( $flagged as $pid ) {
		$pid = (int) $pid;
		if ( $pid <= 0 ) {
			continue;
		}
		if ( get_post_type( $pid ) === 'attachment' ) {
			$attach[ $pid ] = true;
			continue;
		}
		$thumb = (int) get_post_meta( $pid, '_thumbnail_id', true );
		if ( $thumb > 0 ) {
			$attach[ $thumb ] = true;
		}
		$og = (int) get_post_meta( $pid, '_pw_og_image', true );
		if ( $og > 0 ) {
			$attach[ $og ] = true;
		}
		$gal = get_post_meta( $pid, '_pw_gallery', true );
		if ( is_array( $gal ) ) {
			foreach ( $gal as $gid ) {
				$gid = absint( $gid );
				if ( $gid > 0 ) {
					$attach[ $gid ] = true;
				}
			}
		}
	}
	$aids = array_keys( $attach );
	rsort( $aids, SORT_NUMERIC );
	foreach ( $aids as $aid ) {
		wp_delete_post( (int) $aid, true );
	}

	$post_ids = pw_get_sample_flagged_post_ids();
	rsort( $post_ids, SORT_NUMERIC );
	foreach ( $post_ids as $pid ) {
		wp_delete_post( (int) $pid, true );
	}

	foreach ( pw_get_sample_data_taxonomies_for_meta() as $taxonomy ) {
		$term_ids = pw_get_sample_flagged_term_ids_for_taxonomy( $taxonomy );
		foreach ( $term_ids as $term_id ) {
			wp_delete_term( (int) $term_id, $taxonomy );
		}
	}
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
