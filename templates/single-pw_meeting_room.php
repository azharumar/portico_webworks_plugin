<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pw_meeting_room_post_id = get_the_ID();

	do_action( 'pw_before_single_meeting_room', $pw_meeting_room_post_id );

	do_action( 'pw_before_meeting_room_hero', $pw_meeting_room_post_id );
	pw_get_template_part( 'single-meeting-room/hero', '', [ 'post_id' => $pw_meeting_room_post_id ] );
	do_action( 'pw_after_meeting_room_hero', $pw_meeting_room_post_id );

	do_action( 'pw_before_meeting_room_capacity_table', $pw_meeting_room_post_id );
	pw_get_template_part( 'single-meeting-room/capacity-table', '', [ 'post_id' => $pw_meeting_room_post_id ] );
	do_action( 'pw_after_meeting_room_capacity_table', $pw_meeting_room_post_id );

	do_action( 'pw_before_meeting_room_room_specs', $pw_meeting_room_post_id );
	pw_get_template_part( 'single-meeting-room/room-specs', '', [ 'post_id' => $pw_meeting_room_post_id ] );
	do_action( 'pw_after_meeting_room_room_specs', $pw_meeting_room_post_id );

	do_action( 'pw_before_meeting_room_description', $pw_meeting_room_post_id );
	pw_get_template_part( 'single-meeting-room/description', '', [ 'post_id' => $pw_meeting_room_post_id ] );
	do_action( 'pw_after_meeting_room_description', $pw_meeting_room_post_id );

	do_action( 'pw_before_meeting_room_catering_note', $pw_meeting_room_post_id );
	pw_get_template_part( 'single-meeting-room/catering-note', '', [ 'post_id' => $pw_meeting_room_post_id ] );
	do_action( 'pw_after_meeting_room_catering_note', $pw_meeting_room_post_id );

	do_action( 'pw_before_meeting_room_floor_plan', $pw_meeting_room_post_id );
	pw_get_template_part( 'single-meeting-room/floor-plan', '', [ 'post_id' => $pw_meeting_room_post_id ] );
	do_action( 'pw_after_meeting_room_floor_plan', $pw_meeting_room_post_id );

	do_action( 'pw_before_meeting_room_setup_gallery', $pw_meeting_room_post_id );
	pw_get_template_part( 'single-meeting-room/setup-gallery', '', [ 'post_id' => $pw_meeting_room_post_id ] );
	do_action( 'pw_after_meeting_room_setup_gallery', $pw_meeting_room_post_id );

	do_action( 'pw_before_meeting_room_adjacent_venues', $pw_meeting_room_post_id );
	pw_get_template_part( 'single-meeting-room/adjacent-venues', '', [ 'post_id' => $pw_meeting_room_post_id ] );
	do_action( 'pw_after_meeting_room_adjacent_venues', $pw_meeting_room_post_id );

	do_action( 'pw_before_meeting_room_rfp_cta', $pw_meeting_room_post_id );
	pw_get_template_part( 'single-meeting-room/rfp-cta', '', [ 'post_id' => $pw_meeting_room_post_id ] );
	do_action( 'pw_after_meeting_room_rfp_cta', $pw_meeting_room_post_id );

	do_action( 'pw_before_meeting_room_faq', $pw_meeting_room_post_id );
	pw_get_template_part( 'single-meeting-room/faq', '', [ 'post_id' => $pw_meeting_room_post_id ] );
	do_action( 'pw_after_meeting_room_faq', $pw_meeting_room_post_id );

	do_action( 'pw_before_meeting_room_cta', $pw_meeting_room_post_id );
	pw_get_template_part( 'single-meeting-room/cta', '', [ 'post_id' => $pw_meeting_room_post_id ] );
	do_action( 'pw_after_meeting_room_cta', $pw_meeting_room_post_id );

	do_action( 'pw_after_single_meeting_room', $pw_meeting_room_post_id );
endwhile;

get_footer();

