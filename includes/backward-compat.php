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

	return null;
}
