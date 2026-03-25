<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/sample-data-demo-media.php';

/**
 * Create demo pw_contact rows for a property (uses sample insert + meta).
 *
 * @param int   $property_id Property post ID.
 * @param array $rows        Rows: label, phone, mobile, whatsapp, email; optional post_title, scope_cpt, scope_id.
 */
function pw_sample_install_pw_contact_rows( $property_id, array $rows ) {
	$property_id = (int) $property_id;
	if ( $property_id <= 0 ) {
		return;
	}
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$title = isset( $row['post_title'] ) ? (string) $row['post_title'] : ( isset( $row['label'] ) ? (string) $row['label'] : 'Contact' );
		$ins   = pw_sample_wp_insert_post(
			[
				'post_type'   => 'pw_contact',
				'post_status' => 'publish',
				'post_title'  => $title,
			],
			true
		);
		if ( is_wp_error( $ins ) || ! $ins ) {
			continue;
		}
		$cid = (int) $ins;
		update_post_meta( $cid, '_pw_property_id', $property_id );
		update_post_meta( $cid, '_pw_label', isset( $row['label'] ) ? (string) $row['label'] : '' );
		update_post_meta( $cid, '_pw_phone', isset( $row['phone'] ) ? (string) $row['phone'] : '' );
		update_post_meta( $cid, '_pw_mobile', isset( $row['mobile'] ) ? (string) $row['mobile'] : '' );
		update_post_meta( $cid, '_pw_whatsapp', isset( $row['whatsapp'] ) ? (string) $row['whatsapp'] : '' );
		update_post_meta( $cid, '_pw_email', isset( $row['email'] ) ? (string) $row['email'] : '' );
		$scope_cpt = isset( $row['scope_cpt'] ) ? sanitize_key( (string) $row['scope_cpt'] ) : 'property';
		if ( ! pw_contact_is_valid_scope_cpt( $scope_cpt ) ) {
			$scope_cpt = 'property';
		}
		update_post_meta( $cid, '_pw_scope_cpt', $scope_cpt );
		$scope_id = isset( $row['scope_id'] ) ? absint( $row['scope_id'] ) : 0;
		update_post_meta( $cid, '_pw_scope_id', $scope_id );
	}
}

/**
 * Installs two demo properties (Bengaluru + Goa) and related CPT rows.
 *
 * Meta keys and shapes follow DATA-STRUCTURE.md and register_post_meta / CMB2 in the plugin.
 */
