<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

	$organiser_id = pw_sample_ensure_term( 'Portico Demo Events', 'pw_event_organiser' );
	if ( $organiser_id ) {
		update_term_meta( $organiser_id, 'organiser_url', 'https://example.com/events/' );
	}

	$p1_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_property',
			'post_status'  => 'publish',
			'post_title'   => 'The Leela Residency Bengaluru',
			'post_name'    => 'leela-residency-bengaluru',
			'post_content' => '<p>Five-star business hotel on Vittal Mallya Road with 184 rooms, rooftop dining, executive spa, and extensive meeting facilities in central Bengaluru.</p>',
			'post_excerpt' => 'Luxury business hotel in the heart of Bengaluru.',
		],
		true
	);
	$p2_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_property',
			'post_status'  => 'publish',
			'post_title'   => 'Seawind Resort Goa',
			'post_name'    => 'seawind-resort-goa',
			'post_content' => '<p>Four-star beach resort in Calangute with infinity pool, Goan seafood, and water sports — 76 keys between garden villas and sea-facing rooms.</p>',
			'post_excerpt' => 'Beachside resort in North Goa.',
		],
		true
	);
	if ( is_wp_error( $p1_ins ) || ! $p1_ins || is_wp_error( $p2_ins ) || ! $p2_ins ) {
		return;
	}
	$p1 = (int) $p1_ins;
	$p2 = (int) $p2_ins;

	$p1_strings = [
		'_pw_legal_name'         => 'Leela Residency Hotels Private Limited',
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
		'_pw_meta_title'         => 'The Leela Residency Bengaluru — 5-Star Business Hotel on Vittal Mallya Road',
		'_pw_meta_description'   => 'Experience luxury business travel at The Leela Residency Bengaluru. 184 rooms, rooftop dining, executive spa, and meeting facilities in the heart of Bengaluru.',
		'_pw_social_facebook'    => 'https://facebook.com/leelaresidencybengaluru',
		'_pw_social_instagram'   => 'https://instagram.com/leela_residency_blr',
		'_pw_social_twitter'     => 'https://twitter.com/LeelaResidency',
		'_pw_social_tripadvisor' => 'https://tripadvisor.com/hotel-leela-residency-bengaluru',
		'_pw_social_linkedin'    => 'https://linkedin.com/company/leela-residency-hotels',
		'_pw_social_youtube'     => 'https://youtube.com/@LeelaResidencyBLR',
	];
	foreach ( $p1_strings as $k => $v ) {
		update_post_meta( $p1, $k, $v );
	}
	update_post_meta( $p1, '_pw_star_rating', 5 );
	update_post_meta( $p1, '_pw_year_established', 2009 );
	update_post_meta( $p1, '_pw_total_rooms', 184 );
	update_post_meta( $p1, '_pw_lat', 12.97194 );
	update_post_meta( $p1, '_pw_lng', 77.59553 );
	update_post_meta( $p1, '_pw_og_image', 0 );
	pw_sample_install_pw_contact_rows(
		$p1,
		[
			[
				'post_title' => 'Hotel — The Leela Residency Bengaluru',
				'label'      => 'Hotel',
				'phone'      => '+91-80-4123-4500',
				'mobile'     => '',
				'whatsapp'   => '',
				'email'      => 'reservations@leela-residency.com',
				'scope_cpt'  => 'property',
				'scope_id'   => 0,
			],
			[
				'post_title' => 'Reservations — The Leela Residency Bengaluru',
				'label'      => 'Reservations',
				'phone'      => '',
				'mobile'     => '+91-98860-12345',
				'whatsapp'   => '+91-98860-12345',
				'email'      => 'bookings@leela-residency.com',
				'scope_cpt'  => 'property',
				'scope_id'   => 0,
			],
			[
				'post_title' => 'Sales — The Leela Residency Bengaluru',
				'label'      => 'Sales',
				'phone'      => '+91-80-4123-4520',
				'mobile'     => '+91-98860-67890',
				'whatsapp'   => '',
				'email'      => 'sales@leela-residency.com',
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

	$p2_strings = [
		'_pw_legal_name'         => 'Seawind Hospitality LLP',
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
		'_pw_meta_title'         => 'Seawind Resort Goa — Beachside 4-Star Hotel in Calangute',
		'_pw_meta_description'   => 'Stay at Seawind Resort Goa — a 4-star beach resort in Calangute with infinity pool, Goan seafood restaurant, and water sports. Book direct for best rates.',
		'_pw_social_facebook'    => 'https://facebook.com/seawindgoa',
		'_pw_social_instagram'   => 'https://instagram.com/seawind_goa',
		'_pw_social_tripadvisor' => 'https://tripadvisor.com/hotel-seawind-resort-goa',
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
	update_post_meta( $p2, '_pw_og_image', 0 );

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
				'post_title' => 'Hotel — Seawind Resort Goa',
				'label'      => 'Hotel',
				'phone'      => '+91-832-2276-400',
				'mobile'     => '',
				'whatsapp'   => '',
				'email'      => 'hello@seawindgoa.com',
				'scope_cpt'  => 'property',
				'scope_id'   => 0,
			],
			[
				'post_title' => 'Reservations — Seawind Resort Goa',
				'label'      => 'Reservations',
				'phone'      => '',
				'mobile'     => '+91-97650-44321',
				'whatsapp'   => '+91-97650-44321',
				'email'      => 'stay@seawindgoa.com',
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
				[ 'rate_label' => 'Seasonal — Conference Season', 'rate_type' => 'seasonal', 'price' => 28000, 'valid_from' => '2025-09-01', 'valid_to' => '2025-11-30', 'advance_days' => 0, 'includes_breakfast' => true ],
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
				[ 'rate_label' => 'Peak Season', 'rate_type' => 'seasonal', 'price' => 14000, 'valid_from' => '2025-12-20', 'valid_to' => '2026-01-05', 'advance_days' => 0, 'includes_breakfast' => false ],
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
				[ 'rate_label' => 'Peak Season', 'rate_type' => 'seasonal', 'price' => 18000, 'valid_from' => '2025-12-20', 'valid_to' => '2026-01-05', 'advance_days' => 0, 'includes_breakfast' => false ],
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
		if ( ! empty( $rd['beds'] ) ) {
			wp_set_object_terms( $rid, array_filter( array_map( 'intval', $rd['beds'] ) ), 'pw_bed_type' );
		}
		if ( ! empty( $rd['views'] ) ) {
			wp_set_object_terms( $rid, array_filter( array_map( 'intval', $rd['views'] ) ), 'pw_view_type' );
		}
	}

	$bf = pw_sample_ensure_term( 'Breakfast', 'pw_meal_period' );
	$lun = pw_sample_ensure_term( 'Lunch', 'pw_meal_period' );
	$din = pw_sample_ensure_term( 'Dinner', 'pw_meal_period' );
	$sun_br = pw_sample_ensure_term( 'Sunday Brunch', 'pw_meal_period' );
	$br = pw_sample_ensure_term( 'Brunch', 'pw_meal_period' );
	$all_day = pw_sample_ensure_term( 'All-day Dining', 'pw_meal_period' );

	$wd = [ 'monday', 'tuesday', 'wednesday', 'thursday' ];
	$karavali_hours = [];
	foreach ( $wd as $d ) {
		$karavali_hours[ $d ] = pw_sample_operating_day(
			[
				[ 'label' => 'Breakfast', 'open_time' => '07:00', 'close_time' => '10:30' ],
				[ 'label' => 'Lunch', 'open_time' => '12:30', 'close_time' => '15:00' ],
				[ 'label' => 'Dinner', 'open_time' => '19:00', 'close_time' => '23:00' ],
			]
		);
	}
	$karavali_hours['friday'] = pw_sample_operating_day(
		[
			[ 'label' => 'Breakfast', 'open_time' => '07:00', 'close_time' => '10:30' ],
			[ 'label' => 'Lunch', 'open_time' => '12:30', 'close_time' => '15:00' ],
			[ 'label' => 'Dinner', 'open_time' => '19:00', 'close_time' => '23:30' ],
		]
	);
	$karavali_hours['saturday'] = pw_sample_operating_day(
		[
			[ 'label' => 'Breakfast', 'open_time' => '07:00', 'close_time' => '11:00' ],
			[ 'label' => 'Brunch', 'open_time' => '12:00', 'close_time' => '15:30' ],
			[ 'label' => 'Dinner', 'open_time' => '19:00', 'close_time' => '23:30' ],
		]
	);
	$karavali_hours['sunday'] = pw_sample_operating_day(
		[
			[ 'label' => 'Breakfast', 'open_time' => '07:00', 'close_time' => '11:00' ],
			[ 'label' => 'Sunday Brunch', 'open_time' => '12:00', 'close_time' => '16:00' ],
			[ 'label' => 'Dinner', 'open_time' => '19:00', 'close_time' => '22:30' ],
		]
	);

	$karavali_id = (int) pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_restaurant',
			'post_status'  => 'publish',
			'post_name'    => 'karavali-rooftop',
			'post_title'   => 'Karavali Rooftop',
			'post_excerpt' => 'South Indian, Pan-Asian, and Continental on Level 12.',
			'post_content' => '<p>Rooftop dining with city views.</p>',
		],
		true
	);
	if ( $karavali_id ) {
		update_post_meta( $karavali_id, '_pw_property_id', $p1 );
		update_post_meta( $karavali_id, '_pw_location', 'Rooftop, Level 12' );
		update_post_meta( $karavali_id, '_pw_cuisine_type', 'South Indian, Pan-Asian, Continental' );
		update_post_meta( $karavali_id, '_pw_seating_capacity', 120 );
		update_post_meta( $karavali_id, '_pw_reservation_url', 'https://leela-residency.com/reserve/karavali' );
		update_post_meta( $karavali_id, '_pw_menu_url', 'https://leela-residency.com/menus/karavali' );
		update_post_meta( $karavali_id, '_pw_gallery', [] );
		pw_sample_restaurant_set_hours( $karavali_id, $karavali_hours );
		wp_set_object_terms( $karavali_id, array_filter( [ $bf, $lun, $din, $sun_br, $br ] ), 'pw_meal_period' );
	}

	$spice_hours = array_fill_keys(
		[ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ],
		pw_sample_operating_day( [ [ 'label' => 'All Day', 'open_time' => '06:30', 'close_time' => '23:30' ] ] )
	);
	$spice_id = (int) pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_restaurant',
			'post_status'  => 'publish',
			'post_name'    => 'spice-verandah-all-day-dining',
			'post_title'   => 'Spice Verandah (All-Day Dining)',
			'post_excerpt' => 'Indian and international all day.',
			'post_content' => '<p>Lobby-level all-day dining.</p>',
		],
		true
	);
	if ( $spice_id ) {
		update_post_meta( $spice_id, '_pw_property_id', $p1 );
		update_post_meta( $spice_id, '_pw_location', 'Ground Floor, Lobby Level' );
		update_post_meta( $spice_id, '_pw_cuisine_type', 'Indian, International' );
		update_post_meta( $spice_id, '_pw_seating_capacity', 200 );
		update_post_meta( $spice_id, '_pw_reservation_url', 'https://leela-residency.com/reserve/spice-verandah' );
		update_post_meta( $spice_id, '_pw_menu_url', 'https://leela-residency.com/menus/spice-verandah' );
		update_post_meta( $spice_id, '_pw_gallery', [] );
		pw_sample_restaurant_set_hours( $spice_id, $spice_hours );
		wp_set_object_terms( $spice_id, array_filter( [ $bf, $lun, $din, $all_day ] ), 'pw_meal_period' );
	}

	$tides_hours = array_fill_keys(
		[ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ],
		pw_sample_operating_day(
			[
				[ 'label' => 'Breakfast', 'open_time' => '07:30', 'close_time' => '10:30' ],
				[ 'label' => 'Lunch', 'open_time' => '12:00', 'close_time' => '15:30' ],
				[ 'label' => 'Dinner', 'open_time' => '19:00', 'close_time' => '23:00' ],
			]
		)
	);
	$tides_id = (int) pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_restaurant',
			'post_status'  => 'publish',
			'post_name'    => 'the-tides-beach-bar-restaurant',
			'post_title'   => 'The Tides Beach Bar & Restaurant',
			'post_excerpt' => 'Goan seafood by the pool and beach.',
			'post_content' => '<p>Poolside and beachfront dining.</p>',
		],
		true
	);
	if ( $tides_id ) {
		update_post_meta( $tides_id, '_pw_property_id', $p2 );
		update_post_meta( $tides_id, '_pw_location', 'Poolside / Beachfront' );
		update_post_meta( $tides_id, '_pw_cuisine_type', 'Goan, Seafood, Continental' );
		update_post_meta( $tides_id, '_pw_seating_capacity', 90 );
		update_post_meta( $tides_id, '_pw_reservation_url', 'https://seawindgoa.com/reserve/the-tides' );
		update_post_meta( $tides_id, '_pw_menu_url', 'https://seawindgoa.com/menus/the-tides' );
		update_post_meta( $tides_id, '_pw_gallery', [] );
		pw_sample_restaurant_set_hours( $tides_id, $tides_hours );
		wp_set_object_terms( $tides_id, array_filter( [ $bf, $lun, $din ] ), 'pw_meal_period' );
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
			'post_name'    => 'nirvana-spa-wellness',
			'post_title'   => 'Nirvana Spa & Wellness',
			'post_excerpt' => 'Full-service spa in Bengaluru.',
			'post_content' => '<p>Eight treatment rooms and wellness rituals.</p>',
		],
		true
	);
	if ( $spa1 ) {
		update_post_meta( $spa1, '_pw_property_id', $p1 );
		update_post_meta( $spa1, '_pw_booking_url', 'https://leela-residency.com/spa/book' );
		update_post_meta( $spa1, '_pw_menu_url', 'https://leela-residency.com/spa/treatments' );
		update_post_meta( $spa1, '_pw_min_age', 16 );
		update_post_meta( $spa1, '_pw_number_of_treatment_rooms', 8 );
		update_post_meta( $spa1, '_pw_gallery', [] );
		pw_sample_restaurant_set_hours( $spa1, pw_sample_spa_all_days_same( '07:00', '21:00' ) );
		wp_set_object_terms( $spa1, array_filter( [ $ayur, $aroma, $deep, $hot, $couple, $fac ] ), 'pw_treatment_type' );
	}

	$spa2 = (int) pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_spa',
			'post_status'  => 'publish',
			'post_name'    => 'seawind-wellness-hut',
			'post_title'   => 'Seawind Wellness Hut',
			'post_excerpt' => 'Boutique spa by the sea.',
			'post_content' => '<p>Three treatment rooms in a garden hut setting.</p>',
		],
		true
	);
	if ( $spa2 ) {
		update_post_meta( $spa2, '_pw_property_id', $p2 );
		update_post_meta( $spa2, '_pw_booking_url', 'https://seawindgoa.com/spa/book' );
		update_post_meta( $spa2, '_pw_menu_url', 'https://seawindgoa.com/spa/menu' );
		update_post_meta( $spa2, '_pw_min_age', 18 );
		update_post_meta( $spa2, '_pw_number_of_treatment_rooms', 3 );
		update_post_meta( $spa2, '_pw_gallery', [] );
		pw_sample_restaurant_set_hours( $spa2, pw_sample_spa_all_days_same( '09:00', '20:00' ) );
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
			'post_name'    => 'deccan-ballroom',
			'post_title'   => 'Deccan Ballroom',
			'post_excerpt' => 'Grand ballroom for conferences and galas.',
			'post_content' => '<p>Pillar-free ballroom with pre-function space.</p>',
		],
		true
	);
	if ( $deccan ) {
		update_post_meta( $deccan, '_pw_property_id', $p1 );
		update_post_meta( $deccan, '_pw_capacity_theatre', 500 );
		update_post_meta( $deccan, '_pw_capacity_classroom', 300 );
		update_post_meta( $deccan, '_pw_capacity_boardroom', 0 );
		update_post_meta( $deccan, '_pw_capacity_ushape', 0 );
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
			'post_name'    => 'cubbon-boardroom',
			'post_title'   => 'Cubbon Boardroom',
			'post_excerpt' => 'Executive boardroom with natural light.',
			'post_content' => '<p>Intimate boardroom for leadership meetings.</p>',
		],
		true
	);
	if ( $cubbon ) {
		update_post_meta( $cubbon, '_pw_property_id', $p1 );
		update_post_meta( $cubbon, '_pw_capacity_theatre', 0 );
		update_post_meta( $cubbon, '_pw_capacity_classroom', 0 );
		update_post_meta( $cubbon, '_pw_capacity_boardroom', 18 );
		update_post_meta( $cubbon, '_pw_capacity_ushape', 0 );
		update_post_meta( $cubbon, '_pw_area_sqft', 650 );
		update_post_meta( $cubbon, '_pw_area_sqm', 60 );
		update_post_meta( $cubbon, '_pw_prefunction_area_sqft', 0 );
		update_post_meta( $cubbon, '_pw_prefunction_area_sqm', 0 );
		update_post_meta( $cubbon, '_pw_natural_light', true );
		update_post_meta( $cubbon, '_pw_floor_plan', 0 );
		update_post_meta( $cubbon, '_pw_gallery', [] );
		wp_set_object_terms( $cubbon, array_filter( [ $disp, $wp, $vc, $wb ] ), 'pw_av_equipment' );
	}

	pw_sample_multi_install_amenities_policies_faqs_offers_nearby_exp_events(
		$p1,
		$p2,
		$room_ids_p1,
		$room_ids_p2,
		$spa1,
		$spa2,
		$karavali_id,
		$spice_id,
		$tides_id,
		$deccan,
		$cubbon,
		$organiser_id
	);
}

