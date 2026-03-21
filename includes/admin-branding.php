<?php

if (!defined('ABSPATH')) {
	exit;
}

add_action('login_enqueue_scripts', function () {
	$path = plugin_dir_path(PW_PLUGIN_FILE) . 'login-logo.svg';
	if (!is_readable($path)) {
		return;
	}
	$logo = plugins_url('login-logo.svg', PW_PLUGIN_FILE);
	wp_register_style('pw-login-logo', false, array(), PW_VERSION);
	wp_enqueue_style('pw-login-logo');
	$css = '
.login h1 a {
	background-image: url(' . wp_json_encode($logo) . ');
	background-size: contain;
	background-position: center center;
	width: 100%;
	height: 84px;
	margin: 0 auto 16px;
}
';
	wp_add_inline_style('pw-login-logo', $css);
});

add_filter('login_headerurl', function ( $_login_header_url ) {
	return home_url('/');
});

add_filter('login_headertext', function ( $_login_header_text ) {
	return 'Portico Webworks';
});

add_filter('admin_footer_text', function ( $_text ) {
	return sprintf(
		'Hotel website tools by <a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>.',
		esc_url('https://porticowebworks.com'),
		esc_html('Portico Webworks')
	);
});
