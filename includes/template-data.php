<?php
defined( 'ABSPATH' ) || exit;

function pw_room_get_related_offers( $room_post_id ) {
	$room_post_id = (int) $room_post_id;
	if ( $room_post_id <= 0 ) {
		return [];
	}
	$property_id = (int) get_post_meta( $room_post_id, '_pw_property_id', true );
	if ( $property_id <= 0 ) {
		return [];
	}
	$candidates = get_posts(
		[
			'post_type'              => 'pw_offer',
			'post_status'            => 'publish',
			'posts_per_page'         => 50,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
		]
	);
	$out = [];
	foreach ( $candidates as $p ) {
		if ( ! $p instanceof WP_Post ) {
			continue;
		}
		$rooms = get_post_meta( $p->ID, '_pw_room_types', true );
		if ( ! is_array( $rooms ) ) {
			continue;
		}
		$rooms = array_map( 'intval', $rooms );
		if ( in_array( $room_post_id, $rooms, true ) ) {
			$out[] = $p;
		}
	}
	return $out;
}

function pw_restaurant_get_faqs( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return [];
	}

	$items = get_posts(
		[
			'post_type'              => 'pw_faq',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	$out = [];
	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}
		$connected = get_post_meta( $item->ID, '_pw_connected_to', true );
		if ( ! is_array( $connected ) ) {
			continue;
		}
		foreach ( $connected as $edge ) {
			if ( ! is_array( $edge ) ) {
				continue;
			}
			$type = isset( $edge['type'] ) ? sanitize_key( (string) $edge['type'] ) : '';
			$id   = isset( $edge['id'] ) ? (int) $edge['id'] : 0;
			if ( $type === 'pw_restaurant' && $id === $post_id ) {
				$out[] = $item;
				break;
			}
		}
	}

	return $out;
}

function pw_spa_get_faqs( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return [];
	}

	$items = get_posts(
		[
			'post_type'              => 'pw_faq',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	$out = [];
	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}
		$connected = get_post_meta( $item->ID, '_pw_connected_to', true );
		if ( ! is_array( $connected ) ) {
			continue;
		}
		foreach ( $connected as $edge ) {
			if ( ! is_array( $edge ) ) {
				continue;
			}
			$type = isset( $edge['type'] ) ? sanitize_key( (string) $edge['type'] ) : '';
			$id   = isset( $edge['id'] ) ? (int) $edge['id'] : 0;
			if ( $type === 'pw_spa' && $id === $post_id ) {
				$out[] = $item;
				break;
			}
		}
	}

	return $out;
}

function pw_meeting_room_get_faqs( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return [];
	}

	$items = get_posts(
		[
			'post_type'              => 'pw_faq',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	$out = [];
	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}
		$connected = get_post_meta( $item->ID, '_pw_connected_to', true );
		if ( ! is_array( $connected ) ) {
			continue;
		}
		foreach ( $connected as $edge ) {
			if ( ! is_array( $edge ) ) {
				continue;
			}
			$type = isset( $edge['type'] ) ? sanitize_key( (string) $edge['type'] ) : '';
			$id   = isset( $edge['id'] ) ? (int) $edge['id'] : 0;
			if ( $type === 'pw_meeting_room' && $id === $post_id ) {
				$out[] = $item;
				break;
			}
		}
	}

	return $out;
}

function pw_meeting_room_get_adjacent_venues( $post_id, $limit = 3 ) {
	$post_id = (int) $post_id;
	$limit   = (int) $limit;
	if ( $post_id <= 0 || $limit <= 0 ) {
		return [];
	}

	$property_id = (int) get_post_meta( $post_id, '_pw_property_id', true );
	if ( $property_id <= 0 ) {
		return [];
	}

	$items = get_posts(
		[
			'post_type'              => 'pw_meeting_room',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
			'post__not_in'          => [ $post_id ],
		]
	);

	$out = [];
	foreach ( $items as $p ) {
		if ( $p instanceof WP_Post ) {
			$out[] = $p;
		}
	}

	return $out;
}

function pw_meeting_room_get_primary_contact( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return null;
	}

	$property_id = (int) get_post_meta( $post_id, '_pw_property_id', true );
	if ( $property_id <= 0 ) {
		return null;
	}

	return pw_resolve_primary_contact( 'meeting_room', $post_id, $property_id );
}

function pw_experience_get_primary_contact( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return null;
	}

	$property_id = (int) get_post_meta( $post_id, '_pw_property_id', true );
	if ( $property_id <= 0 ) {
		return null;
	}

	return pw_resolve_primary_contact( 'experience', $post_id, $property_id );
}

