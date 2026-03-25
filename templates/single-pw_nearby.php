<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pw_nearby_post_id = get_the_ID();

	do_action( 'pw_before_single_nearby', $pw_nearby_post_id );

	do_action( 'pw_before_nearby_page_header', $pw_nearby_post_id );
	pw_get_template_part( 'single-nearby/page-header', '', [ 'post_id' => $pw_nearby_post_id ] );
	do_action( 'pw_after_nearby_page_header', $pw_nearby_post_id );

	do_action( 'pw_before_nearby_description', $pw_nearby_post_id );
	pw_get_template_part( 'single-nearby/description', '', [ 'post_id' => $pw_nearby_post_id ] );
	do_action( 'pw_after_nearby_description', $pw_nearby_post_id );

	do_action( 'pw_before_nearby_key_details_strip', $pw_nearby_post_id );
	pw_get_template_part( 'single-nearby/key-details-strip', '', [ 'post_id' => $pw_nearby_post_id ] );
	do_action( 'pw_after_nearby_key_details_strip', $pw_nearby_post_id );

	do_action( 'pw_before_nearby_map', $pw_nearby_post_id );
	pw_get_template_part( 'single-nearby/map', '', [ 'post_id' => $pw_nearby_post_id ] );
	do_action( 'pw_after_nearby_map', $pw_nearby_post_id );

	do_action( 'pw_before_nearby_getting_there', $pw_nearby_post_id );
	pw_get_template_part( 'single-nearby/getting-there', '', [ 'post_id' => $pw_nearby_post_id ] );
	do_action( 'pw_after_nearby_getting_there', $pw_nearby_post_id );

	do_action( 'pw_before_nearby_concierge_tip', $pw_nearby_post_id );
	pw_get_template_part( 'single-nearby/concierge-tip', '', [ 'post_id' => $pw_nearby_post_id ] );
	do_action( 'pw_after_nearby_concierge_tip', $pw_nearby_post_id );

	do_action( 'pw_before_nearby_related_places', $pw_nearby_post_id );
	pw_get_template_part( 'single-nearby/related-places', '', [ 'post_id' => $pw_nearby_post_id ] );
	do_action( 'pw_after_nearby_related_places', $pw_nearby_post_id );

	do_action( 'pw_after_single_nearby', $pw_nearby_post_id );
endwhile;

get_footer();

