<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pw_event_post_id = get_the_ID();

	do_action( 'pw_before_single_event', $pw_event_post_id );

	do_action( 'pw_before_event_hero', $pw_event_post_id );
	pw_get_template_part( 'single-event/hero', '', [ 'post_id' => $pw_event_post_id ] );
	do_action( 'pw_after_event_hero', $pw_event_post_id );

	do_action( 'pw_before_event_description', $pw_event_post_id );
	pw_get_template_part( 'single-event/description', '', [ 'post_id' => $pw_event_post_id ] );
	do_action( 'pw_after_event_description', $pw_event_post_id );

	do_action( 'pw_before_event_key_details_strip', $pw_event_post_id );
	pw_get_template_part( 'single-event/key-details-strip', '', [ 'post_id' => $pw_event_post_id ] );
	do_action( 'pw_after_event_key_details_strip', $pw_event_post_id );

	do_action( 'pw_before_event_programme', $pw_event_post_id );
	pw_get_template_part( 'single-event/programme', '', [ 'post_id' => $pw_event_post_id ] );
	do_action( 'pw_after_event_programme', $pw_event_post_id );

	do_action( 'pw_before_event_ticket_cta', $pw_event_post_id );
	pw_get_template_part( 'single-event/ticket-cta', '', [ 'post_id' => $pw_event_post_id ] );
	do_action( 'pw_after_event_ticket_cta', $pw_event_post_id );

	do_action( 'pw_before_event_venue_details', $pw_event_post_id );
	pw_get_template_part( 'single-event/venue-details', '', [ 'post_id' => $pw_event_post_id ] );
	do_action( 'pw_after_event_venue_details', $pw_event_post_id );

	do_action( 'pw_before_event_add_ons', $pw_event_post_id );
	pw_get_template_part( 'single-event/add-ons', '', [ 'post_id' => $pw_event_post_id ] );
	do_action( 'pw_after_event_add_ons', $pw_event_post_id );

	do_action( 'pw_before_event_cta', $pw_event_post_id );
	pw_get_template_part( 'single-event/cta', '', [ 'post_id' => $pw_event_post_id ] );
	do_action( 'pw_after_event_cta', $pw_event_post_id );

	do_action( 'pw_after_single_event', $pw_event_post_id );
endwhile;

get_footer();

