<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Best-effort fatal / uncaught error logging when WP_DEBUG logging is unavailable.
 *
 * wp-config.php (before "That's all, stop editing"):
 *   define( 'PW_FATAL_LOG_PLUGIN_ONLY', true ); // only log errors in this plugin’s path
 *   define( 'PW_FATAL_LOG_BOOT_PROBE', true );  // temporary: proves a log file is writable each request
 */
function pw_fatal_log_candidate_paths() {
	$paths = array();
	if ( defined( 'WP_CONTENT_DIR' ) && WP_CONTENT_DIR ) {
		$paths[] = WP_CONTENT_DIR . '/portico-webworks-fatal.log';
	}
	if ( function_exists( 'wp_upload_dir' ) ) {
		$u = @wp_upload_dir();
		if ( is_array( $u ) && ! empty( $u['basedir'] ) && empty( $u['error'] ) ) {
			$paths[] = trailingslashit( $u['basedir'] ) . 'portico-webworks-fatal.log';
		}
	}
	$key = defined( 'ABSPATH' ) ? md5( ABSPATH ) : 'site';
	$paths[] = sys_get_temp_dir() . '/portico-webworks-fatal-' . $key . '.log';
	if ( defined( 'ABSPATH' ) && ABSPATH ) {
		$paths[] = ABSPATH . 'portico-webworks-fatal.log';
	}
	return array_unique( $paths );
}

/**
 * @param string $line Must end with newline.
 * @return bool True if written to at least one destination.
 */
function pw_fatal_log_write_line( $line ) {
	$ok = false;
	foreach ( pw_fatal_log_candidate_paths() as $path ) {
		if ( $path === '' ) {
			continue;
		}
		$dir = dirname( $path );
		if ( ! is_dir( $dir ) ) {
			continue;
		}
		if ( @file_put_contents( $path, $line, FILE_APPEND | LOCK_EX ) !== false ) {
			$ok = true;
		}
	}
	$trim = trim( $line );
	if ( $trim !== '' ) {
		@error_log( '[Portico Webworks] ' . $trim );
	}
	return $ok;
}

function pw_fatal_log_format_last_error( array $last ) {
	$file = isset( $last['file'] ) ? (string) $last['file'] : '';
	$line = isset( $last['line'] ) ? (int) $last['line'] : 0;
	$msg  = isset( $last['message'] ) ? (string) $last['message'] : '';
	return sprintf( "[%s] PHP fatal (type %d): %s in %s:%d\n", gmdate( 'c' ), isset( $last['type'] ) ? (int) $last['type'] : 0, $msg, $file, $line );
}

function pw_fatal_log_format_throwable( Throwable $e ) {
	$trace = $e->getTraceAsString();
	if ( strlen( $trace ) > 8192 ) {
		$trace = substr( $trace, 0, 8192 ) . "\n…(truncated)";
	}
	return sprintf(
		"[%s] Uncaught %s: %s in %s:%d\n%s\n",
		gmdate( 'c' ),
		get_class( $e ),
		$e->getMessage(),
		$e->getFile(),
		$e->getLine(),
		$trace
	);
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
		$plugin_only = defined( 'PW_FATAL_LOG_PLUGIN_ONLY' ) && PW_FATAL_LOG_PLUGIN_ONLY;
		if ( $plugin_only && ( $file === '' || stripos( $file, 'portico_webworks_plugin' ) === false ) ) {
			return;
		}
		pw_fatal_log_write_line( pw_fatal_log_format_last_error( $last ) );
	}
);

$pw_fatal_log_prev_handler = null;
$pw_fatal_log_prev_handler = set_exception_handler(
	function ( Throwable $e ) use ( &$pw_fatal_log_prev_handler ) {
		pw_fatal_log_write_line( pw_fatal_log_format_throwable( $e ) );
		if ( $pw_fatal_log_prev_handler ) {
			call_user_func( $pw_fatal_log_prev_handler, $e );
		}
	}
);

if ( defined( 'PW_FATAL_LOG_BOOT_PROBE' ) && PW_FATAL_LOG_BOOT_PROBE ) {
	add_action(
		'plugins_loaded',
		static function () {
			pw_fatal_log_write_line( '[' . gmdate( 'c' ) . "] Portico Webworks: fatal logger boot probe (remove PW_FATAL_LOG_BOOT_PROBE from wp-config when done).\n" );
		},
		9999
	);
}
