<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_shutdown_function(
	function () {
		$last = error_get_last();
		if ( ! is_array( $last ) ) {
			return;
		}
		$type = isset( $last['type'] ) ? (int) $last['type'] : 0;
		$fatal_types = array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR );
		if ( ! in_array( $type, $fatal_types, true ) ) {
			return;
		}
		$file = isset( $last['file'] ) ? (string) $last['file'] : '';
		if ( $file === '' || stripos( $file, 'portico_webworks_plugin' ) === false ) {
			return;
		}
		$base = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : dirname( ABSPATH ) . '/wp-content';
		$path = $base . '/portico-webworks-fatal.log';
		$line = sprintf(
			"[%s] %s in %s:%d\n",
			gmdate( 'c' ),
			isset( $last['message'] ) ? $last['message'] : '',
			$file,
			isset( $last['line'] ) ? (int) $last['line'] : 0
		);
		@file_put_contents( $path, $line, FILE_APPEND | LOCK_EX );
	}
);