function pw_install_sample_dataset_multi() {
	$seed_terms = pw_get_taxonomy_seed_terms();
	$tax_batches = array_merge(
		[
			'pw_property_type' => $seed_terms['pw_property_type'],
			'pw_policy_type'   => $seed_terms['pw_policy_type'],
		],
		[
			'pw_bed_type'            => [ 'King', 'Queen', 'Twin', 'Double' ],
			'pw_view_type'           => [ 'City View', 'Pool View', 'Garden View', 'Sea View', 'City', 'Garden', 'Pool', 'Sea', 'Partial Ocean', 'Ocean' ],
			'pw_meal_period'         => [ 'Breakfast', 'Brunch', 'Lunch', 'Dinner', 'Sunday Brunch', 'All-day Dining', 'Late Night' ],
			'pw_treatment_type'      => [ 'Ayurveda', 'Aromatherapy', 'Deep Tissue', 'Hot Stone', 'Couples Treatment', 'Facial', 'Swedish Massage', 'Ayurvedic Abhyanga', 'Foot Reflexology', 'Massage', 'Body Wrap' ],
			'pw_av_equipment'        => [ 'LED Screen', 'Microphone', 'Podium', 'In-built Sound System', 'Stage Lighting', '75" Display Screen', 'Wireless Presentation', 'Video Conferencing System', 'Whiteboard', 'Projector', 'Screen', 'PA System' ],
			'pw_feature_group'       => [ 'Room Features', 'Entertainment', 'Bathroom', 'Business', 'Housekeeping', 'Bedding', 'Connectivity', 'In-room', 'Climate', 'Outdoor' ],
			'pw_nearby_type'         => [ 'Airport', 'Metro', 'Shopping', 'Park', 'Landmark', 'Attraction', 'Beach', 'Heritage', 'Market' ],
			'pw_transport_mode'      => [ 'Walk', 'Drive', 'Taxi', 'Shuttle' ],
			'pw_experience_category' => [ 'Cultural', 'Wellness', 'Culinary', 'Adventure', 'Nature' ],
			'pw_event_type'          => [ 'Gala', 'Conference', 'Beach Event', 'Brunch' ],
		]
	);
	foreach ( $tax_batches as $tax => $names ) {
		foreach ( array_unique( $names ) as $name ) {
			pw_sample_ensure_term( $name, $tax );
		}
	}
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 8, __( 'Demo taxonomy terms ready.', 'portico-webworks' ) );
	}

	$organiser_id = pw_sample_ensure_term( 'Meridian & Azure Demo Events', 'pw_event_organiser' );
	if ( $organiser_id ) {
		update_term_meta( $organiser_id, 'organiser_url', 'https://meridian-azure-demo.example/events/' );
	}

	$p1_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_property',
			'post_status'  => 'publish',
			'post_title'   => 'Meridian Grand Hotel Bengaluru',
			'post_name'    => 'meridian-grand-bengaluru',
			'post_content' => '<p>Five-star business hotel on Vittal Mallya Road with 184 rooms, rooftop dining, a full-service spa, and pillar-free ballrooms for MICE — built for corporate stays, conferences, and after-work dining in central Bengaluru.</p>',
			'post_excerpt' => 'Corporate flagship in Bengaluru — meetings, dining, and express airport access.',
		],
		true
	);
	$p2_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_property',
			'post_status'  => 'publish',
			'post_title'   => 'Azure Bay Beach Resort',
			'post_name'    => 'azure-bay-beach-resort',
			'post_content' => '<p>Four-star beach resort in Calangute with infinity pool, family-friendly pools, Goan seafood, and calm gardens — 76 keys between garden villas and sea-facing rooms for leisure, families, and barefoot weekends.</p>',
			'post_excerpt' => 'North Goa beach resort — families, sunsets, and slow coastal days.',
		],
		true
	);
	if ( is_wp_error( $p1_ins ) || ! $p1_ins || is_wp_error( $p2_ins ) || ! $p2_ins ) {
		return;
	}
	$p1 = (int) $p1_ins;
	$p2 = (int) $p2_ins;
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 14, __( 'Created demo properties.', 'portico-webworks' ) );
	}

	$y_cur            = (int) gmdate( 'Y' );
	$season_conf_from = $y_cur . '-09-01';
	$season_conf_to   = $y_cur . '-11-30';
	$season_peak_from = $y_cur . '-12-20';
	$season_peak_to   = ( $y_cur + 1 ) . '-01-05';
	$festive_from     = $y_cur . '-12-18';
	$festive_to       = ( $y_cur + 1 ) . '-01-07';
	$offer_adv_from   = gmdate( 'Y-m-d', strtotime( '+7 days' ) );
	$offer_adv_to     = gmdate( 'Y-m-d', strtotime( '+200 days' ) );

	$mode_multi = pw_get_setting( 'pw_property_mode', 'single' ) === 'multi';
	foreach ( [ $p1, $p2 ] as $_pid ) {
		$scope = $mode_multi ? $_pid : 0;
		$_fs   = pw_find_generated_page( PW_FACT_SHEET_PAGE_SLUG, $scope );
		if ( $_fs instanceof WP_Post ) {
			update_post_meta( (int) $_fs->ID, '_pw_is_sample_data', '1' );
		}
		if ( ! $mode_multi ) {
			break;
		}
	}

	$p1_strings = [
		'_pw_legal_name'         => 'Meridian Grand Hotels Private Limited',
		'_pw_currency'           => 'INR',
		'_pw_check_in_time'      => '14:00',
		'_pw_check_out_time'     => '12:00',
		'_pw_address_line_1'     => '23 Vittal Mallya Road',
		'_pw_address_line_2'     => 'Ashok Nagar',
		'_pw_city'               => 'Bengaluru',
		'_pw_state'              => 'Karnataka',
		'_pw_postal_code'        => '560001',
		'_pw_country'            => 'India',
		'_pw_country_code'       => 'IN',
		'_pw_timezone'           => 'Asia/Kolkata',
		'_pw_google_place_id'    => 'ChIJ2dGMjBYUrjsRKcmOxuH7Z8A',
		'_pw_social_facebook'    => 'https://facebook.com/meridiangrandbengaluru',
		'_pw_social_instagram'   => 'https://instagram.com/meridian_grand_blr',
		'_pw_social_twitter'     => 'https://twitter.com/MeridianGrandBLR',
		'_pw_social_tripadvisor' => 'https://tripadvisor.com/hotel-meridian-grand-bengaluru',
		'_pw_social_linkedin'    => 'https://linkedin.com/company/meridian-grand-hotels',
		'_pw_social_youtube'     => 'https://youtube.com/@MeridianGrandBLR',
	];
	foreach ( $p1_strings as $k => $v ) {
		update_post_meta( $p1, $k, $v );
	}
	update_post_meta( $p1, '_pw_star_rating', 5 );
	update_post_meta( $p1, '_pw_year_established', 2009 );
	update_post_meta( $p1, '_pw_total_rooms', 184 );
	update_post_meta( $p1, '_pw_lat', 12.97194 );
	update_post_meta( $p1, '_pw_lng', 77.59553 );
	pw_sample_install_pw_contact_rows(
		$p1,
		[
			[
				'post_title' => 'Hotel — Meridian Grand Hotel Bengaluru',
				'label'      => 'Hotel',
				'phone'      => '+91-80-4123-4500',
				'mobile'     => '',
				'whatsapp'   => '',
				'email'      => 'reservations@meridian-grand.example',
				'scope_cpt'  => 'property',
				'scope_id'   => 0,
			],
			[
				'post_title' => 'Reservations — Meridian Grand Hotel Bengaluru',
				'label'      => 'Reservations',
				'phone'      => '',
				'mobile'     => '+91-98860-12345',
				'whatsapp'   => '+91-98860-12345',
				'email'      => 'bookings@meridian-grand.example',
				'scope_cpt'  => 'property',
				'scope_id'   => 0,
			],
			[
				'post_title' => 'Sales — Meridian Grand Hotel Bengaluru',
				'label'      => 'Sales',
				'phone'      => '+91-80-4123-4520',
				'mobile'     => '+91-98860-67890',
				'whatsapp'   => '',
				'email'      => 'sales@meridian-grand.example',
				'scope_cpt'  => 'property',
				'scope_id'   => 0,
			],
		]
	);
	update_post_meta(
		$p1,
		'_pw_pools',
		[
			[ 'name' => 'Main Pool', 'length_m' => 25, 'width_m' => 12, 'depth_m' => 1.4, 'open_time' => '06:00', 'close_time' => '22:00', 'is_heated' => false, 'is_kids' => false, 'is_indoor' => false, 'is_infinity' => false ],
			[ 'name' => 'Kids Pool', 'length_m' => 6, 'width_m' => 4, 'depth_m' => 0.6, 'open_time' => '07:00', 'close_time' => '20:00', 'is_heated' => false, 'is_kids' => true, 'is_indoor' => false, 'is_infinity' => false ],
		]
	);
	update_post_meta(
		$p1,
		'_pw_direct_benefits',
		[
			[ 'title' => 'Best Rate Guarantee', 'description' => 'We match any lower rate found online, guaranteed', 'icon' => 'badge-check' ],
			[ 'title' => 'Complimentary Breakfast', 'description' => 'Breakfast for two included with every direct booking', 'icon' => 'coffee' ],
			[ 'title' => 'Early Check-In', 'description' => 'Subject to availability, check in from 10:00 AM', 'icon' => 'clock' ],
			[ 'title' => 'Welcome Amenity', 'description' => 'Seasonal fruit basket and welcome drink on arrival', 'icon' => 'gift' ],
		]
	);
	update_post_meta(
		$p1,
		'_pw_certifications',
		[
			[ 'name' => 'IGBC Green Building Silver', 'issuer' => 'Indian Green Building Council', 'year' => 2021, 'url' => 'https://igbc.in' ],
			[ 'name' => 'TripAdvisor Travellers\' Choice', 'issuer' => 'TripAdvisor', 'year' => 2024, 'url' => 'https://tripadvisor.com' ],
			[ 'name' => 'Best Business Hotel — South India', 'issuer' => 'Hospitality India Awards', 'year' => 2023, 'url' => 'https://hospitalityindiaawards.com' ],
		]
	);
	update_post_meta(
		$p1,
		PW_SUSTAINABILITY_ITEMS_META_KEY,
		pw_normalize_facet_items(
			[
				[ 'key' => 'solar_power', 'status' => 'available', 'note' => '40kW rooftop solar installation powers common areas' ],
				[ 'key' => 'recycling_program', 'status' => 'available', 'note' => 'Wet and dry waste segregated at source, dry waste to certified recyclers' ],
				[ 'key' => 'water_reuse_program', 'status' => 'available', 'note' => 'STP-treated water reused for landscaping' ],
				[ 'key' => 'reusable_water_bottles', 'status' => 'available', 'note' => 'Single-use plastics eliminated since 2022' ],
				[ 'key' => 'local_food_sourcing', 'status' => 'available', 'note' => '60% of F&B ingredients sourced from Karnataka farms' ],
				[ 'key' => 'green_building_design', 'status' => 'available', 'note' => 'IGBC Green Building — Silver Rated' ],
			],
			pw_get_sustainability_facet_definitions()
		)
	);
	update_post_meta(
		$p1,
		PW_ACCESSIBILITY_ITEMS_META_KEY,
		pw_normalize_facet_items(
			[
				[ 'key' => 'wheelchair_accessible', 'status' => 'available', 'note' => 'Dedicated accessible rooms on ground floor' ],
				[ 'key' => 'elevator', 'status' => 'available', 'note' => 'Three passenger elevators, one service elevator' ],
				[ 'key' => 'accessible_parking', 'status' => 'available', 'note' => 'Four reserved bays near main entrance' ],
				[ 'key' => 'accessible_room_available', 'status' => 'available', 'note' => 'Dedicated accessible rooms on ground floor' ],
				[ 'key' => 'visual_fire_alarm', 'status' => 'available', 'note' => 'Strobe alarms in all accessible rooms' ],
			],
			pw_get_accessibility_facet_definitions()
		)
	);
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 24, __( 'Meridian Grand profile and contacts…', 'portico-webworks' ) );
	}

	$p2_strings = [
		'_pw_legal_name'         => 'Azure Bay Hospitality LLP',
		'_pw_currency'           => 'INR',
		'_pw_check_in_time'      => '13:00',
		'_pw_check_out_time'     => '11:00',
		'_pw_address_line_1'     => 'Survey No. 47, Calangute-Candolim Road',
		'_pw_address_line_2'     => 'Calangute',
		'_pw_city'               => 'North Goa',
		'_pw_state'              => 'Goa',
		'_pw_postal_code'        => '403516',
		'_pw_country'            => 'India',
		'_pw_country_code'       => 'IN',
		'_pw_timezone'           => 'Asia/Kolkata',
		'_pw_google_place_id'    => 'ChIJf7kQv8FFvzsRvkmPjBL4YEk',
		'_pw_social_facebook'    => 'https://facebook.com/azurebaygoa',
		'_pw_social_instagram'   => 'https://instagram.com/azure_bay_goa',
		'_pw_social_tripadvisor' => 'https://tripadvisor.com/hotel-azure-bay-beach-resort',
		'_pw_social_twitter'     => '',
		'_pw_social_youtube'     => '',
		'_pw_social_linkedin'    => '',
	];
	foreach ( $p2_strings as $k => $v ) {
		update_post_meta( $p2, $k, $v );
	}
	update_post_meta( $p2, '_pw_star_rating', 4 );
	update_post_meta( $p2, '_pw_year_established', 2015 );
	update_post_meta( $p2, '_pw_total_rooms', 76 );
	update_post_meta( $p2, '_pw_lat', 15.54382 );
	update_post_meta( $p2, '_pw_lng', 73.75219 );

	$p1_prop_type = pw_sample_ensure_term( 'Hotel', 'pw_property_type' );
	$p2_prop_type = pw_sample_ensure_term( 'Resort', 'pw_property_type' );
	if ( $p1_prop_type ) {
		wp_set_object_terms( $p1, array_filter( [ (int) $p1_prop_type ] ), 'pw_property_type' );
	}
	if ( $p2_prop_type ) {
		wp_set_object_terms( $p2, array_filter( [ (int) $p2_prop_type ] ), 'pw_property_type' );
	}

	pw_sample_install_pw_contact_rows(
		$p2,
		[
			[
				'post_title' => 'Hotel — Azure Bay Beach Resort',
				'label'      => 'Hotel',
				'phone'      => '+91-832-2276-400',
				'mobile'     => '',
				'whatsapp'   => '',
				'email'      => 'hello@azurebay.example',
				'scope_cpt'  => 'property',
				'scope_id'   => 0,
			],
			[
				'post_title' => 'Reservations — Azure Bay Beach Resort',
				'label'      => 'Reservations',
				'phone'      => '',
				'mobile'     => '+91-97650-44321',
				'whatsapp'   => '+91-97650-44321',
				'email'      => 'stay@azurebay.example',
				'scope_cpt'  => 'property',
				'scope_id'   => 0,
			],
		]
	);
	update_post_meta(
		$p2,
		'_pw_pools',
		[
			[ 'name' => 'Infinity Pool', 'length_m' => 20, 'width_m' => 8, 'depth_m' => 1.5, 'open_time' => '07:00', 'close_time' => '22:00', 'is_heated' => false, 'is_kids' => false, 'is_indoor' => false, 'is_infinity' => true ],
			[ 'name' => 'Splash Zone', 'length_m' => 5, 'width_m' => 4, 'depth_m' => 0.5, 'open_time' => '08:00', 'close_time' => '19:00', 'is_heated' => false, 'is_kids' => true, 'is_indoor' => false, 'is_infinity' => false ],
		]
	);
	update_post_meta(
		$p2,
		'_pw_direct_benefits',
		[
			[ 'title' => 'Best Rate Guaranteed', 'description' => 'Direct rates are always the lowest — no third-party markups', 'icon' => 'badge-check' ],
			[ 'title' => 'Complimentary Airport Transfer', 'description' => 'One-way pickup from Goa International Airport on bookings of 3+ nights', 'icon' => 'car' ],
			[ 'title' => 'Free Water Sports Session', 'description' => '30-min kayaking or paddleboarding included per stay', 'icon' => 'waves' ],
			[ 'title' => 'Flexible Cancellation', 'description' => 'Free cancellation up to 48 hours before arrival on direct bookings', 'icon' => 'shield' ],
		]
	);
	update_post_meta( $p2, '_pw_certifications', [] );
	update_post_meta(
		$p2,
		PW_SUSTAINABILITY_ITEMS_META_KEY,
		pw_normalize_facet_items(
			[
				[ 'key' => 'solar_water_heater', 'status' => 'available', 'note' => 'Solar water heaters installed across all villa rooftops' ],
				[ 'key' => 'recycling_program', 'status' => 'available', 'note' => 'Partnership with Goa\'s Clean Goa Initiative' ],
				[ 'key' => 'water_reuse_program', 'status' => 'available', 'note' => 'Rainwater harvesting system, 30,000L capacity' ],
				[ 'key' => 'reusable_water_bottles', 'status' => 'available', 'note' => 'Bamboo toothbrushes, glass water bottles in all rooms' ],
				[ 'key' => 'local_food_sourcing', 'status' => 'available', 'note' => 'Seafood sourced daily from Calangute fishing co-operative' ],
				[ 'key' => 'green_building_design', 'status' => 'not_available', 'note' => '' ],
			],
			pw_get_sustainability_facet_definitions()
		)
	);
	update_post_meta(
		$p2,
		PW_ACCESSIBILITY_ITEMS_META_KEY,
		pw_normalize_facet_items(
			[
				[ 'key' => 'wheelchair_accessible', 'status' => 'available', 'note' => 'Two ground-floor accessible rooms with roll-in shower' ],
				[ 'key' => 'elevator', 'status' => 'not_available', 'note' => 'Single-storey resort layout' ],
				[ 'key' => 'accessible_parking', 'status' => 'available', 'note' => '' ],
				[ 'key' => 'visual_fire_alarm', 'status' => 'not_available', 'note' => '' ],
			],
			pw_get_accessibility_facet_definitions()
		)
	);
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 32, __( 'Azure Bay profile and room features…', 'portico-webworks' ) );
	}

	$feature_defs = [
		[ 'title' => 'Air Conditioning', 'icon' => 'ac', 'group' => 'Room Features' ],
		[ 'title' => 'Flat-Screen TV', 'icon' => 'tv', 'group' => 'Entertainment' ],
		[ 'title' => 'Mini Bar', 'icon' => 'minibar', 'group' => 'Room Features' ],
		[ 'title' => 'Electronic Safe', 'icon' => 'safe', 'group' => 'Room Features' ],
		[ 'title' => 'Bathtub', 'icon' => 'bathtub', 'group' => 'Bathroom' ],
		[ 'title' => 'Rainfall Shower', 'icon' => 'shower', 'group' => 'Bathroom' ],
		[ 'title' => 'Premium Toiletries', 'icon' => 'toiletries', 'group' => 'Bathroom' ],
		[ 'title' => 'Tea/Coffee Maker', 'icon' => 'coffee', 'group' => 'Room Features' ],
		[ 'title' => 'Work Desk', 'icon' => 'desk', 'group' => 'Business' ],
		[ 'title' => 'Balcony', 'icon' => 'balcony', 'group' => 'Room Features' ],
		[ 'title' => 'Blackout Curtains', 'icon' => 'curtains', 'group' => 'Room Features' ],
		[ 'title' => 'High-Speed Wi-Fi', 'icon' => 'wifi', 'group' => 'Connectivity' ],
		[ 'title' => 'Iron & Ironing Board', 'icon' => 'iron', 'group' => 'Housekeeping' ],
		[ 'title' => 'Pillow Menu', 'icon' => 'pillow', 'group' => 'Bedding' ],
		[ 'title' => 'Smart TV with Streaming', 'icon' => 'smart-tv', 'group' => 'Entertainment' ],
	];
	$feature_ids = [];
	foreach ( $feature_defs as $fd ) {
		$gid = pw_sample_ensure_term( $fd['group'], 'pw_feature_group' );
		$fid = pw_sample_wp_insert_post( [ 'post_type' => 'pw_feature', 'post_status' => 'publish', 'post_title' => $fd['title'] ], true );
		if ( is_wp_error( $fid ) || ! $fid ) {
			continue;
		}
		$fid = (int) $fid;
		update_post_meta( $fid, '_pw_icon', $fd['icon'] );
		if ( $gid ) {
			wp_set_object_terms( $fid, [ $gid ], 'pw_feature_group' );
		}
		$feature_ids[] = $fid;
	}
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 40, __( 'Room feature library ready.', 'portico-webworks' ) );
	}

	$king_tid      = pw_sample_ensure_term( 'King', 'pw_bed_type' );
	$queen_tid     = pw_sample_ensure_term( 'Queen', 'pw_bed_type' );
	$twin_tid      = pw_sample_ensure_term( 'Twin', 'pw_bed_type' );
	$city_view_tid = pw_sample_ensure_term( 'City View', 'pw_view_type' );
	$pool_view_tid = pw_sample_ensure_term( 'Pool View', 'pw_view_type' );
	$garden_view_tid = pw_sample_ensure_term( 'Garden View', 'pw_view_type' );
	$sea_view_tid  = pw_sample_ensure_term( 'Sea View', 'pw_view_type' );

	$rooms_p1 = [
		[
			'pid' => $p1, 'slug' => 'deluxe-king', 'title' => 'Deluxe King', 'excerpt' => 'King bed, city-facing room.', 'content' => '<p>Spacious deluxe room with king bed and executive work area.</p>',
			'rate_from' => 8500, 'rate_to' => 12000, 'occ' => 2, 'adults' => 2, 'children' => 0, 'beds' => [ $king_tid ], 'views' => [ $city_view_tid ],
			'sqft' => 380, 'sqm' => 35, 'extra_beds' => 1, 'order' => 1, 'features' => array_slice( $feature_ids, 0, 10 ),
			'rates' => [
				[ 'rate_label' => 'Standard Rate', 'rate_type' => 'rack', 'price' => 9500, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 0, 'includes_breakfast' => false ],
				[ 'rate_label' => 'Advance Purchase 15', 'rate_type' => 'advance', 'price' => 8500, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 15, 'includes_breakfast' => false ],
				[ 'rate_label' => 'Bed & Breakfast', 'rate_type' => 'package', 'price' => 11000, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 0, 'includes_breakfast' => true ],
			],
		],
		[
			'pid' => $p1, 'slug' => 'premier-twin', 'title' => 'Premier Twin', 'excerpt' => 'Twin beds, city view.', 'content' => '<p>Ideal for colleagues or friends travelling together.</p>',
			'rate_from' => 8500, 'rate_to' => 12000, 'occ' => 3, 'adults' => 2, 'children' => 1, 'beds' => [ $twin_tid ], 'views' => [ $city_view_tid ],
			'sqft' => 380, 'sqm' => 35, 'extra_beds' => 1, 'order' => 2, 'features' => array_slice( $feature_ids, 0, 9 ),
			'rates' => [
				[ 'rate_label' => 'Standard Rate', 'rate_type' => 'rack', 'price' => 9500, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 0, 'includes_breakfast' => false ],
				[ 'rate_label' => 'Advance Purchase 15', 'rate_type' => 'advance', 'price' => 8500, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 15, 'includes_breakfast' => false ],
			],
		],
		[
			'pid' => $p1, 'slug' => 'executive-suite', 'title' => 'Executive Suite', 'excerpt' => 'Separate living area, pool view.', 'content' => '<p>Expansive suite with living room and premium bath.</p>',
			'rate_from' => 18000, 'rate_to' => 28000, 'occ' => 3, 'adults' => 2, 'children' => 1, 'beds' => [ $king_tid ], 'views' => [ $pool_view_tid ],
			'sqft' => 720, 'sqm' => 67, 'extra_beds' => 1, 'order' => 3, 'features' => $feature_ids,
			'rates' => [
				[ 'rate_label' => 'Standard Rate', 'rate_type' => 'rack', 'price' => 22000, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 0, 'includes_breakfast' => true ],
				[ 'rate_label' => 'Seasonal — Conference Season', 'rate_type' => 'seasonal', 'price' => 28000, 'valid_from' => $season_conf_from, 'valid_to' => $season_conf_to, 'advance_days' => 0, 'includes_breakfast' => true ],
				[ 'rate_label' => 'Advance Purchase 30', 'rate_type' => 'advance', 'price' => 18000, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 30, 'includes_breakfast' => true ],
			],
		],
	];
	$rooms_p2 = [
		[
			'pid' => $p2, 'slug' => 'garden-villa', 'title' => 'Garden Villa', 'excerpt' => 'Private garden outlook.', 'content' => '<p>Villa-style room opening to landscaped gardens.</p>',
			'rate_from' => 7200, 'rate_to' => 14000, 'occ' => 3, 'adults' => 2, 'children' => 1, 'beds' => [ $queen_tid ], 'views' => [ $garden_view_tid ],
			'sqft' => 450, 'sqm' => 42, 'extra_beds' => 1, 'order' => 1, 'features' => array_slice( $feature_ids, 0, 11 ),
			'rates' => [
				[ 'rate_label' => 'Standard Rate', 'rate_type' => 'rack', 'price' => 9500, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 0, 'includes_breakfast' => false ],
				[ 'rate_label' => 'Peak Season', 'rate_type' => 'seasonal', 'price' => 14000, 'valid_from' => $season_peak_from, 'valid_to' => $season_peak_to, 'advance_days' => 0, 'includes_breakfast' => false ],
				[ 'rate_label' => 'Advance Purchase 21', 'rate_type' => 'advance', 'price' => 7200, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 21, 'includes_breakfast' => false ],
				[ 'rate_label' => 'Breakfast Package', 'rate_type' => 'package', 'price' => 11000, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 0, 'includes_breakfast' => true ],
			],
		],
		[
			'pid' => $p2, 'slug' => 'sea-facing-deluxe', 'title' => 'Sea-Facing Deluxe', 'excerpt' => 'Direct sea views.', 'content' => '<p>Deluxe room with uninterrupted Arabian Sea views.</p>',
			'rate_from' => 9500, 'rate_to' => 18000, 'occ' => 2, 'adults' => 2, 'children' => 0, 'beds' => [ $king_tid ], 'views' => [ $sea_view_tid ],
			'sqft' => 410, 'sqm' => 38, 'extra_beds' => 0, 'order' => 2, 'features' => array_slice( $feature_ids, 0, 12 ),
			'rates' => [
				[ 'rate_label' => 'Standard Rate', 'rate_type' => 'rack', 'price' => 12500, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 0, 'includes_breakfast' => false ],
				[ 'rate_label' => 'Peak Season', 'rate_type' => 'seasonal', 'price' => 18000, 'valid_from' => $season_peak_from, 'valid_to' => $season_peak_to, 'advance_days' => 0, 'includes_breakfast' => false ],
				[ 'rate_label' => 'Romance Package', 'rate_type' => 'package', 'price' => 16000, 'valid_from' => '', 'valid_to' => '', 'advance_days' => 0, 'includes_breakfast' => true ],
			],
		],
	];

	$room_ids_p1 = [];
	$room_ids_p2 = [];
	foreach ( array_merge( $rooms_p1, $rooms_p2 ) as $rd ) {
		$rid = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_room_type',
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $rd['slug'] ?? $rd['title'] ),
				'post_title'   => $rd['title'],
				'post_excerpt' => $rd['excerpt'],
				'post_content' => wp_kses_post( $rd['content'] ),
			],
			true
		);
		if ( is_wp_error( $rid ) || ! $rid ) {
			continue;
		}
		$rid = (int) $rid;
		if ( (int) $rd['pid'] === $p1 ) {
			$room_ids_p1[] = $rid;
		} else {
			$room_ids_p2[] = $rid;
		}
		update_post_meta( $rid, '_pw_property_id', (int) $rd['pid'] );
		update_post_meta( $rid, '_pw_rate_from', (float) $rd['rate_from'] );
		update_post_meta( $rid, '_pw_rate_to', (float) $rd['rate_to'] );
		update_post_meta( $rid, '_pw_rates', pw_sanitize_pw_rates_meta( $rd['rates'] ?? [] ) );
		update_post_meta( $rid, '_pw_max_occupancy', (int) $rd['occ'] );
		update_post_meta( $rid, '_pw_max_adults', (int) $rd['adults'] );
		update_post_meta( $rid, '_pw_max_children', (int) $rd['children'] );
		update_post_meta( $rid, '_pw_size_sqft', (int) $rd['sqft'] );
		update_post_meta( $rid, '_pw_size_sqm', (int) $rd['sqm'] );
		update_post_meta( $rid, '_pw_max_extra_beds', (int) $rd['extra_beds'] );
		update_post_meta( $rid, '_pw_display_order', (int) $rd['order'] );
		update_post_meta( $rid, '_pw_features', array_map( 'intval', $rd['features'] ) );
		update_post_meta( $rid, '_pw_gallery', [] );
		$book_base = ( (int) $rd['pid'] === $p1 ) ? 'https://meridian-grand.example' : 'https://azurebay.example';
		$rslug     = sanitize_title( $rd['slug'] ?? $rd['title'] );
		update_post_meta( $rid, '_pw_booking_url', untrailingslashit( $book_base ) . '/room/' . $rslug . '#book' );
		if ( ! empty( $rd['beds'] ) ) {
			wp_set_object_terms( $rid, array_filter( array_map( 'intval', $rd['beds'] ) ), 'pw_bed_type' );
		}
		if ( ! empty( $rd['views'] ) ) {
			wp_set_object_terms( $rid, array_filter( array_map( 'intval', $rd['views'] ) ), 'pw_view_type' );
		}
	}
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 46, __( 'Room types created.', 'portico-webworks' ) );
	}

	$bf = pw_sample_ensure_term( 'Breakfast', 'pw_meal_period' );
	$lun = pw_sample_ensure_term( 'Lunch', 'pw_meal_period' );
	$din = pw_sample_ensure_term( 'Dinner', 'pw_meal_period' );
	$sun_br = pw_sample_ensure_term( 'Sunday Brunch', 'pw_meal_period' );
	$br = pw_sample_ensure_term( 'Brunch', 'pw_meal_period' );
	$all_day = pw_sample_ensure_term( 'All-day Dining', 'pw_meal_period' );

	$skyline_sessions = [
		[ 'label' => 'Breakfast', 'open_time' => '06:30', 'close_time' => '10:30' ],
		[ 'label' => 'Lunch', 'open_time' => '12:30', 'close_time' => '15:00' ],
		[ 'label' => 'Dinner', 'open_time' => '19:00', 'close_time' => '23:00' ],
	];

	$skyline_restaurant_id = (int) pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_restaurant',
			'post_status'  => 'publish',
			'post_name'    => 'skyline-kitchen-rooftop',
			'post_title'   => 'Skyline Kitchen (Rooftop)',
			'post_excerpt' => 'South Indian, Pan-Asian, and Continental on Level 12.',
			'post_content' => '<p>Rooftop dining with skyline views — ideal for business dinners and weekend brunch.</p>',
		],
		true
	);
	if ( $skyline_restaurant_id ) {
		update_post_meta( $skyline_restaurant_id, '_pw_property_id', $p1 );
		update_post_meta( $skyline_restaurant_id, '_pw_location', 'Rooftop, Level 12' );
		update_post_meta( $skyline_restaurant_id, '_pw_cuisine_type', 'South Indian, Pan-Asian, Continental' );
		update_post_meta( $skyline_restaurant_id, '_pw_seating_capacity', 120 );
		update_post_meta( $skyline_restaurant_id, '_pw_reservation_url', 'https://meridian-grand.example/reserve/skyline-kitchen' );
		update_post_meta( $skyline_restaurant_id, '_pw_menu_url', 'https://meridian-grand.example/menus/skyline-kitchen' );
		update_post_meta( $skyline_restaurant_id, '_pw_gallery', [] );
		pw_sample_set_operating_hours( $skyline_restaurant_id, $skyline_sessions );
		wp_set_object_terms( $skyline_restaurant_id, array_filter( [ $bf, $lun, $din, $sun_br, $br ] ), 'pw_meal_period' );
	}

	$spice_sessions = [
		[ 'label' => 'All Day', 'open_time' => '06:30', 'close_time' => '23:30' ],
	];
	$spice_id = (int) pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_restaurant',
			'post_status'  => 'publish',
			'post_name'    => 'merchants-hall',
			'post_title'   => 'Merchant\'s Hall (All-Day Dining)',
			'post_excerpt' => 'Indian and international all day.',
			'post_content' => '<p>Lobby-level all-day dining for breakfast meetings, power lunches, and family dinners.</p>',
		],
		true
	);
	if ( $spice_id ) {
		update_post_meta( $spice_id, '_pw_property_id', $p1 );
		update_post_meta( $spice_id, '_pw_location', 'Ground Floor, Lobby Level' );
		update_post_meta( $spice_id, '_pw_cuisine_type', 'Indian, International' );
		update_post_meta( $spice_id, '_pw_seating_capacity', 200 );
		update_post_meta( $spice_id, '_pw_reservation_url', 'https://meridian-grand.example/reserve/merchants-hall' );
		update_post_meta( $spice_id, '_pw_menu_url', 'https://meridian-grand.example/menus/merchants-hall' );
		update_post_meta( $spice_id, '_pw_gallery', [] );
		pw_sample_set_operating_hours( $spice_id, $spice_sessions );
		wp_set_object_terms( $spice_id, array_filter( [ $bf, $lun, $din, $all_day ] ), 'pw_meal_period' );
	}

	$late_night_tid = pw_sample_ensure_term( 'Late Night', 'pw_meal_period' );
	$tides_sessions = [
		[ 'label' => 'Breakfast', 'open_time' => '07:30', 'close_time' => '10:30' ],
		[ 'label' => 'Lunch', 'open_time' => '12:00', 'close_time' => '15:30' ],
		[ 'label' => 'Dinner', 'open_time' => '19:00', 'close_time' => '23:00' ],
		[ 'label' => 'Late Night / Bar', 'open_time' => '17:00', 'close_time' => '00:30' ],
	];
	$tides_id = (int) pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_restaurant',
			'post_status'  => 'publish',
			'post_name'    => 'azure-shore-grill',
			'post_title'   => 'Azure Shore Grill',
			'post_excerpt' => 'Goan seafood by the pool and beach.',
			'post_content' => '<p>Poolside and beachfront dining for families and sunset cocktails.</p>',
		],
		true
	);
	if ( $tides_id ) {
		update_post_meta( $tides_id, '_pw_property_id', $p2 );
		update_post_meta( $tides_id, '_pw_location', 'Poolside / Beachfront' );
		update_post_meta( $tides_id, '_pw_cuisine_type', 'Goan, Seafood, Continental' );
		update_post_meta( $tides_id, '_pw_seating_capacity', 90 );
		update_post_meta( $tides_id, '_pw_reservation_url', 'https://azurebay.example/reserve/azure-shore-grill' );
		update_post_meta( $tides_id, '_pw_menu_url', 'https://azurebay.example/menus/azure-shore-grill' );
		update_post_meta( $tides_id, '_pw_gallery', [] );
		pw_sample_set_operating_hours( $tides_id, $tides_sessions );
		wp_set_object_terms( $tides_id, array_filter( [ $bf, $lun, $din, $late_night_tid ] ), 'pw_meal_period' );
	}

	$ayur = pw_sample_ensure_term( 'Ayurveda', 'pw_treatment_type' );
	$aroma = pw_sample_ensure_term( 'Aromatherapy', 'pw_treatment_type' );
	$deep = pw_sample_ensure_term( 'Deep Tissue', 'pw_treatment_type' );
	$hot = pw_sample_ensure_term( 'Hot Stone', 'pw_treatment_type' );
	$couple = pw_sample_ensure_term( 'Couples Treatment', 'pw_treatment_type' );
	$fac = pw_sample_ensure_term( 'Facial', 'pw_treatment_type' );
	$swed = pw_sample_ensure_term( 'Swedish Massage', 'pw_treatment_type' );
	$abh = pw_sample_ensure_term( 'Ayurvedic Abhyanga', 'pw_treatment_type' );
	$foot = pw_sample_ensure_term( 'Foot Reflexology', 'pw_treatment_type' );

	$spa1 = (int) pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_spa',
			'post_status'  => 'publish',
			'post_name'    => 'stillwater-spa-bengaluru',
			'post_title'   => 'Stillwater Spa',
			'post_excerpt' => 'Full-service spa in Bengaluru.',
			'post_content' => '<p>Eight treatment rooms and wellness rituals for post-meeting recovery.</p>',
		],
		true
	);
	if ( $spa1 ) {
		update_post_meta( $spa1, '_pw_property_id', $p1 );
		update_post_meta( $spa1, '_pw_booking_url', 'https://meridian-grand.example/spa/book' );
		update_post_meta( $spa1, '_pw_menu_url', 'https://meridian-grand.example/spa/treatments' );
		update_post_meta( $spa1, '_pw_min_age', 16 );
		update_post_meta( $spa1, '_pw_number_of_treatment_rooms', 8 );
		update_post_meta( $spa1, '_pw_gallery', [] );
		pw_sample_set_operating_hours( $spa1, pw_sample_spa_treatment_hours( '09:00', '21:00' ) );
		wp_set_object_terms( $spa1, array_filter( [ $ayur, $aroma, $deep, $hot, $couple, $fac ] ), 'pw_treatment_type' );
	}

	$spa2 = (int) pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_spa',
			'post_status'  => 'publish',
			'post_name'    => 'tidepool-garden-spa',
			'post_title'   => 'Tidepool Garden Spa',
			'post_excerpt' => 'Boutique spa by the sea.',
			'post_content' => '<p>Three treatment rooms in a garden hut setting between pool and palms.</p>',
		],
		true
	);
	if ( $spa2 ) {
		update_post_meta( $spa2, '_pw_property_id', $p2 );
		update_post_meta( $spa2, '_pw_booking_url', 'https://azurebay.example/spa/book' );
		update_post_meta( $spa2, '_pw_menu_url', 'https://azurebay.example/spa/menu' );
		update_post_meta( $spa2, '_pw_min_age', 18 );
		update_post_meta( $spa2, '_pw_number_of_treatment_rooms', 3 );
		update_post_meta( $spa2, '_pw_gallery', [] );
		pw_sample_set_operating_hours( $spa2, pw_sample_spa_treatment_hours( '09:00', '20:00' ) );
		wp_set_object_terms( $spa2, array_filter( [ $swed, $abh, $foot ] ), 'pw_treatment_type' );
	}

	$led = pw_sample_ensure_term( 'LED Screen', 'pw_av_equipment' );
	$mic = pw_sample_ensure_term( 'Microphone', 'pw_av_equipment' );
	$pod = pw_sample_ensure_term( 'Podium', 'pw_av_equipment' );
	$sound = pw_sample_ensure_term( 'In-built Sound System', 'pw_av_equipment' );
	$stage = pw_sample_ensure_term( 'Stage Lighting', 'pw_av_equipment' );
	$disp = pw_sample_ensure_term( '75" Display Screen', 'pw_av_equipment' );
	$wp = pw_sample_ensure_term( 'Wireless Presentation', 'pw_av_equipment' );
	$vc = pw_sample_ensure_term( 'Video Conferencing System', 'pw_av_equipment' );
	$wb = pw_sample_ensure_term( 'Whiteboard', 'pw_av_equipment' );

	$deccan = (int) pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_meeting_room',
			'post_status'  => 'publish',
			'post_name'    => 'meridian-grand-ballroom',
			'post_title'   => 'Meridian Grand Ballroom',
			'post_excerpt' => 'Grand ballroom for conferences and galas.',
			'post_content' => '<p>Pillar-free ballroom with pre-function space.</p>',
		],
		true
	);
	if ( $deccan ) {
		update_post_meta( $deccan, '_pw_property_id', $p1 );
		update_post_meta( $deccan, '_pw_capacity_theatre', 500 );
		update_post_meta( $deccan, '_pw_capacity_classroom', 300 );
		update_post_meta( $deccan, '_pw_capacity_boardroom', 24 );
		update_post_meta( $deccan, '_pw_capacity_ushape', 32 );
		update_post_meta( $deccan, '_pw_area_sqft', 6000 );
		update_post_meta( $deccan, '_pw_area_sqm', 557 );
		update_post_meta( $deccan, '_pw_prefunction_area_sqft', 1800 );
		update_post_meta( $deccan, '_pw_prefunction_area_sqm', 167 );
		update_post_meta( $deccan, '_pw_natural_light', false );
		update_post_meta( $deccan, '_pw_floor_plan', 0 );
		update_post_meta( $deccan, '_pw_gallery', [] );
		wp_set_object_terms( $deccan, array_filter( [ $led, $mic, $pod, $sound, $stage ] ), 'pw_av_equipment' );
	}

	$cubbon = (int) pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_meeting_room',
			'post_status'  => 'publish',
			'post_name'    => 'horizon-boardroom',
			'post_title'   => 'Horizon Boardroom',
			'post_excerpt' => 'Executive boardroom with natural light.',
			'post_content' => '<p>Intimate boardroom for leadership meetings.</p>',
		],
		true
	);
	if ( $cubbon ) {
		update_post_meta( $cubbon, '_pw_property_id', $p1 );
		update_post_meta( $cubbon, '_pw_capacity_theatre', 24 );
		update_post_meta( $cubbon, '_pw_capacity_classroom', 18 );
		update_post_meta( $cubbon, '_pw_capacity_boardroom', 18 );
		update_post_meta( $cubbon, '_pw_capacity_ushape', 14 );
		update_post_meta( $cubbon, '_pw_area_sqft', 650 );
		update_post_meta( $cubbon, '_pw_area_sqm', 60 );
		update_post_meta( $cubbon, '_pw_prefunction_area_sqft', 0 );
		update_post_meta( $cubbon, '_pw_prefunction_area_sqm', 0 );
		update_post_meta( $cubbon, '_pw_natural_light', true );
		update_post_meta( $cubbon, '_pw_floor_plan', 0 );
		update_post_meta( $cubbon, '_pw_gallery', [] );
		wp_set_object_terms( $cubbon, array_filter( [ $disp, $wp, $vc, $wb ] ), 'pw_av_equipment' );
	}

	if ( $skyline_restaurant_id ) {
		pw_sample_install_pw_contact_rows(
			$p1,
			[
				[
					'post_title' => 'Skyline Kitchen — desk',
					'label'      => 'Restaurant',
					'phone'      => '+91-80-4123-4510',
					'mobile'     => '',
					'whatsapp'   => '',
					'email'      => 'skyline@meridian-grand.example',
					'scope_cpt'  => 'restaurant',
					'scope_id'   => $skyline_restaurant_id,
				],
			]
		);
	}
	if ( $spice_id ) {
		pw_sample_install_pw_contact_rows(
			$p1,
			[
				[
					'post_title' => 'Merchant\'s Hall — desk',
					'label'      => 'Restaurant',
					'phone'      => '+91-80-4123-4511',
					'mobile'     => '',
					'whatsapp'   => '',
					'email'      => 'merchants@meridian-grand.example',
					'scope_cpt'  => 'restaurant',
					'scope_id'   => $spice_id,
				],
			]
		);
	}
	if ( $spa1 ) {
		pw_sample_install_pw_contact_rows(
			$p1,
			[
				[
					'post_title' => 'Stillwater Spa — bookings',
					'label'      => 'Spa',
					'phone'      => '+91-80-4123-4525',
					'mobile'     => '',
					'whatsapp'   => '',
					'email'      => 'spa@meridian-grand.example',
					'scope_cpt'  => 'spa',
					'scope_id'   => $spa1,
				],
			]
		);
	}
	if ( $deccan ) {
		pw_sample_install_pw_contact_rows(
			$p1,
			[
				[
					'post_title' => 'Events — Meridian Grand Ballroom',
					'label'      => 'Banquets',
					'phone'      => '+91-80-4123-4522',
					'mobile'     => '',
					'whatsapp'   => '',
					'email'      => 'events@meridian-grand.example',
					'scope_cpt'  => 'meeting_room',
					'scope_id'   => $deccan,
				],
			]
		);
	}
	if ( $cubbon ) {
		pw_sample_install_pw_contact_rows(
			$p1,
			[
				[
					'post_title' => 'Horizon Boardroom — coordinator',
					'label'      => 'Meetings',
					'phone'      => '+91-80-4123-4523',
					'mobile'     => '',
					'whatsapp'   => '',
					'email'      => 'boardroom@meridian-grand.example',
					'scope_cpt'  => 'meeting_room',
					'scope_id'   => $cubbon,
				],
			]
		);
	}
	if ( $tides_id ) {
		pw_sample_install_pw_contact_rows(
			$p2,
			[
				[
					'post_title' => 'Azure Shore Grill — desk',
					'label'      => 'Restaurant',
					'phone'      => '+91-832-2276-411',
					'mobile'     => '',
					'whatsapp'   => '',
					'email'      => 'shore@azurebay.example',
					'scope_cpt'  => 'restaurant',
					'scope_id'   => $tides_id,
				],
			]
		);
	}
	if ( $spa2 ) {
		pw_sample_install_pw_contact_rows(
			$p2,
			[
				[
					'post_title' => 'Tidepool Garden Spa — bookings',
					'label'      => 'Spa',
					'phone'      => '+91-832-2276-422',
					'mobile'     => '',
					'whatsapp'   => '',
					'email'      => 'spa@azurebay.example',
					'scope_cpt'  => 'spa',
					'scope_id'   => $spa2,
				],
			]
		);
	}
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 54, __( 'Outlets and contacts…', 'portico-webworks' ) );
	}

	pw_sample_multi_install_amenities_policies_faqs_offers_nearby_exp_events(
		$p1,
		$p2,
		$room_ids_p1,
		$room_ids_p2,
		$spa1,
		$spa2,
		$skyline_restaurant_id,
		$spice_id,
		$tides_id,
		$deccan,
		$cubbon,
		$organiser_id,
		$season_conf_from,
		$season_conf_to,
		$festive_from,
		$festive_to,
		$offer_adv_from,
		$offer_adv_to
	);
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 88, __( 'Sideloading demo images…', 'portico-webworks' ) );
	}

	pw_sample_multi_install_apply_demo_media( $p1, $p2 );
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 94, __( 'Demo media attached.', 'portico-webworks' ) );
	}
}

