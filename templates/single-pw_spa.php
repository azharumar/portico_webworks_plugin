<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pw_spa_post_id = get_the_ID();

	do_action( 'pw_before_single_spa', $pw_spa_post_id );

	do_action( 'pw_before_spa_hero', $pw_spa_post_id );
	pw_get_template_part( 'single-spa/hero', '', [ 'post_id' => $pw_spa_post_id ] );
	do_action( 'pw_after_spa_hero', $pw_spa_post_id );

	do_action( 'pw_before_spa_introduction', $pw_spa_post_id );
	pw_get_template_part( 'single-spa/introduction', '', [ 'post_id' => $pw_spa_post_id ] );
	do_action( 'pw_after_spa_introduction', $pw_spa_post_id );

	do_action( 'pw_before_spa_signature_treatments', $pw_spa_post_id );
	pw_get_template_part( 'single-spa/signature-treatments', '', [ 'post_id' => $pw_spa_post_id ] );
	do_action( 'pw_after_spa_signature_treatments', $pw_spa_post_id );

	do_action( 'pw_before_spa_treatments_menu', $pw_spa_post_id );
	pw_get_template_part( 'single-spa/treatments-menu', '', [ 'post_id' => $pw_spa_post_id ] );
	do_action( 'pw_after_spa_treatments_menu', $pw_spa_post_id );

	do_action( 'pw_before_spa_facilities', $pw_spa_post_id );
	pw_get_template_part( 'single-spa/facilities', '', [ 'post_id' => $pw_spa_post_id ] );
	do_action( 'pw_after_spa_facilities', $pw_spa_post_id );

	do_action( 'pw_before_spa_opening_hours', $pw_spa_post_id );
	pw_get_template_part( 'single-spa/opening-hours', '', [ 'post_id' => $pw_spa_post_id ] );
	do_action( 'pw_after_spa_opening_hours', $pw_spa_post_id );

	do_action( 'pw_before_spa_booking_cta', $pw_spa_post_id );
	pw_get_template_part( 'single-spa/booking-cta', '', [ 'post_id' => $pw_spa_post_id ] );
	do_action( 'pw_after_spa_booking_cta', $pw_spa_post_id );

	do_action( 'pw_before_spa_gallery', $pw_spa_post_id );
	pw_get_template_part( 'single-spa/gallery', '', [ 'post_id' => $pw_spa_post_id ] );
	do_action( 'pw_after_spa_gallery', $pw_spa_post_id );

	do_action( 'pw_before_spa_faq', $pw_spa_post_id );
	pw_get_template_part( 'single-spa/faq', '', [ 'post_id' => $pw_spa_post_id ] );
	do_action( 'pw_after_spa_faq', $pw_spa_post_id );

	do_action( 'pw_before_spa_cta', $pw_spa_post_id );
	pw_get_template_part( 'single-spa/cta', '', [ 'post_id' => $pw_spa_post_id ] );
	do_action( 'pw_after_spa_cta', $pw_spa_post_id );

	do_action( 'pw_after_single_spa', $pw_spa_post_id );
endwhile;

get_footer();

