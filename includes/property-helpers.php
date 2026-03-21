<?php

if (!defined('ABSPATH')) {
	exit;
}

function pw_get_property_profile( $property_id = null ) {
	$id = $property_id ?? pw_get_current_property_id();
	if ( ! $id ) {
		return [];
	}

	$keys = [
		'legal_name'         => '_pw_legal_name',
		'star_rating'        => '_pw_star_rating',
		'currency'           => '_pw_currency',
		'check_in_time'      => '_pw_check_in_time',
		'check_out_time'     => '_pw_check_out_time',
		'year_established'   => '_pw_year_established',
		'total_rooms'        => '_pw_total_rooms',
		'address_line_1'     => '_pw_address_line_1',
		'address_line_2'     => '_pw_address_line_2',
		'city'               => '_pw_city',
		'state'              => '_pw_state',
		'postal_code'        => '_pw_postal_code',
		'country'            => '_pw_country',
		'country_code'       => '_pw_country_code',
		'contacts'           => '_pw_contacts',
		'lat'                => '_pw_lat',
		'lng'                => '_pw_lng',
		'google_place_id'    => '_pw_google_place_id',
		'timezone'           => '_pw_timezone',
		'social_facebook'    => '_pw_social_facebook',
		'social_instagram'   => '_pw_social_instagram',
		'social_twitter'     => '_pw_social_twitter',
		'social_tripadvisor' => '_pw_social_tripadvisor',
		'social_linkedin'    => '_pw_social_linkedin',
		'social_youtube'     => '_pw_social_youtube',
	];

	$profile = [];
	foreach ( $keys as $label => $meta_key ) {
		$profile[ $label ] = get_post_meta( (int) $id, $meta_key, true );
	}

	return $profile;
}

function pw_get_all_properties() {
	$ids = get_posts(array(
		'post_type' => 'pw_property',
		'post_status' => 'any',
		'numberposts' => -1,
		'fields' => 'ids',
		'orderby' => 'title',
		'order' => 'ASC',
	));

	if (!is_array($ids)) {
		return array();
	}

	$out = array();
	foreach ($ids as $id) {
		$out[] = array(
			'id' => (int) $id,
			'name' => get_the_title($id),
			'slug' => get_post_field('post_name', $id),
		);
	}

	return $out;
}

function pw_property_base() {
	$base = pw_get_setting('pw_property_base', 'properties');
	$base = is_string($base) ? trim($base) : 'properties';
	$base = trim($base, '/');
	if ($base === '') {
		$base = 'properties';
	}

	return sanitize_title($base);
}

function pw_resolve_property_id_from_url() {
	global $wp;

	$request = '';
	if (isset($wp) && isset($wp->request) && is_string($wp->request)) {
		$request = $wp->request;
	} else {
		$uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
		$path = $uri ? parse_url($uri, PHP_URL_PATH) : '';
		$request = $path ? trim($path, '/') : '';
	}

	$base = pw_property_base();
	if ($base === '') {
		return null;
	}

	$segments = $request !== '' ? explode('/', trim($request, '/')) : array();
	if (count($segments) < 2) {
		return null;
	}

	if ($segments[0] !== $base) {
		return null;
	}

	$slug = sanitize_title($segments[1]);
	if ($slug === '') {
		return null;
	}

	$props = get_posts(array(
		'post_type' => 'pw_property',
		'post_status' => 'publish',
		'name' => $slug,
		'numberposts' => 1,
		'fields' => 'ids',
	));

	if (!empty($props) && is_array($props)) {
		return (int) $props[0];
	}

	return null;
}

function pw_request_matches_property_url_path() {
	global $wp;

	$request = '';
	if ( isset( $wp ) && isset( $wp->request ) && is_string( $wp->request ) ) {
		$request = $wp->request;
	} else {
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path = $uri ? parse_url( $uri, PHP_URL_PATH ) : '';
		$request = $path ? trim( $path, '/' ) : '';
	}

	$base = pw_property_base();
	if ( $base === '' ) {
		return false;
	}

	$segments = $request !== '' ? explode( '/', trim( $request, '/' ) ) : [];

	return count( $segments ) >= 2 && $segments[0] === $base;
}

