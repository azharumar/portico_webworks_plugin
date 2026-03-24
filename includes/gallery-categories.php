<?php
/**
 * Gallery category labels and JSON sanitization for `_pw_gallery_meta`.
 *
 * Implementation plan (reference):
 * - Add `pw_get_gallery_categories()` + `pw_sanitize_pw_gallery_meta_json()`; load before child-post-type-metaboxes.
 * - Register `_pw_gallery_meta` in child-post-types.php (and property `_pw_gallery`); extend `_pw_pools` with `attachment_id`;
 *   register `_pw_og_image` on pw_property in property-post-type.php.
 * - CMB2: property gallery file_list; pools attachment_id in child-post-type-metaboxes.php.
 * - Native gallery details metabox in gallery-meta-metabox.php; require from portico_webworks_plugin.php.
 * - Update DATA-STRUCTURE.md; enhance sample-data-multi-install.php (sideload, galleries, captions, OG, pools).
 *
 * Files explicitly not touched: contact-post-type.php, property-rewrites.php, permalink-config.php.
 *
 * @package Portico_Webworks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize `_pw_gallery` to a list of attachment IDs (CMB2 file_list may store id => url or list of ids).
 *
 * @param mixed $gallery Raw meta value.
 * @return int[] Unique positive attachment IDs in stable order.
 */
function pw_normalize_gallery_attachment_ids( $gallery ) {
	$ids = [];
	if ( ! is_array( $gallery ) ) {
		return $ids;
	}
	foreach ( $gallery as $k => $v ) {
		if ( is_numeric( $k ) && (int) $k > 0 ) {
			$ids[] = (int) $k;
		} elseif ( is_numeric( $v ) ) {
			$ids[] = (int) $v;
		}
	}
	$out = [];
	foreach ( $ids as $i ) {
		if ( $i > 0 && ! in_array( $i, $out, true ) ) {
			$out[] = $i;
		}
	}
	return $out;
}

/**
 * Category slug => label for gallery images, per post type (PHP map only; not a taxonomy).
 *
 * @param string $post_type Post type slug.
 * @return array<string, string>
 */
function pw_get_gallery_categories( $post_type ) {
	$post_type = is_string( $post_type ) ? $post_type : '';

	$map = [
		'pw_property' => [
			'exterior'     => __( 'Exterior', 'portico-webworks' ),
			'lobby'        => __( 'Lobby', 'portico-webworks' ),
			'pool'         => __( 'Pool', 'portico-webworks' ),
			'aerial'       => __( 'Aerial', 'portico-webworks' ),
			'common_area'  => __( 'Common area', 'portico-webworks' ),
			'garden'       => __( 'Garden', 'portico-webworks' ),
			'beach'        => __( 'Beach', 'portico-webworks' ),
		],
		'pw_room_type' => [
			'bedroom'      => __( 'Bedroom', 'portico-webworks' ),
			'bathroom'     => __( 'Bathroom', 'portico-webworks' ),
			'balcony'      => __( 'Balcony', 'portico-webworks' ),
			'view'         => __( 'View', 'portico-webworks' ),
			'living_area'  => __( 'Living area', 'portico-webworks' ),
			'amenities'    => __( 'Amenities', 'portico-webworks' ),
		],
		'pw_restaurant' => [
			'dining_area'   => __( 'Dining area', 'portico-webworks' ),
			'bar'           => __( 'Bar', 'portico-webworks' ),
			'buffet'        => __( 'Buffet', 'portico-webworks' ),
			'private_dining' => __( 'Private dining', 'portico-webworks' ),
			'food'          => __( 'Food', 'portico-webworks' ),
		],
		'pw_spa' => [
			'treatment_room' => __( 'Treatment room', 'portico-webworks' ),
			'reception'      => __( 'Reception', 'portico-webworks' ),
			'facilities'     => __( 'Facilities', 'portico-webworks' ),
			'pool'           => __( 'Pool', 'portico-webworks' ),
		],
		'pw_meeting_room' => [
			'theatre'     => __( 'Theatre', 'portico-webworks' ),
			'boardroom'   => __( 'Boardroom', 'portico-webworks' ),
			'banquet'     => __( 'Banquet', 'portico-webworks' ),
			'prefunction' => __( 'Pre-function', 'portico-webworks' ),
		],
		'pw_experience' => [
			'general' => __( 'General', 'portico-webworks' ),
		],
		'pw_event' => [
			'general' => __( 'General', 'portico-webworks' ),
		],
		'pw_offer' => [
			'general' => __( 'General', 'portico-webworks' ),
		],
		'pw_nearby' => [
			'general' => __( 'General', 'portico-webworks' ),
		],
	];

	return isset( $map[ $post_type ] ) ? $map[ $post_type ] : [];
}

/**
 * Sanitize `_pw_gallery_meta` JSON: object keyed by attachment ID string, values { category, caption }.
 *
 * @param mixed  $value     Raw meta value.
 * @param string $post_type Post type (for allowed category slugs).
 * @return string JSON object or '{}'.
 */
function pw_sanitize_pw_gallery_meta_json( $value, $post_type ) {
	$allowed_cats = pw_get_gallery_categories( $post_type );
	$allowed_slugs = array_keys( $allowed_cats );

	if ( ! is_string( $value ) ) {
		$value = is_scalar( $value ) ? (string) $value : '';
	}
	if ( $value === '' ) {
		return '{}';
	}

	$decoded = json_decode( $value, true );
	if ( ! is_array( $decoded ) ) {
		return '{}';
	}

	$out = [];
	foreach ( $decoded as $key => $item ) {
		$aid = (string) $key;
		if ( $aid === '' || ! ctype_digit( $aid ) ) {
			continue;
		}
		if ( ! is_array( $item ) ) {
			$out[ $aid ] = [ 'category' => '', 'caption' => '' ];
			continue;
		}
		$cat = isset( $item['category'] ) ? (string) $item['category'] : '';
		if ( $cat !== '' && ! in_array( $cat, $allowed_slugs, true ) ) {
			$cat = '';
		}
		$caption = isset( $item['caption'] ) ? sanitize_text_field( (string) $item['caption'] ) : '';
		$out[ $aid ] = [
			'category' => $cat,
			'caption'  => $caption,
		];
	}

	return wp_json_encode( $out, JSON_UNESCAPED_UNICODE );
}
