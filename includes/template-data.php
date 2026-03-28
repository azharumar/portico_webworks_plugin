<?php
defined( 'ABSPATH' ) || exit;

function pw_room_get_related_offers( $room_post_id ) {
	return [];
}

function pw_restaurant_get_faqs( $post_id ) {
	return [];
}

function pw_spa_get_faqs( $post_id ) {
	return [];
}

function pw_meeting_room_get_faqs( $post_id ) {
	return [];
}

function pw_meeting_room_get_adjacent_venues( $post_id, $limit = 3 ) {
	return [];
}

function pw_meeting_room_get_primary_contact( $post_id ) {
	return null;
}

function pw_experience_get_primary_contact( $post_id ) {
	return null;
}

function pw_experience_get_related( $post_id, $limit = 3 ) {
	return [];
}

function pw_offer_get_related( $post_id, $limit = 3 ) {
	return [];
}

function pw_nearby_get_related( $post_id, $limit = 3 ) {
	return [];
}

function pw_archive_get_room_offers( $property_id, $limit = 3 ) {
	return [];
}

function pw_archive_get_upcoming_events( $property_id, $limit = 6 ) {
	return [];
}

function pw_archive_get_past_events( $property_id, $limit = 3 ) {
	return [];
}

function pw_restaurant_get_archive_primary_contact( $property_id ) {
	return null;
}

function pw_meeting_room_get_archive_primary_contact( $property_id ) {
	return null;
}

function pw_property_get_room_preview( $property_id, $limit = 4 ) {
	return [];
}

function pw_property_get_restaurant_preview( $property_id, $limit = 3 ) {
	return [];
}

function pw_property_get_experience_preview( $property_id ) {
	return [];
}

function pw_property_get_active_offers( $property_id, $limit = 3 ) {
	return [];
}

function pw_property_get_announcement_bar( $property_id ) {
	$property_id = (int) $property_id;
	if ( $property_id <= 0 ) {
		return '';
	}

	$active = get_post_meta( $property_id, '_pw_announcement_active', true );
	if ( $active !== '1' && $active !== 1 && $active !== true && $active !== 'true' ) {
		return '';
	}

	$text = (string) get_post_meta( $property_id, '_pw_announcement_text', true );
	if ( trim( $text ) === '' ) {
		return '';
	}

	$now_ts = current_time( 'timestamp' );

	$start = (string) get_post_meta( $property_id, '_pw_announcement_start', true );
	if ( $start !== '' ) {
		$start_ts = is_numeric( $start ) ? (int) $start : strtotime( $start );
		if ( is_int( $start_ts ) && $start_ts > 0 && $now_ts < $start_ts ) {
			return '';
		}
	}

	$end = (string) get_post_meta( $property_id, '_pw_announcement_end', true );
	if ( $end !== '' ) {
		$end_ts = is_numeric( $end ) ? (int) $end : strtotime( $end );
		if ( is_int( $end_ts ) && $end_ts > 0 && $now_ts > $end_ts ) {
			return '';
		}
	}

	return $text;
}