function pw_get_current_property_id() {
	static $done  = false;
	static $value = 0;

	if ( $done ) {
		return $value;
	}
	$done = true;

	$mode = pw_get_setting( 'pw_property_mode', 'single' );

	if ( $mode === 'multi' ) {
		$from_url = pw_resolve_property_id_from_url();
		$value    = ! empty( $from_url ) ? (int) $from_url : 0;
		return $value;
	}

	if ( $mode === 'single' ) {
		$rid = (int) pw_get_setting( 'pw_default_property_id', 0 );
		$value = $rid > 0 ? $rid : 0;
		return $value;
	}

	$value = 0;
	return $value;
}

function pw_get_current_property_profile() {
	return pw_get_property_profile(null);
}

function pw_get_child_posts( $cpt, $property_id ) {
	return get_posts( [
		'post_type'      => $cpt,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => [
			[
				'key'   => '_pw_property_id',
				'value' => (int) $property_id,
			],
		],
	] );
}

function pw_get_room_features( $room_type_id ) {
	$feature_ids = get_post_meta( (int) $room_type_id, '_pw_features', true );
	if ( empty( $feature_ids ) || ! is_array( $feature_ids ) ) return [];

	return get_posts( [
		'post_type'      => 'pw_feature',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'post__in'       => $feature_ids,
		'orderby'        => 'post__in',
	] );
}

function pw_get_experiences_for( $post_type, $post_id ) {
	$post_id = (int) $post_id;
	$experiences = get_posts( [
		'post_type'      => 'pw_experience',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	] );

	return array_values( array_filter( $experiences, function( $exp ) use ( $post_type, $post_id ) {
		if ( $post_type === 'pw_property' && $post_id > 0 ) {
			$scope = (int) get_post_meta( $exp->ID, '_pw_property_id', true );
			if ( $scope === $post_id ) {
				return true;
			}
		}
		$connections = get_post_meta( $exp->ID, '_pw_connected_to', true );
		if ( empty( $connections ) || ! is_array( $connections ) ) {
			return false;
		}
		foreach ( $connections as $c ) {
			if ( isset( $c['type'], $c['id'] ) &&
				$c['type'] === $post_type &&
				(int) $c['id'] === $post_id ) {
				return true;
			}
		}
		return false;
	} ) );
}

function pw_get_faqs_for( $post_type, $post_id ) {
	$post_id = (int) $post_id;
	$faqs    = get_posts( [
		'post_type'      => 'pw_faq',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	] );

	return array_values( array_filter( $faqs, function( $faq ) use ( $post_type, $post_id ) {
		if ( $post_type === 'pw_property' && $post_id > 0 ) {
			$scope = (int) get_post_meta( $faq->ID, '_pw_property_id', true );
			if ( $scope === $post_id ) {
				return true;
			}
		}
		$connections = get_post_meta( $faq->ID, '_pw_connected_to', true );
		if ( empty( $connections ) || ! is_array( $connections ) ) {
			return false;
		}
		foreach ( $connections as $connection ) {
			if (
				isset( $connection['type'], $connection['id'] ) &&
				$connection['type'] === $post_type &&
				(int) $connection['id'] === $post_id
			) {
				return true;
			}
		}
		return false;
	} ) );
}

function pw_get_operating_hours( $post_id ) {
	$days  = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];
	$hours = [];
	foreach ( $days as $day ) {
		$hours[ $day ] = get_post_meta( (int) $post_id, '_pw_hours_' . $day, true ) ?: [
			'is_closed' => false,
			'sessions'  => [],
		];
	}
	return $hours;
}

function pw_get_property_currency( $property_id = null ) {
	$id = $property_id ?? pw_get_current_property_id();
	if ( ! $id ) {
		return 'USD';
	}
	return get_post_meta( (int) $id, '_pw_currency', true ) ?: 'USD';
}

add_action(
	'template_redirect',
	function () {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		if ( pw_get_setting( 'pw_property_mode', 'single' ) !== 'multi' ) {
			return;
		}

		if ( ! pw_request_matches_property_url_path() ) {
			return;
		}

		if ( pw_get_current_property_id() > 0 ) {
			return;
		}

		global $wp_query;
		if ( isset( $wp_query ) && is_object( $wp_query ) ) {
			$wp_query->set_404();
		}

		wp_die(
			'',
			'',
			array(
				'response' => 404,
			)
		);
	}
);

