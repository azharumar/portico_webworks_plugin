<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'pw_admin_tabs',
	function ( $tabs ) {
		$tabs['sample_data'] = 'Sample Data';
		return $tabs;
	},
	20
);

add_action( 'pw_render_tab_sample_data', 'pw_render_sample_data_tab' );

add_action( 'admin_post_pw_install_sample_data', 'pw_handle_install_sample_data' );

function pw_render_sample_data_tab() {
	$has_properties = get_posts(
		[
			'post_type'      => 'pw_property',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);

	if ( isset( $_GET['pw_sample_installed'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Sample data installed successfully.</p></div>';
	}
	if ( isset( $_GET['pw_sample_error'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>Sample data cannot be installed when properties already exist.</p></div>';
	}

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">Sample Data</div></div>';
	echo '<div class="pw-card-body">';
	echo '<p>Install a sample hotel property with room types, restaurants, spa, amenities, policies, FAQs, offers, and more. Use this to quickly populate a fresh site for testing or demonstration.</p>';

	if ( ! empty( $has_properties ) ) {
		echo '<p><strong>Sample data can only be installed when no properties exist.</strong> Delete existing properties first if you want to reinstall.</p>';
	} else {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="pw_install_sample_data" />';
		wp_nonce_field( 'pw_install_sample_data' );
		submit_button( 'Install sample data', 'primary', 'submit', false );
		echo '</form>';
	}

	echo '</div></div>';
}

function pw_handle_install_sample_data() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorised' );
	}
	check_admin_referer( 'pw_install_sample_data' );

	$existing = get_posts(
		[
			'post_type'      => 'pw_property',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);

	if ( ! empty( $existing ) ) {
		wp_safe_redirect(
			add_query_arg(
				'pw_sample_error',
				'1',
				admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=sample_data' )
			)
		);
		exit;
	}

	pw_install_sample_data();

	wp_safe_redirect(
		add_query_arg(
			'pw_sample_installed',
			'1',
			admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=sample_data' )
		)
	);
	exit;
}

function pw_sample_ensure_term( $name, $taxonomy ) {
	$exists = term_exists( $name, $taxonomy );
	if ( $exists ) {
		return is_array( $exists ) ? (int) $exists['term_id'] : (int) $exists;
	}
	$inserted = wp_insert_term( $name, $taxonomy );
	if ( is_wp_error( $inserted ) ) {
		return 0;
	}
	return (int) $inserted['term_id'];
}

function pw_sample_operating_day( $sessions ) {
	return [
		'is_closed' => false,
		'sessions'  => $sessions,
	];
}

function pw_sample_weekday_lunch_dinner() {
	return pw_sample_operating_day(
		[
			[ 'label' => 'Lunch', 'open_time' => '12:00', 'close_time' => '15:00' ],
			[ 'label' => 'Dinner', 'open_time' => '18:00', 'close_time' => '22:00' ],
		]
	);
}

function pw_sample_weekend_all_day() {
	return pw_sample_operating_day(
		[
			[ 'label' => 'All day', 'open_time' => '11:00', 'close_time' => '23:00' ],
		]
	);
}

function pw_sample_spa_weekday() {
	return pw_sample_operating_day(
		[
			[ 'label' => 'Treatments', 'open_time' => '09:00', 'close_time' => '20:00' ],
		]
	);
}

function pw_install_sample_data() {
	$terms = [
		'pw_bed_type'           => [ 'King', 'Queen', 'Twin' ],
		'pw_view_type'          => [ 'Sea View', 'Garden View' ],
		'pw_meal_period'        => [ 'Breakfast', 'Lunch', 'Dinner' ],
		'pw_treatment_type'     => [ 'Massage', 'Facial' ],
		'pw_av_equipment'       => [ 'Projector', 'Video Conferencing' ],
		'pw_feature_group'      => [ 'Room Amenities' ],
		'pw_nearby_type'        => [ 'Beach', 'Airport', 'Attraction' ],
		'pw_transport_mode'     => [ 'Car', 'Walking' ],
		'pw_experience_category'=> [ 'Wellness', 'Adventure' ],
		'pw_event_type'         => [ 'Conference' ],
	];

	foreach ( $terms as $tax => $names ) {
		foreach ( $names as $name ) {
			pw_sample_ensure_term( $name, $tax );
		}
	}

	$organiser_id = pw_sample_ensure_term( 'Resort Events', 'pw_event_organiser' );
	if ( $organiser_id ) {
		update_term_meta( $organiser_id, 'organiser_url', 'https://example.com/events' );
	}

	$property_id = wp_insert_post(
		[
			'post_type'    => 'pw_property',
			'post_status'  => 'publish',
			'post_title'   => 'Grand Sunset Resort',
			'post_name'    => 'grand-sunset-resort',
			'post_content' => '<p>Welcome to Grand Sunset Resort — a sample property for demonstration.</p>',
			'post_excerpt' => 'Beachfront luxury with world-class dining and spa.',
		],
		true
	);

	if ( is_wp_error( $property_id ) || ! $property_id ) {
		return;
	}

	$property_id = (int) $property_id;

	$meta_strings = [
		'_pw_legal_name'         => 'Grand Sunset Resort LLC',
		'_pw_currency'           => 'USD',
		'_pw_check_in_time'      => '14:00',
		'_pw_check_out_time'     => '11:00',
		'_pw_address_line_1'     => '100 Ocean Drive',
		'_pw_address_line_2'     => '',
		'_pw_city'               => 'Miami Beach',
		'_pw_state'              => 'FL',
		'_pw_postal_code'        => '33139',
		'_pw_country'            => 'United States',
		'_pw_country_code'       => 'US',
		'_pw_timezone'           => 'America/New_York',
		'_pw_google_place_id'    => '',
		'_pw_meta_title'         => 'Grand Sunset Resort | Sample Hotel',
		'_pw_meta_description'   => 'Experience beachfront luxury at Grand Sunset Resort — sample content.',
		'_pw_social_facebook'    => 'https://www.facebook.com/example',
		'_pw_social_instagram'   => 'https://www.instagram.com/example',
		'_pw_social_twitter'     => 'https://twitter.com/example',
		'_pw_social_youtube'     => 'https://www.youtube.com/example',
		'_pw_social_linkedin'    => 'https://www.linkedin.com/company/example',
		'_pw_social_tripadvisor' => 'https://www.tripadvisor.com/example',
	];

	foreach ( $meta_strings as $k => $v ) {
		update_post_meta( $property_id, $k, $v );
	}

	update_post_meta( $property_id, '_pw_star_rating', 5 );
	update_post_meta( $property_id, '_pw_year_established', 1998 );
	update_post_meta( $property_id, '_pw_total_rooms', 120 );
	update_post_meta( $property_id, '_pw_lat', 25.7907 );
	update_post_meta( $property_id, '_pw_lng', -80.1300 );
	update_post_meta( $property_id, '_pw_og_image', 0 );

	update_post_meta(
		$property_id,
		'_pw_contacts',
		[
			[
				'label'    => 'Front Desk',
				'phone'    => '+1-555-123-4567',
				'mobile'   => '',
				'whatsapp' => '',
				'email'    => 'info@grandsunset.example',
			],
		]
	);

	update_post_meta(
		$property_id,
		'_pw_pools',
		[
			[
				'name'        => 'Main Pool',
				'length_m'    => 25,
				'width_m'     => 10,
				'depth_m'     => 1.4,
				'open_time'   => '07:00',
				'close_time'  => '22:00',
				'is_heated'   => true,
				'is_kids'     => false,
				'is_indoor'   => false,
				'is_infinity' => true,
			],
		]
	);

	update_post_meta(
		$property_id,
		'_pw_direct_benefits',
		[
			[
				'title'       => 'Best rate guarantee',
				'description' => 'Book direct for the lowest published rate.',
				'icon'        => 'tag',
			],
			[
				'title'       => 'Flexible cancellation',
				'description' => 'Free cancellation up to 48 hours before arrival.',
				'icon'        => 'calendar',
			],
		]
	);

	update_post_meta(
		$property_id,
		'_pw_certifications',
		[
			[
				'name'   => 'Green Key Eco-Rating',
				'issuer' => 'Green Key Global',
				'year'   => 2024,
				'url'    => 'https://example.com/cert',
			],
		]
	);

	update_post_meta( $property_id, '_pw_sus_solar_power', 'available' );
	update_post_meta( $property_id, '_pw_sus_solar_power_note', 'Solar panels supply part of our energy.' );
	update_post_meta( $property_id, '_pw_sus_recycling_program', 'available' );
	update_post_meta( $property_id, '_pw_acc_wheelchair_accessible', 'available' );
	update_post_meta( $property_id, '_pw_acc_elevator', 'available' );

	$feature_defs = [
		'WiFi'             => 'wifi',
		'Air conditioning' => 'ac',
		'Balcony'          => 'balcony',
		'Mini bar'         => 'minibar',
		'Sea view'         => 'sea-view',
	];

	$feature_group_tid = pw_sample_ensure_term( 'Room Amenities', 'pw_feature_group' );
	$feature_ids       = [];

	foreach ( $feature_defs as $title => $icon ) {
		$fid = wp_insert_post(
			[
				'post_type'   => 'pw_feature',
				'post_status' => 'publish',
				'post_title'  => $title,
			],
			true
		);
		if ( ! is_wp_error( $fid ) && $fid ) {
			$fid = (int) $fid;
			update_post_meta( $fid, '_pw_icon', $icon );
			if ( $feature_group_tid ) {
				wp_set_object_terms( $fid, [ $feature_group_tid ], 'pw_feature_group' );
			}
			$feature_ids[] = $fid;
		}
	}

	$king_tid  = pw_sample_ensure_term( 'King', 'pw_bed_type' );
	$queen_tid = pw_sample_ensure_term( 'Queen', 'pw_bed_type' );
	$sea_tid   = pw_sample_ensure_term( 'Sea View', 'pw_view_type' );
	$garden_tid = pw_sample_ensure_term( 'Garden View', 'pw_view_type' );

	$room_defs = [
		[
			'title'   => 'Deluxe King Room',
			'excerpt' => 'Spacious room with king bed and partial ocean view.',
			'rate_from' => 249,
			'rate_to'   => 329,
			'occ'       => 2,
			'adults'    => 2,
			'children'  => 0,
			'beds'      => [ $king_tid ],
			'views'     => [ $sea_tid ],
			'features'  => array_slice( $feature_ids, 0, 4 ),
			'order'     => 1,
		],
		[
			'title'   => 'Ocean Suite',
			'excerpt' => 'Separate living area and full sea view.',
			'rate_from' => 449,
			'rate_to'   => 599,
			'occ'       => 4,
			'adults'    => 3,
			'children'  => 1,
			'beds'      => [ $king_tid, $queen_tid ],
			'views'     => [ $sea_tid ],
			'features'  => $feature_ids,
			'order'     => 2,
		],
		[
			'title'   => 'Family Room',
			'excerpt' => 'Two queen beds, ideal for families.',
			'rate_from' => 299,
			'rate_to'   => 399,
			'occ'       => 5,
			'adults'    => 2,
			'children'  => 3,
			'beds'      => [ $queen_tid ],
			'views'     => [ $garden_tid ],
			'features'  => array_slice( $feature_ids, 0, 3 ),
			'order'     => 3,
		],
	];

	$room_type_ids = [];

	foreach ( $room_defs as $rd ) {
		$rid = wp_insert_post(
			[
				'post_type'    => 'pw_room_type',
				'post_status'  => 'publish',
				'post_title'   => $rd['title'],
				'post_excerpt' => $rd['excerpt'],
				'post_content' => '<p>Sample room description.</p>',
			],
			true
		);
		if ( is_wp_error( $rid ) || ! $rid ) {
			continue;
		}
		$rid = (int) $rid;
		$room_type_ids[] = $rid;

		update_post_meta( $rid, '_pw_property_id', $property_id );
		update_post_meta( $rid, '_pw_rate_from', (float) $rd['rate_from'] );
		update_post_meta( $rid, '_pw_rate_to', (float) $rd['rate_to'] );
		update_post_meta( $rid, '_pw_max_occupancy', (int) $rd['occ'] );
		update_post_meta( $rid, '_pw_max_adults', (int) $rd['adults'] );
		update_post_meta( $rid, '_pw_max_children', (int) $rd['children'] );
		update_post_meta( $rid, '_pw_size_sqft', 380 );
		update_post_meta( $rid, '_pw_size_sqm', 35 );
		update_post_meta( $rid, '_pw_max_extra_beds', 1 );
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

	$breakfast_tid = pw_sample_ensure_term( 'Breakfast', 'pw_meal_period' );
	$lunch_tid     = pw_sample_ensure_term( 'Lunch', 'pw_meal_period' );
	$dinner_tid    = pw_sample_ensure_term( 'Dinner', 'pw_meal_period' );

	$restaurant_hours = [];
	foreach ( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday' ] as $d ) {
		$restaurant_hours[ $d ] = pw_sample_weekday_lunch_dinner();
	}
	foreach ( [ 'saturday', 'sunday' ] as $d ) {
		$restaurant_hours[ $d ] = pw_sample_weekend_all_day();
	}

	$main_rest_id = wp_insert_post(
		[
			'post_type'    => 'pw_restaurant',
			'post_status'  => 'publish',
			'post_title'   => 'Azure Main Restaurant',
			'post_excerpt' => 'Coastal cuisine with seasonal ingredients.',
			'post_content' => '<p>Fine dining with ocean views.</p>',
		],
		true
	);
	if ( ! is_wp_error( $main_rest_id ) && $main_rest_id ) {
		$main_rest_id = (int) $main_rest_id;
		update_post_meta( $main_rest_id, '_pw_property_id', $property_id );
		update_post_meta( $main_rest_id, '_pw_location', 'Lobby Level' );
		update_post_meta( $main_rest_id, '_pw_cuisine_type', 'Contemporary American' );
		update_post_meta( $main_rest_id, '_pw_seating_capacity', 80 );
		update_post_meta( $main_rest_id, '_pw_reservation_url', 'https://example.com/reserve' );
		update_post_meta( $main_rest_id, '_pw_menu_url', 'https://example.com/menu' );
		update_post_meta( $main_rest_id, '_pw_gallery', [] );
		foreach ( $restaurant_hours as $day => $hours ) {
			update_post_meta( $main_rest_id, '_pw_hours_' . $day, $hours );
		}
		wp_set_object_terms( $main_rest_id, array_filter( [ $breakfast_tid, $lunch_tid, $dinner_tid ] ), 'pw_meal_period' );
	}

	$pool_bar_hours = [];
	foreach ( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] as $d ) {
		$pool_bar_hours[ $d ] = pw_sample_operating_day(
			[ [ 'label' => 'Bar', 'open_time' => '12:00', 'close_time' => '23:00' ] ]
		);
	}

	$pool_bar_id = wp_insert_post(
		[
			'post_type'    => 'pw_restaurant',
			'post_status'  => 'publish',
			'post_title'   => 'Pool Bar',
			'post_excerpt' => 'Drinks and light bites by the pool.',
			'post_content' => '<p>Relax with cocktails poolside.</p>',
		],
		true
	);
	if ( ! is_wp_error( $pool_bar_id ) && $pool_bar_id ) {
		$pool_bar_id = (int) $pool_bar_id;
		update_post_meta( $pool_bar_id, '_pw_property_id', $property_id );
		update_post_meta( $pool_bar_id, '_pw_location', 'Pool Deck' );
		update_post_meta( $pool_bar_id, '_pw_cuisine_type', 'Casual' );
		update_post_meta( $pool_bar_id, '_pw_seating_capacity', 40 );
		update_post_meta( $pool_bar_id, '_pw_gallery', [] );
		foreach ( $pool_bar_hours as $day => $hours ) {
			update_post_meta( $pool_bar_id, '_pw_hours_' . $day, $hours );
		}
		wp_set_object_terms( $pool_bar_id, array_filter( [ $lunch_tid, $dinner_tid ] ), 'pw_meal_period' );
	}

	$massage_tid = pw_sample_ensure_term( 'Massage', 'pw_treatment_type' );
	$facial_tid  = pw_sample_ensure_term( 'Facial', 'pw_treatment_type' );

	$spa_hours = [];
	foreach ( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ] as $d ) {
		$spa_hours[ $d ] = pw_sample_spa_weekday();
	}
	$spa_hours['sunday'] = pw_sample_operating_day(
		[ [ 'label' => 'Treatments', 'open_time' => '10:00', 'close_time' => '18:00' ] ]
	);

	$spa_id = 0;
	$spa_insert = wp_insert_post(
		[
			'post_type'    => 'pw_spa',
			'post_status'  => 'publish',
			'post_title'   => 'Serenity Spa',
			'post_excerpt' => 'Full-service spa and wellness.',
			'post_content' => '<p>Rejuvenate with massages and facials.</p>',
		],
		true
	);
	if ( ! is_wp_error( $spa_insert ) && $spa_insert ) {
		$spa_id = (int) $spa_insert;
		update_post_meta( $spa_id, '_pw_property_id', $property_id );
		update_post_meta( $spa_id, '_pw_booking_url', 'https://example.com/spa-book' );
		update_post_meta( $spa_id, '_pw_menu_url', 'https://example.com/spa-menu' );
		update_post_meta( $spa_id, '_pw_min_age', 16 );
		update_post_meta( $spa_id, '_pw_number_of_treatment_rooms', 6 );
		update_post_meta( $spa_id, '_pw_gallery', [] );
		foreach ( $spa_hours as $day => $hours ) {
			update_post_meta( $spa_id, '_pw_hours_' . $day, $hours );
		}
		wp_set_object_terms( $spa_id, array_filter( [ $massage_tid, $facial_tid ] ), 'pw_treatment_type' );
	}

	$proj_tid = pw_sample_ensure_term( 'Projector', 'pw_av_equipment' );
	$vc_tid   = pw_sample_ensure_term( 'Video Conferencing', 'pw_av_equipment' );

	$meeting_id = 0;
	$meeting_insert = wp_insert_post(
		[
			'post_type'    => 'pw_meeting_room',
			'post_status'  => 'publish',
			'post_title'   => 'Grand Ballroom',
			'post_excerpt' => 'Our largest event space.',
			'post_content' => '<p>Ideal for conferences and galas.</p>',
		],
		true
	);
	if ( ! is_wp_error( $meeting_insert ) && $meeting_insert ) {
		$meeting_id = (int) $meeting_insert;
		update_post_meta( $meeting_id, '_pw_property_id', $property_id );
		update_post_meta( $meeting_id, '_pw_capacity_theatre', 400 );
		update_post_meta( $meeting_id, '_pw_capacity_classroom', 200 );
		update_post_meta( $meeting_id, '_pw_capacity_boardroom', 60 );
		update_post_meta( $meeting_id, '_pw_capacity_ushape', 80 );
		update_post_meta( $meeting_id, '_pw_area_sqft', 5000 );
		update_post_meta( $meeting_id, '_pw_area_sqm', 465 );
		update_post_meta( $meeting_id, '_pw_prefunction_area_sqft', 1200 );
		update_post_meta( $meeting_id, '_pw_prefunction_area_sqm', 111 );
		update_post_meta( $meeting_id, '_pw_natural_light', true );
		update_post_meta( $meeting_id, '_pw_floor_plan', 0 );
		update_post_meta( $meeting_id, '_pw_sales_phone', '+1-555-200-3000' );
		update_post_meta( $meeting_id, '_pw_sales_email', 'events@grandsunset.example' );
		update_post_meta( $meeting_id, '_pw_gallery', [] );
		wp_set_object_terms( $meeting_id, array_filter( [ $proj_tid, $vc_tid ] ), 'pw_av_equipment' );
	}

	$amenity_defs = [
		[ 'title' => 'Outdoor Pool', 'type' => 'facility', 'cat' => 'Leisure', 'compl' => true, 'order' => 1 ],
		[ 'title' => 'Fitness Center', 'type' => 'facility', 'cat' => 'Wellness', 'compl' => true, 'order' => 2 ],
		[ 'title' => 'Concierge', 'type' => 'service', 'cat' => 'Guest services', 'compl' => true, 'order' => 3 ],
		[ 'title' => 'Airport shuttle', 'type' => 'service', 'cat' => 'Transport', 'compl' => false, 'order' => 4 ],
		[ 'title' => 'Room service', 'type' => 'service', 'cat' => 'Dining', 'compl' => false, 'order' => 5 ],
	];

	foreach ( $amenity_defs as $ad ) {
		$aid = wp_insert_post(
			[
				'post_type'   => 'pw_amenity',
				'post_status' => 'publish',
				'post_title'  => $ad['title'],
			],
			true
		);
		if ( is_wp_error( $aid ) || ! $aid ) {
			continue;
		}
		$aid = (int) $aid;
		update_post_meta( $aid, '_pw_property_id', $property_id );
		update_post_meta( $aid, '_pw_type', $ad['type'] );
		update_post_meta( $aid, '_pw_category', $ad['cat'] );
		update_post_meta( $aid, '_pw_icon', '' );
		update_post_meta( $aid, '_pw_description', 'Sample amenity description.' );
		update_post_meta( $aid, '_pw_is_complimentary', (bool) $ad['compl'] );
		update_post_meta( $aid, '_pw_display_order', (int) $ad['order'] );
	}

	$policy_defs = [
		[ 'title' => 'Check-in policy', 'type' => 'Check-in', 'content' => 'Check-in from 3:00 PM. Early check-in subject to availability.', 'highlight' => true ],
		[ 'title' => 'Check-out policy', 'type' => 'Check-out', 'content' => 'Check-out by 11:00 AM. Late check-out may incur a fee.', 'highlight' => false ],
		[ 'title' => 'Cancellation policy', 'type' => 'Cancellation', 'content' => 'Free cancellation up to 48 hours before arrival.', 'highlight' => true ],
	];

	foreach ( $policy_defs as $pd ) {
		$pid = wp_insert_post(
			[
				'post_type'   => 'pw_policy',
				'post_status' => 'publish',
				'post_title'  => $pd['title'],
				'post_content'=> '',
			],
			true
		);
		if ( is_wp_error( $pid ) || ! $pid ) {
			continue;
		}
		$pid = (int) $pid;
		update_post_meta( $pid, '_pw_property_id', $property_id );
		update_post_meta( $pid, '_pw_content', $pd['content'] );
		update_post_meta( $pid, '_pw_display_order', 0 );
		update_post_meta( $pid, '_pw_is_highlighted', (bool) $pd['highlight'] );
		update_post_meta( $pid, '_pw_active', true );
		$type_tid = pw_sample_ensure_term( $pd['type'], 'pw_policy_type' );
		if ( $type_tid ) {
			wp_set_object_terms( $pid, [ $type_tid ], 'pw_policy_type' );
		}
	}

	$faq_defs = [
		[
			'q' => 'Is parking available?',
			'a' => '<p>Yes, valet and self-parking are available for a daily fee.</p>',
		],
		[
			'q' => 'Do you allow pets?',
			'a' => '<p>We welcome pets under 25 lbs in select rooms. Please contact us in advance.</p>',
		],
		[
			'q' => 'Is Wi-Fi included?',
			'a' => '<p>Complimentary high-speed Wi-Fi is available throughout the resort.</p>',
		],
	];

	foreach ( $faq_defs as $i => $fd ) {
		$fqid = wp_insert_post(
			[
				'post_type'   => 'pw_faq',
				'post_status' => 'publish',
				'post_title'  => $fd['q'],
			],
			true
		);
		if ( is_wp_error( $fqid ) || ! $fqid ) {
			continue;
		}
		$fqid = (int) $fqid;
		update_post_meta( $fqid, '_pw_answer', $fd['a'] );
		update_post_meta( $fqid, '_pw_display_order', $i + 1 );
		update_post_meta(
			$fqid,
			'_pw_connected_to',
			[
				[ 'type' => 'pw_property', 'id' => $property_id ],
			]
		);
	}

	$offer_id = wp_insert_post(
		[
			'post_type'    => 'pw_offer',
			'post_status'  => 'publish',
			'post_title'   => 'Summer Escape — 15% Off',
			'post_excerpt' => 'Save on stays of 3+ nights this summer.',
			'post_content' => '<p>Book three nights or more and receive 15% off our best available rate.</p>',
		],
		true
	);
	if ( ! is_wp_error( $offer_id ) && $offer_id ) {
		$offer_id = (int) $offer_id;
		update_post_meta( $offer_id, '_pw_offer_type', 'promotion' );
		update_post_meta( $offer_id, '_pw_parents', [ [ 'type' => 'pw_property', 'id' => $property_id ] ] );
		update_post_meta( $offer_id, '_pw_valid_from', gmdate( 'Y-m-d', strtotime( '+7 days' ) ) );
		update_post_meta( $offer_id, '_pw_valid_to', gmdate( 'Y-m-d', strtotime( '+120 days' ) ) );
		update_post_meta( $offer_id, '_pw_booking_url', 'https://example.com/book' );
		update_post_meta( $offer_id, '_pw_is_featured', true );
		update_post_meta( $offer_id, '_pw_discount_type', 'percentage' );
		update_post_meta( $offer_id, '_pw_discount_value', 15 );
		update_post_meta( $offer_id, '_pw_minimum_stay_nights', 3 );
		update_post_meta( $offer_id, '_pw_display_order', 1 );
		$rt_for_offer = array_slice( $room_type_ids, 0, 2 );
		update_post_meta( $offer_id, '_pw_room_types', array_map( 'intval', $rt_for_offer ) );
	}

	$beach_tid = pw_sample_ensure_term( 'Beach', 'pw_nearby_type' );
	$airport_tid = pw_sample_ensure_term( 'Airport', 'pw_nearby_type' );
	$attr_tid = pw_sample_ensure_term( 'Attraction', 'pw_nearby_type' );
	$car_tid = pw_sample_ensure_term( 'Car', 'pw_transport_mode' );
	$walk_tid = pw_sample_ensure_term( 'Walking', 'pw_transport_mode' );

	$nearby_defs = [
		[ 'title' => 'South Beach', 'km' => 2.5, 'min' => 8, 'type' => $beach_tid, 'trans' => $car_tid ],
		[ 'title' => 'Miami International Airport', 'km' => 18, 'min' => 25, 'type' => $airport_tid, 'trans' => $car_tid ],
		[ 'title' => 'Art Deco Historic District', 'km' => 1.2, 'min' => 15, 'type' => $attr_tid, 'trans' => $walk_tid ],
	];

	foreach ( $nearby_defs as $i => $nd ) {
		$nid = wp_insert_post(
			[
				'post_type'    => 'pw_nearby',
				'post_status'  => 'publish',
				'post_title'   => $nd['title'],
				'post_excerpt' => 'Nearby point of interest.',
				'post_content' => '<p>Sample nearby description.</p>',
			],
			true
		);
		if ( is_wp_error( $nid ) || ! $nid ) {
			continue;
		}
		$nid = (int) $nid;
		update_post_meta( $nid, '_pw_property_id', $property_id );
		update_post_meta( $nid, '_pw_distance_km', (float) $nd['km'] );
		update_post_meta( $nid, '_pw_travel_time_min', (int) $nd['min'] );
		update_post_meta( $nid, '_pw_place_url', 'https://maps.google.com/?q=' . rawurlencode( $nd['title'] ) );
		update_post_meta( $nid, '_pw_display_order', $i + 1 );
		wp_set_object_terms( $nid, array_filter( [ (int) $nd['type'] ] ), 'pw_nearby_type' );
		wp_set_object_terms( $nid, array_filter( [ (int) $nd['trans'] ] ), 'pw_transport_mode' );
	}

	$well_tid = pw_sample_ensure_term( 'Wellness', 'pw_experience_category' );
	$adv_tid  = pw_sample_ensure_term( 'Adventure', 'pw_experience_category' );

	$exp1 = wp_insert_post(
		[
			'post_type'    => 'pw_experience',
			'post_status'  => 'publish',
			'post_title'   => 'Spa Day Package',
			'post_excerpt' => 'Massage, facial, and pool access.',
			'post_content' => '<p>A full day of relaxation.</p>',
		],
		true
	);
	if ( ! is_wp_error( $exp1 ) && $exp1 ) {
		$exp1 = (int) $exp1;
		$exp1_connections = [ [ 'type' => 'pw_property', 'id' => $property_id ] ];
		if ( $spa_id > 0 ) {
			$exp1_connections[] = [ 'type' => 'pw_spa', 'id' => $spa_id ];
		}
		update_post_meta( $exp1, '_pw_connected_to', $exp1_connections );
		update_post_meta( $exp1, '_pw_description', 'Includes 60-minute massage and express facial.' );
		update_post_meta( $exp1, '_pw_duration_hours', 4 );
		update_post_meta( $exp1, '_pw_price_from', 199 );
		update_post_meta( $exp1, '_pw_booking_url', 'https://example.com/spa-package' );
		update_post_meta( $exp1, '_pw_is_complimentary', false );
		update_post_meta( $exp1, '_pw_gallery', [] );
		update_post_meta( $exp1, '_pw_display_order', 1 );
		if ( $well_tid ) {
			wp_set_object_terms( $exp1, [ $well_tid ], 'pw_experience_category' );
		}
	}

	$exp2 = wp_insert_post(
		[
			'post_type'    => 'pw_experience',
			'post_status'  => 'publish',
			'post_title'   => 'Sunset Cruise',
			'post_excerpt' => 'Evening boat tour along the coast.',
			'post_content' => '<p>Enjoy champagne and hors d\'oeuvres at sunset.</p>',
		],
		true
	);
	if ( ! is_wp_error( $exp2 ) && $exp2 ) {
		$exp2 = (int) $exp2;
		update_post_meta(
			$exp2,
			'_pw_connected_to',
			[ [ 'type' => 'pw_property', 'id' => $property_id ] ]
		);
		update_post_meta( $exp2, '_pw_description', 'Depart from the marina at 6:00 PM.' );
		update_post_meta( $exp2, '_pw_duration_hours', 2.5 );
		update_post_meta( $exp2, '_pw_price_from', 89 );
		update_post_meta( $exp2, '_pw_booking_url', 'https://example.com/cruise' );
		update_post_meta( $exp2, '_pw_is_complimentary', false );
		update_post_meta( $exp2, '_pw_gallery', [] );
		update_post_meta( $exp2, '_pw_display_order', 2 );
		if ( $adv_tid ) {
			wp_set_object_terms( $exp2, [ $adv_tid ], 'pw_experience_category' );
		}
	}

	$conf_tid = pw_sample_ensure_term( 'Conference', 'pw_event_type' );

	$event_id = wp_insert_post(
		[
			'post_type'    => 'pw_event',
			'post_status'  => 'publish',
			'post_title'   => 'Annual Hospitality Summit',
			'post_excerpt' => 'Industry conference and networking.',
			'post_content' => '<p>Keynotes, panels, and workshops.</p>',
		],
		true
	);
	if ( ! is_wp_error( $event_id ) && $event_id ) {
		$event_id = (int) $event_id;
		$start    = gmdate( 'Y-m-d H:i:s', strtotime( '+60 days 09:00:00' ) );
		$end      = gmdate( 'Y-m-d H:i:s', strtotime( '+60 days 17:00:00' ) );
		update_post_meta( $event_id, '_pw_property_id', $property_id );
		update_post_meta( $event_id, '_pw_venue_id', $meeting_id > 0 ? $meeting_id : 0 );
		update_post_meta( $event_id, '_pw_description', 'Full-day summit in the Grand Ballroom.' );
		update_post_meta( $event_id, '_pw_start_datetime', $start );
		update_post_meta( $event_id, '_pw_end_datetime', $end );
		update_post_meta( $event_id, '_pw_capacity', 350 );
		update_post_meta( $event_id, '_pw_price_from', 199 );
		update_post_meta( $event_id, '_pw_booking_url', 'https://example.com/summit' );
		update_post_meta( $event_id, '_pw_gallery', [] );
		update_post_meta( $event_id, '_pw_recurrence_rule', '' );
		update_post_meta( $event_id, '_pw_event_status', 'EventScheduled' );
		update_post_meta( $event_id, '_pw_event_attendance_mode', 'OfflineEventAttendanceMode' );
		if ( $conf_tid ) {
			wp_set_object_terms( $event_id, [ $conf_tid ], 'pw_event_type' );
		}
		if ( $organiser_id ) {
			wp_set_object_terms( $event_id, [ $organiser_id ], 'pw_event_organiser' );
		}
	}
}