function pw_sample_multi_install_amenities_policies_faqs_offers_nearby_exp_events(
	$p1,
	$p2,
	array $room_ids_p1,
	array $room_ids_p2,
	$spa1,
	$spa2,
	$karavali_id,
	$spice_id,
	$tides_id,
	$deccan,
	$cubbon,
	$organiser_id
) {
	$p1 = (int) $p1;
	$p2 = (int) $p2;
	$amen_p1 = [
		[ 'title' => '24-Hour Front Desk', 'type' => 'service', 'cat' => 'Guest Services', 'compl' => true, 'order' => 1, 'icon' => 'desk', 'desc' => '' ],
		[ 'title' => 'Concierge Service', 'type' => 'service', 'cat' => 'Guest Services', 'compl' => true, 'order' => 2, 'icon' => 'concierge', 'desc' => '' ],
		[ 'title' => 'Airport Transfer', 'type' => 'service', 'cat' => 'Transportation', 'compl' => false, 'order' => 3, 'icon' => 'car', 'desc' => '' ],
		[ 'title' => 'Valet Parking', 'type' => 'service', 'cat' => 'Transportation', 'compl' => false, 'order' => 4, 'icon' => 'parking', 'desc' => '' ],
		[ 'title' => 'Fitness Centre', 'type' => 'facility', 'cat' => 'Recreation', 'compl' => true, 'order' => 5, 'icon' => 'gym', 'desc' => '' ],
		[ 'title' => 'Business Centre', 'type' => 'facility', 'cat' => 'Business', 'compl' => true, 'order' => 6, 'icon' => 'business', 'desc' => '' ],
		[ 'title' => 'Wi-Fi — High Speed', 'type' => 'amenity', 'cat' => 'Connectivity', 'compl' => true, 'order' => 7, 'icon' => 'wifi', 'desc' => '' ],
		[ 'title' => 'Laundry & Dry Cleaning', 'type' => 'service', 'cat' => 'Housekeeping', 'compl' => false, 'order' => 8, 'icon' => 'laundry', 'desc' => '' ],
		[ 'title' => 'Doctor on Call', 'type' => 'service', 'cat' => 'Medical', 'compl' => false, 'order' => 9, 'icon' => 'medical', 'desc' => '' ],
		[ 'title' => 'EV Charging Station', 'type' => 'facility', 'cat' => 'Transportation', 'compl' => false, 'order' => 10, 'icon' => 'ev', 'desc' => '' ],
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
		update_post_meta( $aid, '_pw_description', $ad['desc'] );
		update_post_meta( $aid, '_pw_is_complimentary', (bool) $ad['compl'] );
		update_post_meta( $aid, '_pw_display_order', (int) $ad['order'] );
	}
	$amen_p2 = [
		[ 'title' => '24-Hour Front Desk', 'type' => 'service', 'cat' => 'Guest Services', 'compl' => true, 'order' => 1, 'icon' => 'desk', 'desc' => '' ],
		[ 'title' => 'Wi-Fi — High Speed', 'type' => 'amenity', 'cat' => 'Connectivity', 'compl' => true, 'order' => 2, 'icon' => 'wifi', 'desc' => '' ],
		[ 'title' => 'Beach Access', 'type' => 'facility', 'cat' => 'Recreation', 'compl' => true, 'order' => 3, 'icon' => 'beach', 'desc' => '' ],
		[ 'title' => 'Water Sports Desk', 'type' => 'service', 'cat' => 'Recreation', 'compl' => false, 'order' => 4, 'icon' => 'waves', 'desc' => '' ],
		[ 'title' => 'Bicycle Rental', 'type' => 'service', 'cat' => 'Transportation', 'compl' => false, 'order' => 5, 'icon' => 'bike', 'desc' => '' ],
		[ 'title' => 'Room Service', 'type' => 'service', 'cat' => 'Dining', 'compl' => false, 'order' => 6, 'icon' => 'room-service', 'desc' => '' ],
		[ 'title' => 'Laundry Service', 'type' => 'service', 'cat' => 'Housekeeping', 'compl' => false, 'order' => 7, 'icon' => 'laundry', 'desc' => '' ],
		[ 'title' => 'Airport Transfer', 'type' => 'service', 'cat' => 'Transportation', 'compl' => false, 'order' => 8, 'icon' => 'car', 'desc' => '' ],
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
		update_post_meta( $aid, '_pw_description', $ad['desc'] );
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
		[ 'q' => 'What is the dress code at Karavali Rooftop?', 'a' => '<p>Smart casual for lunch; smart casual to business casual for dinner. We request guests to avoid beachwear, flip-flops, and sleeveless vests in the restaurant.</p>' ],
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
		if ( $karavali_id && str_contains( strtolower( $fd['q'] ), 'karavali' ) ) {
			$conn[] = [ 'type' => 'pw_restaurant', 'id' => $karavali_id ];
		}
		update_post_meta( $fqid, '_pw_connected_to', $conn );
		++$o;
	}
	$faq_p2 = [
		[ 'q' => 'How far is the property from Goa Airport?', 'a' => '<p>Seawind Resort is approximately 42 km from Goa International Airport (GOI), roughly a 60-minute drive. We offer paid airport transfers — please contact us to pre-arrange.</p>' ],
		[ 'q' => 'Is the beach private?', 'a' => '<p>We have direct beach access via a dedicated resort pathway. While the beach itself is a public beach (as all Goa beaches are), our section is reserved for guests during resort hours and is managed by our beach attendants.</p>' ],
		[ 'q' => 'What water sports are available?', 'a' => '<p>We operate a water sports desk offering kayaking, paddleboarding, parasailing, jet skiing, and banana boat rides. Prices vary by activity. One complimentary 30-minute kayaking or paddleboarding session is included for direct bookings.</p>' ],
		[ 'q' => 'Is it safe to bring children?', 'a' => '<p>Absolutely. We have a shallow splash pool for children, a kids\' play area, and our beach section is patrolled during resort hours. We provide child menus at The Tides restaurant.</p>' ],
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
		if ( ! empty( $args['meta_title'] ) ) {
			update_post_meta( $id, '_pw_meta_title', $args['meta_title'] );
		}
		if ( ! empty( $args['meta_desc'] ) ) {
			update_post_meta( $id, '_pw_meta_description', $args['meta_desc'] );
		}
	};

	$offer_insert(
		[
			'slug' => 'advance-purchase-save-15',
			'title' => 'Advance Purchase — Save 15%', 'excerpt' => '', 'content' => '',
			'property_id' => $p1, 'offer_type' => 'promotion', 'discount_type' => 'percentage', 'discount_value' => 15,
			'featured' => false, 'min_stay' => 1, 'valid_from' => '', 'valid_to' => '', 'order' => 1,
			'booking_url' => 'https://leela-residency.com/offers/advance-purchase', 'room_types' => $room_ids_p1,
		]
	);
	$offer_insert(
		[
			'slug' => 'bengaluru-business-package',
			'title' => 'Bengaluru Business Package', 'excerpt' => 'Breakfast, transfer, minibar, spa discount.',
			'content' => '<p>Includes breakfast for two, one-way airport transfer, complimentary minibar refresh, and 15% off spa services.</p>',
			'property_id' => $p1, 'offer_type' => 'package', 'discount_type' => 'value_add', 'discount_value' => 0,
			'featured' => true, 'min_stay' => 2, 'valid_from' => '', 'valid_to' => '', 'order' => 2,
			'booking_url' => 'https://leela-residency.com/packages/business', 'room_types' => $room_ids_p1,
		]
	);
	$offer_insert(
		[
			'slug' => 'extended-stay-4-nights-1-free',
			'title' => 'Extended Stay — 4 Nights + 1 Free', 'excerpt' => '', 'content' => '<p>Fifth night complimentary on qualifying stays.</p>',
			'property_id' => $p1, 'offer_type' => 'promotion', 'discount_type' => 'value_add', 'discount_value' => 0,
			'featured' => false, 'min_stay' => 4, 'valid_from' => '', 'valid_to' => '', 'order' => 3,
			'booking_url' => 'https://leela-residency.com/offers/extended-stay', 'room_types' => $room_ids_p1,
		]
	);
	$offer_insert(
		[
			'slug' => 'conference-season-special',
			'title' => 'Conference Season Special', 'excerpt' => 'September–November',
			'content' => '<p>10% off best available rate during conference season.</p>',
			'property_id' => $p1, 'offer_type' => 'promotion', 'discount_type' => 'percentage', 'discount_value' => 10,
			'featured' => true, 'min_stay' => 2, 'valid_from' => '2025-09-01', 'valid_to' => '2025-11-30', 'order' => 4,
			'booking_url' => 'https://leela-residency.com/offers/conference-season', 'room_types' => $room_ids_p1,
		]
	);

	$offer_insert(
		[
			'slug' => 'early-bird-goa-escape-save-20',
			'title' => 'Early Bird Goa Escape — Save 20%', 'excerpt' => '', 'content' => '',
			'property_id' => $p2, 'offer_type' => 'promotion', 'discount_type' => 'percentage', 'discount_value' => 20,
			'featured' => true, 'min_stay' => 2, 'valid_from' => '', 'valid_to' => '', 'order' => 1,
			'booking_url' => 'https://seawindgoa.com/offers/early-bird', 'room_types' => $room_ids_p2,
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
			'content' => '<p>Includes sea-facing room, flower-decorated room on arrival, candlelight dinner for two at The Tides, couple\'s massage at Seawind Wellness Hut, and late check-out.</p>',
			'property_id' => $p2, 'offer_type' => 'package', 'discount_type' => 'value_add', 'discount_value' => 0,
			'featured' => true, 'min_stay' => 3, 'valid_from' => '', 'valid_to' => '', 'order' => 2,
			'booking_url' => 'https://seawindgoa.com/packages/honeymoon', 'room_types' => $room_ids_p2, 'parents' => $parents_honeymoon,
		]
	);
	$offer_insert(
		[
			'slug' => 'festive-goa-christmas-new-year',
			'title' => 'Festive Goa — Christmas & New Year', 'excerpt' => 'Flat savings on qualifying stays.',
			'content' => '<p>₹2,000 off festive stays; see terms at booking.</p>',
			'property_id' => $p2, 'offer_type' => 'promotion', 'discount_type' => 'flat', 'discount_value' => 2000,
			'featured' => true, 'min_stay' => 3, 'valid_from' => '2025-12-18', 'valid_to' => '2026-01-07', 'order' => 3,
			'booking_url' => 'https://seawindgoa.com/offers/festive', 'room_types' => $room_ids_p2,
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
		[ 'slug' => 'kempegowda-international-airport-blr', 'title' => 'Kempegowda International Airport', 'excerpt' => 'International and domestic hub.', 'km' => 38, 'min' => 60, 'lat' => 13.19890, 'lng' => 77.70680, 'type' => $airport_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'mg-road-metro-station', 'title' => 'MG Road Metro Station', 'excerpt' => 'Namma Metro connectivity.', 'km' => 0.8, 'min' => 10, 'lat' => 12.97533, 'lng' => 77.60780, 'type' => $metro_tid, 'trans' => $walk_tid ],
		[ 'slug' => 'ub-city-mall', 'title' => 'UB City Mall', 'excerpt' => 'Luxury retail and dining.', 'km' => 0.5, 'min' => 7, 'lat' => 12.97262, 'lng' => 77.59660, 'type' => $shop_tid, 'trans' => $walk_tid ],
		[ 'slug' => 'cubbon-park', 'title' => 'Cubbon Park', 'excerpt' => 'Historic green lung of Bengaluru.', 'km' => 1.2, 'min' => 15, 'lat' => 12.97619, 'lng' => 77.59290, 'type' => $park_tid, 'trans' => $walk_tid ],
		[ 'slug' => 'lido-mall', 'title' => 'Lido Mall', 'excerpt' => 'Shopping and cinema.', 'km' => 4.5, 'min' => 20, 'lat' => 12.99320, 'lng' => 77.64510, 'type' => $shop_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'st-marks-cathedral', 'title' => 'St. Mark\'s Cathedral', 'excerpt' => 'Anglican landmark.', 'km' => 0.9, 'min' => 12, 'lat' => 12.97277, 'lng' => 77.59560, 'type' => $land_tid, 'trans' => $walk_tid ],
		[ 'slug' => 'bangalore-palace', 'title' => 'Bangalore Palace', 'excerpt' => 'Tudor-style palace and events venue.', 'km' => 3.2, 'min' => 15, 'lat' => 12.99844, 'lng' => 77.59240, 'type' => $attr_tid, 'trans' => $drive_tid ],
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
				'post_content' => '',
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
		[ 'slug' => 'goa-international-airport', 'title' => 'Goa International Airport', 'excerpt' => 'Dabolim / Mopa region.', 'km' => 42, 'min' => 60, 'lat' => 15.38018, 'lng' => 73.83141, 'type' => $airport_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'calangute-beach', 'title' => 'Calangute Beach', 'excerpt' => 'Walking distance.', 'km' => 0.3, 'min' => 4, 'lat' => 15.54426, 'lng' => 73.75572, 'type' => $beach_tid, 'trans' => $walk_tid ],
		[ 'slug' => 'baga-beach', 'title' => 'Baga Beach', 'excerpt' => 'Popular stretch north of Calangute.', 'km' => 2.1, 'min' => 8, 'lat' => 15.55633, 'lng' => 73.75276, 'type' => $beach_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'saturday-night-market-arpora', 'title' => 'Saturday Night Market, Arpora', 'excerpt' => 'Seasonal night market.', 'km' => 4.8, 'min' => 15, 'lat' => 15.56100, 'lng' => 73.76890, 'type' => $market_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'basilica-of-bom-jesus', 'title' => 'Basilica of Bom Jesus', 'excerpt' => 'UNESCO World Heritage church.', 'km' => 18.5, 'min' => 35, 'lat' => 15.50064, 'lng' => 73.91146, 'type' => $herit_tid, 'trans' => $drive_tid ],
		[ 'slug' => 'anjuna-flea-market', 'title' => 'Anjuna Flea Market', 'excerpt' => 'Wednesday market.', 'km' => 5.2, 'min' => 18, 'lat' => 15.57370, 'lng' => 73.74170, 'type' => $market_tid, 'trans' => $drive_tid ],
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
				'post_content' => '',
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

	$cul = pw_sample_ensure_term( 'Culinary', 'pw_experience_category' );
	$wel = pw_sample_ensure_term( 'Wellness', 'pw_experience_category' );
	$cult = pw_sample_ensure_term( 'Cultural', 'pw_experience_category' );
	$adv = pw_sample_ensure_term( 'Adventure', 'pw_experience_category' );
	$nat = pw_sample_ensure_term( 'Nature', 'pw_experience_category' );

	$exp_rows = [
		[ 'pid' => $p1, 'slug' => 'craft-beer-bengaluru-food-walk', 'title' => 'Craft Beer & Bengaluru Food Walk', 'excerpt' => 'Evening culinary tour.', 'cat' => $cul, 'hours' => 3, 'price' => 2500, 'free' => false, 'order' => 1, 'url' => 'https://leela-residency.com/experiences/food-walk' ],
		[ 'pid' => $p1, 'slug' => 'yoga-meditation-sunrise', 'title' => 'Yoga & Meditation at Sunrise', 'excerpt' => 'Complimentary wellness session.', 'cat' => $wel, 'hours' => 1, 'price' => 0, 'free' => true, 'order' => 2, 'url' => 'https://leela-residency.com/experiences/sunrise-yoga' ],
		[ 'pid' => $p1, 'slug' => 'bengaluru-heritage-city-tour', 'title' => 'Bengaluru Heritage City Tour', 'excerpt' => 'Half-day heritage routing.', 'cat' => $cult, 'hours' => 4, 'price' => 1800, 'free' => false, 'order' => 3, 'url' => 'https://leela-residency.com/experiences/heritage' ],
		[ 'pid' => $p1, 'slug' => 'whisky-masterclass-spice-verandah', 'title' => 'Whisky Masterclass at Spice Verandah', 'excerpt' => 'Guided tasting experience.', 'cat' => $cul, 'hours' => 2, 'price' => 3500, 'free' => false, 'order' => 4, 'url' => 'https://leela-residency.com/experiences/whisky' ],
		[ 'pid' => $p2, 'slug' => 'sunrise-kayaking', 'title' => 'Sunrise Kayaking', 'excerpt' => 'Complimentary for direct bookers.', 'cat' => $adv, 'hours' => 1.5, 'price' => 0, 'free' => true, 'order' => 1, 'url' => 'https://seawindgoa.com/experiences/kayak' ],
		[ 'pid' => $p2, 'slug' => 'goan-cooking-masterclass', 'title' => 'Goan Cooking Masterclass', 'excerpt' => 'Learn classic Goan dishes.', 'cat' => $cul, 'hours' => 3, 'price' => 2800, 'free' => false, 'order' => 2, 'url' => 'https://seawindgoa.com/experiences/cooking' ],
		[ 'pid' => $p2, 'slug' => 'spice-plantation-half-day-tour', 'title' => 'Spice Plantation Half-Day Tour', 'excerpt' => 'Nature and spice estates.', 'cat' => $nat, 'hours' => 4, 'price' => 1500, 'free' => false, 'order' => 3, 'url' => 'https://seawindgoa.com/experiences/plantation' ],
		[ 'pid' => $p2, 'slug' => 'dolphin-watching-boat-trip', 'title' => 'Dolphin Watching Boat Trip', 'excerpt' => 'Coastal wildlife outing.', 'cat' => $adv, 'hours' => 2, 'price' => 1200, 'free' => false, 'order' => 4, 'url' => 'https://seawindgoa.com/experiences/dolphins' ],
		[ 'pid' => $p2, 'slug' => 'full-moon-beach-bonfire-dinner', 'title' => 'Full Moon Beach Bonfire Dinner', 'excerpt' => 'Seasonal beach dining.', 'cat' => $cul, 'hours' => 3, 'price' => 3500, 'free' => false, 'order' => 5, 'url' => 'https://seawindgoa.com/experiences/bonfire' ],
	];
	foreach ( $exp_rows as $er ) {
		$eid = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_experience',
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $er['slug'] ?? $er['title'] ),
				'post_title'   => $er['title'],
				'post_excerpt' => $er['excerpt'],
				'post_content' => '',
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
		update_post_meta( $eid, '_pw_booking_url', $er['url'] );
		update_post_meta( $eid, '_pw_gallery', [] );
		update_post_meta( $eid, '_pw_display_order', (int) $er['order'] );
		if ( ! empty( $er['cat'] ) ) {
			wp_set_object_terms( $eid, [ (int) $er['cat'] ], 'pw_experience_category' );
		}
	}

	$gala_tid = pw_sample_ensure_term( 'Gala', 'pw_event_type' );
	$conf_tid = pw_sample_ensure_term( 'Conference', 'pw_event_type' );
	$beach_ev_tid = pw_sample_ensure_term( 'Beach Event', 'pw_event_type' );
	$brunch_tid = pw_sample_ensure_term( 'Brunch', 'pw_event_type' );

	$event_rows = [
		[ 'pid' => $p1, 'slug' => 'diwali-gala-dinner-karavali', 'title' => 'Diwali Gala Dinner — Karavali Rooftop', 'start' => '2025-10-20 19:30:00', 'end' => '2025-10-20 23:30:00', 'price' => 4500, 'cap' => 120, 'type' => $gala_tid, 'venue' => 0 ],
		[ 'pid' => $p1, 'slug' => 'corporate-leadership-summit', 'title' => 'Corporate Leadership Summit', 'start' => '2025-10-15 09:00:00', 'end' => '2025-10-16 18:00:00', 'price' => 12000, 'cap' => 200, 'type' => $conf_tid, 'venue' => $deccan ? $deccan : 0 ],
		[ 'pid' => $p1, 'slug' => 'new-years-eve-bengaluru-countdown', 'title' => 'New Year\'s Eve — Bengaluru Countdown', 'start' => '2025-12-31 20:00:00', 'end' => '2026-01-01 01:30:00', 'price' => 8000, 'cap' => 300, 'type' => $gala_tid, 'venue' => $deccan ? $deccan : 0 ],
		[ 'pid' => $p2, 'slug' => 'full-moon-beach-party-goa', 'title' => 'Full Moon Beach Party — Goa', 'start' => '2025-11-05 19:00:00', 'end' => '2025-11-05 23:59:00', 'price' => 1500, 'cap' => 150, 'type' => $beach_ev_tid, 'venue' => 0 ],
		[ 'pid' => $p2, 'slug' => 'goa-christmas-brunch', 'title' => 'Goa Christmas Brunch', 'start' => '2025-12-25 11:00:00', 'end' => '2025-12-25 15:00:00', 'price' => 3200, 'cap' => 90, 'type' => $brunch_tid, 'venue' => 0 ],
		[ 'pid' => $p2, 'slug' => 'new-years-eve-beach-bash-goa', 'title' => 'New Year\'s Eve Beach Bash — Goa', 'start' => '2025-12-31 19:00:00', 'end' => '2026-01-01 02:00:00', 'price' => 5500, 'cap' => 200, 'type' => $beach_ev_tid, 'venue' => 0 ],
	];
	foreach ( $event_rows as $ev ) {
		$eid = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_event',
				'post_status'  => 'publish',
				'post_name'    => sanitize_title( $ev['slug'] ?? $ev['title'] ),
				'post_title'   => $ev['title'],
				'post_excerpt' => '',
				'post_content' => '<p>Demo event for sample data.</p>',
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
		update_post_meta( $eid, '_pw_price_from', (float) $ev['price'] );
		update_post_meta( $eid, '_pw_capacity', (int) $ev['cap'] );
		update_post_meta( $eid, '_pw_event_status', 'EventScheduled' );
		update_post_meta( $eid, '_pw_event_attendance_mode', 'OfflineEventAttendanceMode' );
		update_post_meta( $eid, '_pw_gallery', [] );
		update_post_meta( $eid, '_pw_recurrence_rule', '' );
		update_post_meta( $eid, '_pw_booking_url', '' );
		if ( ! empty( $ev['type'] ) ) {
			wp_set_object_terms( $eid, [ (int) $ev['type'] ], 'pw_event_type' );
		}
		if ( $organiser_id ) {
			wp_set_object_terms( $eid, [ (int) $organiser_id ], 'pw_event_organiser' );
		}
	}

	$sample_cat_id = pw_sample_ensure_term( 'Portico Demo Hotels', 'category' );
	pw_sample_wp_insert_post(
		[
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Portico multi-property demo',
			'post_name'    => 'portico-multi-property-demo',
			'post_content' => '<p>Sample landing page for The Leela Residency Bengaluru and Seawind Resort Goa (Portico Webworks demo data).</p>',
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
}