function pw_filter_generateblocks_query_loop_property_scope( $query_args, $attributes ) {
	if ( empty( $attributes['pw_scope_to_property'] ) ) {
		return $query_args;
	}

	$pid = pw_get_current_property_id();
	if ( $pid <= 0 ) {
		return $query_args;
	}

	$clause = array(
		'key'     => '_pw_property_id',
		'value'   => (int) $pid,
		'type'    => 'NUMERIC',
		'compare' => '=',
	);

	$existing = isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] ) ? $query_args['meta_query'] : array();

	if ( empty( $existing ) ) {
		$query_args['meta_query'] = array( $clause );
	} else {
		$query_args['meta_query'] = array(
			'relation' => 'AND',
			$existing,
			$clause,
		);
	}

	return $query_args;
}

add_filter( 'generateblocks_query_loop_args', 'pw_filter_generateblocks_query_loop_property_scope', 10, 2 );

add_filter(
	'register_block_type_args',
	function ( $args, $name ) {
		if ( ! in_array( $name, array( 'generateblocks/query', 'generateblocks/looper' ), true ) ) {
			return $args;
		}
		if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}
		$args['attributes']['pw_scope_to_property'] = array(
			'type'    => 'boolean',
			'default' => false,
		);
		return $args;
	},
	10,
	2
);

/**
 * IANA timezone for interpreting pw_event local datetimes (linked property, else WP timezone).
 */
function pw_event_timezone_for_property( $property_id ) {
	$id = (int) $property_id;
	if ( $id > 0 ) {
		$tz_id = (string) get_post_meta( $id, '_pw_timezone', true );
		if ( $tz_id !== '' ) {
			try {
				return new DateTimeZone( $tz_id );
			} catch ( Exception $e ) {
				// Invalid IANA id — fall back.
			}
		}
	}
	return wp_timezone();
}

/**
 * Interprets stored event wall time (Y-m-d H:i:s) in the property timezone; returns ISO 8601 with offset (schema.org Event startDate/endDate).
 */
function pw_event_local_datetime_to_iso8601( $local_ymd_his, $property_id ) {
	$local_ymd_his = is_string( $local_ymd_his ) ? trim( $local_ymd_his ) : '';
	if ( $local_ymd_his === '' || ! preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $local_ymd_his ) ) {
		return '';
	}
	$tz = pw_event_timezone_for_property( $property_id );
	$dt = DateTime::createFromFormat( 'Y-m-d H:i:s', $local_ymd_his, $tz );
	if ( ! $dt ) {
		return '';
	}
	return $dt->format( 'c' );
}

add_action( 'rest_api_init', function () {
	register_rest_field(
		'pw_event',
		'pw_start_datetime_iso8601',
		[
			'get_callback' => function ( $obj ) {
				$post_id = isset( $obj['id'] ) ? (int) $obj['id'] : 0;
				if ( ! $post_id ) {
					return '';
				}
				$prop = (int) get_post_meta( $post_id, '_pw_property_id', true );
				$raw  = get_post_meta( $post_id, '_pw_start_datetime', true );
				return pw_event_local_datetime_to_iso8601( $raw, $prop );
			},
			'schema'       => [
				'description' => 'startDate: ISO 8601 with offset; _pw_start_datetime interpreted in linked property _pw_timezone.',
				'type'        => 'string',
				'context'     => [ 'view', 'edit', 'embed' ],
				'readonly'    => true,
			],
		]
	);
	register_rest_field(
		'pw_event',
		'pw_end_datetime_iso8601',
		[
			'get_callback' => function ( $obj ) {
				$post_id = isset( $obj['id'] ) ? (int) $obj['id'] : 0;
				if ( ! $post_id ) {
					return '';
				}
				$prop = (int) get_post_meta( $post_id, '_pw_property_id', true );
				$raw  = get_post_meta( $post_id, '_pw_end_datetime', true );
				return pw_event_local_datetime_to_iso8601( $raw, $prop );
			},
			'schema'       => [
				'description' => 'endDate: ISO 8601 with offset; _pw_end_datetime interpreted in linked property _pw_timezone.',
				'type'        => 'string',
				'context'     => [ 'view', 'edit', 'embed' ],
				'readonly'    => true,
			],
		]
	);
} );

