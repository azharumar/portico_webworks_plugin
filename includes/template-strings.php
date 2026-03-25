<?php
defined( 'ABSPATH' ) || exit;

function pw_get_cta_label( $post_type, $post_id = 0 ) {
	$post_type = sanitize_key( (string) $post_type );
	$post_id   = (int) $post_id;

	$defaults = [
		'pw_room_type'    => __( 'Book now', 'portico-webworks' ),
		'pw_restaurant'   => __( 'Reserve a table', 'portico-webworks' ),
		'pw_spa'          => __( 'Book a treatment', 'portico-webworks' ),
		'pw_meeting_room' => __( 'Request a proposal', 'portico-webworks' ),
		'pw_experience'   => __( 'Book this experience', 'portico-webworks' ),
		'pw_event'        => __( 'Get tickets', 'portico-webworks' ),
		'pw_offer'        => __( 'Book this offer', 'portico-webworks' ),
		'pw_nearby'       => __( 'Learn more', 'portico-webworks' ),
	];

	$label = $defaults[ $post_type ] ?? __( 'Book now', 'portico-webworks' );

	return apply_filters( 'pw_cta_label', $label, $post_type, $post_id );
}

function pw_get_room_overview_occupancy_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Max occupancy', 'portico-webworks' );
	return apply_filters( 'pw_room_overview_occupancy_label', $default, $post_id );
}

function pw_get_room_overview_size_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Room size', 'portico-webworks' );
	return apply_filters( 'pw_room_overview_size_label', $default, $post_id );
}

function pw_get_room_hero_title( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$title   = $post_id ? get_the_title( $post_id ) : '';
	return apply_filters( 'pw_room_hero_title', $title, $post_id );
}

function pw_get_room_booking_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Reservation', 'portico-webworks' );
	return apply_filters( 'pw_room_booking_heading', $default, $post_id );
}

function pw_get_room_rate_from_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'From', 'portico-webworks' );
	return apply_filters( 'pw_room_rate_from_label', $default, $post_id );
}

function pw_get_room_rate_to_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'To', 'portico-webworks' );
	return apply_filters( 'pw_room_rate_to_label', $default, $post_id );
}

function pw_get_room_overview_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Overview', 'portico-webworks' );
	return apply_filters( 'pw_room_overview_heading', $default, $post_id );
}

function pw_get_room_overview_adults_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Max adults', 'portico-webworks' );
	return apply_filters( 'pw_room_overview_adults_label', $default, $post_id );
}

function pw_get_room_overview_children_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Max children', 'portico-webworks' );
	return apply_filters( 'pw_room_overview_children_label', $default, $post_id );
}

function pw_get_room_overview_extra_beds_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Extra beds', 'portico-webworks' );
	return apply_filters( 'pw_room_overview_extra_beds_label', $default, $post_id );
}

function pw_get_room_size_sqm_suffix( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'm²', 'portico-webworks' );
	return apply_filters( 'pw_room_size_sqm_suffix', $default, $post_id );
}

function pw_get_room_size_sqft_suffix( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'sq ft', 'portico-webworks' );
	return apply_filters( 'pw_room_size_sqft_suffix', $default, $post_id );
}

function pw_get_room_description_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'About this room', 'portico-webworks' );
	return apply_filters( 'pw_room_description_heading', $default, $post_id );
}

function pw_get_room_amenities_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Amenities & details', 'portico-webworks' );
	return apply_filters( 'pw_room_amenities_heading', $default, $post_id );
}

function pw_get_room_gallery_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Gallery', 'portico-webworks' );
	return apply_filters( 'pw_room_gallery_heading', $default, $post_id );
}

function pw_get_room_rates_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Rates', 'portico-webworks' );
	return apply_filters( 'pw_room_rates_heading', $default, $post_id );
}

function pw_get_room_rates_price_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Price', 'portico-webworks' );
	return apply_filters( 'pw_room_rates_price_label', $default, $post_id );
}

function pw_get_room_rates_plan_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Plan', 'portico-webworks' );
	return apply_filters( 'pw_room_rates_plan_label', $default, $post_id );
}

function pw_get_room_upgrade_prompt_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Upgrade your stay', 'portico-webworks' );
	return apply_filters( 'pw_room_upgrade_prompt_heading', $default, $post_id );
}

function pw_get_room_related_offers_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Offers for this room', 'portico-webworks' );
	return apply_filters( 'pw_room_related_offers_heading', $default, $post_id );
}

function pw_get_room_cta_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Ready to book?', 'portico-webworks' );
	return apply_filters( 'pw_room_cta_heading', $default, $post_id );
}

function pw_get_restaurant_hero_title( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$title   = $post_id ? get_the_title( $post_id ) : '';
	return apply_filters( 'pw_restaurant_hero_title', $title, $post_id );
}

function pw_get_restaurant_introduction_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'About the restaurant', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_introduction_heading', $default, $post_id );
}

function pw_get_restaurant_menu_preview_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Menu preview', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_menu_preview_heading', $default, $post_id );
}

function pw_get_restaurant_opening_hours_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Opening hours', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_opening_hours_heading', $default, $post_id );
}

function pw_get_restaurant_opening_hours_day_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Day', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_opening_hours_day_label', $default, $post_id );
}

function pw_get_restaurant_opening_hours_time_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Time', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_opening_hours_time_label', $default, $post_id );
}

function pw_get_restaurant_reservation_cta_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Reserve your table', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_reservation_cta_heading', $default, $post_id );
}

function pw_get_restaurant_gallery_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Gallery', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_gallery_heading', $default, $post_id );
}

function pw_get_restaurant_private_dining_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Private dining', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_private_dining_heading', $default, $post_id );
}

function pw_get_restaurant_location_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Location', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_location_heading', $default, $post_id );
}

function pw_get_restaurant_cuisine_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Cuisine', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_cuisine_label', $default, $post_id );
}

function pw_get_restaurant_capacity_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Seating capacity', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_capacity_label', $default, $post_id );
}

function pw_get_restaurant_faq_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Frequently asked questions', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_faq_heading', $default, $post_id );
}

function pw_get_restaurant_cta_heading( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'Book your dining experience', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_cta_heading', $default, $post_id );
}

function pw_get_restaurant_menu_preview_menu_label( $post_id = 0 ) {
	$post_id = (int) $post_id;
	$default = __( 'View menu', 'portico-webworks' );
	return apply_filters( 'pw_restaurant_menu_preview_menu_label', $default, $post_id );
}
