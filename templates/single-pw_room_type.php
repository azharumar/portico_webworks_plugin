<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pw_room_post_id = get_the_ID();

	do_action( 'pw_before_single_room', $pw_room_post_id );

	do_action( 'pw_before_room_hero', $pw_room_post_id );
	pw_get_template_part( 'single-room/hero', '', [ 'post_id' => $pw_room_post_id ] );
	do_action( 'pw_after_room_hero', $pw_room_post_id );

	do_action( 'pw_before_room_booking_widget', $pw_room_post_id );
	pw_get_template_part( 'single-room/booking-widget', '', [ 'post_id' => $pw_room_post_id ] );
	do_action( 'pw_after_room_booking_widget', $pw_room_post_id );

	do_action( 'pw_before_room_overview', $pw_room_post_id );
	pw_get_template_part( 'single-room/overview', '', [ 'post_id' => $pw_room_post_id ] );
	do_action( 'pw_after_room_overview', $pw_room_post_id );

	do_action( 'pw_before_room_description', $pw_room_post_id );
	pw_get_template_part( 'single-room/description', '', [ 'post_id' => $pw_room_post_id ] );
	do_action( 'pw_after_room_description', $pw_room_post_id );

	do_action( 'pw_before_room_amenities', $pw_room_post_id );
	pw_get_template_part( 'single-room/amenities', '', [ 'post_id' => $pw_room_post_id ] );
	do_action( 'pw_after_room_amenities', $pw_room_post_id );

	do_action( 'pw_before_room_gallery', $pw_room_post_id );
	pw_get_template_part( 'single-room/gallery', '', [ 'post_id' => $pw_room_post_id ] );
	do_action( 'pw_after_room_gallery', $pw_room_post_id );

	do_action( 'pw_before_room_rates', $pw_room_post_id );
	pw_get_template_part( 'single-room/rates', '', [ 'post_id' => $pw_room_post_id ] );
	do_action( 'pw_after_room_rates', $pw_room_post_id );

	do_action( 'pw_before_room_upgrade_prompt', $pw_room_post_id );
	pw_get_template_part( 'single-room/upgrade-prompt', '', [ 'post_id' => $pw_room_post_id ] );
	do_action( 'pw_after_room_upgrade_prompt', $pw_room_post_id );

	do_action( 'pw_before_room_related_offers', $pw_room_post_id );
	pw_get_template_part( 'single-room/related-offers', '', [ 'post_id' => $pw_room_post_id ] );
	do_action( 'pw_after_room_related_offers', $pw_room_post_id );

	do_action( 'pw_before_room_cta', $pw_room_post_id );
	pw_get_template_part( 'single-room/cta', '', [ 'post_id' => $pw_room_post_id ] );
	do_action( 'pw_after_room_cta', $pw_room_post_id );

	do_action( 'pw_after_single_room', $pw_room_post_id );

endwhile;

get_footer();
