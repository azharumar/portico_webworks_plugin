<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed values for pw_permalink_base_source.
 *
 * @return array<string, true>
 */
function pw_permalink_base_source_allowed() {
	return [
		'fixed'             => true,
		'_pw_city'          => true,
		'_pw_state'         => true,
		'_pw_country'       => true,
		'_pw_country_code'  => true,
		'pw_property_type'  => true,
	];
}

/**
 * @return string
 */
function pw_get_permalink_base_source() {
	$s = pw_get_setting( 'pw_permalink_base_source', 'fixed' );
	return isset( pw_permalink_base_source_allowed()[ $s ] ) ? $s : 'fixed';
}

/**
 * @return string post_name|_pw_url_slug
 */
function pw_get_permalink_slug_source() {
	$s = pw_get_setting( 'pw_permalink_slug_source', 'post_name' );
	return $s === '_pw_url_slug' ? '_pw_url_slug' : 'post_name';
}

/**
 * @return array<string, string> path_segment => page_slug
 */
function pw_get_permalink_subpaths() {
	$v = pw_get_setting( 'pw_permalink_subpaths', [] );
	if ( ! is_array( $v ) ) {
		return [];
	}
	$out = [];
	foreach ( $v as $seg => $slug ) {
		$seg  = sanitize_title( (string) $seg );
		$slug = sanitize_title( (string) $slug );
		if ( $seg !== '' && $slug !== '' ) {
			$out[ $seg ] = $slug;
		}
	}
	return $out;
}

/**
 * Fixed URL prefix when base source is "fixed". Syncs with legacy pw_property_base.
 *
 * @return string
 */
function pw_get_fixed_permalink_base() {
	$fixed = pw_get_setting( 'pw_permalink_base_fixed', '' );
	if ( $fixed === '' || $fixed === null ) {
		$fixed = pw_get_setting( 'pw_property_base', 'properties' );
	}
	$fixed = is_string( $fixed ) ? trim( $fixed ) : 'properties';
	$fixed = trim( $fixed, '/' );
	if ( $fixed === '' ) {
		$fixed = 'properties';
	}
	return sanitize_title( $fixed );
}

/**
 * Whether multi-property URLs use a dynamic first segment (city, country, etc.).
 */
function pw_permalink_uses_dynamic_base() {
	return pw_get_setting( 'pw_property_mode', 'single' ) === 'multi'
		&& pw_get_permalink_base_source() !== 'fixed';
}
