<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pw_resolve_contact( $scope_cpt, $scope_id, $property_id ) {
	return [];
}

function pw_resolve_primary_contact( $scope_cpt, $scope_id, $property_id ) {
	return null;
}

function pw_register_contact_rest_routes() {
	register_rest_route(
		'pw/v1',
		'/contacts',
		[
			'methods'             => 'GET',
			'permission_callback' => static function () {
				return current_user_can( 'edit_posts' );
			},
			'callback'            => static function () {
				return rest_ensure_response( [] );
			},
		]
	);

	register_rest_route(
		'pw/v1',
		'/contact-scope-posts',
		[
			'methods'             => 'GET',
			'permission_callback' => static function () {
				return current_user_can( 'edit_posts' );
			},
			'callback'            => static function () {
				return rest_ensure_response( [] );
			},
		]
	);
}

add_action( 'rest_api_init', 'pw_register_contact_rest_routes' );
