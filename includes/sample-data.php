<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'pw_admin_tabs',
	function ( $tabs ) {
		$tabs['data'] = 'Data';
		return $tabs;
	},
	20
);

add_action( 'pw_render_tab_data', 'pw_render_data_tab' );

add_action( 'admin_post_pw_install_sample_data', 'pw_handle_install_sample_data' );
add_action( 'admin_post_pw_remove_sample_data', 'pw_handle_remove_sample_data' );
add_action( 'admin_post_pw_reseed_taxonomies', 'pw_handle_reseed_taxonomies' );
add_action( 'admin_post_pw_purge_plugin_data', 'pw_handle_purge_plugin_data' );

function pw_render_data_tab() {
	pw_strip_sample_flags_from_seed_terms();

	$has_properties = get_posts(
		[
			'post_type'              => 'pw_property',
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'suppress_filters'       => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	if ( isset( $_GET['pw_sample_installed'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Sample data installed successfully.</p></div>';
	}
	if ( isset( $_GET['pw_sample_error'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>Sample data cannot be installed when properties already exist.</p></div>';
	}
	if ( isset( $_GET['pw_sample_removed'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Sample data removed.</p></div>';
	}
	if ( isset( $_GET['pw_taxonomy_reseeded'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Default taxonomy terms were added where they were missing.</p></div>';
	}
	if ( isset( $_GET['pw_plugin_purged'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>All plugin content and taxonomy terms were removed.</p></div>';
	}

	pw_render_import_export_section();

	$flagged_posts = pw_count_sample_flagged_posts_only();
	$flagged_terms = pw_count_sample_flagged_terms_only();
	$flagged       = pw_count_sample_flagged_items();

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">Sample content</div></div>';
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

	if ( $flagged > 0 ) {
		echo '<hr style="margin:1.25em 0;" />';
		echo '<p>' . esc_html(
			sprintf(
				1 === $flagged
					? '%1$d item is tagged as sample data (%2$d posts, %3$d terms).'
					: '%1$d items are tagged as sample data (%2$d posts, %3$d terms).',
				$flagged,
				$flagged_posts,
				$flagged_terms
			)
		) . '</p>';
		$items = pw_list_sample_flagged_items();
		echo '<details style="margin-bottom:1em;"><summary>' . esc_html( 'Tagged items' ) . '</summary>';
		echo '<ul style="list-style:disc;margin:0.5em 0 0 1.5em;max-height:16em;overflow:auto;">';
		foreach ( $items['posts'] as $row ) {
			echo '<li>' . esc_html( sprintf( '[%1$s] %2$s (ID %3$d)', $row['type'], $row['title'], $row['id'] ) ) . '</li>';
		}
		foreach ( $items['terms'] as $row ) {
			echo '<li>' . esc_html( sprintf( '[term:%1$s] %2$s (ID %3$d)', $row['taxonomy'], $row['name'], $row['id'] ) ) . '</li>';
		}
		echo '</ul></details>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'' . esc_js( 'Delete all posts, pages, and plugin content tagged as sample data, and remove sample-only taxonomy terms?' ) . '\');">';
		echo '<input type="hidden" name="action" value="pw_remove_sample_data" />';
		wp_nonce_field( 'pw_remove_sample_data' );
		submit_button( 'Remove sample data', 'delete', 'submit', false );
		echo '</form>';
	}

	echo '</div></div>';

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">Default taxonomy terms</div></div>';
	echo '<div class="pw-card-body">';
	echo '<p>' . esc_html( 'Re-run the default taxonomy term lists (bed types, views, meal periods, etc.): only missing names are created; nothing is renamed or removed.' ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="pw_reseed_taxonomies" />';
	wp_nonce_field( 'pw_reseed_taxonomies' );
	submit_button( 'Reinstall default taxonomy terms', 'secondary', 'submit', false );
	echo '</form>';
	echo '</div></div>';

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">Remove all plugin data</div></div>';
	echo '<div class="pw-card-body">';
	echo '<p><strong>' . esc_html( 'Remove all plugin data' ) . '</strong> — ' . esc_html( 'Deletes every property, room type, and all other Portico hotel content, all terms in plugin taxonomies, clears orphaned post/term meta rows, and resets the taxonomy seed prompt option. Does not delete normal WordPress posts, pages, categories, or tags.' ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'' . esc_js( 'Permanently delete ALL Portico plugin posts and plugin taxonomy terms? This cannot be undone.' ) . '\');">';
	echo '<input type="hidden" name="action" value="pw_purge_plugin_data" />';
	wp_nonce_field( 'pw_purge_plugin_data' );
	submit_button( 'Remove all plugin data', 'delete', 'submit', false );
	echo '</form>';
	echo '</div></div>';
}

function pw_handle_install_sample_data() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorised' );
	}
	check_admin_referer( 'pw_install_sample_data' );

	$existing = get_posts(
		[
			'post_type'              => 'pw_property',
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'suppress_filters'       => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	if ( ! empty( $existing ) ) {
		wp_safe_redirect(
			add_query_arg(
				'pw_sample_error',
				'1',
				admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
			)
		);
		exit;
	}

	pw_install_sample_data();

	wp_safe_redirect(
		add_query_arg(
			'pw_sample_installed',
			'1',
			admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
		)
	);
	exit;
}

function pw_handle_remove_sample_data() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorised' );
	}
	check_admin_referer( 'pw_remove_sample_data' );

	pw_delete_all_sample_data();

	wp_safe_redirect(
		add_query_arg(
			'pw_sample_removed',
			'1',
			admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
		)
	);
	exit;
}

function pw_handle_reseed_taxonomies() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorised' );
	}
	check_admin_referer( 'pw_reseed_taxonomies' );
	pw_seed_taxonomy_terms();
	wp_safe_redirect(
		add_query_arg(
			'pw_taxonomy_reseeded',
			'1',
			admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
		)
	);
	exit;
}

function pw_handle_purge_plugin_data() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorised' );
	}
	check_admin_referer( 'pw_purge_plugin_data' );
	pw_purge_all_plugin_data();
	wp_safe_redirect(
		add_query_arg(
			'pw_plugin_purged',
			'1',
			admin_url( 'admin.php?page=' . pw_admin_page_slug() . '&tab=data' )
		)
	);
	exit;
}

function pw_sample_wp_insert_post( $postarr, $wp_error = false ) {
	$post_id = wp_insert_post( $postarr, $wp_error );
	if ( ! is_wp_error( $post_id ) && $post_id ) {
		pw_sample_flag_post( (int) $post_id );
	}
	return $post_id;
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
	$tid = (int) $inserted['term_id'];
	if ( ! pw_term_name_is_taxonomy_seed_value( $name, $taxonomy ) ) {
		pw_sample_flag_term( $tid );
	}
	return $tid;
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
	pw_strip_sample_flags_from_seed_terms();
	pw_sample_install_lock_open();
	try {
	$base_url = 'https://www.grandsunsetresort.com';

	$terms = [
		'pw_bed_type'            => [ 'King', 'Queen', 'Twin', 'Double' ],
		'pw_view_type'           => [ 'Ocean', 'Garden', 'Partial Ocean', 'City' ],
		'pw_meal_period'         => [ 'Breakfast', 'Brunch', 'Lunch', 'Dinner', 'Late Night' ],
		'pw_treatment_type'      => [ 'Massage', 'Facial', 'Body Wrap', 'Aromatherapy' ],
		'pw_av_equipment'        => [ 'Projector', 'Video Conferencing', 'Microphone', 'Screen', 'PA System' ],
		'pw_feature_group'       => [ 'Bedding', 'Bathroom', 'In-room', 'Entertainment', 'Climate', 'Connectivity', 'Outdoor' ],
		'pw_nearby_type'         => [ 'Beach', 'Airport', 'Attraction', 'Shopping', 'Dining' ],
		'pw_transport_mode'      => [ 'Drive', 'Walk', 'Taxi', 'Shuttle' ],
		'pw_experience_category' => [ 'Wellness', 'Adventure', 'Culinary', 'Water Sports' ],
		'pw_event_type'          => [ 'Conference', 'Wedding', 'Gala' ],
	];

	foreach ( $terms as $tax => $names ) {
		foreach ( $names as $name ) {
			pw_sample_ensure_term( $name, $tax );
		}
	}

	$organiser_id = pw_sample_ensure_term( 'Grand Sunset Group Events', 'pw_event_organiser' );
	if ( $organiser_id ) {
		update_term_meta( $organiser_id, 'organiser_url', $base_url . '/events/' );
	}

	$property_id = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_property',
			'post_status'  => 'publish',
			'post_title'   => 'Grand Sunset Resort',
			'post_name'    => 'grand-sunset-resort',
			'post_content' => '<p>Set on Miami Beach, Grand Sunset Resort pairs Atlantic views with attentive service, a full-service spa, and dining from sunrise to nightcap. Our 120 rooms and suites cater to couples, families, and corporate groups alike.</p><p>Guests enjoy direct beach access, a heated infinity pool, and a dedicated concierge team for reservations and local recommendations.</p>',
			'post_excerpt' => 'Beachfront resort on Miami Beach with spa, fine dining, and flexible event spaces.',
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
		'_pw_meta_title'         => 'Grand Sunset Resort | Miami Beach Oceanfront Hotel',
		'_pw_meta_description'   => 'Oceanfront rooms, spa, pools, and dining on Miami Beach. Book direct for the best rates and flexible cancellation.',
		'_pw_social_facebook'    => 'https://www.facebook.com/grandsunsetresort',
		'_pw_social_instagram'   => 'https://www.instagram.com/grandsunsetresort',
		'_pw_social_twitter'     => 'https://twitter.com/grandsunset',
		'_pw_social_youtube'     => 'https://www.youtube.com/@grandsunsetresort',
		'_pw_social_linkedin'    => 'https://www.linkedin.com/company/grand-sunset-resort',
		'_pw_social_tripadvisor' => 'https://www.tripadvisor.com/Hotel_Review-g34439-dGrandSunset',
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
				'email'    => 'reservations@grandsunsetresort.com',
			],
			[
				'label'    => 'Concierge',
				'phone'    => '+1-305-555-0142',
				'mobile'   => '+1-305-555-0143',
				'whatsapp' => '+13055550143',
				'email'    => 'concierge@grandsunsetresort.com',
			],
		]
	);

	update_post_meta(
		$property_id,
		'_pw_pools',
		[
			[
				'name'        => 'Atlantic Infinity Pool',
				'length_m'    => 28,
				'width_m'     => 12,
				'depth_m'     => 1.35,
				'open_time'   => '07:00',
				'close_time'  => '22:00',
				'is_heated'   => true,
				'is_kids'     => false,
				'is_indoor'   => false,
				'is_infinity' => true,
			],
			[
				'name'        => 'Family Lagoon Pool',
				'length_m'    => 18,
				'width_m'     => 14,
				'depth_m'     => 1.1,
				'open_time'   => '08:00',
				'close_time'  => '20:00',
				'is_heated'   => true,
				'is_kids'     => true,
				'is_indoor'   => false,
				'is_infinity' => false,
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
				'url'    => 'https://www.greenkeyglobal.com/',
			],
		]
	);

	update_post_meta( $property_id, '_pw_sus_solar_power', 'available' );
	update_post_meta( $property_id, '_pw_sus_solar_power_note', 'Rooftop solar supplements common-area electricity.' );
	update_post_meta( $property_id, '_pw_sus_recycling_program', 'available' );
	update_post_meta( $property_id, '_pw_sus_local_food_sourcing', 'available' );
	update_post_meta( $property_id, '_pw_acc_wheelchair_accessible', 'available' );
	update_post_meta( $property_id, '_pw_acc_elevator', 'available' );
	update_post_meta( $property_id, '_pw_acc_accessible_room_available', 'available' );

	$feature_defs = [
		[ 'title' => 'High-speed Wi-Fi', 'icon' => 'wifi', 'group' => 'Connectivity' ],
		[ 'title' => 'Individually controlled AC', 'icon' => 'ac', 'group' => 'Climate' ],
		[ 'title' => 'Private balcony or terrace', 'icon' => 'balcony', 'group' => 'Outdoor' ],
		[ 'title' => 'Nespresso and kettle', 'icon' => 'coffee', 'group' => 'In-room' ],
		[ 'title' => '55-inch smart TV', 'icon' => 'tv', 'group' => 'Entertainment' ],
		[ 'title' => 'Rain shower', 'icon' => 'shower', 'group' => 'Bathroom' ],
		[ 'title' => 'Premium bedding', 'icon' => 'bed', 'group' => 'Bedding' ],
		[ 'title' => 'In-room safe', 'icon' => 'safe', 'group' => 'In-room' ],
		[ 'title' => 'Blackout drapes', 'icon' => 'drapes', 'group' => 'Bedding' ],
	];

	$feature_ids = [];

	foreach ( $feature_defs as $fd ) {
		$gid = pw_sample_ensure_term( $fd['group'], 'pw_feature_group' );
		$fid = pw_sample_wp_insert_post(
			[
				'post_type'   => 'pw_feature',
				'post_status' => 'publish',
				'post_title'  => $fd['title'],
			],
			true
		);
		if ( ! is_wp_error( $fid ) && $fid ) {
			$fid = (int) $fid;
			update_post_meta( $fid, '_pw_icon', $fd['icon'] );
			if ( $gid ) {
				wp_set_object_terms( $fid, [ $gid ], 'pw_feature_group' );
			}
			$feature_ids[] = $fid;
		}
	}

	$king_tid    = pw_sample_ensure_term( 'King', 'pw_bed_type' );
	$queen_tid   = pw_sample_ensure_term( 'Queen', 'pw_bed_type' );
	$twin_tid    = pw_sample_ensure_term( 'Twin', 'pw_bed_type' );
	$double_tid  = pw_sample_ensure_term( 'Double', 'pw_bed_type' );
	$ocean_tid   = pw_sample_ensure_term( 'Ocean', 'pw_view_type' );
	$partial_tid = pw_sample_ensure_term( 'Partial Ocean', 'pw_view_type' );
	$garden_tid  = pw_sample_ensure_term( 'Garden', 'pw_view_type' );
	$city_tid    = pw_sample_ensure_term( 'City', 'pw_view_type' );

	$room_defs = [
		[
			'title'     => 'Deluxe King, Partial Ocean',
			'excerpt'   => 'King bed, walk-in rain shower, and a partial Atlantic view.',
			'content'   => '<p>Floor-to-ceiling windows face northeast over the ocean. Includes workspace, sitting area, and evening turndown on request.</p>',
			'rate_from' => 289,
			'rate_to'   => 379,
			'occ'       => 2,
			'adults'    => 2,
			'children'  => 0,
			'beds'      => [ $king_tid ],
			'views'     => [ $partial_tid ],
			'features'  => array_slice( $feature_ids, 0, 6 ),
			'sqft'      => 385,
			'sqm'       => 36,
			'order'     => 1,
			'meta_title'=> 'Deluxe King Room | Grand Sunset Resort',
			'meta_desc' => 'Partial ocean views, king bed, and rain shower on Miami Beach.',
		],
		[
			'title'     => 'Oceanfront One-Bedroom Suite',
			'excerpt'   => 'Separate living and dining with full ocean frontage.',
			'content'   => '<p>Corner suite with wraparound balcony, powder room, and soaking tub. Living room sofa converts for one child under 12 with advance notice.</p>',
			'rate_from' => 529,
			'rate_to'   => 719,
			'occ'       => 4,
			'adults'    => 3,
			'children'  => 1,
			'beds'      => [ $king_tid, $queen_tid ],
			'views'     => [ $ocean_tid ],
			'features'  => $feature_ids,
			'sqft'      => 720,
			'sqm'       => 67,
			'order'     => 2,
			'meta_title'=> 'Oceanfront Suite Miami Beach | Grand Sunset Resort',
			'meta_desc' => 'Spacious oceanfront suite with balcony, living room, and premium amenities.',
		],
		[
			'title'     => 'Garden Wing Family Room',
			'excerpt'   => 'Two queen beds overlooking the palm garden—ideal for families.',
			'content'   => '<p>Quiet wing facing the interior garden and pool. Connecting rooms available. Cribs and rollaways subject to availability.</p>',
			'rate_from' => 319,
			'rate_to'   => 429,
			'occ'       => 5,
			'adults'    => 2,
			'children'  => 3,
			'beds'      => [ $queen_tid ],
			'views'     => [ $garden_tid ],
			'features'  => array_slice( $feature_ids, 0, 7 ),
			'sqft'      => 410,
			'sqm'       => 38,
			'order'     => 3,
			'meta_title'=> 'Family Room Miami Beach | Grand Sunset Resort',
			'meta_desc' => 'Two queens, garden views, and space for up to five guests.',
		],
		[
			'title'     => 'Classic Twin City View',
			'excerpt'   => 'Two twin beds and skyline views—perfect for colleagues or friends.',
			'content'   => '<p>High-floor rooms facing Collins Avenue and the city. Compact footprint with the same premium bath products and Wi-Fi as our deluxe category.</p>',
			'rate_from' => 219,
			'rate_to'   => 279,
			'occ'       => 2,
			'adults'    => 2,
			'children'  => 0,
			'beds'      => [ $twin_tid ],
			'views'     => [ $city_tid ],
			'features'  => array_slice( $feature_ids, 2, 6 ),
			'sqft'      => 310,
			'sqm'       => 29,
			'order'     => 4,
			'meta_title'=> 'Twin Room City View | Grand Sunset Resort',
			'meta_desc' => 'Two twin beds, city views, near the beach in Miami Beach.',
		],
	];

	$room_type_ids = [];

	foreach ( $room_defs as $rd ) {
		$rid = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_room_type',
				'post_status'  => 'publish',
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
		$room_type_ids[] = $rid;

		update_post_meta( $rid, '_pw_property_id', $property_id );
		update_post_meta( $rid, '_pw_rate_from', (float) $rd['rate_from'] );
		update_post_meta( $rid, '_pw_rate_to', (float) $rd['rate_to'] );
		update_post_meta( $rid, '_pw_max_occupancy', (int) $rd['occ'] );
		update_post_meta( $rid, '_pw_max_adults', (int) $rd['adults'] );
		update_post_meta( $rid, '_pw_max_children', (int) $rd['children'] );
		update_post_meta( $rid, '_pw_size_sqft', (int) $rd['sqft'] );
		update_post_meta( $rid, '_pw_size_sqm', (int) $rd['sqm'] );
		update_post_meta( $rid, '_pw_max_extra_beds', 1 );
		update_post_meta( $rid, '_pw_display_order', (int) $rd['order'] );
		update_post_meta( $rid, '_pw_features', array_map( 'intval', $rd['features'] ) );
		update_post_meta( $rid, '_pw_gallery', [] );
		update_post_meta( $rid, '_pw_meta_title', $rd['meta_title'] );
		update_post_meta( $rid, '_pw_meta_description', $rd['meta_desc'] );

		if ( ! empty( $rd['beds'] ) ) {
			wp_set_object_terms( $rid, array_filter( array_map( 'intval', $rd['beds'] ) ), 'pw_bed_type' );
		}
		if ( ! empty( $rd['views'] ) ) {
			wp_set_object_terms( $rid, array_filter( array_map( 'intval', $rd['views'] ) ), 'pw_view_type' );
		}
	}

	$breakfast_tid = pw_sample_ensure_term( 'Breakfast', 'pw_meal_period' );
	$brunch_tid    = pw_sample_ensure_term( 'Brunch', 'pw_meal_period' );
	$lunch_tid     = pw_sample_ensure_term( 'Lunch', 'pw_meal_period' );
	$dinner_tid    = pw_sample_ensure_term( 'Dinner', 'pw_meal_period' );
	$late_tid      = pw_sample_ensure_term( 'Late Night', 'pw_meal_period' );

	$restaurant_hours = [];
	foreach ( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday' ] as $d ) {
		$restaurant_hours[ $d ] = pw_sample_weekday_lunch_dinner();
	}
	foreach ( [ 'saturday', 'sunday' ] as $d ) {
		$restaurant_hours[ $d ] = pw_sample_weekend_all_day();
	}

	$main_rest_id = 0;
	$main_ins     = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_restaurant',
			'post_status'  => 'publish',
			'post_title'   => 'Azure Main Restaurant',
			'post_excerpt' => 'Coastal cuisine with seasonal ingredients.',
			'post_content' => '<p>Chef Amara Voss leads a kitchen focused on Florida seafood, citrus, and Latin accents. Floor-to-ceiling glass faces the Atlantic; jackets are welcome but never required.</p>',
		],
		true
	);
	if ( ! is_wp_error( $main_ins ) && $main_ins ) {
		$main_rest_id = (int) $main_ins;
		update_post_meta( $main_rest_id, '_pw_property_id', $property_id );
		update_post_meta( $main_rest_id, '_pw_location', 'Lobby level, ocean side' );
		update_post_meta( $main_rest_id, '_pw_cuisine_type', 'Coastal American & Floridian' );
		update_post_meta( $main_rest_id, '_pw_seating_capacity', 86 );
		update_post_meta( $main_rest_id, '_pw_reservation_url', $base_url . '/dining/azure-reservations/' );
		update_post_meta( $main_rest_id, '_pw_menu_url', $base_url . '/dining/azure-menu/' );
		update_post_meta( $main_rest_id, '_pw_gallery', [] );
		update_post_meta( $main_rest_id, '_pw_meta_title', 'Azure Restaurant | Fine Dining Miami Beach' );
		update_post_meta( $main_rest_id, '_pw_meta_description', 'Ocean-view dining at Grand Sunset Resort. Dinner, brunch, and chef\'s tasting menus.' );
		foreach ( $restaurant_hours as $day => $hours ) {
			update_post_meta( $main_rest_id, '_pw_hours_' . $day, $hours );
		}
		wp_set_object_terms( $main_rest_id, array_filter( [ $breakfast_tid, $brunch_tid, $lunch_tid, $dinner_tid ] ), 'pw_meal_period' );
	}

	$pool_bar_hours = [];
	foreach ( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] as $d ) {
		$pool_bar_hours[ $d ] = pw_sample_operating_day(
			[ [ 'label' => 'Bar', 'open_time' => '12:00', 'close_time' => '23:00' ] ]
		);
	}

	$pool_bar_id = 0;
	$pool_ins    = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_restaurant',
			'post_status'  => 'publish',
			'post_title'   => 'Tide & Tan Pool Bar',
			'post_excerpt' => 'Frozen drinks, ceviche, and sunset views by the infinity pool.',
			'post_content' => '<p>Open-air bar with cabana service. Happy hour weekdays 4–6 PM. Swimwear welcome; cover-ups appreciated after 6 PM.</p>',
		],
		true
	);
	if ( ! is_wp_error( $pool_ins ) && $pool_ins ) {
		$pool_bar_id = (int) $pool_ins;
		update_post_meta( $pool_bar_id, '_pw_property_id', $property_id );
		update_post_meta( $pool_bar_id, '_pw_location', 'Pool deck, tower wing' );
		update_post_meta( $pool_bar_id, '_pw_cuisine_type', 'Casual Latin & bar bites' );
		update_post_meta( $pool_bar_id, '_pw_seating_capacity', 52 );
		update_post_meta( $pool_bar_id, '_pw_reservation_url', $base_url . '/dining/pool-bar/' );
		update_post_meta( $pool_bar_id, '_pw_menu_url', $base_url . '/dining/pool-bar-menu/' );
		update_post_meta( $pool_bar_id, '_pw_gallery', [] );
		update_post_meta( $pool_bar_id, '_pw_meta_title', 'Pool Bar | Grand Sunset Resort' );
		update_post_meta( $pool_bar_id, '_pw_meta_description', 'Poolside cocktails and light fare on Miami Beach.' );
		foreach ( $pool_bar_hours as $day => $hours ) {
			update_post_meta( $pool_bar_id, '_pw_hours_' . $day, $hours );
		}
		wp_set_object_terms( $pool_bar_id, array_filter( [ $lunch_tid, $dinner_tid, $late_tid ] ), 'pw_meal_period' );
	}

	$coral_hours = [];
	foreach ( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ] as $d ) {
		$coral_hours[ $d ] = pw_sample_operating_day(
			[
				[ 'label' => 'Lunch', 'open_time' => '11:30', 'close_time' => '15:00' ],
				[ 'label' => 'Dinner', 'open_time' => '17:30', 'close_time' => '22:30' ],
			]
		);
	}

	$coral_grill_id = 0;
	$coral_ins      = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_restaurant',
			'post_status'  => 'publish',
			'post_title'   => 'Coral Grill & Terrace',
			'post_excerpt' => 'Wood-fired steaks, salads, and craft beer on the garden terrace.',
			'post_content' => '<p>Relaxed lunch and dinner spot overlooking the palm court. Kids\' menu available; outdoor heaters in winter months.</p>',
		],
		true
	);
	if ( ! is_wp_error( $coral_ins ) && $coral_ins ) {
		$coral_grill_id = (int) $coral_ins;
		update_post_meta( $coral_grill_id, '_pw_property_id', $property_id );
		update_post_meta( $coral_grill_id, '_pw_location', 'Garden terrace, north wing' );
		update_post_meta( $coral_grill_id, '_pw_cuisine_type', 'Grill & American bistro' );
		update_post_meta( $coral_grill_id, '_pw_seating_capacity', 64 );
		update_post_meta( $coral_grill_id, '_pw_reservation_url', $base_url . '/dining/coral-grill/' );
		update_post_meta( $coral_grill_id, '_pw_menu_url', $base_url . '/dining/coral-grill-menu/' );
		update_post_meta( $coral_grill_id, '_pw_gallery', [] );
		update_post_meta( $coral_grill_id, '_pw_meta_title', 'Coral Grill Miami Beach | Grand Sunset Resort' );
		update_post_meta( $coral_grill_id, '_pw_meta_description', 'Terrace dining for lunch and dinner at Grand Sunset Resort.' );
		foreach ( $coral_hours as $day => $hours ) {
			update_post_meta( $coral_grill_id, '_pw_hours_' . $day, $hours );
		}
		wp_set_object_terms( $coral_grill_id, array_filter( [ $lunch_tid, $dinner_tid, $brunch_tid ] ), 'pw_meal_period' );
	}

	$massage_tid = pw_sample_ensure_term( 'Massage', 'pw_treatment_type' );
	$facial_tid  = pw_sample_ensure_term( 'Facial', 'pw_treatment_type' );
	$wrap_tid    = pw_sample_ensure_term( 'Body Wrap', 'pw_treatment_type' );
	$aroma_tid   = pw_sample_ensure_term( 'Aromatherapy', 'pw_treatment_type' );

	$spa_hours = [];
	foreach ( [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ] as $d ) {
		$spa_hours[ $d ] = pw_sample_spa_weekday();
	}
	$spa_hours['sunday'] = pw_sample_operating_day(
		[ [ 'label' => 'Treatments', 'open_time' => '10:00', 'close_time' => '18:00' ] ]
	);

	$spa_id = 0;
	$spa_insert = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_spa',
			'post_status'  => 'publish',
			'post_title'   => 'Serenity Spa by Grand Sunset',
			'post_excerpt' => 'Eight treatment suites, couples\' rooms, and a relaxation lounge.',
			'post_content' => '<p>Our therapists use Biologique Recherche and local botanical oils. Arrive 20 minutes early to use the steam room and herbal tea lounge. Spa guests receive same-day pool access when booking a 50-minute service or longer.</p>',
		],
		true
	);
	if ( ! is_wp_error( $spa_insert ) && $spa_insert ) {
		$spa_id = (int) $spa_insert;
		update_post_meta( $spa_id, '_pw_property_id', $property_id );
		update_post_meta( $spa_id, '_pw_booking_url', $base_url . '/spa/book/' );
		update_post_meta( $spa_id, '_pw_menu_url', $base_url . '/spa/services/' );
		update_post_meta( $spa_id, '_pw_min_age', 16 );
		update_post_meta( $spa_id, '_pw_number_of_treatment_rooms', 8 );
		update_post_meta( $spa_id, '_pw_gallery', [] );
		update_post_meta( $spa_id, '_pw_meta_title', 'Serenity Spa | Grand Sunset Resort Miami Beach' );
		update_post_meta( $spa_id, '_pw_meta_description', 'Massages, facials, and body rituals. Book spa treatments online.' );
		foreach ( $spa_hours as $day => $hours ) {
			update_post_meta( $spa_id, '_pw_hours_' . $day, $hours );
		}
		wp_set_object_terms( $spa_id, array_filter( [ $massage_tid, $facial_tid, $wrap_tid, $aroma_tid ] ), 'pw_treatment_type' );
	}

	$proj_tid = pw_sample_ensure_term( 'Projector', 'pw_av_equipment' );
	$vc_tid   = pw_sample_ensure_term( 'Video Conferencing', 'pw_av_equipment' );
	$mic_tid  = pw_sample_ensure_term( 'Microphone', 'pw_av_equipment' );
	$pa_tid   = pw_sample_ensure_term( 'PA System', 'pw_av_equipment' );
	$screen_tid = pw_sample_ensure_term( 'Screen', 'pw_av_equipment' );

	$meeting_id = 0;
	$meeting_insert = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_meeting_room',
			'post_status'  => 'publish',
			'post_title'   => 'Atlantic Grand Ballroom',
			'post_excerpt' => 'Pillar-free ballroom for galas, conferences, and weddings up to 400 guests.',
			'post_content' => '<p>14-foot ceilings, divisible air walls, and a dedicated loading dock. Includes built-in stage, bridal suite access, and dedicated AV technician for contracted events.</p>',
		],
		true
	);
	if ( ! is_wp_error( $meeting_insert ) && $meeting_insert ) {
		$meeting_id = (int) $meeting_insert;
		update_post_meta( $meeting_id, '_pw_property_id', $property_id );
		update_post_meta( $meeting_id, '_pw_capacity_theatre', 420 );
		update_post_meta( $meeting_id, '_pw_capacity_classroom', 220 );
		update_post_meta( $meeting_id, '_pw_capacity_boardroom', 72 );
		update_post_meta( $meeting_id, '_pw_capacity_ushape', 96 );
		update_post_meta( $meeting_id, '_pw_area_sqft', 5200 );
		update_post_meta( $meeting_id, '_pw_area_sqm', 483 );
		update_post_meta( $meeting_id, '_pw_prefunction_area_sqft', 1400 );
		update_post_meta( $meeting_id, '_pw_prefunction_area_sqm', 130 );
		update_post_meta( $meeting_id, '_pw_natural_light', true );
		update_post_meta( $meeting_id, '_pw_floor_plan', 0 );
		update_post_meta( $meeting_id, '_pw_sales_phone', '+1-305-555-0200' );
		update_post_meta( $meeting_id, '_pw_sales_mobile', '+1-305-555-0201' );
		update_post_meta( $meeting_id, '_pw_sales_whatsapp', '+13055550201' );
		update_post_meta( $meeting_id, '_pw_sales_email', 'events@grandsunsetresort.com' );
		update_post_meta( $meeting_id, '_pw_gallery', [] );
		update_post_meta( $meeting_id, '_pw_meta_title', 'Grand Ballroom Events | Grand Sunset Resort' );
		update_post_meta( $meeting_id, '_pw_meta_description', 'Miami Beach ballroom for conferences, weddings, and galas.' );
		wp_set_object_terms( $meeting_id, array_filter( [ $proj_tid, $vc_tid, $pa_tid, $screen_tid ] ), 'pw_av_equipment' );
	}

	$boardroom_id = 0;
	$board_ins    = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_meeting_room',
			'post_status'  => 'publish',
			'post_title'   => 'Boardroom 12A — Horizon',
			'post_excerpt' => 'Executive boardroom on the 12th floor with panoramic ocean views.',
			'post_content' => '<p>Seats 18 at a single mahogany table. Includes 85-inch display, wireless presentation, and privacy film on demand. Half-day and full-day catering packages available.</p>',
		],
		true
	);
	if ( ! is_wp_error( $board_ins ) && $board_ins ) {
		$boardroom_id = (int) $board_ins;
		update_post_meta( $boardroom_id, '_pw_property_id', $property_id );
		update_post_meta( $boardroom_id, '_pw_capacity_theatre', 0 );
		update_post_meta( $boardroom_id, '_pw_capacity_classroom', 0 );
		update_post_meta( $boardroom_id, '_pw_capacity_boardroom', 18 );
		update_post_meta( $boardroom_id, '_pw_capacity_ushape', 0 );
		update_post_meta( $boardroom_id, '_pw_area_sqft', 780 );
		update_post_meta( $boardroom_id, '_pw_area_sqm', 72 );
		update_post_meta( $boardroom_id, '_pw_prefunction_area_sqft', 0 );
		update_post_meta( $boardroom_id, '_pw_prefunction_area_sqm', 0 );
		update_post_meta( $boardroom_id, '_pw_natural_light', true );
		update_post_meta( $boardroom_id, '_pw_floor_plan', 0 );
		update_post_meta( $boardroom_id, '_pw_sales_phone', '+1-305-555-0200' );
		update_post_meta( $boardroom_id, '_pw_sales_email', 'groups@grandsunsetresort.com' );
		update_post_meta( $boardroom_id, '_pw_gallery', [] );
		update_post_meta( $boardroom_id, '_pw_meta_title', 'Executive Boardroom | Grand Sunset Resort' );
		wp_set_object_terms( $boardroom_id, array_filter( [ $vc_tid, $screen_tid, $mic_tid ] ), 'pw_av_equipment' );
	}

	$amenity_defs = [
		[ 'title' => 'Heated infinity pool', 'type' => 'facility', 'cat' => 'Pools & beach', 'compl' => true, 'order' => 1, 'icon' => 'pool', 'desc' => 'Adults-oriented infinity edge facing the Atlantic; towel service 7 AM–10 PM.' ],
		[ 'title' => 'Family lagoon pool', 'type' => 'facility', 'cat' => 'Pools & beach', 'compl' => true, 'order' => 2, 'icon' => 'pool-family', 'desc' => 'Shallow entry, lifeguard on duty weekends, adjacent splash zone.' ],
		[ 'title' => '24-hour fitness studio', 'type' => 'facility', 'cat' => 'Wellness', 'compl' => true, 'order' => 3, 'icon' => 'gym', 'desc' => 'Peloton bikes, free weights, yoga mats, and chilled towels.' ],
		[ 'title' => 'Private beach club', 'type' => 'facility', 'cat' => 'Pools & beach', 'compl' => true, 'order' => 4, 'icon' => 'beach', 'desc' => 'Chaise lounges, umbrellas, and attendants; seasonal water sports desk.' ],
		[ 'title' => 'Concierge desk', 'type' => 'service', 'cat' => 'Guest services', 'compl' => true, 'order' => 5, 'icon' => 'concierge', 'desc' => 'Restaurant reservations, theater tickets, yacht charters, and babysitting referrals.' ],
		[ 'title' => 'House car (3-mile radius)', 'type' => 'service', 'cat' => 'Transport', 'compl' => true, 'order' => 6, 'icon' => 'car', 'desc' => 'Electric SUV on a first-come basis; inquire at the front desk.' ],
		[ 'title' => 'Miami Airport transfer', 'type' => 'service', 'cat' => 'Transport', 'compl' => false, 'order' => 7, 'icon' => 'shuttle', 'desc' => 'Private sedan or SUV from MIA or FLL; book 24 hours ahead.' ],
		[ 'title' => 'In-room dining', 'type' => 'amenity', 'cat' => 'Dining', 'compl' => false, 'order' => 8, 'icon' => 'room-service', 'desc' => 'Full Azure and Coral menus until midnight; breakfast from 6:30 AM.' ],
	];

	foreach ( $amenity_defs as $ad ) {
		$aid = pw_sample_wp_insert_post(
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
		update_post_meta( $aid, '_pw_icon', $ad['icon'] );
		update_post_meta( $aid, '_pw_description', $ad['desc'] );
		update_post_meta( $aid, '_pw_is_complimentary', (bool) $ad['compl'] );
		update_post_meta( $aid, '_pw_display_order', (int) $ad['order'] );
	}

	$policy_defs = [
		[ 'title' => 'Check-in & arrival', 'type' => 'Check-in', 'content' => 'Check-in begins at 4:00 PM. Government-issued photo ID and the credit card used to guarantee the reservation are required. Early check-in may be arranged after 12:00 PM based on occupancy.', 'highlight' => true, 'order' => 1 ],
		[ 'title' => 'Check-out & departure', 'type' => 'Check-out', 'content' => 'Check-out is 11:00 AM. Express checkout is available on your in-room tablet or at the front desk. Late check-out until 2:00 PM may be purchased subject to availability.', 'highlight' => false, 'order' => 2 ],
		[ 'title' => 'Cancellation & no-show', 'type' => 'Cancellation', 'content' => 'Flexible rate: cancel by 3:00 PM hotel time two days before arrival without penalty. Non-refundable and advance-purchase rates cannot be cancelled. No-shows are charged the first night\'s room and tax.', 'highlight' => true, 'order' => 3 ],
		[ 'title' => 'Pet policy', 'type' => 'Pet', 'content' => 'Dogs up to 40 lbs are welcome in designated garden-wing rooms for a nightly fee. Maximum two pets per room; vaccination records required. Pets are not permitted in Azure Restaurant or the spa lounge.', 'highlight' => false, 'order' => 4 ],
		[ 'title' => 'Children & extra guests', 'type' => 'Child', 'content' => 'Children 17 and under stay free in existing bedding. Rollaway beds and cribs are available for a fee. Occupancy limits per room category are enforced for safety and fire code compliance.', 'highlight' => false, 'order' => 5 ],
		[ 'title' => 'Payment & incidentals', 'type' => 'Payment', 'content' => 'We accept major credit cards and debit cards. A nightly incidental hold of $150 applies at check-in. Resort fee includes pool towels, fitness access, and two bottled waters daily.', 'highlight' => false, 'order' => 6 ],
	];

	foreach ( $policy_defs as $pd ) {
		$pid = pw_sample_wp_insert_post(
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
		update_post_meta( $pid, '_pw_display_order', (int) $pd['order'] );
		update_post_meta( $pid, '_pw_is_highlighted', (bool) $pd['highlight'] );
		update_post_meta( $pid, '_pw_active', true );
		$type_tid = pw_sample_ensure_term( $pd['type'], 'pw_policy_type' );
		if ( $type_tid ) {
			wp_set_object_terms( $pid, [ $type_tid ], 'pw_policy_type' );
		}
	}

	$faq_link_targets = [
		'property'  => [ 'type' => 'pw_property', 'id' => $property_id ],
		'spa'       => [ 'type' => 'pw_spa', 'id' => $spa_id ],
		'azure'     => [ 'type' => 'pw_restaurant', 'id' => $main_rest_id ],
		'pool_bar'  => [ 'type' => 'pw_restaurant', 'id' => $pool_bar_id ],
		'coral'     => [ 'type' => 'pw_restaurant', 'id' => $coral_grill_id ],
		'ballroom'  => [ 'type' => 'pw_meeting_room', 'id' => $meeting_id ],
		'boardroom' => [ 'type' => 'pw_meeting_room', 'id' => $boardroom_id ],
	];

	$faq_defs = [
		[
			'q' => 'What time is check-in and check-out?',
			'a' => '<p>Check-in starts at 4:00 PM; check-out is by 11:00 AM. Early check-in may be available from noon based on occupancy.</p>',
			'link' => [ 'property' ],
		],
		[
			'q' => 'Is parking available at the resort?',
			'a' => '<p>Overnight valet parking is available in our covered garage. Self-parking is offered one block north at a partner garage with validated rates for guests.</p>',
			'link' => [ 'property' ],
		],
		[
			'q' => 'Do you welcome dogs?',
			'a' => '<p>Yes — dogs up to 40 lbs are allowed in designated garden-wing rooms for a nightly fee. Please review our pet policy for dining and spa restrictions.</p>',
			'link' => [ 'property' ],
		],
		[
			'q' => 'Is Wi-Fi included in the resort fee?',
			'a' => '<p>Complimentary high-speed Wi-Fi is available throughout guest rooms, pools, and meeting spaces.</p>',
			'link' => [ 'property' ],
		],
		[
			'q' => 'Does Azure Restaurant require reservations?',
			'a' => '<p>Reservations are strongly recommended for dinner and weekend brunch. Walk-ins are seated when tables are available.</p>',
			'link' => [ 'property', 'azure' ],
		],
		[
			'q' => 'What is the dress code at Azure?',
			'a' => '<p>Resort elegant: collared shirts or dresses for dinner; casual resort wear for breakfast and brunch. Beach cover-ups are welcome at lunch only on the terrace.</p>',
			'link' => [ 'azure' ],
		],
		[
			'q' => 'Can I order poolside service from Tide & Tan?',
			'a' => '<p>Yes — scan the QR code on your chaise to order drinks and light bites. Cabana guests may request full Coral Grill lunch delivery.</p>',
			'link' => [ 'pool_bar' ],
		],
		[
			'q' => 'How do I book a massage at Serenity Spa?',
			'a' => '<p>Use the online spa menu to choose your treatment and preferred time, or call the spa desk from your room phone. Arrive 20 minutes early to enjoy the steam room.</p>',
			'link' => [ 'spa' ],
		],
		[
			'q' => 'What AV is included in the Atlantic Grand Ballroom?',
			'a' => '<p>Standard rental includes house sound, two wireless microphones, and a 16:9 projection package. Dedicated technicians are assigned for weddings and conferences over 150 guests.</p>',
			'link' => [ 'ballroom' ],
		],
		[
			'q' => 'Can I host a board meeting in Horizon boardroom?',
			'a' => '<p>Boardroom 12A seats up to 18 and includes video conferencing and catering from Coral Grill or Azure. Half-day minimum applies Monday–Friday.</p>',
			'link' => [ 'boardroom', 'property' ],
		],
	];

	foreach ( $faq_defs as $i => $fd ) {
		$conn = [];
		foreach ( $fd['link'] as $key ) {
			if ( ! isset( $faq_link_targets[ $key ] ) || (int) $faq_link_targets[ $key ]['id'] <= 0 ) {
				continue;
			}
			$conn[] = [
				'type' => $faq_link_targets[ $key ]['type'],
				'id'   => (int) $faq_link_targets[ $key ]['id'],
			];
		}
		if ( empty( $conn ) ) {
			$conn[] = [ 'type' => 'pw_property', 'id' => $property_id ];
		}
		$fqid = pw_sample_wp_insert_post(
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
		update_post_meta( $fqid, '_pw_connected_to', $conn );
	}

	$offer_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_offer',
			'post_status'  => 'publish',
			'post_title'   => 'Stay Longer — Fourth Night on Us',
			'post_excerpt' => 'Book three consecutive paid nights and receive the fourth night complimentary.',
			'post_content' => '<p>Valid on oceanfront and garden categories. Blackout dates apply during holidays and special events. Must book direct on our website or reservations line.</p>',
		],
		true
	);
	if ( ! is_wp_error( $offer_ins ) && $offer_ins ) {
		$offer_id = (int) $offer_ins;
		update_post_meta( $offer_id, '_pw_offer_type', 'promotion' );
		update_post_meta( $offer_id, '_pw_parents', [ [ 'type' => 'pw_property', 'id' => $property_id ] ] );
		update_post_meta( $offer_id, '_pw_valid_from', gmdate( 'Y-m-d', strtotime( '+7 days' ) ) );
		update_post_meta( $offer_id, '_pw_valid_to', gmdate( 'Y-m-d', strtotime( '+120 days' ) ) );
		update_post_meta( $offer_id, '_pw_booking_url', $base_url . '/offers/stay-longer/' );
		update_post_meta( $offer_id, '_pw_is_featured', true );
		update_post_meta( $offer_id, '_pw_discount_type', 'value_add' );
		update_post_meta( $offer_id, '_pw_discount_value', 0 );
		update_post_meta( $offer_id, '_pw_minimum_stay_nights', 4 );
		update_post_meta( $offer_id, '_pw_display_order', 1 );
		$rt_for_offer = array_slice( $room_type_ids, 0, 3 );
		update_post_meta( $offer_id, '_pw_room_types', array_map( 'intval', $rt_for_offer ) );
		update_post_meta( $offer_id, '_pw_meta_title', 'Fourth Night Free | Grand Sunset Resort' );
		update_post_meta( $offer_id, '_pw_meta_description', 'Book three nights, get the fourth night complimentary when you book direct.' );
	}

	$pkg_parents = [ [ 'type' => 'pw_property', 'id' => $property_id ] ];
	if ( $spa_id > 0 ) {
		$pkg_parents[] = [ 'type' => 'pw_spa', 'id' => $spa_id ];
	}
	$pkg_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_offer',
			'post_status'  => 'publish',
			'post_title'   => 'Restore & Retreat Spa Package',
			'post_excerpt' => 'Two nights, daily breakfast, and a 90-minute couples massage.',
			'post_content' => '<p>Includes Serenity Spa welcome ritual, access to the thermal lounge, and a dinner credit at Coral Grill. Gratuity on spa services not included.</p>',
		],
		true
	);
	if ( ! is_wp_error( $pkg_ins ) && $pkg_ins ) {
		$pkg_id = (int) $pkg_ins;
		update_post_meta( $pkg_id, '_pw_offer_type', 'package' );
		update_post_meta( $pkg_id, '_pw_parents', $pkg_parents );
		update_post_meta( $pkg_id, '_pw_valid_from', gmdate( 'Y-m-d', strtotime( '+1 day' ) ) );
		update_post_meta( $pkg_id, '_pw_valid_to', gmdate( 'Y-m-d', strtotime( '+365 days' ) ) );
		update_post_meta( $pkg_id, '_pw_booking_url', $base_url . '/packages/restore-retreat/' );
		update_post_meta( $pkg_id, '_pw_is_featured', false );
		update_post_meta( $pkg_id, '_pw_discount_type', 'flat' );
		update_post_meta( $pkg_id, '_pw_discount_value', 125 );
		update_post_meta( $pkg_id, '_pw_minimum_stay_nights', 2 );
		update_post_meta( $pkg_id, '_pw_display_order', 2 );
		update_post_meta( $pkg_id, '_pw_room_types', array_slice( array_map( 'intval', $room_type_ids ), 0, 2 ) );
	}

	if ( $main_rest_id > 0 ) {
		$dine_ins = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_offer',
				'post_status'  => 'publish',
				'post_title'   => 'Chef\'s Table at Azure — Wine Pairing',
				'post_excerpt' => 'Seven-course tasting with optional Old World wine flight.',
				'post_content' => '<p>Hosted Thursday and Saturday evenings for parties of two to six. Dietary restrictions accommodated with 72-hour notice.</p>',
			],
			true
		);
		if ( ! is_wp_error( $dine_ins ) && $dine_ins ) {
			$dine_id = (int) $dine_ins;
			update_post_meta( $dine_id, '_pw_offer_type', 'promotion' );
			update_post_meta( $dine_id, '_pw_parents', [ [ 'type' => 'pw_restaurant', 'id' => $main_rest_id ] ] );
			update_post_meta( $dine_id, '_pw_valid_from', gmdate( 'Y-m-d' ) );
			update_post_meta( $dine_id, '_pw_valid_to', gmdate( 'Y-m-d', strtotime( '+180 days' ) ) );
			update_post_meta( $dine_id, '_pw_booking_url', $base_url . '/dining/azure-chefs-table/' );
			update_post_meta( $dine_id, '_pw_is_featured', false );
			update_post_meta( $dine_id, '_pw_discount_type', 'percentage' );
			update_post_meta( $dine_id, '_pw_discount_value', 0 );
			update_post_meta( $dine_id, '_pw_minimum_stay_nights', 0 );
			update_post_meta( $dine_id, '_pw_display_order', 3 );
			update_post_meta( $dine_id, '_pw_room_types', [] );
		}
	}

	$beach_tid    = pw_sample_ensure_term( 'Beach', 'pw_nearby_type' );
	$airport_tid  = pw_sample_ensure_term( 'Airport', 'pw_nearby_type' );
	$attr_tid     = pw_sample_ensure_term( 'Attraction', 'pw_nearby_type' );
	$shopping_tid = pw_sample_ensure_term( 'Shopping', 'pw_nearby_type' );
	$dining_tid   = pw_sample_ensure_term( 'Dining', 'pw_nearby_type' );
	$drive_tid    = pw_sample_ensure_term( 'Drive', 'pw_transport_mode' );
	$walk_tid     = pw_sample_ensure_term( 'Walk', 'pw_transport_mode' );
	$taxi_tid     = pw_sample_ensure_term( 'Taxi', 'pw_transport_mode' );
	$shuttle_tid  = pw_sample_ensure_term( 'Shuttle', 'pw_transport_mode' );

	$nearby_defs = [
		[
			'title'   => 'South Beach & Ocean Drive',
			'excerpt' => 'Iconic stretch of sand, nightlife, and Art Deco architecture.',
			'content' => '<p>Five minutes by car to the heart of South Beach. Ask concierge for guest-list access at partner beach clubs.</p>',
			'km' => 2.8, 'min' => 10, 'type' => $beach_tid, 'trans' => $drive_tid,
		],
		[
			'title'   => 'Miami International Airport (MIA)',
			'excerpt' => 'Major hub with domestic and international carriers.',
			'content' => '<p>Approximately 25 minutes by car depending on traffic. Private sedan transfers can be arranged through the front desk.</p>',
			'km' => 17.5, 'min' => 28, 'type' => $airport_tid, 'trans' => $shuttle_tid,
		],
		[
			'title'   => 'Lincoln Road Mall',
			'excerpt' => 'Open-air shopping, dining, and weekend farmers market.',
			'content' => '<p>Pedestrian promenade 15 minutes on foot. Our house car drops within a three-mile radius when available.</p>',
			'km' => 1.6, 'min' => 18, 'type' => $shopping_tid, 'trans' => $walk_tid,
		],
		[
			'title'   => 'Wynwood Walls',
			'excerpt' => 'Outdoor street art museum and gallery district.',
			'content' => '<p>Twenty minutes by taxi. Combine with a craft brewery tour — itineraries available from concierge.</p>',
			'km' => 9.2, 'min' => 22, 'type' => $attr_tid, 'trans' => $taxi_tid,
		],
		[
			'title'   => 'Joe\'s Stone Crab',
			'excerpt' => 'Historic seafood institution (seasonal stone crab).',
			'content' => '<p>Reservations strongly recommended October–May. Located on Washington Avenue, eight minutes by car.</p>',
			'km' => 2.1, 'min' => 9, 'type' => $dining_tid, 'trans' => $drive_tid,
		],
		[
			'title'   => 'Art Deco Welcome Center',
			'excerpt' => 'Walking tours and preservation exhibits.',
			'content' => '<p>Start here for guided walks through the historic district — a pleasant stroll from the resort.</p>',
			'km' => 0.9, 'min' => 12, 'type' => $attr_tid, 'trans' => $walk_tid,
		],
	];

	foreach ( $nearby_defs as $i => $nd ) {
		$nid = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_nearby',
				'post_status'  => 'publish',
				'post_title'   => $nd['title'],
				'post_excerpt' => $nd['excerpt'],
				'post_content' => wp_kses_post( $nd['content'] ),
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
		update_post_meta( $nid, '_pw_place_url', 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $nd['title'] . ' Miami Beach FL' ) );
		update_post_meta( $nid, '_pw_display_order', $i + 1 );
		update_post_meta( $nid, '_pw_meta_title', $nd['title'] . ' | Near Grand Sunset Resort' );
		update_post_meta( $nid, '_pw_meta_description', wp_strip_all_tags( $nd['excerpt'] ) );
		wp_set_object_terms( $nid, array_filter( [ (int) $nd['type'] ] ), 'pw_nearby_type' );
		wp_set_object_terms( $nid, array_filter( [ (int) $nd['trans'] ] ), 'pw_transport_mode' );
	}

	$well_tid    = pw_sample_ensure_term( 'Wellness', 'pw_experience_category' );
	$adv_tid     = pw_sample_ensure_term( 'Adventure', 'pw_experience_category' );
	$culinary_tid = pw_sample_ensure_term( 'Culinary', 'pw_experience_category' );
	$water_tid   = pw_sample_ensure_term( 'Water Sports', 'pw_experience_category' );

	$exp1_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_experience',
			'post_status'  => 'publish',
			'post_title'   => 'Serenity Full-Day Ritual',
			'post_excerpt' => 'Custom massage, Biologique facial, lunch at Coral Grill, and afternoon pool time.',
			'post_content' => '<p>Arrive at 9:00 AM for herbal tea and a consultation. Your therapist tailors pressure and aromatherapy oils. Lunch credit applies toward Coral Grill same day only.</p>',
		],
		true
	);
	if ( ! is_wp_error( $exp1_ins ) && $exp1_ins ) {
		$exp1 = (int) $exp1_ins;
		$exp1_connections = [ [ 'type' => 'pw_property', 'id' => $property_id ] ];
		if ( $spa_id > 0 ) {
			$exp1_connections[] = [ 'type' => 'pw_spa', 'id' => $spa_id ];
		}
		update_post_meta( $exp1, '_pw_connected_to', $exp1_connections );
		update_post_meta( $exp1, '_pw_description', 'Includes 75-minute massage, 50-minute facial, and $45 Coral Grill credit.' );
		update_post_meta( $exp1, '_pw_duration_hours', 6 );
		update_post_meta( $exp1, '_pw_price_from', 389 );
		update_post_meta( $exp1, '_pw_booking_url', $base_url . '/experiences/serenity-ritual/' );
		update_post_meta( $exp1, '_pw_is_complimentary', false );
		update_post_meta( $exp1, '_pw_gallery', [] );
		update_post_meta( $exp1, '_pw_display_order', 1 );
		update_post_meta( $exp1, '_pw_meta_title', 'Serenity Spa Day | Grand Sunset Resort' );
		if ( $well_tid ) {
			wp_set_object_terms( $exp1, [ $well_tid ], 'pw_experience_category' );
		}
	}

	$exp2_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_experience',
			'post_status'  => 'publish',
			'post_title'   => 'Golden Hour Catamaran Sail',
			'post_excerpt' => 'Two-hour sail with sparkling wine and light canapés.',
			'post_content' => '<p>Board at Miami Beach Marina. Captain and crew included; maximum 12 guests. Weather contingency date offered within 48 hours if winds exceed 25 knots.</p>',
		],
		true
	);
	if ( ! is_wp_error( $exp2_ins ) && $exp2_ins ) {
		$exp2 = (int) $exp2_ins;
		update_post_meta(
			$exp2,
			'_pw_connected_to',
			[ [ 'type' => 'pw_property', 'id' => $property_id ] ]
		);
		update_post_meta( $exp2, '_pw_description', 'Departs 5:30 PM; returns after sunset. Private buyouts available.' );
		update_post_meta( $exp2, '_pw_duration_hours', 2 );
		update_post_meta( $exp2, '_pw_price_from', 125 );
		update_post_meta( $exp2, '_pw_booking_url', $base_url . '/experiences/catamaran-sail/' );
		update_post_meta( $exp2, '_pw_is_complimentary', false );
		update_post_meta( $exp2, '_pw_gallery', [] );
		update_post_meta( $exp2, '_pw_display_order', 2 );
		if ( $adv_tid ) {
			wp_set_object_terms( $exp2, [ $adv_tid ], 'pw_experience_category' );
		}
	}

	if ( $coral_grill_id > 0 ) {
		$exp3_ins = pw_sample_wp_insert_post(
			[
				'post_type'    => 'pw_experience',
				'post_status'  => 'publish',
				'post_title'   => 'Grill Master Class & Lunch',
				'post_excerpt' => 'Behind-the-scenes with our executive chef, then a three-course lunch.',
				'post_content' => '<p>Saturday mornings on the terrace kitchen. Aprons and recipes provided; wine pairing optional. Ages 14+ with adult.</p>',
			],
			true
		);
		if ( ! is_wp_error( $exp3_ins ) && $exp3_ins ) {
			$exp3 = (int) $exp3_ins;
			update_post_meta(
				$exp3,
				'_pw_connected_to',
				[
					[ 'type' => 'pw_property', 'id' => $property_id ],
					[ 'type' => 'pw_restaurant', 'id' => $coral_grill_id ],
				]
			);
			update_post_meta( $exp3, '_pw_description', '10:00 AM–2:00 PM; 12 seats per class.' );
			update_post_meta( $exp3, '_pw_duration_hours', 4 );
			update_post_meta( $exp3, '_pw_price_from', 165 );
			update_post_meta( $exp3, '_pw_booking_url', $base_url . '/experiences/grill-master-class/' );
			update_post_meta( $exp3, '_pw_is_complimentary', false );
			update_post_meta( $exp3, '_pw_gallery', [] );
			update_post_meta( $exp3, '_pw_display_order', 3 );
			if ( $culinary_tid ) {
				wp_set_object_terms( $exp3, [ $culinary_tid ], 'pw_experience_category' );
			}
		}
	}

	$exp4_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_experience',
			'post_status'  => 'publish',
			'post_title'   => 'Complimentary Sunrise Beach Yoga',
			'post_excerpt' => 'Gentle flow on the sand — mats and water provided.',
			'post_content' => '<p>Meet at the beach club gate. Certified instructors; all levels welcome. Canceled in case of lightning.</p>',
		],
		true
	);
	if ( ! is_wp_error( $exp4_ins ) && $exp4_ins ) {
		$exp4 = (int) $exp4_ins;
		update_post_meta(
			$exp4,
			'_pw_connected_to',
			[ [ 'type' => 'pw_property', 'id' => $property_id ] ]
		);
		update_post_meta( $exp4, '_pw_description', 'Tuesday, Thursday, and Saturday at 7:00 AM; approximately 45 minutes.' );
		update_post_meta( $exp4, '_pw_duration_hours', 0.75 );
		update_post_meta( $exp4, '_pw_price_from', 0 );
		update_post_meta( $exp4, '_pw_booking_url', $base_url . '/experiences/beach-yoga/' );
		update_post_meta( $exp4, '_pw_is_complimentary', true );
		update_post_meta( $exp4, '_pw_gallery', [] );
		update_post_meta( $exp4, '_pw_display_order', 4 );
		if ( $well_tid ) {
			wp_set_object_terms( $exp4, [ $well_tid ], 'pw_experience_category' );
		}
	}

	$exp5_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_experience',
			'post_status'  => 'publish',
			'post_title'   => 'Jet Ski Island Hop',
			'post_excerpt' => 'Guided one-hour ride with photo stops — license required.',
			'post_content' => '<p>Partnership with South Beach Watersports. Wetsuits and instruction included; guests must be 18+ with valid ID.</p>',
		],
		true
	);
	if ( ! is_wp_error( $exp5_ins ) && $exp5_ins ) {
		$exp5 = (int) $exp5_ins;
		update_post_meta(
			$exp5,
			'_pw_connected_to',
			[ [ 'type' => 'pw_property', 'id' => $property_id ] ]
		);
		update_post_meta( $exp5, '_pw_description', 'Morning and afternoon departures from 17th Street launch.' );
		update_post_meta( $exp5, '_pw_duration_hours', 1.5 );
		update_post_meta( $exp5, '_pw_price_from', 175 );
		update_post_meta( $exp5, '_pw_booking_url', $base_url . '/experiences/jet-ski/' );
		update_post_meta( $exp5, '_pw_is_complimentary', false );
		update_post_meta( $exp5, '_pw_gallery', [] );
		update_post_meta( $exp5, '_pw_display_order', 5 );
		if ( $water_tid ) {
			wp_set_object_terms( $exp5, [ $water_tid ], 'pw_experience_category' );
		}
	}

	$conf_tid    = pw_sample_ensure_term( 'Conference', 'pw_event_type' );
	$wedding_tid = pw_sample_ensure_term( 'Wedding', 'pw_event_type' );
	$social_ev_tid = pw_sample_ensure_term( 'Social Event', 'pw_event_type' );

	$summit_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_event',
			'post_status'  => 'publish',
			'post_title'   => 'Southeast Lodging Leadership Summit 2026',
			'post_excerpt' => 'Two-day conference for hotel GMs and asset managers.',
			'post_content' => '<p>General sessions, breakout tracks on labor, distribution, and sustainability. Closing reception on the Azure terrace.</p>',
		],
		true
	);
	if ( ! is_wp_error( $summit_ins ) && $summit_ins ) {
		$summit_id = (int) $summit_ins;
		$start     = gmdate( 'Y-m-d H:i:s', strtotime( '+75 days 08:30:00' ) );
		$end       = gmdate( 'Y-m-d H:i:s', strtotime( '+76 days 18:00:00' ) );
		update_post_meta( $summit_id, '_pw_property_id', $property_id );
		update_post_meta( $summit_id, '_pw_venue_id', $meeting_id > 0 ? $meeting_id : 0 );
		update_post_meta( $summit_id, '_pw_description', 'Keynotes from AHLA and STR; CE credits for CHA holders.' );
		update_post_meta( $summit_id, '_pw_start_datetime', $start );
		update_post_meta( $summit_id, '_pw_end_datetime', $end );
		update_post_meta( $summit_id, '_pw_capacity', 380 );
		update_post_meta( $summit_id, '_pw_price_from', 649 );
		update_post_meta( $summit_id, '_pw_booking_url', $base_url . '/events/lodging-summit/' );
		update_post_meta( $summit_id, '_pw_gallery', [] );
		update_post_meta( $summit_id, '_pw_recurrence_rule', '' );
		update_post_meta( $summit_id, '_pw_event_status', 'EventScheduled' );
		update_post_meta( $summit_id, '_pw_event_attendance_mode', 'OfflineEventAttendanceMode' );
		update_post_meta( $summit_id, '_pw_meta_title', 'Lodging Leadership Summit | Grand Sunset Resort' );
		if ( $conf_tid ) {
			wp_set_object_terms( $summit_id, [ $conf_tid ], 'pw_event_type' );
		}
		if ( $organiser_id ) {
			wp_set_object_terms( $summit_id, [ $organiser_id ], 'pw_event_organiser' );
		}
	}

	$wedding_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_event',
			'post_status'  => 'publish',
			'post_title'   => 'Oceanfront Wedding Open House',
			'post_excerpt' => 'Tour the ballroom, meet preferred planners, and taste Azure canapés.',
			'post_content' => '<p>Bridal suite preview, lighting demos, and mini cake tasting. RSVP required; couples plus three guests maximum.</p>',
		],
		true
	);
	if ( ! is_wp_error( $wedding_ins ) && $wedding_ins ) {
		$wedding_event_id = (int) $wedding_ins;
		$w_start          = gmdate( 'Y-m-d H:i:s', strtotime( '+40 days 13:00:00' ) );
		$w_end            = gmdate( 'Y-m-d H:i:s', strtotime( '+40 days 17:00:00' ) );
		update_post_meta( $wedding_event_id, '_pw_property_id', $property_id );
		update_post_meta( $wedding_event_id, '_pw_venue_id', $meeting_id > 0 ? $meeting_id : 0 );
		update_post_meta( $wedding_event_id, '_pw_description', 'Complimentary valet for registered attendees.' );
		update_post_meta( $wedding_event_id, '_pw_start_datetime', $w_start );
		update_post_meta( $wedding_event_id, '_pw_end_datetime', $w_end );
		update_post_meta( $wedding_event_id, '_pw_capacity', 80 );
		update_post_meta( $wedding_event_id, '_pw_price_from', 0 );
		update_post_meta( $wedding_event_id, '_pw_booking_url', $base_url . '/events/wedding-open-house/' );
		update_post_meta( $wedding_event_id, '_pw_gallery', [] );
		update_post_meta( $wedding_event_id, '_pw_recurrence_rule', '' );
		update_post_meta( $wedding_event_id, '_pw_event_status', 'EventScheduled' );
		update_post_meta( $wedding_event_id, '_pw_event_attendance_mode', 'OfflineEventAttendanceMode' );
		if ( $wedding_tid ) {
			wp_set_object_terms( $wedding_event_id, [ $wedding_tid ], 'pw_event_type' );
		}
		if ( $organiser_id ) {
			wp_set_object_terms( $wedding_event_id, [ $organiser_id ], 'pw_event_organiser' );
		}
	}

	$yoga_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_event',
			'post_status'  => 'publish',
			'post_title'   => 'Beach Yoga — Weekly Series',
			'post_excerpt' => 'Complimentary guest sessions (see Experiences for schedule).',
			'post_content' => '<p>Recurring through peak season. Instructors rotate weekly.</p>',
		],
		true
	);
	if ( ! is_wp_error( $yoga_ins ) && $yoga_ins ) {
		$yoga_id = (int) $yoga_ins;
		$y_start = gmdate( 'Y-m-d H:i:s', strtotime( 'next tuesday 07:00:00' ) );
		$y_end   = gmdate( 'Y-m-d H:i:s', strtotime( 'next tuesday 07:45:00' ) );
		update_post_meta( $yoga_id, '_pw_property_id', $property_id );
		update_post_meta( $yoga_id, '_pw_venue_id', 0 );
		update_post_meta( $yoga_id, '_pw_description', 'Meet at the beach club; mats provided while supplies last.' );
		update_post_meta( $yoga_id, '_pw_start_datetime', $y_start );
		update_post_meta( $yoga_id, '_pw_end_datetime', $y_end );
		update_post_meta( $yoga_id, '_pw_capacity', 30 );
		update_post_meta( $yoga_id, '_pw_price_from', 0 );
		update_post_meta( $yoga_id, '_pw_booking_url', $base_url . '/experiences/beach-yoga/' );
		update_post_meta( $yoga_id, '_pw_gallery', [] );
		update_post_meta( $yoga_id, '_pw_recurrence_rule', 'FREQ=WEEKLY;BYDAY=TU,TH,SA;UNTIL=20261231T235959Z' );
		update_post_meta( $yoga_id, '_pw_event_status', 'EventScheduled' );
		update_post_meta( $yoga_id, '_pw_event_attendance_mode', 'OfflineEventAttendanceMode' );
		if ( $social_ev_tid ) {
			wp_set_object_terms( $yoga_id, [ $social_ev_tid ], 'pw_event_type' );
		}
		if ( $organiser_id ) {
			wp_set_object_terms( $yoga_id, [ $organiser_id ], 'pw_event_organiser' );
		}
	}

	$db_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'pw_offer',
			'post_status'  => 'publish',
			'post_title'   => 'Direct Booking Perks',
			'post_excerpt' => 'Exclusive to reservations made on our website or by phone.',
			'post_content' => '<p>Includes priority room assignment, two welcome cocktails at Tide & Tan, and 10% off Serenity Spa retail on day of arrival.</p>',
		],
		true
	);
	if ( ! is_wp_error( $db_ins ) && $db_ins ) {
		$db_id = (int) $db_ins;
		update_post_meta( $db_id, '_pw_offer_type', 'direct_booking_benefit' );
		update_post_meta( $db_id, '_pw_parents', [ [ 'type' => 'pw_property', 'id' => $property_id ] ] );
		update_post_meta( $db_id, '_pw_valid_from', gmdate( 'Y-m-d' ) );
		update_post_meta( $db_id, '_pw_valid_to', gmdate( 'Y-m-d', strtotime( '+730 days' ) ) );
		update_post_meta( $db_id, '_pw_booking_url', $base_url . '/book/direct-benefits/' );
		update_post_meta( $db_id, '_pw_is_featured', true );
		update_post_meta( $db_id, '_pw_discount_type', 'value_add' );
		update_post_meta( $db_id, '_pw_discount_value', 0 );
		update_post_meta( $db_id, '_pw_minimum_stay_nights', 0 );
		update_post_meta( $db_id, '_pw_display_order', 4 );
		update_post_meta( $db_id, '_pw_room_types', [] );
	}

	$sample_cat_id = pw_sample_ensure_term( 'Grand Sunset Resort', 'category' );
	pw_sample_wp_insert_post(
		[
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Grand Sunset Resort',
			'post_name'    => 'grand-sunset-resort-landing',
			'post_content' => '<p>Beachfront Miami resort — explore rooms, dining, and events on the main property profile.</p>',
			'post_excerpt' => 'Gateway page for the Grand Sunset Resort demonstration property.',
		],
		true
	);

	$blog_post_ins = pw_sample_wp_insert_post(
		[
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => 'Spa renovation unveils new thermal suite',
			'post_name'    => 'grand-sunset-spa-renovation',
			'post_content' => '<p>The Serenity Spa expansion adds a hammam-inspired thermal suite and couples\' treatment rooms. Bookings open to resort guests December 1.</p>',
			'post_excerpt' => 'Thermal suite and new couples\' rooms at Serenity Spa.',
		],
		true
	);
	if ( ! is_wp_error( $blog_post_ins ) && $blog_post_ins && $sample_cat_id ) {
		wp_set_object_terms( (int) $blog_post_ins, [ (int) $sample_cat_id ], 'category' );
	}
	$tag_id = pw_sample_ensure_term( 'Miami Beach', 'post_tag' );
	if ( ! is_wp_error( $blog_post_ins ) && $blog_post_ins && $tag_id ) {
		wp_set_object_terms( (int) $blog_post_ins, [ (int) $tag_id ], 'post_tag', true );
	}

	} finally {
		pw_sample_install_lock_close();
	}
}
