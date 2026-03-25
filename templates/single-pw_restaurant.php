<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pw_restaurant_post_id = get_the_ID();

	do_action( 'pw_before_single_restaurant', $pw_restaurant_post_id );

	do_action( 'pw_before_restaurant_hero', $pw_restaurant_post_id );
	pw_get_template_part( 'single-restaurant/hero', '', [ 'post_id' => $pw_restaurant_post_id ] );
	do_action( 'pw_after_restaurant_hero', $pw_restaurant_post_id );

	do_action( 'pw_before_restaurant_introduction', $pw_restaurant_post_id );
	pw_get_template_part( 'single-restaurant/introduction', '', [ 'post_id' => $pw_restaurant_post_id ] );
	do_action( 'pw_after_restaurant_introduction', $pw_restaurant_post_id );

	do_action( 'pw_before_restaurant_menu_preview', $pw_restaurant_post_id );
	pw_get_template_part( 'single-restaurant/menu-preview', '', [ 'post_id' => $pw_restaurant_post_id ] );
	do_action( 'pw_after_restaurant_menu_preview', $pw_restaurant_post_id );

	do_action( 'pw_before_restaurant_opening_hours', $pw_restaurant_post_id );
	pw_get_template_part( 'single-restaurant/opening-hours', '', [ 'post_id' => $pw_restaurant_post_id ] );
	do_action( 'pw_after_restaurant_opening_hours', $pw_restaurant_post_id );

	do_action( 'pw_before_restaurant_reservation_cta', $pw_restaurant_post_id );
	pw_get_template_part( 'single-restaurant/reservation-cta', '', [ 'post_id' => $pw_restaurant_post_id ] );
	do_action( 'pw_after_restaurant_reservation_cta', $pw_restaurant_post_id );

	do_action( 'pw_before_restaurant_gallery', $pw_restaurant_post_id );
	pw_get_template_part( 'single-restaurant/gallery', '', [ 'post_id' => $pw_restaurant_post_id ] );
	do_action( 'pw_after_restaurant_gallery', $pw_restaurant_post_id );

	do_action( 'pw_before_restaurant_private_dining', $pw_restaurant_post_id );
	pw_get_template_part( 'single-restaurant/private-dining', '', [ 'post_id' => $pw_restaurant_post_id ] );
	do_action( 'pw_after_restaurant_private_dining', $pw_restaurant_post_id );

	do_action( 'pw_before_restaurant_location', $pw_restaurant_post_id );
	pw_get_template_part( 'single-restaurant/location', '', [ 'post_id' => $pw_restaurant_post_id ] );
	do_action( 'pw_after_restaurant_location', $pw_restaurant_post_id );

	do_action( 'pw_before_restaurant_faq', $pw_restaurant_post_id );
	pw_get_template_part( 'single-restaurant/faq', '', [ 'post_id' => $pw_restaurant_post_id ] );
	do_action( 'pw_after_restaurant_faq', $pw_restaurant_post_id );

	do_action( 'pw_before_restaurant_cta', $pw_restaurant_post_id );
	pw_get_template_part( 'single-restaurant/cta', '', [ 'post_id' => $pw_restaurant_post_id ] );
	do_action( 'pw_after_restaurant_cta', $pw_restaurant_post_id );

	do_action( 'pw_after_single_restaurant', $pw_restaurant_post_id );
endwhile;

get_footer();

