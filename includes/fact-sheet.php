<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode(
	'pw_fact_sheet',
	static function () {
		$pid = pw_get_current_property_id();
		if ( is_wp_error( $pid ) || ! $pid ) {
			return '<p class="pw-fact-sheet-empty">' . esc_html( 'No property found.' ) . '</p>';
		}
		ob_start();
		$property_id = (int) $pid;
		require __DIR__ . '/fact-sheet-template.php';
		return ob_get_clean();
	}
);
