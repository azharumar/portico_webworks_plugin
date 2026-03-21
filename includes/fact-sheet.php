<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/fact-sheet-render.php';
require_once __DIR__ . '/fact-sheet-dynamic-tags.php';

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! is_singular( 'page' ) ) {
			return;
		}
		$fid = (int) get_option( 'pw_fact_sheet_page_id', 0 );
		if ( $fid <= 0 || (int) get_queried_object_id() !== $fid ) {
			return;
		}
		wp_enqueue_style(
			'pw-fact-sheet',
			plugins_url( 'assets/fact-sheet.css', PW_PLUGIN_FILE ),
			array(),
			defined( 'PW_VERSION' ) ? PW_VERSION : '1'
		);
	},
	20
);

add_filter( 'the_content', 'pw_fact_sheet_replace_content_tokens', 12 );