function pw_experience_get_related( $post_id, $limit = 3 ) {
	$post_id = (int) $post_id;
	$limit   = (int) $limit;
	if ( $post_id <= 0 || $limit <= 0 ) {
		return [];
	}

	$property_id = (int) get_post_meta( $post_id, '_pw_property_id', true );
	if ( $property_id <= 0 ) {
		return [];
	}

	$terms = get_the_terms( $post_id, 'pw_experience_category' );
	if ( ! is_array( $terms ) || $terms === [] ) {
		return [];
	}

	$term_ids = [];
	foreach ( $terms as $t ) {
		if ( $t instanceof WP_Term ) {
			$term_ids[] = (int) $t->term_id;
		}
	}
	$term_ids = array_values( array_unique( array_filter( $term_ids ) ) );
	if ( $term_ids === [] ) {
		return [];
	}

	$items = get_posts(
		[
			'post_type'              => 'pw_experience',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'post__not_in'          => [ $post_id ],
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
			'tax_query'              => [
				[
					'taxonomy' => 'pw_experience_category',
					'terms'    => $term_ids,
					'field'    => 'term_id',
				],
			],
		]
	);

	return array_values( array_filter( $items, static function ( $p ) {
		return $p instanceof WP_Post;
	} ) );
}

function pw_offer_get_related( $post_id, $limit = 3 ) {
	$post_id = (int) $post_id;
	$limit   = (int) $limit;
	if ( $post_id <= 0 || $limit <= 0 ) {
		return [];
	}

	$property_id = (int) get_post_meta( $post_id, '_pw_property_id', true );
	if ( $property_id <= 0 ) {
		return [];
	}

	$now_ts = current_time( 'timestamp' );

	$candidates = get_posts(
		[
			'post_type'              => 'pw_offer',
			'post_status'            => 'publish',
			'posts_per_page'         => 20,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'post__not_in'          => [ $post_id ],
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
		]
	);

	$out = [];
	foreach ( $candidates as $p ) {
		if ( ! $p instanceof WP_Post ) {
			continue;
		}

		$from_raw = (string) get_post_meta( $p->ID, '_pw_valid_from', true );
		$to_raw   = (string) get_post_meta( $p->ID, '_pw_valid_to', true );

		$from_ok = true;
		if ( trim( $from_raw ) !== '' ) {
			$from_ts = is_numeric( $from_raw ) ? (int) $from_raw : strtotime( $from_raw );
			$from_ok = is_int( $from_ts ) && $from_ts > 0 ? $now_ts >= $from_ts : true;
		}

		$to_ok = true;
		if ( trim( $to_raw ) !== '' ) {
			$to_ts = is_numeric( $to_raw ) ? (int) $to_raw : strtotime( $to_raw );
			$to_ok = is_int( $to_ts ) && $to_ts > 0 ? $now_ts <= $to_ts : true;
		}

		if ( $from_ok && $to_ok ) {
			$out[] = $p;
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
	}

	return $out;
}

function pw_nearby_get_related( $post_id, $limit = 3 ) {
	$post_id = (int) $post_id;
	$limit   = (int) $limit;
	if ( $post_id <= 0 || $limit <= 0 ) {
		return [];
	}

	$property_id = (int) get_post_meta( $post_id, '_pw_property_id', true );
	if ( $property_id <= 0 ) {
		return [];
	}

	$terms = get_the_terms( $post_id, 'pw_nearby_type' );
	if ( ! is_array( $terms ) || $terms === [] ) {
		return [];
	}

	$term_ids = [];
	foreach ( $terms as $t ) {
		if ( $t instanceof WP_Term ) {
			$term_ids[] = (int) $t->term_id;
		}
	}
	$term_ids = array_values( array_unique( array_filter( $term_ids ) ) );
	if ( $term_ids === [] ) {
		return [];
	}

	$items = get_posts(
		[
			'post_type'              => 'pw_nearby',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'post__not_in'          => [ $post_id ],
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
			'tax_query'              => [
				[
					'taxonomy' => 'pw_nearby_type',
					'terms'    => $term_ids,
					'field'    => 'term_id',
				],
			],
		]
	);

	return array_values( array_filter( $items, static function ( $p ) {
		return $p instanceof WP_Post;
	} ) );
}

function pw_archive_get_room_offers( $property_id, $limit = 3 ) {
	$property_id = (int) $property_id;
	$limit       = (int) $limit;
	if ( $property_id <= 0 || $limit <= 0 ) {
		return [];
	}

	$now_ts = current_time( 'timestamp' );

	$candidates = get_posts(
		[
			'post_type'              => 'pw_offer',
			'post_status'            => 'publish',
			'posts_per_page'         => 30,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
		]
	);

	$out = [];
	foreach ( $candidates as $p ) {
		if ( ! $p instanceof WP_Post ) {
			continue;
		}
		$vf_raw = (string) get_post_meta( $p->ID, '_pw_valid_from', true );
		$vt_raw = (string) get_post_meta( $p->ID, '_pw_valid_to', true );

		$vf_ok = true;
		if ( trim( $vf_raw ) !== '' ) {
			$vf_ts = is_numeric( $vf_raw ) ? (int) $vf_raw : strtotime( $vf_raw );
			$vf_ok = is_int( $vf_ts ) && $vf_ts > 0 ? $now_ts >= $vf_ts : true;
		}

		$vt_ok = true;
		if ( trim( $vt_raw ) !== '' ) {
			$vt_ts = is_numeric( $vt_raw ) ? (int) $vt_raw : strtotime( $vt_raw );
			$vt_ok = is_int( $vt_ts ) && $vt_ts > 0 ? $now_ts <= $vt_ts : true;
		}

		if ( $vf_ok && $vt_ok ) {
			$out[] = $p;
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
	}

	return $out;
}

function pw_archive_get_upcoming_events( $property_id, $limit = 6 ) {
	$property_id = (int) $property_id;
	$limit       = (int) $limit;
	if ( $property_id <= 0 || $limit <= 0 ) {
		return [];
	}

	$now_ts = current_time( 'timestamp' );

	$events = get_posts(
		[
			'post_type'              => 'pw_event',
			'post_status'            => 'publish',
			'posts_per_page'         => 50,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
		]
	);

	$upcoming = [];
	foreach ( $events as $e ) {
		if ( ! $e instanceof WP_Post ) {
			continue;
		}
		$start_iso = (string) get_post_meta( $e->ID, '_pw_start_datetime_iso8601', true );
		if ( trim( $start_iso ) === '' ) {
			continue;
		}
		$ts = strtotime( $start_iso );
		if ( ! is_int( $ts ) || $ts <= 0 ) {
			continue;
		}
		if ( $ts >= $now_ts ) {
			$upcoming[] = [ 'post' => $e, 'ts' => $ts ];
		}
	}

	usort(
		$upcoming,
		static function ( $a, $b ) {
			return (int) $a['ts'] <=> (int) $b['ts'];
		}
	);

	$out = [];
	foreach ( $upcoming as $row ) {
		$out[] = $row['post'];
		if ( count( $out ) >= $limit ) {
			break;
		}
	}

	return $out;
}

function pw_archive_get_past_events( $property_id, $limit = 3 ) {
	$property_id = (int) $property_id;
	$limit       = (int) $limit;
	if ( $property_id <= 0 || $limit <= 0 ) {
		return [];
	}

	$now_ts = current_time( 'timestamp' );

	$events = get_posts(
		[
			'post_type'              => 'pw_event',
			'post_status'            => 'publish',
			'posts_per_page'         => 50,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
		]
	);

	$past = [];
	foreach ( $events as $e ) {
		if ( ! $e instanceof WP_Post ) {
			continue;
		}
		$start_iso = (string) get_post_meta( $e->ID, '_pw_start_datetime_iso8601', true );
		if ( trim( $start_iso ) === '' ) {
			continue;
		}
		$ts = strtotime( $start_iso );
		if ( ! is_int( $ts ) || $ts <= 0 ) {
			continue;
		}
		if ( $ts < $now_ts ) {
			$past[] = [ 'post' => $e, 'ts' => $ts ];
		}
	}

	usort(
		$past,
		static function ( $a, $b ) {
			return (int) $a['ts'] <=> (int) $b['ts'];
		}
	);

	$out = [];
	$from_end = count( $past ) - 1;
	for ( $i = $from_end; $i >= 0; -- $i ) {
		$out[] = $past[ $i ]['post'];
		if ( count( $out ) >= $limit ) {
			break;
		}
	}

	return $out;
}

function pw_restaurant_get_archive_primary_contact( $property_id ) {
	$property_id = (int) $property_id;
	if ( $property_id <= 0 ) {
		return null;
	}

	return pw_resolve_primary_contact( 'restaurant', 0, $property_id );
}

function pw_meeting_room_get_archive_primary_contact( $property_id ) {
	$property_id = (int) $property_id;
	if ( $property_id <= 0 ) {
		return null;
	}

	return pw_resolve_primary_contact( 'meeting_room', 0, $property_id );
}

function pw_property_get_room_preview( $property_id, $limit = 4 ) {
	$property_id = (int) $property_id;
	$limit       = (int) $limit;
	if ( $property_id <= 0 || $limit <= 0 ) {
		return [];
	}

	$rooms = get_posts(
		[
			'post_type'              => 'pw_room_type',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
		]
	);

	return array_values( array_filter( $rooms, static function ( $p ) {
		return $p instanceof WP_Post;
	} ) );
}

function pw_property_get_restaurant_preview( $property_id, $limit = 3 ) {
	$property_id = (int) $property_id;
	$limit       = (int) $limit;
	if ( $property_id <= 0 || $limit <= 0 ) {
		return [];
	}

	$restaurants = get_posts(
		[
			'post_type'              => 'pw_restaurant',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
		]
	);

	return array_values( array_filter( $restaurants, static function ( $p ) {
		return $p instanceof WP_Post;
	} ) );
}

function pw_property_get_experience_preview( $property_id ) {
	$property_id = (int) $property_id;
	if ( $property_id <= 0 ) {
		return [];
	}

	$rooms = [];

	$spa_posts = get_posts(
		[
			'post_type'              => 'pw_spa',
			'post_status'            => 'publish',
			'posts_per_page'         => 2,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
		]
	);
	foreach ( $spa_posts as $p ) {
		if ( $p instanceof WP_Post ) {
			$rooms[] = [ 'type' => 'pw_spa', 'post' => $p ];
		}
	}

	$exp_posts = get_posts(
		[
			'post_type'              => 'pw_experience',
			'post_status'            => 'publish',
			'posts_per_page'         => 2,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
		]
	);
	foreach ( $exp_posts as $p ) {
		if ( $p instanceof WP_Post ) {
			$rooms[] = [ 'type' => 'pw_experience', 'post' => $p ];
		}
	}

	$meet_posts = get_posts(
		[
			'post_type'              => 'pw_meeting_room',
			'post_status'            => 'publish',
			'posts_per_page'         => 2,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
		]
	);
	foreach ( $meet_posts as $p ) {
		if ( $p instanceof WP_Post ) {
			$rooms[] = [ 'type' => 'pw_meeting_room', 'post' => $p ];
		}
	}

	return $rooms;
}

function pw_property_get_active_offers( $property_id, $limit = 3 ) {
	$property_id = (int) $property_id;
	$limit       = (int) $limit;
	if ( $property_id <= 0 || $limit <= 0 ) {
		return [];
	}

	$now_ts = current_time( 'timestamp' );

	$candidates = get_posts(
		[
			'post_type'              => 'pw_offer',
			'post_status'            => 'publish',
			'posts_per_page'         => 20,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'meta_query'             => [
				[
					'key'   => '_pw_property_id',
					'value' => $property_id,
				],
			],
		]
	);

	$out = [];
	foreach ( $candidates as $p ) {
		if ( ! $p instanceof WP_Post ) {
			continue;
		}

		$vf_raw = (string) get_post_meta( $p->ID, '_pw_valid_from', true );
		$vt_raw = (string) get_post_meta( $p->ID, '_pw_valid_to', true );

		$vf_ok = true;
		if ( trim( $vf_raw ) !== '' ) {
			$vf_ts = is_numeric( $vf_raw ) ? (int) $vf_raw : strtotime( $vf_raw );
			$vf_ok = is_int( $vf_ts ) && $vf_ts > 0 ? $now_ts >= $vf_ts : true;
		}

		$vt_ok = true;
		if ( trim( $vt_raw ) !== '' ) {
			$vt_ts = is_numeric( $vt_raw ) ? (int) $vt_raw : strtotime( $vt_raw );
			$vt_ok = is_int( $vt_ts ) && $vt_ts > 0 ? $now_ts <= $vt_ts : true;
		}

		if ( $vf_ok && $vt_ok ) {
			$out[] = $p;
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
	}

	return $out;
}

function pw_property_get_announcement_bar( $property_id ) {
	$property_id = (int) $property_id;
	if ( $property_id <= 0 ) {
		return '';
	}

	$active = get_post_meta( $property_id, '_pw_announcement_active', true );
	if ( $active !== '1' && $active !== 1 && $active !== true && $active !== 'true' ) {
		return '';
	}

	$text = (string) get_post_meta( $property_id, '_pw_announcement_text', true );
	if ( trim( $text ) === '' ) {
		return '';
	}

	$now_ts = current_time( 'timestamp' );

	$start = (string) get_post_meta( $property_id, '_pw_announcement_start', true );
	if ( $start !== '' ) {
		$start_ts = is_numeric( $start ) ? (int) $start : strtotime( $start );
		if ( is_int( $start_ts ) && $start_ts > 0 && $now_ts < $start_ts ) {
			return '';
		}
	}

	$end = (string) get_post_meta( $property_id, '_pw_announcement_end', true );
	if ( $end !== '' ) {
		$end_ts = is_numeric( $end ) ? (int) $end : strtotime( $end );
		if ( is_int( $end_ts ) && $end_ts > 0 && $now_ts > $end_ts ) {
			return '';
		}
	}

	return $text;
}
