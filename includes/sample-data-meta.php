<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Internal flag for content created by the sample installer. Not exposed in REST or editor UI.
 * (Documented as sample-data identifier; storage uses leading underscore = protected in WP.)
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
		'category',
		'post_tag',
	];
}

function pw_register_sample_data_meta() {
	$args = [
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => false,
		'auth_callback'     => '__return_false',
		'sanitize_callback' => static function ( $value ) {
			return $value ? '1' : '';
		},
	];

	foreach ( pw_get_sample_data_post_types_for_meta() as $post_type ) {
		if ( ! post_type_exists( $post_type ) ) {
			continue;
		}
		register_post_meta( $post_type, PW_IS_SAMPLE_DATA_META_KEY, $args );
	}

	foreach ( pw_get_sample_data_taxonomies_for_meta() as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		register_term_meta( $taxonomy, PW_IS_SAMPLE_DATA_META_KEY, $args );
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

function pw_count_sample_flagged_posts() {
	$q = new WP_Query(
		[
			'post_type'              => 'any',
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'meta_key'               => PW_IS_SAMPLE_DATA_META_KEY,
			'meta_value'             => '1',
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);
	return (int) $q->found_posts;
}

function pw_delete_all_sample_data() {
	$post_ids = get_posts(
		[
			'post_type'              => 'any',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'meta_key'               => PW_IS_SAMPLE_DATA_META_KEY,
			'meta_value'             => '1',
			'suppress_filters'       => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	foreach ( $post_ids as $pid ) {
		wp_delete_post( (int) $pid, true );
	}

	foreach ( pw_get_sample_data_taxonomies_for_meta() as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'fields'     => 'ids',
				'meta_query' => [
					[
						'key'   => PW_IS_SAMPLE_DATA_META_KEY,
						'value' => '1',
					],
				],
			]
		);
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}
		foreach ( $terms as $term_id ) {
			wp_delete_term( (int) $term_id, $taxonomy );
		}
	}
}
