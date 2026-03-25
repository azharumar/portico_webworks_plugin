<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pw_offer_post_id = get_the_ID();

	do_action( 'pw_before_single_offer', $pw_offer_post_id );

	do_action( 'pw_before_offer_hero', $pw_offer_post_id );
	pw_get_template_part( 'single-offer/hero', '', [ 'post_id' => $pw_offer_post_id ] );
	do_action( 'pw_after_offer_hero', $pw_offer_post_id );

	do_action( 'pw_before_offer_summary', $pw_offer_post_id );
	pw_get_template_part( 'single-offer/summary', '', [ 'post_id' => $pw_offer_post_id ] );
	do_action( 'pw_after_offer_summary', $pw_offer_post_id );

	do_action( 'pw_before_offer_key_terms_strip', $pw_offer_post_id );
	pw_get_template_part( 'single-offer/key-terms-strip', '', [ 'post_id' => $pw_offer_post_id ] );
	do_action( 'pw_after_offer_key_terms_strip', $pw_offer_post_id );

	do_action( 'pw_before_offer_whats_included', $pw_offer_post_id );
	pw_get_template_part( 'single-offer/whats-included', '', [ 'post_id' => $pw_offer_post_id ] );
	do_action( 'pw_after_offer_whats_included', $pw_offer_post_id );

	do_action( 'pw_before_offer_booking_cta', $pw_offer_post_id );
	pw_get_template_part( 'single-offer/booking-cta', '', [ 'post_id' => $pw_offer_post_id ] );
	do_action( 'pw_after_offer_booking_cta', $pw_offer_post_id );

	do_action( 'pw_before_offer_related_offers', $pw_offer_post_id );
	pw_get_template_part( 'single-offer/related-offers', '', [ 'post_id' => $pw_offer_post_id ] );
	do_action( 'pw_after_offer_related_offers', $pw_offer_post_id );

	do_action( 'pw_before_offer_cta', $pw_offer_post_id );
	pw_get_template_part( 'single-offer/cta', '', [ 'post_id' => $pw_offer_post_id ] );
	do_action( 'pw_after_offer_cta', $pw_offer_post_id );

	do_action( 'pw_after_single_offer', $pw_offer_post_id );
endwhile;

get_footer();

