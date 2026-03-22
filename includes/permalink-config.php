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
 * Child section CPTs (outlets + per-property listings). Excludes `pw_property`.
 *
 * @return list<string>
 */
function pw_url_section_cpts() {
	return [
		'pw_room_type',
		'pw_restaurant',
		'pw_spa',
		'pw_meeting_room',
		'pw_experience',
		'pw_event',
		'pw_offer',
		'pw_nearby',
	];
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
 * @return string Always empty; property URLs use no shared path prefix.
 */
function pw_get_fixed_permalink_base() {
	return '';
}

/**
 * @deprecated Property URL prefix removed; always true.
 */
function pw_property_base_disabled(): bool {
	return true;
}

/**
 * @return string Always empty.
 */
function pw_multi_property_url_prefix(): string {
	return '';
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
		'pw_property_mode'           => 'single',
		'pw_default_property_id'     => 0,
		'pw_github_releases_url'     => '',
		'pw_section_bases'           => pw_default_section_bases(),
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

	$allowed_base_keys = array_keys( pw_default_section_bases() );
	foreach ( array_keys( $out['pw_section_bases'] ) as $sk ) {
		if ( ! in_array( $sk, $allowed_base_keys, true ) ) {
			unset( $out['pw_section_bases'][ $sk ] );
		}
	}

	unset(
		$out['pw_permalink_base_fixed'],
		$out['pw_property_base'],
		$out['pw_disable_property_base'],
		$out['pw_property_plural_base'],
		$out['pw_property_archive'],
		$out['pw_permalink_slug_source'],
		$out['pw_permalink_subpaths'],
		$out['pw_permalink_base_source']
	);

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
