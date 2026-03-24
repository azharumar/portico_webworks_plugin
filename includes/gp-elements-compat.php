<?php
/**
 * Bridge Portico virtual routing and GP Premium Block Elements: GP evaluates display on `wp`
 * before Portico shapes the main query on `template_redirect`.
 *
 * @package Portico_Webworks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'generate_block_element_display', 'pw_filter_block_element_display_for_portico_routes', 10, 2 );

/**
 * @param bool $display   Whether GP shows the element.
 * @param int  $post_id   gp_elements post ID.
 * @return bool
 */
function pw_filter_block_element_display_for_portico_routes( $display, $post_id ) {
	if ( $display || is_admin() ) {
		return $display;
	}

	$post_id = (int) $post_id;
	if ( ! post_type_exists( 'gp_elements' ) || get_post_type( $post_id ) !== 'gp_elements' ) {
		return $display;
	}

	if ( get_post_meta( $post_id, '_pw_generated', true ) !== '1' ) {
		return $display;
	}

	$section_cpt_el = sanitize_key( (string) get_post_meta( $post_id, '_pw_section_cpt', true ) );
	$element_type   = (string) get_post_meta( $post_id, '_pw_element_type', true );

	if ( $section_cpt_el === '' || ! in_array( $element_type, [ 'singular', 'archive' ], true ) ) {
		return $display;
	}

	$prop_slug = (string) get_query_var( 'pw_property_slug', '' );
	if ( $prop_slug !== '' ) {
		$prop_id = (int) pw_resolve_property_slug( sanitize_title( $prop_slug ) );
	} else {
		$prop_id = (int) pw_get_setting( 'pw_default_property_id', 0 );
	}

	if ( $prop_id <= 0 ) {
		return $display;
	}

	$section_var = sanitize_key( (string) get_query_var( 'pw_section_cpt', '' ) );
	$outlet_slug = (string) get_query_var( 'pw_outlet_slug', '' );
	$static_slug = (string) get_query_var( 'pw_static_page_slug', '' );
	$bare        = (int) get_query_var( 'pw_bare_singular', 0 );

	if ( $element_type === 'singular' && $section_cpt_el === 'pw_property' ) {
		if ( $section_var !== '' || $outlet_slug !== '' || $static_slug !== '' || $bare === 1 ) {
			return $display;
		}
		return true;
	}

	if ( ! in_array( $section_cpt_el, pw_url_section_cpts(), true ) ) {
		return $display;
	}

	if ( $element_type === 'singular' ) {
		if ( $section_var !== $section_cpt_el || $outlet_slug === '' ) {
			return $display;
		}
		return true;
	}

	if ( $element_type === 'archive' ) {
		if ( $section_var !== $section_cpt_el || $outlet_slug !== '' ) {
			return $display;
		}
		return pw_is_section_enabled( $prop_id, $section_cpt_el );
	}

	return $display;
}
