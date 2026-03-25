<?php
/**
 * Demo media: sideload assets/sample-media and attach to multi-install sample posts.
 *
 * @package Portico_Webworks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/sample-demo-media-seo.php';

/**
 * @param string $slug      Post slug.
 * @param string $post_type Post type.
 * @return int
 */
function pw_sample_get_post_id_by_slug( $slug, $post_type ) {
	$slug = sanitize_title( (string) $slug );
	if ( $slug === '' ) {
		return 0;
	}
	$posts = get_posts(
		[
			'name'           => $slug,
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		]
	);
	return ! empty( $posts ) ? (int) $posts[0] : 0;
}

/**
 * Sideload plugin assets/sample-media into the media library (filename => attachment ID).
 *
 * @return array<string, int>
 */
function pw_sample_sideload_sample_media_map() {
	static $done = null;
	if ( $done !== null ) {
		return $done;
	}
	$done = [];
	$dir  = dirname( __DIR__ ) . '/assets/sample-media/';
	if ( ! is_dir( $dir ) ) {
		return $done;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$list = scandir( $dir, SCANDIR_SORT_ASCENDING );
	if ( ! is_array( $list ) ) {
		return $done;
	}
	foreach ( $list as $f ) {
		if ( $f === '.' || $f === '..' ) {
			continue;
		}
		if ( ! is_file( $dir . $f ) ) {
			continue;
		}
		if ( ! preg_match( '/\.(jpe?g|png|gif)$/i', $f ) ) {
			continue;
		}
		$aid = pw_sample_sideload_sample_media_file( $dir . $f );
		if ( $aid > 0 ) {
			$done[ $f ] = $aid;
		}
	}
	return $done;
}

/**
 * @param string $abs_path Absolute path to image file.
 * @return int Attachment ID or 0.
 */
function pw_sample_sideload_sample_media_file( $abs_path ) {
	static $cache = [];
	if ( isset( $cache[ $abs_path ] ) ) {
		return $cache[ $abs_path ];
	}
	$cache[ $abs_path ] = 0;
	if ( ! is_readable( $abs_path ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( $abs_path );
	if ( ! $tmp ) {
		return 0;
	}
	if ( ! copy( $abs_path, $tmp ) ) {
		@unlink( $tmp );
		return 0;
	}
	$file_array = [
		'name'     => basename( $abs_path ),
		'tmp_name' => $tmp,
	];
	$id = media_handle_sideload( $file_array, 0 );
	@unlink( $tmp );
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	$id = (int) $id;
	pw_sample_flag_post( $id );
	pw_sample_apply_demo_media_attachment_seo( $id, basename( $abs_path ) );
	$cache[ $abs_path ] = $id;
	return $id;
}

/**
 * @param int    $post_id   Post ID.
 * @param string $post_type Post type.
 * @param array  $items     List of [ 'file' => ... ] in order.
 * @param array  $map       Filename => attachment ID.
 */
function pw_sample_set_gallery_with_meta( $post_id, $post_type, array $items, array $map ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 || $items === [] ) {
		return;
	}
	$gallery = [];
	foreach ( $items as $it ) {
		$fn = isset( $it['file'] ) ? (string) $it['file'] : '';
		if ( $fn === '' || empty( $map[ $fn ] ) ) {
			continue;
		}
		$aid = (int) $map[ $fn ];
		$url = wp_get_attachment_url( $aid );
		if ( ! $url ) {
			continue;
		}
		$gallery[ $aid ] = $url;
	}
	if ( $gallery === [] ) {
		return;
	}
	update_post_meta( $post_id, '_pw_gallery', $gallery );
}

/**
 * @param int    $post_id Post ID.
 * @param string $file    Filename under assets/sample-media.
 * @param array  $map     Filename => attachment ID.
 */
function pw_sample_set_featured_if_file( $post_id, $file, array $map ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 || $file === '' || empty( $map[ $file ] ) ) {
		return;
	}
	set_post_thumbnail( $post_id, (int) $map[ $file ] );
}

/**
 * @param int   $property_id Property post ID.
 * @param array $map         Filename => attachment ID.
 */
function pw_sample_merge_pool_attachment_ids( $property_id, array $map ) {
	$property_id = (int) $property_id;
	if ( $property_id <= 0 ) {
		return;
	}
	$pools = get_post_meta( $property_id, '_pw_pools', true );
	if ( ! is_array( $pools ) ) {
		return;
	}
	$post   = get_post( $property_id );
	$slug   = $post instanceof WP_Post ? $post->post_name : '';
	$updated = false;
	foreach ( $pools as $i => $row ) {
		if ( ! is_array( $pools[ $i ] ) ) {
			continue;
		}
		if ( ! isset( $pools[ $i ]['attachment_id'] ) ) {
			$pools[ $i ]['attachment_id'] = 0;
		}
	}
	if ( $slug === 'meridian-grand-bengaluru' ) {
		if ( isset( $pools[0] ) && is_array( $pools[0] ) && ! empty( $map['indoor-pool.jpeg'] ) ) {
			$pools[0]['attachment_id'] = (int) $map['indoor-pool.jpeg'];
			$updated                   = true;
		}
		if ( isset( $pools[1] ) && is_array( $pools[1] ) && ! empty( $map['indoor-pool.jpeg'] ) ) {
			$pools[1]['attachment_id'] = (int) $map['indoor-pool.jpeg'];
			$updated                   = true;
		}
	} elseif ( $slug === 'azure-bay-beach-resort' ) {
		if ( isset( $pools[0] ) && is_array( $pools[0] ) && ! empty( $map['nearby-beach.jpeg'] ) ) {
			$pools[0]['attachment_id'] = (int) $map['nearby-beach.jpeg'];
			$updated                   = true;
		}
		if ( isset( $pools[1] ) && is_array( $pools[1] ) && ! empty( $map['indoor-pool.jpeg'] ) ) {
			$pools[1]['attachment_id'] = (int) $map['indoor-pool.jpeg'];
			$updated                   = true;
		}
	}
	if ( $updated ) {
		update_post_meta( $property_id, '_pw_pools', $pools );
	}
}

/**
 * @param int $p1 Property ID Bengaluru.
 * @param int $p2 Property ID Goa.
 */
function pw_sample_multi_install_apply_demo_media( $p1, $p2 ) {
	$p1 = (int) $p1;
	$p2 = (int) $p2;
	if ( $p1 <= 0 || $p2 <= 0 ) {
		return;
	}
	$map = pw_sample_sideload_sample_media_map();
	if ( $map === [] ) {
		return;
	}

	$m = static function ( $map, $file ) {
		return isset( $map[ $file ] ) ? (int) $map[ $file ] : 0;
	};

	if ( $m( $map, 'hotel-exterior-areal-view.jpeg' ) ) {
		update_post_meta( $p1, '_pw_og_image', $m( $map, 'hotel-exterior-areal-view.jpeg' ) );
		update_post_meta( $p2, '_pw_og_image', $m( $map, 'hotel-exterior-areal-view.jpeg' ) );
	}

	pw_sample_merge_pool_attachment_ids( $p1, $map );
	pw_sample_merge_pool_attachment_ids( $p2, $map );

	$p1_prop_gallery = [
		[ 'file' => 'hotel-exterior-porch.jpeg', 'category' => 'exterior', 'caption' => 'Arrival at the porte-cochère with landscaped forecourt.' ],
		[ 'file' => 'lobby-01.jpeg', 'category' => 'lobby', 'caption' => 'Lobby with reception and seating areas.' ],
		[ 'file' => 'lobby-common space.jpeg', 'category' => 'lobby', 'caption' => 'Lobby lounge and circulation for guests.' ],
		[ 'file' => 'hotel-interior-corridoor.jpeg', 'category' => 'common_area', 'caption' => 'Guest corridors with soft lighting and warm finishes.' ],
		[ 'file' => 'interior-lift-landing-area.jpeg', 'category' => 'common_area', 'caption' => 'Lift lobby on the guest room floors.' ],
		[ 'file' => 'indoor-gym.jpeg', 'category' => 'common_area', 'caption' => 'Fitness centre with cardio and strength equipment.' ],
		[ 'file' => 'indoor-pool.jpeg', 'category' => 'pool', 'caption' => 'Indoor pool area for year-round swimming.' ],
		[ 'file' => 'nearby-mg-road-metro-station-bengaluru.jpeg', 'category' => 'exterior', 'caption' => 'Metro connectivity near the hotel.' ],
		[ 'file' => 'nearby-ub-city-mall-bengaluru.jpeg', 'category' => 'exterior', 'caption' => 'Luxury retail and dining within walking distance.' ],
	];
	pw_sample_set_gallery_with_meta( $p1, 'pw_property', $p1_prop_gallery, $map );

	$p2_prop_gallery = [
		[ 'file' => 'hotel-exterior-areal-view.jpeg', 'category' => 'aerial', 'caption' => 'Resort overview from above, with gardens and pool.' ],
		[ 'file' => 'hotel-exterior-porch.jpeg', 'category' => 'exterior', 'caption' => 'Arrival drive and resort entrance.' ],
		[ 'file' => 'nearby-baga-beach-goa.jpeg', 'category' => 'beach', 'caption' => 'North Goa beaches a short drive from the resort.' ],
		[ 'file' => 'nearby-beach.jpeg', 'category' => 'beach', 'caption' => 'Coastal scenery and sunsets near the property.' ],
		[ 'file' => 'nearby-goa-international-airport-terminal.jpeg', 'category' => 'exterior', 'caption' => 'Arrival options via Goa\'s international airport.' ],
		[ 'file' => 'indoor-pool.jpeg', 'category' => 'pool', 'caption' => 'Pool deck for relaxation between beach and spa visits.' ],
	];
	pw_sample_set_gallery_with_meta( $p2, 'pw_property', $p2_prop_gallery, $map );

	$rid = pw_sample_get_post_id_by_slug( 'deluxe-king', 'pw_room_type' );
	if ( $rid ) {
		pw_sample_set_featured_if_file( $rid, 'deluxe-king-room.jpeg', $map );
		pw_sample_set_gallery_with_meta(
			$rid,
			'pw_room_type',
			[
				[ 'file' => 'deluxe-king-room.jpeg', 'category' => 'bedroom', 'caption' => 'Deluxe king room with city-facing outlook and work area.' ],
				[ 'file' => 'rooms-work-desk.jpeg', 'category' => 'amenities', 'caption' => 'Writing desk and ergonomic seating for business stays.' ],
				[ 'file' => 'rooms-bathroom.jpeg', 'category' => 'bathroom', 'caption' => 'Marble bathroom with walk-in shower.' ],
			],
			$map
		);
	}
	$rid = pw_sample_get_post_id_by_slug( 'premier-twin', 'pw_room_type' );
	if ( $rid ) {
		pw_sample_set_featured_if_file( $rid, 'rooms-twin-room.png', $map );
		pw_sample_set_gallery_with_meta(
			$rid,
			'pw_room_type',
			[
				[ 'file' => 'rooms-twin-room.png', 'category' => 'bedroom', 'caption' => 'Premier twin room with twin beds and city views.' ],
				[ 'file' => 'rooms-bathroom-02.jpeg', 'category' => 'bathroom', 'caption' => 'Guest bathroom with modern fixtures.' ],
			],
			$map
		);
	}
	$rid = pw_sample_get_post_id_by_slug( 'executive-suite', 'pw_room_type' );
	if ( $rid ) {
		pw_sample_set_featured_if_file( $rid, 'rooms-swuite-living-room.jpeg', $map );
		pw_sample_set_gallery_with_meta(
			$rid,
			'pw_room_type',
			[
				[ 'file' => 'rooms-swuite-living-room.jpeg', 'category' => 'living_area', 'caption' => 'Separate living area with lounge seating.' ],
				[ 'file' => 'rooms-balcony.jpeg', 'category' => 'balcony', 'caption' => 'Private balcony with pool outlook.' ],
				[ 'file' => 'rooms-bathroom.jpeg', 'category' => 'bathroom', 'caption' => 'Spa-style bathroom with soaking tub.' ],
			],
			$map
		);
	}
	$rid = pw_sample_get_post_id_by_slug( 'garden-villa', 'pw_room_type' );
	if ( $rid ) {
		pw_sample_set_featured_if_file( $rid, 'room-pool-villa.jpeg', $map );
		pw_sample_set_gallery_with_meta(
			$rid,
			'pw_room_type',
			[
				[ 'file' => 'room-pool-villa.jpeg', 'category' => 'view', 'caption' => 'Garden villa with private terrace and greenery.' ],
				[ 'file' => 'rooms-pool-villa-02.jpeg', 'category' => 'bedroom', 'caption' => 'Bedroom with natural light and resort textures.' ],
			],
			$map
		);
	}
	$rid = pw_sample_get_post_id_by_slug( 'sea-facing-deluxe', 'pw_room_type' );
	if ( $rid ) {
		pw_sample_set_featured_if_file( $rid, 'rooms-balcony.jpeg', $map );
		pw_sample_set_gallery_with_meta(
			$rid,
			'pw_room_type',
			[
				[ 'file' => 'rooms-balcony.jpeg', 'category' => 'balcony', 'caption' => 'Sea-facing balcony for morning coffee and sunset views.' ],
				[ 'file' => 'rooms-bathroom-02.jpeg', 'category' => 'bathroom', 'caption' => 'Refresh in a bright bathroom with premium fittings.' ],
			],
			$map
		);
	}

	$kid = pw_sample_get_post_id_by_slug( 'skyline-kitchen-rooftop', 'pw_restaurant' );
	if ( $kid ) {
		pw_sample_set_featured_if_file( $kid, 'restaurant-roof-top.jpeg', $map );
		pw_sample_set_gallery_with_meta(
			$kid,
			'pw_restaurant',
			[
				[ 'file' => 'restaurant-roof-top.jpeg', 'category' => 'dining_area', 'caption' => 'Rooftop dining with city views at Skyline Kitchen.' ],
				[ 'file' => 'restaurant-bar.jpeg', 'category' => 'bar', 'caption' => 'Bar and lounge area for cocktails and small plates.' ],
			],
			$map
		);
	}
	$kid = pw_sample_get_post_id_by_slug( 'merchants-hall', 'pw_restaurant' );
	if ( $kid ) {
		pw_sample_set_featured_if_file( $kid, 'restaurant-all-day-dining.jpeg', $map );
		pw_sample_set_gallery_with_meta(
			$kid,
			'pw_restaurant',
			[
				[ 'file' => 'restaurant-all-day-dining.jpeg', 'category' => 'dining_area', 'caption' => 'All-day dining with buffet and à la carte options.' ],
				[ 'file' => 'raustaurant-all-day-dining-02.jpeg', 'category' => 'dining_area', 'caption' => 'Spacious seating for breakfast through dinner.' ],
				[ 'file' => 'restaruant-private-dining-room.jpeg', 'category' => 'private_dining', 'caption' => 'Private dining room for celebrations and events.' ],
				[ 'file' => 'restaurant-breakfast-buffet.jpeg', 'category' => 'buffet', 'caption' => 'Morning buffet with continental and Indian favourites.' ],
			],
			$map
		);
	}
	$kid = pw_sample_get_post_id_by_slug( 'azure-shore-grill', 'pw_restaurant' );
	if ( $kid ) {
		pw_sample_set_featured_if_file( $kid, 'restaurant-bar.jpeg', $map );
		pw_sample_set_gallery_with_meta(
			$kid,
			'pw_restaurant',
			[
				[ 'file' => 'restaurant-bar.jpeg', 'category' => 'bar', 'caption' => 'Beach bar with signature cocktails and coastal views.' ],
				[ 'file' => 'restaurant-roof-top.jpeg', 'category' => 'dining_area', 'caption' => 'Open-air seating by the water.' ],
			],
			$map
		);
	}

	$sid = pw_sample_get_post_id_by_slug( 'stillwater-spa-bengaluru', 'pw_spa' );
	if ( $sid ) {
		pw_sample_set_featured_if_file( $sid, 'spa-treatment-room.jpeg', $map );
		pw_sample_set_gallery_with_meta(
			$sid,
			'pw_spa',
			[
				[ 'file' => 'spa-treatment-room.jpeg', 'category' => 'treatment_room', 'caption' => 'Treatment room for massages and therapies.' ],
				[ 'file' => 'spas-reception.jpeg', 'category' => 'reception', 'caption' => 'Spa reception and welcome lounge.' ],
				[ 'file' => 'spas-couple-therapy-room.jpeg', 'category' => 'treatment_room', 'caption' => 'Couples suite for shared rituals.' ],
				[ 'file' => 'steam-room.jpeg', 'category' => 'facilities', 'caption' => 'Heat and steam facilities for pre- and post-treatment relaxation.' ],
			],
			$map
		);
	}
	$sid = pw_sample_get_post_id_by_slug( 'tidepool-garden-spa', 'pw_spa' );
	if ( $sid ) {
		pw_sample_set_featured_if_file( $sid, 'spa-treatment-room.jpeg', $map );
		pw_sample_set_gallery_with_meta(
			$sid,
			'pw_spa',
			[
				[ 'file' => 'spa-treatment-room.jpeg', 'category' => 'treatment_room', 'caption' => 'Garden spa treatment room.' ],
				[ 'file' => 'spas-reception.jpeg', 'category' => 'reception', 'caption' => 'Spa reception and arrival experience.' ],
			],
			$map
		);
	}

	$mid = pw_sample_get_post_id_by_slug( 'meridian-grand-ballroom', 'pw_meeting_room' );
	if ( $mid ) {
		pw_sample_set_featured_if_file( $mid, 'meeting-gala-dinner.jpeg', $map );
		pw_sample_set_gallery_with_meta(
			$mid,
			'pw_meeting_room',
			[
				[ 'file' => 'meeting-gala-dinner.jpeg', 'category' => 'banquet', 'caption' => 'Ballroom set for gala dinner and awards.' ],
				[ 'file' => 'meeting-theatre-style-seating.jpeg', 'category' => 'theatre', 'caption' => 'Theatre-style seating for conference keynotes.' ],
				[ 'file' => 'meeting-classroom-style-seating.jpeg', 'category' => 'theatre', 'caption' => 'Classroom layout for training and workshops.' ],
				[ 'file' => 'meeting-wedding-round-table.jpeg', 'category' => 'banquet', 'caption' => 'Round tables for weddings and celebrations.' ],
			],
			$map
		);
	}
	$mid = pw_sample_get_post_id_by_slug( 'horizon-boardroom', 'pw_meeting_room' );
	if ( $mid ) {
		pw_sample_set_featured_if_file( $mid, 'meeting-board-room.jpeg', $map );
		pw_sample_set_gallery_with_meta(
			$mid,
			'pw_meeting_room',
			[
				[ 'file' => 'meeting-board-room.jpeg', 'category' => 'boardroom', 'caption' => 'Executive boardroom with natural light.' ],
				[ 'file' => 'meeting-u-shape-boardroom-setup.jpeg', 'category' => 'boardroom', 'caption' => 'U-shape setup for meetings and presentations.' ],
			],
			$map
		);
	}

	$exp_slugs = [
		'craft-beer-bengaluru-food-walk'    => [ [ 'file' => 'experience-craft-beer-food-walk-bengaluru.jpeg', 'category' => 'general', 'caption' => 'Evening food walk with craft beer and local bites.' ] ],
		'yoga-meditation-sunrise'           => [ [ 'file' => 'experience-morning-yoga-session.jpeg', 'category' => 'general', 'caption' => 'Morning yoga and meditation on the rooftop lawn.' ] ],
		'bengaluru-heritage-city-tour'      => [ [ 'file' => 'experience-bengaluru-heritage-city-tour.jpeg', 'category' => 'general', 'caption' => 'Heritage routing through Bengaluru landmarks.' ] ],
		'whisky-masterclass-merchants-hall' => [ [ 'file' => 'experience-whisky-masterclass-spice-verandah.jpeg', 'category' => 'general', 'caption' => 'Guided whisky tasting at Merchant\'s Hall.' ] ],
		'sunrise-kayaking'                  => [ [ 'file' => 'experience-sunrise-kayaking-goa.jpeg', 'category' => 'general', 'caption' => 'Sunrise kayaking along the coast.' ] ],
		'goan-cooking-masterclass'          => [ [ 'file' => 'experience-cooking-class-by-chef.jpeg', 'category' => 'general', 'caption' => 'Hands-on Goan cooking with our chef.' ] ],
		'spice-plantation-half-day-tour'    => [ [ 'file' => 'experience-spice-plantation-half-day-tour.jpeg', 'category' => 'general', 'caption' => 'Spice plantation walk and tasting.' ] ],
		'dolphin-watching-boat-trip'        => [ [ 'file' => 'experience-dolphin-watching-boat-trip.jpeg', 'category' => 'general', 'caption' => 'Boat trip to spot dolphins along the coast.' ] ],
		'full-moon-beach-bonfire-dinner'    => [ [ 'file' => 'experience-full-moon-beach-bonfire-dinner.jpeg', 'category' => 'general', 'caption' => 'Bonfire dinner under the full moon on the beach.' ] ],
	];
	foreach ( $exp_slugs as $slug => $items ) {
		$eid = pw_sample_get_post_id_by_slug( $slug, 'pw_experience' );
		if ( ! $eid ) {
			continue;
		}
		$feat = $items[0]['file'];
		pw_sample_set_featured_if_file( $eid, $feat, $map );
		pw_sample_set_gallery_with_meta( $eid, 'pw_experience', $items, $map );
	}

	$evt_slugs = [
		'festival-gala-dinner-skyline'       => [
			[ 'file' => 'event-diwali-gala-rooftop-restaurant.jpeg', 'category' => 'general', 'caption' => 'Festival gala dinner on the rooftop with skyline views.' ],
		],
		'corporate-leadership-summit'          => [
			[ 'file' => 'meeting-classroom-style-seating.jpeg', 'category' => 'general', 'caption' => 'Summit sessions in the ballroom.' ],
		],
		'new-years-eve-bengaluru-countdown'  => [
			[ 'file' => 'event-new-years-eve-ballroom-bengaluru.jpeg', 'category' => 'general', 'caption' => 'New Year\'s Eve countdown in the ballroom.' ],
		],
		'full-moon-beach-party-goa'          => [
			[ 'file' => 'event-new-years-eve-beach-party-goa-night.jpeg', 'category' => 'general', 'caption' => 'Beach party under the stars.' ],
		],
		'azure-bay-christmas-brunch'         => [
			[ 'file' => 'restaurant-breakfast-buffet.jpeg', 'category' => 'general', 'caption' => 'Festive brunch spread with seasonal favourites.' ],
		],
		'new-years-eve-beach-bash-goa'       => [
			[ 'file' => 'event-new-years-eve-beach-party-goa-night.jpeg', 'category' => 'general', 'caption' => 'New Year\'s Eve celebration on the beach.' ],
		],
	];
	foreach ( $evt_slugs as $slug => $items ) {
		$eid = pw_sample_get_post_id_by_slug( $slug, 'pw_event' );
		if ( ! $eid ) {
			continue;
		}
		pw_sample_set_featured_if_file( $eid, $items[0]['file'], $map );
		pw_sample_set_gallery_with_meta( $eid, 'pw_event', $items, $map );
	}

	$offers = [
		'advance-purchase-save-15'     => 'offer-promo-advance-purchase-bengaluru.jpeg',
		'bengaluru-business-package'     => 'offer-package-business-bengaluru.jpeg',
		'extended-stay-4-nights-1-free' => 'offer-promo-advance-purchase-bengaluru.jpeg',
		'conference-season-special'     => 'offer-package-business-bengaluru.jpeg',
		'early-bird-goa-escape-save-20' => 'offer-promo-early-bird-goa.jpeg',
		'honeymoon-by-the-sea-package'  => 'offer-package-honeymoon-goa.jpeg',
		'festive-goa-christmas-new-year' => 'offer-promo-festive-goa-christmas.jpeg',
	];
	foreach ( $offers as $slug => $file ) {
		$oid = pw_sample_get_post_id_by_slug( $slug, 'pw_offer' );
		if ( $oid ) {
			pw_sample_set_featured_if_file( $oid, $file, $map );
		}
	}

	$near_p1 = [
		'kempegowda-international-airport-blr' => 'nearby-airport.jpeg',
		'mg-road-metro-station'               => 'nearby-mg-road-metro-station-bengaluru.jpeg',
		'ub-city-mall'                        => 'nearby-ub-city-mall-bengaluru.jpeg',
		'cubbon-park'                         => 'nearby-park.jpeg',
		'lido-mall'                           => 'nearby-lido-mall-bengaluru.jpeg',
		'st-marks-cathedral'                  => 'nearby-st-marks-cathedral-bengaluru.jpeg',
		'bangalore-palace'                    => 'nearby-bangalore-palace-bengaluru.jpeg',
	];
	foreach ( $near_p1 as $slug => $file ) {
		$nid = pw_sample_get_post_id_by_slug( $slug, 'pw_nearby' );
		if ( $nid ) {
			pw_sample_set_featured_if_file( $nid, $file, $map );
		}
	}
	$near_p2 = [
		'goa-international-airport'    => 'nearby-goa-international-airport-terminal.jpeg',
		'calangute-beach'              => 'nearby-beach.jpeg',
		'baga-beach'                   => 'nearby-baga-beach-goa.jpeg',
		'saturday-night-market-arpora' => 'nearby-saturday-night-market-arpora.jpeg',
		'basilica-of-bom-jesus'        => 'nearby-basilica-bom-jesus-old-goa.jpeg',
		'anjuna-flea-market'           => 'nearby-anjuna-flea-market-goa.jpeg',
	];
	foreach ( $near_p2 as $slug => $file ) {
		$nid = pw_sample_get_post_id_by_slug( $slug, 'pw_nearby' );
		if ( $nid ) {
			pw_sample_set_featured_if_file( $nid, $file, $map );
		}
	}
}
