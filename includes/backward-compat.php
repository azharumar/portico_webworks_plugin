<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
			$tz = wp_timezone();
			$dt = DateTime::createFromFormat( 'Y-m-d H:i:s', trim( $stored ), $tz );
			if ( $dt ) {
				return $single ? json_encode( $dt ) : [ json_encode( $dt ) ];
			}
		}
	}

	return null;
}
