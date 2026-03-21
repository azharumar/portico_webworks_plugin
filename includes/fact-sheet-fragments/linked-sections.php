<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$property_id = isset( $property_id ) ? (int) $property_id : 0;
if ( $property_id <= 0 ) {
	return;
}
$pw_fact_meta_query = array(
		array(
			'key'   => '_pw_property_id',
			'value' => (int) $property_id,
		),
	);
	$pw_fact_child_types = array(
		'pw_room_type'    => 'Room types',
		'pw_restaurant'   => 'Restaurants',
		'pw_spa'          => 'Spas',
		'pw_meeting_room' => 'Meeting rooms',
		'pw_amenity'      => 'Amenities',
		'pw_policy'       => 'Policies',
		'pw_nearby'       => 'Nearby places',
		'pw_event'        => 'Events',
		'pw_experience'   => 'Experiences',
		'pw_faq'          => 'FAQs',
		'pw_offer'        => 'Offers',
	);
	foreach ( $pw_fact_child_types as $pt => $heading ) {
		$posts = get_posts(
			array(
				'post_type'      => $pt,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_query'     => $pw_fact_meta_query,
			)
		);
		if ( ! $posts ) {
			continue;
		}
		$sec_id = 'pw-fact-' . str_replace( '_', '-', $pt );
		echo '<section class="pw-fact-sheet-' . esc_attr( str_replace( '_', '-', $pt ) ) . '" aria-labelledby="' . esc_attr( $sec_id ) . '">';
		echo '<h2 id="' . esc_attr( $sec_id ) . '">' . esc_html( $heading ) . '</h2>';
		foreach ( $posts as $post_obj ) {
			$pid = (int) $post_obj->ID;
			echo '<article class="pw-fact-item"><header><h3>' . esc_html( get_the_title( $pid ) ) . '</h3>';
			echo '<p class="pw-fact-item-meta"><span class="pw-fact-post-type">' . esc_html( $pt ) . '</span> · ID <code>' . esc_html( (string) $pid ) . '</code></p></header>';

			if ( $pt === 'pw_room_type' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p class="pw-fact-excerpt"><strong>' . esc_html( 'Excerpt' ) . '</strong> — ' . esc_html( $ex ) . '</p>';
				}
				$rows = array();
				$rf   = (float) get_post_meta( $pid, '_pw_rate_from', true );
				$rt   = (float) get_post_meta( $pid, '_pw_rate_to', true );
				if ( $rf > 0 || $rt > 0 ) {
					$rows[] = array( 'l' => 'Rate from (summary)', 'v' => pw_fact_esc( (string) $rf ) );
					$rows[] = array( 'l' => 'Rate to (summary)', 'v' => pw_fact_esc( (string) $rt ) );
				}
				foreach (
					array(
						'_pw_max_occupancy'  => 'Max occupancy',
						'_pw_max_adults'     => 'Max adults',
						'_pw_max_children'   => 'Max children',
						'_pw_size_sqft'      => 'Size (sq ft)',
						'_pw_size_sqm'       => 'Size (sq m)',
						'_pw_max_extra_beds' => 'Max extra beds',
						'_pw_display_order'  => 'Display order',
					) as $mk => $lab
				) {
					$iv = (int) get_post_meta( $pid, $mk, true );
					if ( $iv > 0 ) {
						$rows[] = array( 'l' => $lab, 'v' => pw_fact_esc( (string) $iv ) );
					}
				}
				$t1 = get_the_terms( $pid, 'pw_bed_type' );
				if ( $t1 && ! is_wp_error( $t1 ) ) {
					$rows[] = array( 'l' => 'Bed types', 'v' => pw_fact_esc( implode( ', ', wp_list_pluck( $t1, 'name' ) ) ) );
				}
				$t2 = get_the_terms( $pid, 'pw_view_type' );
				if ( $t2 && ! is_wp_error( $t2 ) ) {
					$rows[] = array( 'l' => 'View types', 'v' => pw_fact_esc( implode( ', ', wp_list_pluck( $t2, 'name' ) ) ) );
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					$rows[] = array( 'l' => 'SEO meta title', 'v' => pw_fact_esc( $mt ) );
				}
				if ( is_string( $md ) && $md !== '' ) {
					$rows[] = array( 'l' => 'SEO meta description', 'v' => pw_fact_esc( $md ) );
				}
				pw_fact_kv_table( 'Room type summary', $rows );

				$rates = get_post_meta( $pid, '_pw_rates', true );
				if ( is_array( $rates ) && $rates ) {
					echo '<table class="pw-fact-rates"><caption>' . esc_html( 'Rate plans (_pw_rates)' ) . '</caption>';
					echo '<thead><tr><th scope="col">' . esc_html( 'Label' ) . '</th><th scope="col">' . esc_html( 'Type' ) . '</th><th scope="col">' . esc_html( 'Price' ) . '</th><th scope="col">' . esc_html( 'Valid from' ) . '</th><th scope="col">' . esc_html( 'Valid to' ) . '</th><th scope="col">' . esc_html( 'Advance days' ) . '</th><th scope="col">' . esc_html( 'Breakfast' ) . '</th></tr></thead><tbody>';
					foreach ( $rates as $r ) {
						if ( ! is_array( $r ) ) {
							continue;
						}
						$rl = isset( $r['rate_label'] ) ? (string) $r['rate_label'] : '—';
						$rt = isset( $r['rate_type'] ) ? (string) $r['rate_type'] : '—';
						$pr = isset( $r['price'] ) ? $r['price'] : '';
						$vf = isset( $r['valid_from'] ) ? (string) $r['valid_from'] : '';
						$vt = isset( $r['valid_to'] ) ? (string) $r['valid_to'] : '';
						$ad = isset( $r['advance_days'] ) ? (string) $r['advance_days'] : '';
						$ib = array_key_exists( 'includes_breakfast', $r ) ? $r['includes_breakfast'] : null;
						echo '<tr><th scope="row">' . esc_html( $rl ) . '</th><td>' . esc_html( $rt ) . '</td><td>' . esc_html( is_scalar( $pr ) ? (string) $pr : '' ) . '</td><td>' . esc_html( $vf !== '' ? $vf : '—' ) . '</td><td>' . esc_html( $vt !== '' ? $vt : '—' ) . '</td><td>' . esc_html( $ad !== '' ? $ad : '—' ) . '</td><td>' . ( is_bool( $ib ) ? pw_fact_bool_cell( $ib ) : '—' ) . '</td></tr>';
					}
					echo '</tbody></table>';
				}
				$feat_ids = get_post_meta( $pid, '_pw_features', true );
				if ( is_array( $feat_ids ) && $feat_ids ) {
					echo '<p><strong>' . esc_html( 'Linked features' ) . '</strong></p><ul class="pw-fact-inline-list">';
					foreach ( $feat_ids as $fid ) {
						$fid = (int) $fid;
						if ( $fid <= 0 ) {
							continue;
						}
						echo '<li>' . esc_html( get_the_title( $fid ) ) . ' <code>' . esc_html( (string) $fid ) . '</code></li>';
					}
					echo '</ul>';
				}
				pw_fact_gallery_table( get_post_meta( $pid, '_pw_gallery', true ) );
			} elseif ( $pt === 'pw_restaurant' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p class="pw-fact-excerpt"><strong>' . esc_html( 'Excerpt' ) . '</strong> — ' . esc_html( $ex ) . '</p>';
				}
				$rows = array();
				foreach (
					array(
						'_pw_location'         => 'Location',
						'_pw_cuisine_type'     => 'Cuisine type',
						'_pw_seating_capacity' => 'Seating capacity',
					) as $mk => $lab
				) {
					$v = get_post_meta( $pid, $mk, true );
					if ( $mk === '_pw_seating_capacity' ) {
						$iv = (int) $v;
						if ( $iv > 0 ) {
							$rows[] = array( 'l' => $lab, 'v' => pw_fact_esc( (string) $iv ) );
						}
					} elseif ( is_string( $v ) && $v !== '' ) {
						$rows[] = array( 'l' => $lab, 'v' => pw_fact_esc( $v ) );
					}
				}
				$v = get_post_meta( $pid, '_pw_reservation_url', true );
				if ( is_string( $v ) && $v !== '' ) {
					$rows[] = array( 'l' => 'Reservation URL', 'v' => pw_fact_url_cell( $v ) );
				}
				$v = get_post_meta( $pid, '_pw_menu_url', true );
				if ( is_string( $v ) && $v !== '' ) {
					$rows[] = array( 'l' => 'Menu URL', 'v' => pw_fact_url_cell( $v ) );
				}
				$tm = get_the_terms( $pid, 'pw_meal_period' );
				if ( $tm && ! is_wp_error( $tm ) ) {
					$rows[] = array( 'l' => 'Meal periods', 'v' => pw_fact_esc( implode( ', ', wp_list_pluck( $tm, 'name' ) ) ) );
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					$rows[] = array( 'l' => 'SEO meta title', 'v' => pw_fact_esc( $mt ) );
				}
				if ( is_string( $md ) && $md !== '' ) {
					$rows[] = array( 'l' => 'SEO meta description', 'v' => pw_fact_esc( $md ) );
				}
				pw_fact_kv_table( 'Restaurant details', $rows );
				pw_fact_hours_block( $pid );
				pw_fact_gallery_table( get_post_meta( $pid, '_pw_gallery', true ) );
			} elseif ( $pt === 'pw_spa' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p class="pw-fact-excerpt"><strong>' . esc_html( 'Excerpt' ) . '</strong> — ' . esc_html( $ex ) . '</p>';
				}
				$rows = array();
				$v    = get_post_meta( $pid, '_pw_booking_url', true );
				if ( is_string( $v ) && $v !== '' ) {
					$rows[] = array( 'l' => 'Booking URL', 'v' => pw_fact_url_cell( $v ) );
				}
				$v = get_post_meta( $pid, '_pw_menu_url', true );
				if ( is_string( $v ) && $v !== '' ) {
					$rows[] = array( 'l' => 'Menu URL', 'v' => pw_fact_url_cell( $v ) );
				}
				foreach ( array( '_pw_min_age' => 'Minimum age', '_pw_number_of_treatment_rooms' => 'Treatment rooms' ) as $mk => $lab ) {
					$iv = (int) get_post_meta( $pid, $mk, true );
					if ( $iv > 0 ) {
						$rows[] = array( 'l' => $lab, 'v' => pw_fact_esc( (string) $iv ) );
					}
				}
				$tt = get_the_terms( $pid, 'pw_treatment_type' );
				if ( $tt && ! is_wp_error( $tt ) ) {
					$rows[] = array( 'l' => 'Treatment types', 'v' => pw_fact_esc( implode( ', ', wp_list_pluck( $tt, 'name' ) ) ) );
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					$rows[] = array( 'l' => 'SEO meta title', 'v' => pw_fact_esc( $mt ) );
				}
				if ( is_string( $md ) && $md !== '' ) {
					$rows[] = array( 'l' => 'SEO meta description', 'v' => pw_fact_esc( $md ) );
				}
				pw_fact_kv_table( 'Spa details', $rows );
				pw_fact_hours_block( $pid );
				pw_fact_gallery_table( get_post_meta( $pid, '_pw_gallery', true ) );
			} elseif ( $pt === 'pw_meeting_room' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p class="pw-fact-excerpt"><strong>' . esc_html( 'Excerpt' ) . '</strong> — ' . esc_html( $ex ) . '</p>';
				}
				$rows = array();
				foreach (
					array(
						'_pw_capacity_theatre'      => 'Capacity (theatre)',
						'_pw_capacity_classroom'    => 'Capacity (classroom)',
						'_pw_capacity_boardroom'    => 'Capacity (boardroom)',
						'_pw_capacity_ushape'       => 'Capacity (U-shape)',
						'_pw_area_sqft'             => 'Area (sq ft)',
						'_pw_area_sqm'              => 'Area (sq m)',
						'_pw_prefunction_area_sqft' => 'Pre-function area (sq ft)',
						'_pw_prefunction_area_sqm'  => 'Pre-function area (sq m)',
					) as $mk => $lab
				) {
					$iv = (int) get_post_meta( $pid, $mk, true );
					if ( $iv > 0 ) {
						$rows[] = array( 'l' => $lab, 'v' => pw_fact_esc( (string) $iv ) );
					}
				}
				$rows[] = array( 'l' => 'Natural light', 'v' => pw_fact_bool_cell( get_post_meta( $pid, '_pw_natural_light', true ) ) );
				$fp = (int) get_post_meta( $pid, '_pw_floor_plan', true );
				if ( $fp > 0 ) {
					$fu = wp_get_attachment_url( $fp );
					$rows[] = array(
						'l' => 'Floor plan (attachment)',
						'v' => pw_fact_esc( (string) $fp ) . ( $fu ? ' ' . pw_fact_url_cell( $fu ) : '' ),
					);
				}
				foreach (
					array(
						'_pw_sales_phone'    => 'Sales phone',
						'_pw_sales_mobile'   => 'Sales mobile',
						'_pw_sales_whatsapp' => 'Sales WhatsApp',
						'_pw_sales_email'    => 'Sales email',
					) as $mk => $lab
				) {
					$v = get_post_meta( $pid, $mk, true );
					if ( is_string( $v ) && $v !== '' ) {
						$rows[] = array( 'l' => $lab, 'v' => pw_fact_esc( $v ) );
					}
				}
				$av = get_the_terms( $pid, 'pw_av_equipment' );
				if ( $av && ! is_wp_error( $av ) ) {
					$rows[] = array( 'l' => 'AV equipment', 'v' => pw_fact_esc( implode( ', ', wp_list_pluck( $av, 'name' ) ) ) );
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					$rows[] = array( 'l' => 'SEO meta title', 'v' => pw_fact_esc( $mt ) );
				}
				if ( is_string( $md ) && $md !== '' ) {
					$rows[] = array( 'l' => 'SEO meta description', 'v' => pw_fact_esc( $md ) );
				}
				pw_fact_kv_table( 'Meeting room details', $rows );
				pw_fact_gallery_table( get_post_meta( $pid, '_pw_gallery', true ) );
			} elseif ( $pt === 'pw_amenity' ) {
				$rows = array();
				$typ  = get_post_meta( $pid, '_pw_type', true );
				if ( is_string( $typ ) && $typ !== '' ) {
					$rows[] = array( 'l' => 'Type', 'v' => pw_fact_esc( $typ ) );
				}
				foreach ( array( '_pw_category' => 'Category', '_pw_icon' => 'Icon', '_pw_description' => 'Description' ) as $mk => $lab ) {
					$v = get_post_meta( $pid, $mk, true );
					if ( is_string( $v ) && $v !== '' ) {
						$rows[] = array( 'l' => $lab, 'v' => pw_fact_esc( $v ) );
					}
				}
				$rows[] = array( 'l' => 'Complimentary', 'v' => pw_fact_bool_cell( get_post_meta( $pid, '_pw_is_complimentary', true ) ) );
				$do     = (int) get_post_meta( $pid, '_pw_display_order', true );
				if ( $do > 0 ) {
					$rows[] = array( 'l' => 'Display order', 'v' => pw_fact_esc( (string) $do ) );
				}
				pw_fact_kv_table( 'Amenity / service / facility', $rows );
			} elseif ( $pt === 'pw_policy' ) {
				$rows   = array();
				$pc     = get_post_meta( $pid, '_pw_content', true );
				$rows[] = array(
					'l' => 'Policy body',
					'v' => is_string( $pc ) && $pc !== '' ? wp_kses_post( $pc ) : '—',
				);
				$do = (int) get_post_meta( $pid, '_pw_display_order', true );
				if ( $do > 0 ) {
					$rows[] = array( 'l' => 'Display order', 'v' => pw_fact_esc( (string) $do ) );
				}
				$rows[] = array( 'l' => 'Highlighted', 'v' => pw_fact_bool_cell( get_post_meta( $pid, '_pw_is_highlighted', true ) ) );
				$rows[] = array( 'l' => 'Active', 'v' => pw_fact_bool_cell( get_post_meta( $pid, '_pw_active', true ) ) );
				$ptt = get_the_terms( $pid, 'pw_policy_type' );
				if ( $ptt && ! is_wp_error( $ptt ) ) {
					$rows[] = array( 'l' => 'Policy type(s)', 'v' => pw_fact_esc( implode( ', ', wp_list_pluck( $ptt, 'name' ) ) ) );
				}
				pw_fact_kv_table( 'Policy', $rows );
			} elseif ( $pt === 'pw_nearby' ) {
				$rows = array();
				$dk   = (float) get_post_meta( $pid, '_pw_distance_km', true );
				if ( $dk > 0 ) {
					$rows[] = array( 'l' => 'Distance (km)', 'v' => pw_fact_esc( (string) $dk ) );
				}
				$ttm = (int) get_post_meta( $pid, '_pw_travel_time_min', true );
				if ( $ttm > 0 ) {
					$rows[] = array( 'l' => 'Travel time (minutes)', 'v' => pw_fact_esc( (string) $ttm ) );
				}
				$nlat = (float) get_post_meta( $pid, '_pw_lat', true );
				$nlng = (float) get_post_meta( $pid, '_pw_lng', true );
				if ( $nlat !== 0.0 || $nlng !== 0.0 ) {
					$rows[] = array( 'l' => 'Latitude', 'v' => pw_fact_esc( (string) $nlat ) );
					$rows[] = array( 'l' => 'Longitude', 'v' => pw_fact_esc( (string) $nlng ) );
				}
				$pu = get_post_meta( $pid, '_pw_place_url', true );
				if ( is_string( $pu ) && $pu !== '' ) {
					$rows[] = array( 'l' => 'Place URL', 'v' => pw_fact_url_cell( $pu ) );
				}
				$do = (int) get_post_meta( $pid, '_pw_display_order', true );
				if ( $do > 0 ) {
					$rows[] = array( 'l' => 'Display order', 'v' => pw_fact_esc( (string) $do ) );
				}
				$t1 = get_the_terms( $pid, 'pw_nearby_type' );
				if ( $t1 && ! is_wp_error( $t1 ) ) {
					$rows[] = array( 'l' => 'Location types', 'v' => pw_fact_esc( implode( ', ', wp_list_pluck( $t1, 'name' ) ) ) );
				}
				$t2 = get_the_terms( $pid, 'pw_transport_mode' );
				if ( $t2 && ! is_wp_error( $t2 ) ) {
					$rows[] = array( 'l' => 'Transport modes', 'v' => pw_fact_esc( implode( ', ', wp_list_pluck( $t2, 'name' ) ) ) );
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					$rows[] = array( 'l' => 'SEO meta title', 'v' => pw_fact_esc( $mt ) );
				}
				if ( is_string( $md ) && $md !== '' ) {
					$rows[] = array( 'l' => 'SEO meta description', 'v' => pw_fact_esc( $md ) );
				}
				pw_fact_kv_table( 'Nearby place', $rows );
			} elseif ( $pt === 'pw_event' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p class="pw-fact-excerpt"><strong>' . esc_html( 'Excerpt' ) . '</strong> — ' . esc_html( $ex ) . '</p>';
				}
				$ev_prop = (int) get_post_meta( $pid, '_pw_property_id', true );
				$rows    = array();
				$vid     = (int) get_post_meta( $pid, '_pw_venue_id', true );
				if ( $vid > 0 ) {
					$rows[] = array(
						'l' => 'Venue (meeting room)',
						'v' => pw_fact_esc( get_the_title( $vid ) ) . ' <code>' . esc_html( (string) $vid ) . '</code>',
					);
				}
				$ed = get_post_meta( $pid, '_pw_description', true );
				if ( is_string( $ed ) && $ed !== '' ) {
					$rows[] = array( 'l' => 'Short description', 'v' => pw_fact_esc( $ed ) );
				}
				$sd = get_post_meta( $pid, '_pw_start_datetime', true );
				if ( is_string( $sd ) && $sd !== '' ) {
					$rows[] = array( 'l' => 'Start (stored local wall time)', 'v' => pw_fact_esc( $sd ) );
					if ( function_exists( 'pw_event_local_datetime_to_iso8601' ) ) {
						$iso = pw_event_local_datetime_to_iso8601( $sd, $ev_prop );
						if ( $iso !== '' ) {
							$rows[] = array( 'l' => 'Start (ISO 8601, property TZ)', 'v' => pw_fact_esc( $iso ) );
						}
					}
				}
				$edt = get_post_meta( $pid, '_pw_end_datetime', true );
				if ( is_string( $edt ) && $edt !== '' ) {
					$rows[] = array( 'l' => 'End (stored local wall time)', 'v' => pw_fact_esc( $edt ) );
					if ( function_exists( 'pw_event_local_datetime_to_iso8601' ) ) {
						$iso = pw_event_local_datetime_to_iso8601( $edt, $ev_prop );
						if ( $iso !== '' ) {
							$rows[] = array( 'l' => 'End (ISO 8601, property TZ)', 'v' => pw_fact_esc( $iso ) );
						}
					}
				}
				$cap = (int) get_post_meta( $pid, '_pw_capacity', true );
				if ( $cap > 0 ) {
					$rows[] = array( 'l' => 'Capacity', 'v' => pw_fact_esc( (string) $cap ) );
				}
				$pf = (float) get_post_meta( $pid, '_pw_price_from', true );
				if ( $pf > 0 ) {
					$rows[] = array( 'l' => 'Price from', 'v' => pw_fact_esc( (string) $pf ) );
				}
				$bu = get_post_meta( $pid, '_pw_booking_url', true );
				if ( is_string( $bu ) && $bu !== '' ) {
					$rows[] = array( 'l' => 'Booking URL', 'v' => pw_fact_url_cell( $bu ) );
				}
				$rr = get_post_meta( $pid, '_pw_recurrence_rule', true );
				if ( is_string( $rr ) && $rr !== '' ) {
					$rows[] = array( 'l' => 'Recurrence (iCal RRULE)', 'v' => pw_fact_esc( $rr ) );
				}
				$es = get_post_meta( $pid, '_pw_event_status', true );
				if ( is_string( $es ) && $es !== '' ) {
					$rows[] = array( 'l' => 'Event status', 'v' => pw_fact_esc( $es ) );
				}
				$am = get_post_meta( $pid, '_pw_event_attendance_mode', true );
				if ( is_string( $am ) && $am !== '' ) {
					$rows[] = array( 'l' => 'Attendance mode', 'v' => pw_fact_esc( $am ) );
				}
				$et = get_the_terms( $pid, 'pw_event_type' );
				if ( $et && ! is_wp_error( $et ) ) {
					$rows[] = array( 'l' => 'Event types', 'v' => pw_fact_esc( implode( ', ', wp_list_pluck( $et, 'name' ) ) ) );
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					$rows[] = array( 'l' => 'SEO meta title', 'v' => pw_fact_esc( $mt ) );
				}
				if ( is_string( $md ) && $md !== '' ) {
					$rows[] = array( 'l' => 'SEO meta description', 'v' => pw_fact_esc( $md ) );
				}
				pw_fact_kv_table( 'Event details', $rows );
				$eo = get_the_terms( $pid, 'pw_event_organiser' );
				if ( $eo && ! is_wp_error( $eo ) ) {
					echo '<table class="pw-fact-organisers"><caption>' . esc_html( 'Organisers' ) . '</caption>';
					echo '<thead><tr><th scope="col">' . esc_html( 'Name' ) . '</th><th scope="col">' . esc_html( 'URL' ) . '</th></tr></thead><tbody>';
					foreach ( $eo as $term ) {
						$ou = get_term_meta( $term->term_id, 'organiser_url', true );
						echo '<tr><th scope="row">' . esc_html( $term->name ) . '</th><td>' . ( is_string( $ou ) && $ou !== '' ? pw_fact_url_cell( $ou ) : '—' ) . '</td></tr>';
					}
					echo '</tbody></table>';
				}
				pw_fact_gallery_table( get_post_meta( $pid, '_pw_gallery', true ) );
			} elseif ( $pt === 'pw_experience' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p class="pw-fact-excerpt"><strong>' . esc_html( 'Excerpt' ) . '</strong> — ' . esc_html( $ex ) . '</p>';
				}
				$rows = array();
				$ed   = get_post_meta( $pid, '_pw_description', true );
				if ( is_string( $ed ) && $ed !== '' ) {
					$rows[] = array( 'l' => 'Description', 'v' => pw_fact_esc( $ed ) );
				}
				$dh = (float) get_post_meta( $pid, '_pw_duration_hours', true );
				if ( $dh > 0 ) {
					$rows[] = array( 'l' => 'Duration (hours)', 'v' => pw_fact_esc( (string) $dh ) );
				}
				$pf = (float) get_post_meta( $pid, '_pw_price_from', true );
				if ( $pf > 0 ) {
					$rows[] = array( 'l' => 'Price from', 'v' => pw_fact_esc( (string) $pf ) );
				}
				$bu = get_post_meta( $pid, '_pw_booking_url', true );
				if ( is_string( $bu ) && $bu !== '' ) {
					$rows[] = array( 'l' => 'Booking URL', 'v' => pw_fact_url_cell( $bu ) );
				}
				$rows[] = array( 'l' => 'Complimentary', 'v' => pw_fact_bool_cell( get_post_meta( $pid, '_pw_is_complimentary', true ) ) );
				$do = (int) get_post_meta( $pid, '_pw_display_order', true );
				if ( $do > 0 ) {
					$rows[] = array( 'l' => 'Display order', 'v' => pw_fact_esc( (string) $do ) );
				}
				$ec = get_the_terms( $pid, 'pw_experience_category' );
				if ( $ec && ! is_wp_error( $ec ) ) {
					$rows[] = array( 'l' => 'Categories', 'v' => pw_fact_esc( implode( ', ', wp_list_pluck( $ec, 'name' ) ) ) );
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					$rows[] = array( 'l' => 'SEO meta title', 'v' => pw_fact_esc( $mt ) );
				}
				if ( is_string( $md ) && $md !== '' ) {
					$rows[] = array( 'l' => 'SEO meta description', 'v' => pw_fact_esc( $md ) );
				}
				pw_fact_kv_table( 'Experience details', $rows );
				pw_fact_gallery_table( get_post_meta( $pid, '_pw_gallery', true ) );
			} elseif ( $pt === 'pw_faq' ) {
				$rows   = array();
				$do     = (int) get_post_meta( $pid, '_pw_display_order', true );
				$rows[] = array( 'l' => 'Display order', 'v' => $do > 0 ? pw_fact_esc( (string) $do ) : '—' );
				$ans    = get_post_meta( $pid, '_pw_answer', true );
				$rows[] = array(
					'l' => 'Answer',
					'v' => is_string( $ans ) && $ans !== '' ? wp_kses_post( $ans ) : '—',
				);
				pw_fact_kv_table( 'FAQ', $rows );
			} elseif ( $pt === 'pw_offer' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p class="pw-fact-excerpt"><strong>' . esc_html( 'Excerpt' ) . '</strong> — ' . esc_html( $ex ) . '</p>';
				}
				$rows = array();
				$ot   = get_post_meta( $pid, '_pw_offer_type', true );
				if ( is_string( $ot ) && $ot !== '' ) {
					$rows[] = array( 'l' => 'Offer type', 'v' => pw_fact_esc( $ot ) );
				}
				foreach ( array( '_pw_valid_from' => 'Valid from', '_pw_valid_to' => 'Valid to' ) as $mk => $lab ) {
					$v = get_post_meta( $pid, $mk, true );
					if ( is_string( $v ) && $v !== '' ) {
						$rows[] = array( 'l' => $lab, 'v' => pw_fact_esc( $v ) );
					}
				}
				$bu = get_post_meta( $pid, '_pw_booking_url', true );
				if ( is_string( $bu ) && $bu !== '' ) {
					$rows[] = array( 'l' => 'Booking URL', 'v' => pw_fact_url_cell( $bu ) );
				}
				$rows[] = array( 'l' => 'Featured', 'v' => pw_fact_bool_cell( get_post_meta( $pid, '_pw_is_featured', true ) ) );
				$dt = get_post_meta( $pid, '_pw_discount_type', true );
				if ( is_string( $dt ) && $dt !== '' ) {
					$rows[] = array( 'l' => 'Discount type', 'v' => pw_fact_esc( $dt ) );
				}
				$dv = (float) get_post_meta( $pid, '_pw_discount_value', true );
				if ( $dv > 0 ) {
					$rows[] = array( 'l' => 'Discount value', 'v' => pw_fact_esc( (string) $dv ) );
				}
				$msn = (int) get_post_meta( $pid, '_pw_minimum_stay_nights', true );
				if ( $msn > 0 ) {
					$rows[] = array( 'l' => 'Minimum stay (nights)', 'v' => pw_fact_esc( (string) $msn ) );
				}
				$do = (int) get_post_meta( $pid, '_pw_display_order', true );
				if ( $do > 0 ) {
					$rows[] = array( 'l' => 'Display order', 'v' => pw_fact_esc( (string) $do ) );
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					$rows[] = array( 'l' => 'SEO meta title', 'v' => pw_fact_esc( $mt ) );
				}
				if ( is_string( $md ) && $md !== '' ) {
					$rows[] = array( 'l' => 'SEO meta description', 'v' => pw_fact_esc( $md ) );
				}
				pw_fact_kv_table( 'Offer details', $rows );
				$rts = get_post_meta( $pid, '_pw_room_types', true );
				if ( is_array( $rts ) && $rts ) {
					echo '<p><strong>' . esc_html( 'Applicable room types' ) . '</strong></p><ul class="pw-fact-inline-list">';
					foreach ( $rts as $rid ) {
						$rid = (int) $rid;
						if ( $rid <= 0 ) {
							continue;
						}
						echo '<li>' . esc_html( get_the_title( $rid ) ) . ' <code>' . esc_html( (string) $rid ) . '</code></li>';
					}
					echo '</ul>';
				}
			}
			echo '</article>';
		}
		echo '</section>';
	}
