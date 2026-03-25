<?php
defined( 'ABSPATH' ) || exit;

function pw_get_template_part( $slug, $name = '', $args = [] ) {
	$template = '';
	$file     = $name ? "{$slug}-{$name}.php" : "{$slug}.php";

	$theme_path = get_stylesheet_directory() . '/portico-webworks/' . $file;
	if ( file_exists( $theme_path ) ) {
		$template = $theme_path;
	}

	if ( ! $template ) {
		$plugin_path = PW_PLUGIN_DIR . 'templates/' . $file;
		if ( file_exists( $plugin_path ) ) {
			$template = $plugin_path;
		}
	}

	if ( $template ) {
		if ( $args && is_array( $args ) ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract
		}
		include $template;
	}
}
