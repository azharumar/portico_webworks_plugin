<?php
defined( 'ABSPATH' ) || exit;

function pw_template_include_section_singular( $template, $cpt ) {
	$cpt = sanitize_key( (string) $cpt );
	if ( $cpt === '' || ! in_array( $cpt, pw_url_section_cpts(), true ) ) {
		return $template;
	}
	$specific = "single-{$cpt}.php";
	$found    = locate_template( [ $specific ] );
	if ( $found ) {
		return $found;
	}
	$plugin = PW_PLUGIN_DIR . 'templates/' . $specific;
	if ( file_exists( $plugin ) ) {
		return $plugin;
	}
	$fallback = locate_template( [ 'single.php', 'singular.php', 'index.php' ] );
	return $fallback ? $fallback : $template;
}

function pw_template_include_section_archive( $template, $cpt ) {
	$cpt = sanitize_key( (string) $cpt );
	if ( $cpt === '' || ! in_array( $cpt, pw_url_section_cpts(), true ) ) {
		return $template;
	}
	$specific = "archive-{$cpt}.php";
	$found    = locate_template( [ $specific ] );
	if ( $found ) {
		return $found;
	}
	$plugin = PW_PLUGIN_DIR . 'templates/' . $specific;
	if ( file_exists( $plugin ) ) {
		return $plugin;
	}
	$fallback = locate_template( [ 'archive.php', 'index.php' ] );
	return $fallback ? $fallback : $template;
}

function pw_template_include_property_singular( $template ) {
	$specific = 'single-pw_property.php';
	$found    = locate_template( [ $specific ] );
	if ( $found ) {
		return $found;
	}
	$plugin = PW_PLUGIN_DIR . 'templates/' . $specific;
	if ( file_exists( $plugin ) ) {
		return $plugin;
	}
	$fallback = locate_template( [ 'single.php', 'singular.php', 'index.php' ] );
	return $fallback ? $fallback : $template;
}

function pw_template_include_property_archive( $template ) {
	$specific = 'archive-pw_property.php';
	$found    = locate_template( [ $specific ] );
	if ( $found ) {
		return $found;
	}
	$plugin = PW_PLUGIN_DIR . 'templates/' . $specific;
	if ( file_exists( $plugin ) ) {
		return $plugin;
	}
	$fallback = locate_template( [ 'archive.php', 'index.php' ] );
	return $fallback ? $fallback : $template;
}

function pw_template_include_core_queries( $template ) {
	if ( is_embed() || is_feed() || is_admin() ) {
		return $template;
	}
	if (
		(string) get_query_var( 'pw_section_cpt', '' ) !== ''
		|| (string) get_query_var( 'pw_property_slug', '' ) !== ''
		|| (string) get_query_var( 'pw_outlet_slug', '' ) !== ''
	) {
		return $template;
	}
	foreach ( pw_url_section_cpts() as $cpt ) {
		if ( is_singular( $cpt ) ) {
			return pw_template_include_section_singular( $template, $cpt );
		}
	}
	foreach ( pw_url_section_cpts() as $cpt ) {
		if ( is_post_type_archive( $cpt ) ) {
			return pw_template_include_section_archive( $template, $cpt );
		}
	}
	if ( is_singular( 'pw_property' ) ) {
		return pw_template_include_property_singular( $template );
	}
	if ( is_post_type_archive( 'pw_property' ) ) {
		return pw_template_include_property_archive( $template );
	}
	return $template;
}

add_filter( 'template_include', 'pw_template_include_core_queries', 99 );
