<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pw_get_taxonomy_seed_terms() {
	return [
		'pw_policy_type' => [
			'Check-in',
			'Check-out',
			'Cancellation',
			'Pet',
			'Child',
			'Payment',
			'Smoking',
			'Custom',
		],
		'pw_bed_type' => [
			'Twin',
			'Double',
			'Queen',
			'King',
			'Single',
			'Sofa Bed',
			'Bunk Bed',
			'Murphy Bed',
			'Rollaway',
			'Crib',
		],
		'pw_view_type' => [
			'Ocean',
			'Sea',
			'Beach',
			'Pool',
			'Garden',
			'City',
			'Mountain',
			'Lake',
			'Courtyard',
			'Partial Ocean',
			'Partial Sea',
			'No View',
		],
		'pw_meal_period' => [
			'Breakfast',
			'Brunch',
			'Lunch',
			'Dinner',
			'All-day Dining',
			'Afternoon Tea',
			'Late Night',
			'24-Hour',
		],
		'pw_treatment_type' => [
			'Massage',
			'Facial',
			'Body Wrap',
			'Body Scrub',
			'Manicure',
			'Pedicure',
			'Hair',
			'Waxing',
			'Aromatherapy',
			'Hot Stone',
			'Reflexology',
			'Couples Treatment',
			'Pre/Post Natal',
		],
		'pw_av_equipment' => [
			'Projector',
			'Screen',
			'Video Conferencing',
			'Microphone',
			'PA System',
			'Whiteboard',
			'Flip Chart',
			'HDMI Connection',
			'Wireless Presentation',
			'Recording',
		],
		'pw_feature_group' => [
			'Bedding',
			'Bathroom',
			'In-room',
			'Entertainment',
			'Climate',
			'Connectivity',
			'Outdoor',
		],
		'pw_nearby_type' => [
			'Beach',
			'Airport',
			'Train Station',
			'Attraction',
			'Shopping',
			'Dining',
			'Park',
			'Museum',
			'Golf',
			'Hospital',
			'Bank/ATM',
			'Supermarket',
		],
		'pw_transport_mode' => [
			'Walk',
			'Drive',
			'Taxi',
			'Public Transport',
			'Shuttle',
			'Boat',
			'Bicycle',
		],
		'pw_experience_category' => [
			'Adventure',
			'Cultural',
			'Culinary',
			'Wellness',
			'Water Sports',
			'Land Activities',
			'Kids',
			'Nightlife',
			'Shopping',
			'Nature',
		],
		'pw_event_type' => [
			'Wedding',
			'Conference',
			'Meeting',
			'Seminar',
			'Gala',
			'Private Dining',
			'Team Building',
			'Product Launch',
			'Social Event',
			'Exhibition',
		],
	];
}

function pw_seed_taxonomy_terms() {
	$seeds = pw_get_taxonomy_seed_terms();
	foreach ( $seeds as $taxonomy => $terms ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		foreach ( $terms as $name ) {
			if ( ! term_exists( $name, $taxonomy ) ) {
				wp_insert_term( $name, $taxonomy );
			}
		}
	}
}
