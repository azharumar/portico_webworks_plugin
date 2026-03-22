<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default section URL bases (plural = listing segment, singular = outlet prefix).
 *
 * @return array<string, array{plural: string, singular: string}>
 */
function pw_default_section_bases() {
	return [
		'pw_room_type'    => [ 'plural' => 'rooms', 'singular' => 'room' ],
		'pw_restaurant'   => [ 'plural' => 'restaurants', 'singular' => 'restaurant' ],
		'pw_spa'          => [ 'plural' => 'spas', 'singular' => 'spa' ],
		'pw_meeting_room' => [ 'plural' => 'meetings', 'singular' => 'meeting' ],
		'pw_experience'   => [ 'plural' => 'experiences', 'singular' => 'experience' ],
		'pw_event'        => [ 'plural' => 'events', 'singular' => 'event' ],
		'pw_offer'        => [ 'plural' => 'offers', 'singular' => 'offer' ],
		'pw_nearby'       => [ 'plural' => 'places', 'singular' => 'place' ],
	];
}

/**
 * Section CPTs that participate in tiered URLs (order matches defaults).
 *
 * @return list<string>
 */
function pw_url_section_cpts() {
	return array_keys( pw_default_section_bases() );
}

/**
 * Merged section bases from settings + defaults.
 *
 * @return array<string, array{plural: string, singular: string}>
 */
function pw_get_section_bases() {
	$defaults = pw_default_section_bases();
	$stored   = pw_get_setting( 'pw_section_bases', [] );
	if ( ! is_array( $stored ) ) {
		$stored = [];
	}
	$out = [];
	foreach ( $defaults as $cpt => $pair ) {
		$out[ $cpt ] = [
			'plural'   => $pair['plural'],
			'singular' => $pair['singular'],
		];
		if ( isset( $stored[ $cpt ] ) && is_array( $stored[ $cpt ] ) ) {
			$p = sanitize_title( (string) ( $stored[ $cpt ]['plural'] ?? '' ) );
			$s = sanitize_title( (string) ( $stored[ $cpt ]['singular'] ?? '' ) );
			if ( $p !== '' ) {
				$out[ $cpt ]['plural'] = $p;
			}
			if ( $s !== '' ) {
				$out[ $cpt ]['singular'] = $s;
			}
		}
	}
	return $out;
}

/**
 * @param string $cpt    One of pw_url_section_cpts().
 * @param string $form   'plural' or 'singular'.
 * @return string
 */
function pw_get_section_base( $cpt, $form ) {
	$bases = pw_get_section_bases();
	if ( ! isset( $bases[ $cpt ] ) ) {
		return '';
	}
	return $form === 'singular'
		? (string) $bases[ $cpt ]['singular']
		: (string) $bases[ $cpt ]['plural'];
}

/**
 * Sanitize optional URL prefix; empty = property slug at site root (multi mode).
 *
 * @param mixed $value Raw input.
 * @return string
 */
function pw_sanitize_property_base( $value, $field_args = null, $field = null ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	$value = trim( $value, '/' );
	return sanitize_title( $value );
}

/**
 * Optional fixed prefix for multi-property URLs (synced with pw_property_base).
 *
 * @return string Empty when no prefix.
 */
function pw_get_fixed_permalink_base() {
	$fixed = pw_get_setting( 'pw_permalink_base_fixed', null );
	if ( $fixed === null || $fixed === '' ) {
		$fixed = pw_get_setting( 'pw_property_base', '' );
	}
	$fixed = is_string( $fixed ) ? trim( $fixed ) : '';
	$fixed = trim( $fixed, '/' );
	return sanitize_title( $fixed );
}

/**
 * @deprecated Dynamic first-segment bases removed; always false.
 */
function pw_permalink_uses_dynamic_base() {
	return false;
}

/**
 * @deprecated Retained for backward compat; always 'fixed'.
 */
function pw_get_permalink_base_source() {
	return 'fixed';
}

/**
 * @deprecated URL slugs use post_name only.
 */
function pw_get_permalink_slug_source() {
	return 'post_name';
}

/**
 * Full pw_settings array from option with defaults for missing keys.
 *
 * @return array<string, mixed>
 */
function pw_get_merged_pw_settings() {
	$raw = get_option( 'pw_settings', [] );
	$raw = is_array( $raw ) ? $raw : [];
	$defaults = [
		'pw_property_mode'         => 'single',
		'pw_property_base'         => '',
		'pw_default_property_id'   => 0,
		'pw_github_releases_url'   => '',
		'pw_permalink_base_fixed'  => '',
		'pw_section_bases'         => pw_default_section_bases(),
		'pw_property_plural_base'  => 'hotels',
		'pw_property_archive'      => '1',
	];
	$out = wp_parse_args( $raw, $defaults );

	if ( ! is_array( $out['pw_section_bases'] ) ) {
		$out['pw_section_bases'] = pw_default_section_bases();
	} else {
		$merged_bases = pw_default_section_bases();
		foreach ( $merged_bases as $cpt => $pair ) {
			if ( ! isset( $out['pw_section_bases'][ $cpt ] ) || ! is_array( $out['pw_section_bases'][ $cpt ] ) ) {
				$out['pw_section_bases'][ $cpt ] = $pair;
				continue;
			}
			$sub = $out['pw_section_bases'][ $cpt ];
			$out['pw_section_bases'][ $cpt ] = [
				'plural'   => sanitize_title( (string) ( $sub['plural'] ?? $pair['plural'] ) ) ?: $pair['plural'],
				'singular' => sanitize_title( (string) ( $sub['singular'] ?? $pair['singular'] ) ) ?: $pair['singular'],
			];
		}
	}

	$out['pw_property_plural_base'] = sanitize_title( (string) ( $out['pw_property_plural_base'] ?? 'hotels' ) ) ?: 'hotels';
	$out['pw_property_archive']    = isset( $out['pw_property_archive'] ) && (string) $out['pw_property_archive'] === '1' ? '1' : '0';

	if ( $out['pw_permalink_base_fixed'] === '' && ! empty( $raw['pw_property_base'] ) ) {
		$out['pw_permalink_base_fixed'] = $raw['pw_property_base'];
	}
	$fixed = pw_sanitize_property_base( (string) $out['pw_permalink_base_fixed'] );
	$out['pw_permalink_base_fixed'] = $fixed;
	$out['pw_property_base']        = $fixed;

	unset( $out['pw_permalink_slug_source'], $out['pw_permalink_subpaths'], $out['pw_permalink_base_source'] );

	return $out;
}

/**
 * @param string $key     Setting key.
 * @param mixed  $default Fallback if key missing from merged settings.
 * @return mixed
 */
function pw_get_setting( $key, $default = '' ) {
	$opts = pw_get_merged_pw_settings();
	if ( array_key_exists( $key, $opts ) ) {
		return $opts[ $key ];
	}
	return get_option( $key, $default );
}
