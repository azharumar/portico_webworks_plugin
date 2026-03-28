<?php
defined( 'ABSPATH' ) || exit;

function pw_get_cta_label( $post_type, $post_id = 0 ) {
	$post_type = sanitize_key( (string) $post_type );
	$post_id   = (int) $post_id;

	$label = __( 'Book now', 'portico-webworks' );

	return apply_filters( 'pw_cta_label', $label, $post_type, $post_id );
}