function pw_sample_multi_install_amenities_policies_faqs_offers_nearby_exp_events(
	$p1,
	$p2,
	array $room_ids_p1,
	array $room_ids_p2,
	$spa1,
	$spa2,
	$skyline_restaurant_id,
	$spice_id,
	$tides_id,
	$deccan,
	$cubbon,
	$organiser_id,
	$season_conf_from,
	$season_conf_to,
	$festive_from,
	$festive_to,
	$offer_adv_from,
	$offer_adv_to
) {
	$p1 = (int) $p1;
	$p2 = (int) $p2;
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 58, __( 'Amenities, policies, offers, and nearby places…', 'portico-webworks' ) );
	}
	$ev_base          = strtotime( gmdate( 'Y-m-d' ) );
	$ev_fmt           = static function ( $ts ) {
		return gmdate( 'Y-m-d H:i:s', $ts );
	};
	$ev_gala_start    = $ev_fmt( strtotime( '+120 days 19:30:00', $ev_base ) );
	$ev_gala_end      = $ev_fmt( strtotime( '+120 days 23:30:00', $ev_base ) );
	$ev_conf_start    = $ev_fmt( strtotime( '+90 days 09:00:00', $ev_base ) );
	$ev_conf_end      = $ev_fmt( strtotime( '+91 days 18:00:00', $ev_base ) );
	$ev_beach_start   = $ev_fmt( strtotime( '+65 days 19:00:00', $ev_base ) );
	$ev_beach_end     = $ev_fmt( strtotime( '+65 days 23:59:00', $ev_base ) );
	$nye_year         = (int) gmdate( 'Y' );
	$nye_blr_ts       = strtotime( $nye_year . '-12-31 20:00:00' );
	if ( $nye_blr_ts < time() ) {
		++$nye_year;
		$nye_blr_ts = strtotime( $nye_year . '-12-31 20:00:00' );
	}
	$ev_nye_blr_start = $ev_fmt( $nye_blr_ts );
	$ev_nye_blr_end   = $ev_fmt( strtotime( '+5 hours 30 minutes', $nye_blr_ts ) );
	$ev_nye_goa_ts    = strtotime( $nye_year . '-12-31 19:00:00' );
	if ( $ev_nye_goa_ts < time() ) {
		$ev_nye_goa_ts = strtotime( ( $nye_year + 1 ) . '-12-31 19:00:00' );
	}
	$ev_nye_goa_start = $ev_fmt( $ev_nye_goa_ts );
	$ev_nye_goa_end   = $ev_fmt( strtotime( '+7 hours', $ev_nye_goa_ts ) );
	$xmas_y           = (int) gmdate( 'Y' );
	$xmas_ts          = strtotime( $xmas_y . '-12-25 11:00:00' );
	if ( $xmas_ts < time() ) {
		++$xmas_y;
		$xmas_ts = strtotime( $xmas_y . '-12-25 11:00:00' );
	}
	$ev_xmas_start    = $ev_fmt( $xmas_ts );
	$ev_xmas_end      = $ev_fmt( strtotime( '+4 hours', $xmas_ts ) );
	$amen_p1 = [
		[ 'title' => '24-Hour Front Desk', 'type' => 'service', 'cat' => 'Guest Services', 'compl' => true, 'order' => 1, 'icon' => 'desk', 'desc' => 'Dedicated team for arrivals, departures, and MICE group coordination.' ],
		[ 'title' => 'Concierge Service', 'type' => 'service', 'cat' => 'Guest Services', 'compl' => true, 'order' => 2, 'icon' => 'concierge', 'desc' => 'Restaurant reservations, theatre tickets, and city routing for executives.' ],
		[ 'title' => 'Airport Transfer', 'type' => 'service', 'cat' => 'Transportation', 'compl' => false, 'order' => 3, 'icon' => 'car', 'desc' => 'Sedan and SUV transfers to BLR with meet-and-greet on request.' ],
		[ 'title' => 'Valet Parking', 'type' => 'service', 'cat' => 'Transportation', 'compl' => false, 'order' => 4, 'icon' => 'parking', 'desc' => 'Covered drop-off and secure parking for delegates and overnight guests.' ],
		[ 'title' => 'Fitness Centre', 'type' => 'facility', 'cat' => 'Recreation', 'compl' => true, 'order' => 5, 'icon' => 'gym', 'desc' => 'Cardio, free weights, and stretching zone — open early for pre-meeting workouts.' ],
		[ 'title' => 'Business Centre', 'type' => 'facility', 'cat' => 'Business', 'compl' => true, 'order' => 6, 'icon' => 'business', 'desc' => 'Printing, scanning, and quiet workstations for last-minute deck edits.' ],
		[ 'title' => 'Meeting-Grade Wi-Fi', 'type' => 'amenity', 'cat' => 'Connectivity', 'compl' => true, 'order' => 7, 'icon' => 'wifi', 'desc' => 'Symmetric fibre-backed Wi-Fi across lobby, ballrooms, and guest floors.' ],
		[ 'title' => 'Laundry & Dry Cleaning', 'type' => 'service', 'cat' => 'Housekeeping', 'compl' => false, 'order' => 8, 'icon' => 'laundry', 'desc' => 'Same-day service for shirts and suits when dropped before noon.' ],
		[ 'title' => 'Doctor on Call', 'type' => 'service', 'cat' => 'Medical', 'compl' => false, 'order' => 9, 'icon' => 'medical', 'desc' => 'Registered physician visits arranged via front desk.' ],
		[ 'title' => 'EV Charging Station', 'type' => 'facility', 'cat' => 'Transportation', 'compl' => false, 'order' => 10, 'icon' => 'ev', 'desc' => 'Fast chargers in the basement car park for guest vehicles.' ],
	];
	foreach ( $amen_p1 as $ad ) {
		$aid = pw_sample_wp_insert_post( [ 'post_type' => 'pw_amenity', 'post_status' => 'publish', 'post_title' => $ad['title'] ], true );
		if ( is_wp_error( $aid ) || ! $aid ) {
			continue;
		}
		$aid = (int) $aid;
		update_post_meta( $aid, '_pw_property_id', $p1 );
		update_post_meta( $aid, '_pw_type', $ad['type'] );
		update_post_meta( $aid, '_pw_category', $ad['cat'] );
		update_post_meta( $aid, '_pw_icon', $ad['icon'] );
		update_post_meta( $aid, '_pw_content', $ad['desc'] );
		update_post_meta( $aid, '_pw_is_complimentary', (bool) $ad['compl'] );
		update_post_meta( $aid, '_pw_display_order', (int) $ad['order'] );
	}
	$amen_p2 = [
		[ 'title' => 'Beach Concierge Desk', 'type' => 'service', 'cat' => 'Guest Services', 'compl' => true, 'order' => 1, 'icon' => 'desk', 'desc' => 'Towels, shade, and family-friendly setup on our beach access path.' ],
		[ 'title' => 'Resort Wi-Fi', 'type' => 'amenity', 'cat' => 'Connectivity', 'compl' => true, 'order' => 2, 'icon' => 'wifi', 'desc' => 'Pool-to-villa coverage for streaming and video calls back home.' ],
		[ 'title' => 'Beach Access', 'type' => 'facility', 'cat' => 'Recreation', 'compl' => true, 'order' => 3, 'icon' => 'beach', 'desc' => 'Short walk to Calangute sand with resort attendants during daylight hours.' ],
		[ 'title' => 'Water Sports Desk', 'type' => 'service', 'cat' => 'Recreation', 'compl' => false, 'order' => 4, 'icon' => 'waves', 'desc' => 'Kayaks, paddleboards, and coastal excursions booked on-site.' ],
		[ 'title' => 'Bicycle Rental', 'type' => 'service', 'cat' => 'Transportation', 'compl' => false, 'order' => 5, 'icon' => 'bike', 'desc' => 'Cruisers for village lanes and sunset rides toward Baga.' ],
		[ 'title' => 'Room Service', 'type' => 'service', 'cat' => 'Dining', 'compl' => false, 'order' => 6, 'icon' => 'room-service', 'desc' => 'Coastal and continental favourites delivered to your villa or balcony.' ],
		[ 'title' => 'Laundry Service', 'type' => 'service', 'cat' => 'Housekeeping', 'compl' => false, 'order' => 7, 'icon' => 'laundry', 'desc' => 'Wash-and-fold for sandy swimwear and holiday wardrobes.' ],
		[ 'title' => 'Airport Transfer', 'type' => 'service', 'cat' => 'Transportation', 'compl' => false, 'order' => 8, 'icon' => 'car', 'desc' => 'GOI/MOPA transfers in AC minivans — book via reservations.' ],
	];
	foreach ( $amen_p2 as $ad ) {
		$aid = pw_sample_wp_insert_post( [ 'post_type' => 'pw_amenity', 'post_status' => 'publish', 'post_title' => $ad['title'] ], true );
		if ( is_wp_error( $aid ) || ! $aid ) {
			continue;
		}
		$aid = (int) $aid;
		update_post_meta( $aid, '_pw_property_id', $p2 );
		update_post_meta( $aid, '_pw_type', $ad['type'] );
		update_post_meta( $aid, '_pw_category', $ad['cat'] );
		update_post_meta( $aid, '_pw_icon', $ad['icon'] );
		update_post_meta( $aid, '_pw_content', $ad['desc'] );
		update_post_meta( $aid, '_pw_is_complimentary', (bool) $ad['compl'] );
		update_post_meta( $aid, '_pw_display_order', (int) $ad['order'] );
	}

	$policy_rows = [
		[ 'pid' => $p1, 'title' => 'Check-In Policy', 'type' => 'Check-in', 'content' => 'Check-in from 14:00. Early check-in from 10:00 subject to availability and may attract a half-day charge. Valid government-issued photo ID mandatory at check-in.', 'highlight' => true, 'order' => 1 ],
		[ 'pid' => $p1, 'title' => 'Check-Out Policy', 'type' => 'Check-out', 'content' => 'Check-out by 12:00 noon. Late check-out until 18:00 available on request, charged at 50% of the daily room rate.', 'highlight' => true, 'order' => 2 ],
		[ 'pid' => $p1, 'title' => 'Cancellation Policy', 'type' => 'Cancellation', 'content' => 'Reservations cancelled more than 48 hours before arrival receive a full refund. Cancellations within 48 hours will be charged one night\'s room rate. No-shows are charged the full booking amount.', 'highlight' => true, 'order' => 3 ],
		[ 'pid' => $p1, 'title' => 'Pet Policy', 'type' => 'Pet', 'content' => 'Pets are not permitted on the property. Registered guide dogs are welcome with prior notice.', 'highlight' => false, 'order' => 4 ],
		[ 'pid' => $p1, 'title' => 'Child Policy', 'type' => 'Child', 'content' => 'Children of all ages are welcome. Children under 6 stay free in existing bedding. Extra bed for children (6–12 years) available at ₹1,500 per night.', 'highlight' => false, 'order' => 5 ],
		[ 'pid' => $p1, 'title' => 'Smoking Policy', 'type' => 'Smoking', 'content' => 'This is a 100% non-smoking property. Smoking is permitted in designated outdoor areas only. A deep-cleaning fee of ₹5,000 will be levied for smoking in rooms.', 'highlight' => false, 'order' => 6 ],
		[ 'pid' => $p1, 'title' => 'Payment Policy', 'type' => 'Payment', 'content' => 'We accept all major credit and debit cards (Visa, Mastercard, Amex, RuPay), UPI, and net banking. Cash payments accepted in INR only. Full payment at check-in for walk-in guests.', 'highlight' => false, 'order' => 7 ],
		[ 'pid' => $p2, 'title' => 'Check-In Policy', 'type' => 'Check-in', 'content' => 'Check-in from 13:00. Early check-in available from 09:00 at ₹1,000 flat charge, subject to room availability. Valid photo ID required.', 'highlight' => true, 'order' => 1 ],
		[ 'pid' => $p2, 'title' => 'Check-Out Policy', 'type' => 'Check-out', 'content' => 'Check-out by 11:00. Late check-out until 15:00 at ₹1,500, until 18:00 at 50% of daily rate.', 'highlight' => true, 'order' => 2 ],
		[ 'pid' => $p2, 'title' => 'Cancellation Policy', 'type' => 'Cancellation', 'content' => 'Peak season (Dec 20–Jan 5): non-refundable. All other dates: free cancellation up to 72 hours before arrival. Within 72 hours: one night charged.', 'highlight' => true, 'order' => 3 ],
		[ 'pid' => $p2, 'title' => 'Pet Policy', 'type' => 'Pet', 'content' => 'Small pets (under 10kg) permitted in garden villas with prior approval. Pet fee of ₹500 per night applies. Pets not allowed in pool or restaurant areas.', 'highlight' => false, 'order' => 4 ],
		[ 'pid' => $p2, 'title' => 'Smoking Policy', 'type' => 'Smoking', 'content' => 'Non-smoking rooms throughout. Designated smoking zone near beach garden. ₹3,000 deep-cleaning fee if smoked in-room.', 'highlight' => false, 'order' => 5 ],
		[ 'pid' => $p2, 'title' => 'Payment Policy', 'type' => 'Payment', 'content' => 'Visa, Mastercard, RuPay, UPI, and net banking accepted. 50% advance payment required at booking for peak season stays.', 'highlight' => false, 'order' => 6 ],
		[ 'pid' => $p2, 'title' => 'Child Policy', 'type' => 'Child', 'content' => 'Children of all ages welcome. Kids under 6 stay free in existing bedding in garden villas. Extra mattress for older children at ₹800 per night. Babysitting on request (24h notice).', 'highlight' => false, 'order' => 7 ],
	];
	foreach ( $policy_rows as $pd ) {
		$pid = pw_sample_wp_insert_post( [ 'post_type' => 'pw_policy', 'post_status' => 'publish', 'post_title' => $pd['title'], 'post_content' => '' ], true );
		if ( is_wp_error( $pid ) || ! $pid ) {
			continue;
		}
		$pid = (int) $pid;
		update_post_meta( $pid, '_pw_property_id', (int) $pd['pid'] );
		update_post_meta( $pid, '_pw_content', $pd['content'] );
		update_post_meta( $pid, '_pw_display_order', (int) $pd['order'] );
		update_post_meta( $pid, '_pw_is_highlighted', (bool) $pd['highlight'] );
		update_post_meta( $pid, '_pw_active', true );
		$type_tid = pw_sample_ensure_term( $pd['type'], 'pw_policy_type' );
		if ( $type_tid ) {
			wp_set_object_terms( $pid, [ $type_tid ], 'pw_policy_type' );
		}
	}

	$faq_p1 = [
		[ 'q' => 'Is parking available at the hotel?', 'a' => '<p>Yes, we offer complimentary valet parking for in-house guests. Self-parking is available in the basement at ₹200 per hour for visitors. EV charging stations are available at ₹15/unit.</p>' ],
		[ 'q' => 'Do you have an airport transfer service?', 'a' => '<p>Yes. Airport transfers to/from Kempegowda International Airport are available at ₹1,800 one way for a sedan and ₹2,400 for an SUV. Please book at least 4 hours in advance via reservations.</p>' ],
		[ 'q' => 'Is the swimming pool heated?', 'a' => '<p>Our main pool is not heated. The water temperature is naturally comfortable year-round given Bengaluru\'s climate. The pool is open from 06:00 to 22:00 daily.</p>' ],
		[ 'q' => 'What is the dress code at Skyline Kitchen?', 'a' => '<p>Smart casual for lunch; smart casual to business casual for dinner. We request guests to avoid beachwear, flip-flops, and sleeveless vests in the restaurant.</p>' ],
		[ 'q' => 'Can I store luggage after check-out?', 'a' => '<p>Yes, complimentary luggage storage is available at the front desk for up to 12 hours after check-out.</p>' ],
	];
	$o = 1;
	foreach ( $faq_p1 as $fd ) {
		$fqid = pw_sample_wp_insert_post( [ 'post_type' => 'pw_faq', 'post_status' => 'publish', 'post_title' => $fd['q'] ], true );
		if ( is_wp_error( $fqid ) || ! $fqid ) {
			continue;
		}
		$fqid = (int) $fqid;
		update_post_meta( $fqid, '_pw_property_id', $p1 );
		update_post_meta( $fqid, '_pw_answer', $fd['a'] );
		update_post_meta( $fqid, '_pw_display_order', $o );
		$conn = [ [ 'type' => 'pw_property', 'id' => $p1 ] ];
		if ( $skyline_restaurant_id && str_contains( strtolower( $fd['q'] ), 'skyline' ) ) {
			$conn[] = [ 'type' => 'pw_restaurant', 'id' => $skyline_restaurant_id ];
		}
		update_post_meta( $fqid, '_pw_connected_to', $conn );
		++$o;
	}
	$faq_p2 = [
		[ 'q' => 'How far is the property from Goa Airport?', 'a' => '<p>Azure Bay Beach Resort is approximately 42 km from Goa International Airport (GOI), roughly a 60-minute drive. We offer paid airport transfers — please contact us to pre-arrange.</p>' ],
		[ 'q' => 'Is the beach private?', 'a' => '<p>We have direct beach access via a dedicated resort pathway. While the beach itself is a public beach (as all Goa beaches are), our section is reserved for guests during resort hours and is managed by our beach attendants.</p>' ],
		[ 'q' => 'What water sports are available?', 'a' => '<p>We operate a water sports desk offering kayaking, paddleboarding, parasailing, jet skiing, and banana boat rides. Prices vary by activity. One complimentary 30-minute kayaking or paddleboarding session is included for direct bookings.</p>' ],
		[ 'q' => 'Is it safe to bring children?', 'a' => '<p>Absolutely. We have a shallow splash pool for children, a kids\' play area, and our beach section is patrolled during resort hours. We provide child menus at Azure Shore Grill.</p>' ],
		[ 'q' => 'What is the peak season surcharge?', 'a' => '<p>Peak season (December 20 to January 5) rates are approximately 40–60% higher than standard rates. These bookings are non-refundable. We strongly recommend booking directly for the best available rate.</p>' ],
	];
	$o = 1;
	foreach ( $faq_p2 as $fd ) {
		$fqid = pw_sample_wp_insert_post( [ 'post_type' => 'pw_faq', 'post_status' => 'publish', 'post_title' => $fd['q'] ], true );
		if ( is_wp_error( $fqid ) || ! $fqid ) {
			continue;
		}
		$fqid = (int) $fqid;
		update_post_meta( $fqid, '_pw_property_id', $p2 );
		update_post_meta( $fqid, '_pw_answer', $fd['a'] );
		update_post_meta( $fqid, '_pw_display_order', $o );
		update_post_meta( $fqid, '_pw_connected_to', [ [ 'type' => 'pw_property', 'id' => $p2 ] ] );
		++$o;
	}

	$offer_insert = static function ( $args ) {
		$post_name = isset( $args['slug'] ) ? sanitize_title( (string) $args['slug'] ) : sanitize_title( (string) $args['title'] );
		$id        = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_offer',
				'post_status'  => 'publish',
				'post_name'    => $post_name,
				'post_title'   => $args['title'],
				'post_excerpt' => $args['excerpt'] ?? '',
				'post_content' => $args['content'] ?? '',
			],
			true
		);
		if ( is_wp_error( $id ) || ! $id ) {
			return;
		}
		$id = (int) $id;
		$prop = (int) $args['property_id'];
		update_post_meta( $id, '_pw_offer_type', $args['offer_type'] );
		update_post_meta( $id, '_pw_property_id', $prop );
		update_post_meta( $id, '_pw_parents', $args['parents'] ?? [ [ 'type' => 'pw_property', 'id' => $prop ] ] );
		update_post_meta( $id, '_pw_valid_from', $args['valid_from'] ?? '' );
		update_post_meta( $id, '_pw_valid_to', $args['valid_to'] ?? '' );
		update_post_meta( $id, '_pw_booking_url', $args['booking_url'] ?? '' );
		update_post_meta( $id, '_pw_is_featured', ! empty( $args['featured'] ) );
		update_post_meta( $id, '_pw_discount_type', $args['discount_type'] ?? 'value_add' );
		update_post_meta( $id, '_pw_discount_value', isset( $args['discount_value'] ) ? (float) $args['discount_value'] : 0 );
		update_post_meta( $id, '_pw_minimum_stay_nights', (int) ( $args['min_stay'] ?? 0 ) );
		update_post_meta( $id, '_pw_display_order', (int) ( $args['order'] ?? 0 ) );
		update_post_meta( $id, '_pw_room_types', array_map( 'intval', $args['room_types'] ?? [] ) );
	};

	$offer_insert(
		[
			'slug' => 'advance-purchase-save-15',
			'title' => 'Advance Purchase — Save 15%', 'excerpt' => 'Book early and save on best available rates.', 'content' => '',
			'property_id' => $p1, 'offer_type' => 'promotion', 'discount_type' => 'percentage', 'discount_value' => 15,
			'featured' => false, 'min_stay' => 1, 'valid_from' => $offer_adv_from, 'valid_to' => $offer_adv_to, 'order' => 1,
			'booking_url' => 'https://meridian-grand.example/offers/advance-purchase', 'room_types' => $room_ids_p1,
		]
	);
	$offer_insert(
		[
			'slug' => 'bengaluru-business-package',
			'title' => 'Bengaluru Business Package', 'excerpt' => 'Breakfast, transfer, minibar, spa discount.',
			'content' => '<p>Includes breakfast for two, one-way airport transfer, complimentary minibar refresh, and 15% off spa services.</p>',
			'property_id' => $p1, 'offer_type' => 'package', 'discount_type' => 'value_add', 'discount_value' => 0,
			'featured' => true, 'min_stay' => 2, 'valid_from' => '', 'valid_to' => '', 'order' => 2,
			'booking_url' => 'https://meridian-grand.example/packages/business', 'room_types' => $room_ids_p1,
		]
	);
	$offer_insert(
		[
			'slug' => 'extended-stay-4-nights-1-free',
			'title' => 'Extended Stay — 4 Nights + 1 Free', 'excerpt' => 'Fifth night on us for longer Bengaluru stays.', 'content' => '<p>Fifth night complimentary on qualifying stays.</p>',
			'property_id' => $p1, 'offer_type' => 'promotion', 'discount_type' => 'value_add', 'discount_value' => 0,
			'featured' => false, 'min_stay' => 4, 'valid_from' => $offer_adv_from, 'valid_to' => $offer_adv_to, 'order' => 3,
			'booking_url' => 'https://meridian-grand.example/offers/extended-stay', 'room_types' => $room_ids_p1,
		]
	);
	$offer_insert(
		[
			'slug' => 'conference-season-special',
			'title' => 'Conference Season Special', 'excerpt' => 'September–November',
			'content' => '<p>10% off best available rate during conference season.</p>',
			'property_id' => $p1, 'offer_type' => 'promotion', 'discount_type' => 'percentage', 'discount_value' => 10,
			'featured' => true, 'min_stay' => 2, 'valid_from' => $season_conf_from, 'valid_to' => $season_conf_to, 'order' => 4,
			'booking_url' => 'https://meridian-grand.example/offers/conference-season', 'room_types' => $room_ids_p1,
		]
	);

	$offer_insert(
		[
			'slug' => 'early-bird-goa-escape-save-20',
			'title' => 'Early Bird Goa Escape — Save 20%', 'excerpt' => 'Lock in coastal savings when you plan ahead.', 'content' => '',
			'property_id' => $p2, 'offer_type' => 'promotion', 'discount_type' => 'percentage', 'discount_value' => 20,
			'featured' => true, 'min_stay' => 2, 'valid_from' => $offer_adv_from, 'valid_to' => $offer_adv_to, 'order' => 1,
			'booking_url' => 'https://azurebay.example/offers/early-bird', 'room_types' => $room_ids_p2,
		]
	);
	$parents_honeymoon = [ [ 'type' => 'pw_property', 'id' => $p2 ] ];
	if ( $spa2 ) {
		$parents_honeymoon[] = [ 'type' => 'pw_spa', 'id' => $spa2 ];
	}
	if ( $tides_id ) {
		$parents_honeymoon[] = [ 'type' => 'pw_restaurant', 'id' => $tides_id ];
	}
	$offer_insert(
		[
			'slug' => 'honeymoon-by-the-sea-package',
			'title' => 'Honeymoon by the Sea Package', 'excerpt' => 'Sea-facing room, dinner, massage, late checkout.',
			'content' => '<p>Includes sea-facing room, flower-decorated room on arrival, candlelight dinner for two at Azure Shore Grill, couple\'s massage at Tidepool Garden Spa, and late check-out.</p>',
			'property_id' => $p2, 'offer_type' => 'package', 'discount_type' => 'value_add', 'discount_value' => 0,
			'featured' => true, 'min_stay' => 3, 'valid_from' => $offer_adv_from, 'valid_to' => $offer_adv_to, 'order' => 2,
			'booking_url' => 'https://azurebay.example/packages/honeymoon', 'room_types' => $room_ids_p2, 'parents' => $parents_honeymoon,
		]
	);
	$offer_insert(
		[
			'slug' => 'festive-goa-christmas-new-year',
			'title' => 'Festive Goa — Christmas & New Year', 'excerpt' => 'Flat savings on qualifying stays.',
			'content' => '<p>₹2,000 off festive stays; see terms at booking.</p>',
			'property_id' => $p2, 'offer_type' => 'promotion', 'discount_type' => 'flat', 'discount_value' => 2000,
			'featured' => true, 'min_stay' => 3, 'valid_from' => $festive_from, 'valid_to' => $festive_to, 'order' => 3,
			'booking_url' => 'https://azurebay.example/offers/festive', 'room_types' => $room_ids_p2,
		]
	);

	$airport_tid = pw_sample_ensure_term( 'Airport', 'pw_nearby_type' );
	$metro_tid   = pw_sample_ensure_term( 'Metro', 'pw_nearby_type' );
	$shop_tid    = pw_sample_ensure_term( 'Shopping', 'pw_nearby_type' );
	$park_tid    = pw_sample_ensure_term( 'Park', 'pw_nearby_type' );
	$land_tid    = pw_sample_ensure_term( 'Landmark', 'pw_nearby_type' );
	$attr_tid    = pw_sample_ensure_term( 'Attraction', 'pw_nearby_type' );
	$beach_tid   = pw_sample_ensure_term( 'Beach', 'pw_nearby_type' );
	$herit_tid   = pw_sample_ensure_term( 'Heritage', 'pw_nearby_type' );
	$market_tid  = pw_sample_ensure_term( 'Market', 'pw_nearby_type' );
	$walk_tid    = pw_sample_ensure_term( 'Walk', 'pw_transport_mode' );
	$drive_tid   = pw_sample_ensure_term( 'Drive', 'pw_transport_mode' );

	$near_p1 = [
		[ 'slug' => 'kempegowda-international-airport-blr', 'title' => 'Kempegowda International Airport', 'excerpt' => 'International and domestic hub.', 'content' => '<p>BLR is the main gateway for delegates flying into Bengaluru; allow about an hour by car in normal traffic.</p>', 'km' => 38, 'min' => 60, 'lat' => 13.19890, 'lng' => 77.70680, 'type' => $airport_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'mg-road-metro-station', 'title' => 'MG Road Metro Station', 'excerpt' => 'Namma Metro connectivity.', 'content' => '<p>Purple and green lines meet nearby — handy for quick hops without battling MG Road traffic.</p>', 'km' => 0.8, 'min' => 10, 'lat' => 12.97533, 'lng' => 77.60780, 'type' => $metro_tid, 'trans' => $walk_tid ],
		[ 'slug' => 'ub-city-mall', 'title' => 'UB City Mall', 'excerpt' => 'Luxury retail and dining.', 'content' => '<p>High-end shopping and restaurants a few minutes on foot from the hotel lobby.</p>', 'km' => 0.5, 'min' => 7, 'lat' => 12.97262, 'lng' => 77.59660, 'type' => $shop_tid, 'trans' => $walk_tid ],
		[ 'slug' => 'cubbon-park', 'title' => 'Cubbon Park', 'excerpt' => 'Historic green lung of Bengaluru.', 'content' => '<p>Morning walks under rain trees — a calm break between meetings.</p>', 'km' => 1.2, 'min' => 15, 'lat' => 12.97619, 'lng' => 77.59290, 'type' => $park_tid, 'trans' => $walk_tid ],
		[ 'slug' => 'lido-mall', 'title' => 'Lido Mall', 'excerpt' => 'Shopping and cinema.', 'content' => '<p>Multiplex and casual dining a short drive east for downtime evenings.</p>', 'km' => 4.5, 'min' => 20, 'lat' => 12.99320, 'lng' => 77.64510, 'type' => $shop_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'st-marks-cathedral', 'title' => 'St. Mark\'s Cathedral', 'excerpt' => 'Anglican landmark.', 'content' => '<p>Neo-Gothic church on MG Road — worth a photo stop on a city stroll.</p>', 'km' => 0.9, 'min' => 12, 'lat' => 12.97277, 'lng' => 77.59560, 'type' => $land_tid, 'trans' => $walk_tid ],
		[ 'slug' => 'bangalore-palace', 'title' => 'Bangalore Palace', 'excerpt' => 'Tudor-style palace and events venue.', 'content' => '<p>Historic palace grounds host concerts and fairs — combine with an afternoon drive.</p>', 'km' => 3.2, 'min' => 15, 'lat' => 12.99844, 'lng' => 77.59240, 'type' => $attr_tid, 'trans' => $drive_tid ],
	];
	$i = 1;
	foreach ( $near_p1 as $nd ) {
		$nid = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_nearby',
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $nd['slug'] ?? $nd['title'] ),
				'post_title'   => $nd['title'],
				'post_excerpt' => $nd['excerpt'],
				'post_content' => wp_kses_post( isset( $nd['content'] ) ? (string) $nd['content'] : '' ),
			],
			true
		);
		if ( is_wp_error( $nid ) || ! $nid ) {
			continue;
		}
		$nid = (int) $nid;
		update_post_meta( $nid, '_pw_property_id', $p1 );
		update_post_meta( $nid, '_pw_distance_km', (float) $nd['km'] );
		update_post_meta( $nid, '_pw_travel_time_min', (int) $nd['min'] );
		update_post_meta( $nid, '_pw_lat', (float) $nd['lat'] );
		update_post_meta( $nid, '_pw_lng', (float) $nd['lng'] );
		update_post_meta( $nid, '_pw_place_url', 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $nd['title'] . ' Bengaluru India' ) );
		update_post_meta( $nid, '_pw_display_order', $i );
		wp_set_object_terms( $nid, array_filter( [ (int) $nd['type'] ] ), 'pw_nearby_type' );
		wp_set_object_terms( $nid, array_filter( [ (int) $nd['trans'] ] ), 'pw_transport_mode' );
		++$i;
	}

	$near_p2 = [
		[ 'slug' => 'goa-international-airport', 'title' => 'Goa International Airport', 'excerpt' => 'Dabolim / Mopa region.', 'content' => '<p>Most guests arrive via GOI or Mopa; we can arrange a private transfer to the resort.</p>', 'km' => 42, 'min' => 60, 'lat' => 15.38018, 'lng' => 73.83141, 'type' => $airport_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'calangute-beach', 'title' => 'Calangute Beach', 'excerpt' => 'Walking distance.', 'content' => '<p>Wide sandy stretch minutes from the gate — great for a sunset walk with kids.</p>', 'km' => 0.3, 'min' => 4, 'lat' => 15.54426, 'lng' => 73.75572, 'type' => $beach_tid, 'trans' => $walk_tid ],
		[ 'slug' => 'baga-beach', 'title' => 'Baga Beach', 'excerpt' => 'Popular stretch north of Calangute.', 'content' => '<p>Livelier scene with shacks and music — a short taxi hop north.</p>', 'km' => 2.1, 'min' => 8, 'lat' => 15.55633, 'lng' => 73.75276, 'type' => $beach_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'saturday-night-market-arpora', 'title' => 'Saturday Night Market, Arpora', 'excerpt' => 'Seasonal night market.', 'content' => '<p>Handicrafts, street food, and live acts — go on a Saturday evening.</p>', 'km' => 4.8, 'min' => 15, 'lat' => 15.56100, 'lng' => 73.76890, 'type' => $market_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'basilica-of-bom-jesus', 'title' => 'Basilica of Bom Jesus', 'excerpt' => 'UNESCO World Heritage church.', 'content' => '<p>Baroque masterpiece in Old Goa — pair with a half-day heritage drive.</p>', 'km' => 18.5, 'min' => 35, 'lat' => 15.50064, 'lng' => 73.91146, 'type' => $herit_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'anjuna-flea-market', 'title' => 'Anjuna Flea Market', 'excerpt' => 'Wednesday market.', 'content' => '<p>Bohemian stalls and sunset views — classic North Goa outing.</p>', 'km' => 5.2, 'min' => 18, 'lat' => 15.57370, 'lng' => 73.74170, 'type' => $market_tid, 'trans' => $drive_tid ],
	];
	$i = 1;
	foreach ( $near_p2 as $nd ) {
		$nid = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_nearby',
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $nd['slug'] ?? $nd['title'] ),
				'post_title'   => $nd['title'],
				'post_excerpt' => $nd['excerpt'],
				'post_content' => wp_kses_post( isset( $nd['content'] ) ? (string) $nd['content'] : '' ),
			],
			true
		);
		if ( is_wp_error( $nid ) || ! $nid ) {
			continue;
		}
		$nid = (int) $nid;
		update_post_meta( $nid, '_pw_property_id', $p2 );
		update_post_meta( $nid, '_pw_distance_km', (float) $nd['km'] );
		update_post_meta( $nid, '_pw_travel_time_min', (int) $nd['min'] );
		update_post_meta( $nid, '_pw_lat', (float) $nd['lat'] );
		update_post_meta( $nid, '_pw_lng', (float) $nd['lng'] );
		update_post_meta( $nid, '_pw_place_url', 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $nd['title'] . ' Goa India' ) );
		update_post_meta( $nid, '_pw_display_order', $i );
		wp_set_object_terms( $nid, array_filter( [ (int) $nd['type'] ] ), 'pw_nearby_type' );
		wp_set_object_terms( $nid, array_filter( [ (int) $nd['trans'] ] ), 'pw_transport_mode' );
		++$i;
	}
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 68, __( 'Nearby places added.', 'portico-webworks' ) );
	}

	$cul = pw_sample_ensure_term( 'Culinary', 'pw_experience_category' );
	$wel = pw_sample_ensure_term( 'Wellness', 'pw_experience_category' );
	$cult = pw_sample_ensure_term( 'Cultural', 'pw_experience_category' );
	$adv = pw_sample_ensure_term( 'Adventure', 'pw_experience_category' );
	$nat = pw_sample_ensure_term( 'Nature', 'pw_experience_category' );

	$exp_rows = [
		[ 'pid' => $p1, 'slug' => 'craft-beer-bengaluru-food-walk', 'title' => 'Craft Beer & Bengaluru Food Walk', 'excerpt' => 'Evening culinary tour.', 'desc' => 'Guided evening through microbreweries and classic Karnataka snacks.', 'content' => '<p>Meet at the lobby; small groups with a local host and transport between stops.</p>', 'cat' => $cul, 'hours' => 3, 'price' => 2500, 'free' => false, 'order' => 1, 'url' => 'https://meridian-grand.example/experiences/food-walk' ],
		[ 'pid' => $p1, 'slug' => 'yoga-meditation-sunrise', 'title' => 'Yoga & Meditation at Sunrise', 'excerpt' => 'Complimentary wellness session.', 'desc' => 'Gentle flow and breathwork as the city wakes.', 'content' => '<p>Mats provided on the pool deck; suitable for beginners.</p>', 'cat' => $wel, 'hours' => 1, 'price' => 0, 'free' => true, 'order' => 2, 'url' => '' ],
		[ 'pid' => $p1, 'slug' => 'bengaluru-heritage-city-tour', 'title' => 'Bengaluru Heritage City Tour', 'excerpt' => 'Half-day heritage routing.', 'desc' => 'Victorian cantonment lanes, markets, and temples with a storyteller guide.', 'content' => '<p>Includes bottled water and AC vehicle; mornings recommended.</p>', 'cat' => $cult, 'hours' => 4, 'price' => 1800, 'free' => false, 'order' => 3, 'url' => 'https://meridian-grand.example/experiences/heritage' ],
		[ 'pid' => $p1, 'slug' => 'whisky-masterclass-merchants-hall', 'title' => 'Whisky Masterclass at Merchant\'s Hall', 'excerpt' => 'Guided tasting experience.', 'desc' => 'Regional and international drams paired with small plates.', 'content' => '<p>Hosted in a private dining room; advance booking required.</p>', 'cat' => $cul, 'hours' => 2, 'price' => 3500, 'free' => false, 'order' => 4, 'url' => 'https://meridian-grand.example/experiences/whisky' ],
		[ 'pid' => $p2, 'slug' => 'sunrise-kayaking', 'title' => 'Sunrise Kayaking', 'excerpt' => 'Complimentary for direct bookers.', 'desc' => 'Quiet paddle as the coast lights up.', 'content' => '<p>Life jackets and guides included; tide-dependent schedule.</p>', 'cat' => $adv, 'hours' => 1.5, 'price' => 0, 'free' => true, 'order' => 1, 'url' => '' ],
		[ 'pid' => $p2, 'slug' => 'goan-cooking-masterclass', 'title' => 'Goan Cooking Masterclass', 'excerpt' => 'Learn classic Goan dishes.', 'desc' => 'Market visit optional, then hands-on coconut-based curries.', 'content' => '<p>Recipes to take home; vegetarian options on request.</p>', 'cat' => $cul, 'hours' => 3, 'price' => 2800, 'free' => false, 'order' => 2, 'url' => 'https://azurebay.example/experiences/cooking' ],
		[ 'pid' => $p2, 'slug' => 'spice-plantation-half-day-tour', 'title' => 'Spice Plantation Half-Day Tour', 'excerpt' => 'Nature and spice estates.', 'desc' => 'Walk shade-grown pepper, vanilla, and nutmeg with a planter.', 'content' => '<p>Includes plantation lunch thali; return by mid-afternoon.</p>', 'cat' => $nat, 'hours' => 4, 'price' => 1500, 'free' => false, 'order' => 3, 'url' => 'https://azurebay.example/experiences/plantation' ],
		[ 'pid' => $p2, 'slug' => 'dolphin-watching-boat-trip', 'title' => 'Dolphin Watching Boat Trip', 'excerpt' => 'Coastal wildlife outing.', 'desc' => 'Coastal motorboat safari with naturalist commentary.', 'content' => '<p>Early departures for calmer seas; sightings not guaranteed.</p>', 'cat' => $adv, 'hours' => 2, 'price' => 1200, 'free' => false, 'order' => 4, 'url' => 'https://azurebay.example/experiences/dolphins' ],
		[ 'pid' => $p2, 'slug' => 'full-moon-beach-bonfire-dinner', 'title' => 'Full Moon Beach Bonfire Dinner', 'excerpt' => 'Seasonal beach dining.', 'desc' => 'Chef-led barbecue and acoustic set on the sand.', 'content' => '<p>Offered on select full-moon dates; weather dependent.</p>', 'cat' => $cul, 'hours' => 3, 'price' => 3500, 'free' => false, 'order' => 5, 'url' => 'https://azurebay.example/experiences/bonfire' ],
	];
	foreach ( $exp_rows as $er ) {
		$eid = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_experience',
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $er['slug'] ?? $er['title'] ),
				'post_title'   => $er['title'],
				'post_excerpt' => $er['excerpt'],
				'post_content' => wp_kses_post( isset( $er['content'] ) ? (string) $er['content'] : '' ),
			],
			true
		);
		if ( is_wp_error( $eid ) || ! $eid ) {
			continue;
		}
		$eid = (int) $eid;
		$prop = (int) $er['pid'];
		update_post_meta( $eid, '_pw_property_id', $prop );
		update_post_meta( $eid, '_pw_connected_to', [ [ 'type' => 'pw_property', 'id' => $prop ] ] );
		update_post_meta( $eid, '_pw_duration_hours', (float) $er['hours'] );
		update_post_meta( $eid, '_pw_price_from', (float) $er['price'] );
		update_post_meta( $eid, '_pw_is_complimentary', (bool) $er['free'] );
		update_post_meta( $eid, '_pw_booking_url', isset( $er['url'] ) ? (string) $er['url'] : '' );
		update_post_meta( $eid, '_pw_description', isset( $er['desc'] ) ? (string) $er['desc'] : '' );
		update_post_meta( $eid, '_pw_gallery', [] );
		update_post_meta( $eid, '_pw_display_order', (int) $er['order'] );
		if ( ! empty( $er['cat'] ) ) {
			wp_set_object_terms( $eid, [ (int) $er['cat'] ], 'pw_experience_category' );
		}
	}
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 76, __( 'Experiences created.', 'portico-webworks' ) );
	}

	$gala_tid = pw_sample_ensure_term( 'Gala', 'pw_event_type' );
	$conf_tid = pw_sample_ensure_term( 'Conference', 'pw_event_type' );
	$beach_ev_tid = pw_sample_ensure_term( 'Beach Event', 'pw_event_type' );
	$brunch_tid = pw_sample_ensure_term( 'Brunch', 'pw_event_type' );

	$event_rows = [
		[ 'pid' => $p1, 'slug' => 'festival-gala-dinner-skyline', 'title' => 'Festival Gala Dinner — Skyline Kitchen', 'desc' => 'Rooftop set-menu dinner with skyline views and live ensemble.', 'start' => $ev_gala_start, 'end' => $ev_gala_end, 'price' => 4500, 'cap' => 120, 'type' => $gala_tid, 'venue' => 0, 'book' => 'https://meridian-grand.example/events/festival-gala' ],
		[ 'pid' => $p1, 'slug' => 'corporate-leadership-summit', 'title' => 'Corporate Leadership Summit', 'desc' => 'Two-day summit with keynotes and workshops in the Meridian Grand Ballroom.', 'start' => $ev_conf_start, 'end' => $ev_conf_end, 'price' => 12000, 'cap' => 200, 'type' => $conf_tid, 'venue' => $deccan ? $deccan : 0, 'book' => 'https://meridian-grand.example/events/leadership-summit' ],
		[ 'pid' => $p1, 'slug' => 'new-years-eve-bengaluru-countdown', 'title' => 'New Year\'s Eve — Bengaluru Countdown', 'desc' => 'Gala dinner, DJ, and midnight toast in the ballroom.', 'start' => $ev_nye_blr_start, 'end' => $ev_nye_blr_end, 'price' => 8000, 'cap' => 300, 'type' => $gala_tid, 'venue' => $deccan ? $deccan : 0, 'book' => 'https://meridian-grand.example/events/nye-countdown' ],
		[ 'pid' => $p2, 'slug' => 'full-moon-beach-party-goa', 'title' => 'Full Moon Beach Party — North Goa', 'desc' => 'DJ set, beach bar, and fire pits on a full-moon evening.', 'start' => $ev_beach_start, 'end' => $ev_beach_end, 'price' => 1500, 'cap' => 150, 'type' => $beach_ev_tid, 'venue' => 0, 'book' => 'https://azurebay.example/events/full-moon-party' ],
		[ 'pid' => $p2, 'slug' => 'azure-bay-christmas-brunch', 'title' => 'Azure Bay Christmas Brunch', 'desc' => 'Festive buffet with live carving and dessert room.', 'start' => $ev_xmas_start, 'end' => $ev_xmas_end, 'price' => 3200, 'cap' => 90, 'type' => $brunch_tid, 'venue' => 0, 'book' => 'https://azurebay.example/events/christmas-brunch' ],
		[ 'pid' => $p2, 'slug' => 'new-years-eve-beach-bash-goa', 'title' => 'New Year\'s Eve Beach Bash — Goa', 'desc' => 'Sand, fireworks offshore, and countdown by the Arabian Sea.', 'start' => $ev_nye_goa_start, 'end' => $ev_nye_goa_end, 'price' => 5500, 'cap' => 200, 'type' => $beach_ev_tid, 'venue' => 0, 'book' => 'https://azurebay.example/events/nye-beach-bash' ],
	];
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 82, __( 'Creating scheduled events…', 'portico-webworks' ) );
	}
	foreach ( $event_rows as $ev ) {
		$eid = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_event',
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $ev['slug'] ?? $ev['title'] ),
				'post_title'   => $ev['title'],
				'post_excerpt' => '',
				'post_content' => '<p>Demo event for Portico Webworks sample data.</p>',
			],
			true
		);
		if ( is_wp_error( $eid ) || ! $eid ) {
			continue;
		}
		$eid = (int) $eid;
		update_post_meta( $eid, '_pw_property_id', (int) $ev['pid'] );
		update_post_meta( $eid, '_pw_venue_id', (int) $ev['venue'] );
		update_post_meta( $eid, '_pw_start_datetime', $ev['start'] );
		update_post_meta( $eid, '_pw_end_datetime', $ev['end'] );
		update_post_meta( $eid, '_pw_description', isset( $ev['desc'] ) ? (string) $ev['desc'] : '' );
		update_post_meta( $eid, '_pw_price_from', (float) $ev['price'] );
		update_post_meta( $eid, '_pw_capacity', (int) $ev['cap'] );
		update_post_meta( $eid, '_pw_event_status', 'EventScheduled' );
		update_post_meta( $eid, '_pw_event_attendance_mode', 'OfflineEventAttendanceMode' );
		update_post_meta( $eid, '_pw_gallery', [] );
		update_post_meta( $eid, '_pw_recurrence_rule', '' );
		update_post_meta( $eid, '_pw_booking_url', isset( $ev['book'] ) ? (string) $ev['book'] : '' );
		if ( ! empty( $ev['type'] ) ) {
			wp_set_object_terms( $eid, [ (int) $ev['type'] ], 'pw_event_type' );
		}
		if ( $organiser_id ) {
			wp_set_object_terms( $eid, [ (int) $organiser_id ], 'pw_event_organiser' );
		}
		$pst = get_post( $eid );
		if ( $pst instanceof WP_Post ) {
			pw_sync_pw_event_iso8601_meta( $eid, $pst, false );
		}
	}

	$sample_cat_id = pw_sample_ensure_term( 'Portico Demo Hotels', 'category' );
	pw_sample_wp_insert_post(
		[
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Portico multi-property demo',
			'post_name'    => 'portico-multi-property-demo',
			'post_content' => '<p>Sample landing page for Meridian Grand Hotel Bengaluru and Azure Bay Beach Resort (Portico Webworks demo data).</p>',
			'post_excerpt' => 'Demo properties: Bengaluru and Goa.',
		],
		true
	);
	$blog_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => 'Why book direct in India\'s leisure markets',
			'post_name'    => 'portico-demo-book-direct-india',
			'post_content' => '<p>Sample article paired with the Portico Webworks multi-property demo dataset.</p>',
			'post_excerpt' => 'Short read on direct booking benefits.',
		],
		true
	);
	if ( ! is_wp_error( $blog_ins ) && $blog_ins && $sample_cat_id ) {
		wp_set_object_terms( (int) $blog_ins, [ (int) $sample_cat_id ], 'category' );
	}
	$tag_id = pw_sample_ensure_term( 'India hotels', 'post_tag' );
	if ( ! is_wp_error( $blog_ins ) && $blog_ins && $tag_id ) {
		wp_set_object_terms( (int) $blog_ins, [ (int) $tag_id ], 'post_tag', true );
	}
	if ( function_exists( 'pw_sample_install_progress' ) ) {
		pw_sample_install_progress( 86, __( 'Demo blog content ready.', 'portico-webworks' ) );
	}
}
