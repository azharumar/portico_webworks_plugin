<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'rank_math/frontend/title', 'pw_seo_title_override' );
add_filter( 'rank_math/frontend/description', 'pw_seo_description_override' );

function pw_seo_title_override( $title ) {
	if ( ! is_singular() ) return $title;
	$custom = get_post_meta( get_the_ID(), '_pw_meta_title', true );
	return $custom ?: $title;
}

function pw_seo_description_override( $description ) {
	if ( ! is_singular() ) return $description;
	$custom = get_post_meta( get_the_ID(), '_pw_meta_description', true );
	if ( $custom ) return $custom;
	$excerpt = get_the_excerpt( get_the_ID() );
	return $excerpt ?: $description;
}
