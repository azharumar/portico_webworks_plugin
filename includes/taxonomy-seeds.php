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

function pw_get_taxonomy_seed_prompt_status() {
	$v = get_option( 'pw_taxonomy_seed_prompt_status', '' );
	return is_string( $v ) ? $v : '';
}

add_action(
	'admin_init',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$st = pw_get_taxonomy_seed_prompt_status();
		if ( $st !== '' ) {
			return;
		}
		if ( ! get_option( 'pw_install_defaults_applied' ) ) {
			return;
		}
		update_option( 'pw_taxonomy_seed_prompt_status', 'pending' );
	},
	5
);

add_action( 'admin_notices', 'pw_render_taxonomy_seed_upgrade_notice' );

function pw_render_taxonomy_seed_upgrade_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_GET['pw_taxonomy_seed_dismissed'] ) ) {
		echo '<div class="notice notice-info is-dismissible"><p>Default taxonomy terms prompt dismissed.</p></div>';
		return;
	}
	if ( isset( $_GET['pw_taxonomy_seed_done'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Default taxonomy terms were added where they were missing.</p></div>';
		return;
	}
	if ( pw_get_taxonomy_seed_prompt_status() !== 'pending' ) {
		return;
	}

	$accept_url = admin_url( 'admin-post.php' );
	$dismiss_url = admin_url( 'admin-post.php' );
	$list        = implode( ', ', array_keys( pw_get_taxonomy_seed_terms() ) );

	echo '<div class="notice notice-warning"><p><strong>Portico Webworks:</strong> You can add the plugin&rsquo;s default taxonomy terms (bed types, view types, meal periods, spa treatments, and more).</p>';
	echo '<ul style="list-style:disc;margin-left:1.5em;">';
	echo '<li><strong>No existing terms are removed or renamed.</strong> Only term names that do not already exist are created.</li>';
	echo '<li>If you already use custom terms, they stay as-is; this only fills gaps.</li>';
	echo '<li>Taxonomies affected: <code style="font-size:12px">' . esc_html( $list ) . '</code></li>';
	echo '</ul>';
	echo '<p>';
	echo '<form method="post" action="' . esc_url( $accept_url ) . '" style="display:inline;margin-right:8px">';
	echo '<input type="hidden" name="action" value="pw_accept_taxonomy_seed" />';
	wp_nonce_field( 'pw_accept_taxonomy_seed' );
	submit_button( 'Add default terms', 'primary', 'submit', false );
	echo '</form>';
	echo '<form method="post" action="' . esc_url( $dismiss_url ) . '" style="display:inline">';
	echo '<input type="hidden" name="action" value="pw_dismiss_taxonomy_seed" />';
	wp_nonce_field( 'pw_dismiss_taxonomy_seed' );
	submit_button( 'Dismiss', 'secondary', 'submit', false );
	echo '</form>';
	echo '</p></div>';
}

add_action( 'admin_post_pw_accept_taxonomy_seed', 'pw_handle_accept_taxonomy_seed' );
add_action( 'admin_post_pw_dismiss_taxonomy_seed', 'pw_handle_dismiss_taxonomy_seed' );

function pw_handle_accept_taxonomy_seed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorised' );
	}
	check_admin_referer( 'pw_accept_taxonomy_seed' );
	if ( pw_get_taxonomy_seed_prompt_status() !== 'pending' ) {
		wp_safe_redirect( admin_url( 'index.php' ) );
		exit;
	}
	pw_seed_taxonomy_terms();
	update_option( 'pw_taxonomy_seed_prompt_status', 'completed' );
	wp_safe_redirect( admin_url( 'index.php?pw_taxonomy_seed_done=1' ) );
	exit;
}

function pw_handle_dismiss_taxonomy_seed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorised' );
	}
	check_admin_referer( 'pw_dismiss_taxonomy_seed' );
	if ( pw_get_taxonomy_seed_prompt_status() === 'pending' ) {
		update_option( 'pw_taxonomy_seed_prompt_status', 'dismissed' );
	}
	wp_safe_redirect( admin_url( 'index.php?pw_taxonomy_seed_dismissed=1' ) );
	exit;
}
