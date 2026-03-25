<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pw_experience_post_id = get_the_ID();

	do_action( 'pw_before_single_experience', $pw_experience_post_id );

	do_action( 'pw_before_experience_hero', $pw_experience_post_id );
	pw_get_template_part( 'single-experience/hero', '', [ 'post_id' => $pw_experience_post_id ] );
	do_action( 'pw_after_experience_hero', $pw_experience_post_id );

	do_action( 'pw_before_experience_overview', $pw_experience_post_id );
	pw_get_template_part( 'single-experience/overview', '', [ 'post_id' => $pw_experience_post_id ] );
	do_action( 'pw_after_experience_overview', $pw_experience_post_id );

	do_action( 'pw_before_experience_key_details_strip', $pw_experience_post_id );
	pw_get_template_part( 'single-experience/key-details-strip', '', [ 'post_id' => $pw_experience_post_id ] );
	do_action( 'pw_after_experience_key_details_strip', $pw_experience_post_id );

	do_action( 'pw_before_experience_booking_cta', $pw_experience_post_id );
	pw_get_template_part( 'single-experience/booking-cta', '', [ 'post_id' => $pw_experience_post_id ] );
	do_action( 'pw_after_experience_booking_cta', $pw_experience_post_id );

	do_action( 'pw_before_experience_related_experiences', $pw_experience_post_id );
	pw_get_template_part( 'single-experience/related-experiences', '', [ 'post_id' => $pw_experience_post_id ] );
	do_action( 'pw_after_experience_related_experiences', $pw_experience_post_id );

	do_action( 'pw_before_experience_cta', $pw_experience_post_id );
	pw_get_template_part( 'single-experience/cta', '', [ 'post_id' => $pw_experience_post_id ] );
	do_action( 'pw_after_experience_cta', $pw_experience_post_id );

	do_action( 'pw_after_single_experience', $pw_experience_post_id );
endwhile;

get_footer();

