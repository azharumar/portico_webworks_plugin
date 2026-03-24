<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'pw_migrate_amenity_description_to_content_meta', 1 );

/**
 * One-time: rename pw_amenity `_pw_description` to `_pw_content`.
 */
function pw_migrate_amenity_description_to_content_meta(): void {
	if ( get_option( 'pw_amenity_description_to_content_migrated' ) ) {
		return;
	}
	if ( ! post_type_exists( 'pw_amenity' ) ) {
		return;
	}
	$ids = get_posts(
		[
			'post_type'              => 'pw_amenity',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);
	foreach ( $ids as $post_id ) {
		$post_id = (int) $post_id;
		$old     = get_post_meta( $post_id, '_pw_description', true );
		if ( $old === '' || $old === false ) {
			delete_post_meta( $post_id, '_pw_description' );
			continue;
		}
		$cur = get_post_meta( $post_id, '_pw_content', true );
		if ( $cur === '' || $cur === false ) {
			update_post_meta( $post_id, '_pw_content', $old );
		}
		delete_post_meta( $post_id, '_pw_description' );
	}
	update_option( 'pw_amenity_description_to_content_migrated', '1', true );
}

add_filter( 'get_post_metadata', 'pw_backward_compat_meta', 10, 4 );

function pw_backward_compat_meta( $value, $object_id, $meta_key, $single ) {
	if ( $value !== null ) {
		return $value;
	}

	$post_type = get_post_type( $object_id );
	if ( ! $post_type ) {
		return null;
	}

	if ( $post_type === 'pw_property' ) {
		if ( $meta_key === '_pw_property_name' ) {
			return $single ? get_the_title( $object_id ) : [ get_the_title( $object_id ) ];
		}
		if ( $meta_key === '_pw_slug' ) {
			$slug = get_post_field( 'post_name', $object_id );
			return $single ? $slug : [ $slug ];
		}
	}

	if ( $post_type === 'pw_event' && $meta_key === '_pw_is_recurring' ) {
		$rule = get_post_meta( $object_id, '_pw_recurrence_rule', true );
		$val  = ! empty( $rule ) ? '1' : '';
		return $single ? $val : [ $val ];
	}

	if ( $post_type === 'pw_event' && in_array( $meta_key, [ '_pw_start_datetime', '_pw_end_datetime' ], true ) && is_admin() ) {
		remove_filter( 'get_post_metadata', 'pw_backward_compat_meta', 10 );
		$stored = get_post_meta( $object_id, $meta_key, $single );
		add_filter( 'get_post_metadata', 'pw_backward_compat_meta', 10, 4 );

		if ( is_string( $stored ) && preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', trim( $stored ) ) ) {
			$prop_id = (int) get_post_meta( $object_id, '_pw_property_id', true );
			$tz      = pw_event_timezone_for_property( $prop_id );
			$dt      = DateTime::createFromFormat( 'Y-m-d H:i:s', trim( $stored ), $tz );
			if ( $dt ) {
				return $single ? json_encode( $dt ) : [ json_encode( $dt ) ];
			}
		}
	}

	return null;
}
