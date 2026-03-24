<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pw_admin_list_column_post_types() {
	return [
		'pw_property',
		'pw_feature',
		'pw_room_type',
		'pw_restaurant',
		'pw_spa',
		'pw_meeting_room',
		'pw_amenity',
		'pw_policy',
		'pw_faq',
		'pw_offer',
		'pw_nearby',
		'pw_experience',
		'pw_event',
		'pw_contact',
	];
}

function pw_admin_post_types_with_property_column() {
	return array_diff( pw_admin_list_column_post_types(), [ 'pw_property' ] );
}

function pw_admin_insert_columns_after_key( array $columns, array $insert, $after_key ) {
	$out = [];
	foreach ( $columns as $key => $label ) {
		$out[ $key ] = $label;
		if ( $after_key === $key ) {
			foreach ( $insert as $col_key => $col_label ) {
				$out[ $col_key ] = $col_label;
			}
		}
	}
	return $out;
}

function pw_admin_render_property_cell( $post_id ) {
	$pid = (int) get_post_meta( $post_id, '_pw_property_id', true );
	if ( $pid <= 0 ) {
		echo '—';
		return;
	}
	$p = get_post( $pid );
	if ( ! $p || 'pw_property' !== $p->post_type ) {
		echo '—';
		return;
	}
	$link = get_edit_post_link( $pid );
	if ( $link ) {
		echo '<a href="' . esc_url( $link ) . '">' . esc_html( get_the_title( $pid ) ) . '</a>';
	} else {
		echo esc_html( get_the_title( $pid ) );
	}
}

function pw_admin_format_datetime_list_text( $raw ) {
	$raw = is_string( $raw ) ? trim( $raw ) : '';
	if ( $raw === '' ) {
		return '—';
	}
	$t = strtotime( $raw );
	if ( ! $t ) {
		return $raw;
	}
	return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $t );
}

function pw_admin_posts_columns( $columns, $post_type ) {
	if ( 'pw_property' === $post_type ) {
		return pw_admin_insert_columns_after_key(
			$columns,
			[
				'pw_city'    => __( 'City', 'portico-webworks' ),
				'pw_state'   => __( 'State', 'portico-webworks' ),
				'pw_country' => __( 'Country', 'portico-webworks' ),
			],
			'title'
		);
	}

	$extra = [];

	if ( in_array( $post_type, pw_admin_post_types_with_property_column(), true ) ) {
		$extra['pw_property'] = __( 'Property', 'portico-webworks' );
	}

	switch ( $post_type ) {
		case 'pw_feature':
			$extra['pw_feature_icon'] = __( 'Icon', 'portico-webworks' );
			break;
		case 'pw_room_type':
			$extra['pw_rate_range'] = __( 'Rate range', 'portico-webworks' );
			$extra['pw_max_occ']    = __( 'Max occ.', 'portico-webworks' );
			break;
		case 'pw_restaurant':
			$extra['pw_location'] = __( 'Location', 'portico-webworks' );
			$extra['pw_cuisine']  = __( 'Cuisine', 'portico-webworks' );
			break;
		case 'pw_spa':
			$extra['pw_spa_min_age'] = __( 'Min age', 'portico-webworks' );
			$extra['pw_spa_rooms']   = __( 'Treatment rooms', 'portico-webworks' );
			break;
		case 'pw_meeting_room':
			$extra['pw_mr_theatre'] = __( 'Theatre cap.', 'portico-webworks' );
			$extra['pw_mr_area']    = __( 'Area (sqft)', 'portico-webworks' );
			break;
		case 'pw_amenity':
			$extra['pw_amenity_type']     = __( 'Type', 'portico-webworks' );
			$extra['pw_amenity_category'] = __( 'Category', 'portico-webworks' );
			break;
		case 'pw_contact':
			$extra['pw_contact_label'] = __( 'Label', 'portico-webworks' );
			$extra['pw_contact_phone'] = __( 'Phone', 'portico-webworks' );
			$extra['pw_contact_scope'] = __( 'Scope', 'portico-webworks' );
			break;
		case 'pw_policy':
			$extra['pw_policy_order']  = __( 'Order', 'portico-webworks' );
			$extra['pw_policy_active'] = __( 'Active', 'portico-webworks' );
			break;
		case 'pw_faq':
			$extra['pw_faq_order'] = __( 'Order', 'portico-webworks' );
			break;
		case 'pw_offer':
			$extra['pw_offer_type']   = __( 'Offer type', 'portico-webworks' );
			$extra['pw_offer_valid']  = __( 'Valid', 'portico-webworks' );
			break;
		case 'pw_nearby':
			$extra['pw_nearby_km']   = __( 'Distance (km)', 'portico-webworks' );
			$extra['pw_nearby_time'] = __( 'Travel (min)', 'portico-webworks' );
			break;
		case 'pw_experience':
			$extra['pw_exp_price'] = __( 'Price from', 'portico-webworks' );
			$extra['pw_exp_hours'] = __( 'Duration (h)', 'portico-webworks' );
			break;
		case 'pw_event':
			$extra['pw_event_start'] = __( 'Starts', 'portico-webworks' );
			$extra['pw_event_end']   = __( 'Ends', 'portico-webworks' );
			$extra['pw_event_venue'] = __( 'Venue', 'portico-webworks' );
			break;
	}

	return pw_admin_insert_columns_after_key( $columns, $extra, 'title' );
}

function pw_admin_render_post_list_column( $column, $post_id ) {
	switch ( $column ) {
		case 'pw_city':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_city', true ) );
			break;
		case 'pw_state':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_state', true ) );
			break;
		case 'pw_country':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_country', true ) );
			break;
		case 'pw_property':
			pw_admin_render_property_cell( $post_id );
			break;
		case 'pw_feature_icon':
			$icon = (string) get_post_meta( $post_id, '_pw_icon', true );
			if ( $icon === '' ) {
				echo '—';
				break;
			}
			$trim = function_exists( 'mb_substr' ) ? mb_substr( $icon, 0, 48 ) : substr( $icon, 0, 48 );
			echo esc_html( $trim . ( strlen( $icon ) > 48 ? '…' : '' ) );
			break;
		case 'pw_rate_range':
			$from = (float) get_post_meta( $post_id, '_pw_rate_from', true );
			$to   = (float) get_post_meta( $post_id, '_pw_rate_to', true );
			if ( $from <= 0 && $to <= 0 ) {
				echo '—';
				break;
			}
			if ( $to > 0 && $from > 0 && abs( $from - $to ) > 0.0001 ) {
				echo esc_html( number_format_i18n( $from, 2 ) . ' – ' . number_format_i18n( $to, 2 ) );
			} elseif ( $from > 0 ) {
				echo esc_html( number_format_i18n( $from, 2 ) );
			} else {
				echo esc_html( number_format_i18n( $to, 2 ) );
			}
			break;
		case 'pw_max_occ':
			$o = (int) get_post_meta( $post_id, '_pw_max_occupancy', true );
			echo $o > 0 ? esc_html( (string) $o ) : '—';
			break;
		case 'pw_location':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_location', true ) ) ?: '—';
			break;
		case 'pw_cuisine':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_cuisine_type', true ) ) ?: '—';
			break;
		case 'pw_spa_min_age':
			$a = (int) get_post_meta( $post_id, '_pw_min_age', true );
			echo $a > 0 ? esc_html( (string) $a ) : '—';
			break;
		case 'pw_spa_rooms':
			$n = (int) get_post_meta( $post_id, '_pw_number_of_treatment_rooms', true );
			echo $n > 0 ? esc_html( (string) $n ) : '—';
			break;
		case 'pw_mr_theatre':
			$n = (int) get_post_meta( $post_id, '_pw_capacity_theatre', true );
			echo $n > 0 ? esc_html( (string) $n ) : '—';
			break;
		case 'pw_mr_area':
			$n = (int) get_post_meta( $post_id, '_pw_area_sqft', true );
			echo $n > 0 ? esc_html( (string) $n ) : '—';
			break;
		case 'pw_amenity_type':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_type', true ) ) ?: '—';
			break;
		case 'pw_amenity_category':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_category', true ) ) ?: '—';
			break;
		case 'pw_contact_label':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_label', true ) ) ?: '—';
			break;
		case 'pw_contact_phone':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_phone', true ) ) ?: '—';
			break;
		case 'pw_contact_scope':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_scope_cpt', true ) ) ?: '—';
			break;
		case 'pw_policy_order':
			echo esc_html( (string) (int) get_post_meta( $post_id, '_pw_display_order', true ) );
			break;
		case 'pw_policy_active':
			$on = get_post_meta( $post_id, '_pw_active', true );
			$active = ( $on === '' || $on === null || $on === true || $on === 1 || $on === '1' || $on === 'on' );
			if ( $on === false || $on === 0 || $on === '0' ) {
				$active = false;
			}
			echo $active ? esc_html__( 'Yes', 'portico-webworks' ) : esc_html__( 'No', 'portico-webworks' );
			break;
		case 'pw_faq_order':
			echo esc_html( (string) (int) get_post_meta( $post_id, '_pw_display_order', true ) );
			break;
		case 'pw_offer_type':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_offer_type', true ) ) ?: '—';
			break;
		case 'pw_offer_valid':
			$vf = (string) get_post_meta( $post_id, '_pw_valid_from', true );
			$vt = (string) get_post_meta( $post_id, '_pw_valid_to', true );
			if ( $vf === '' && $vt === '' ) {
				echo '—';
				break;
			}
			echo esc_html( trim( $vf . ( $vf && $vt ? ' → ' : '' ) . $vt ) );
			break;
		case 'pw_nearby_km':
			$k = (float) get_post_meta( $post_id, '_pw_distance_km', true );
			echo $k > 0 ? esc_html( (string) $k ) : '—';
			break;
		case 'pw_nearby_time':
			$m = (int) get_post_meta( $post_id, '_pw_travel_time_min', true );
			echo $m > 0 ? esc_html( (string) $m ) : '—';
			break;
		case 'pw_exp_price':
			$pf = (float) get_post_meta( $post_id, '_pw_price_from', true );
			echo $pf > 0 ? esc_html( number_format_i18n( $pf, 2 ) ) : '—';
			break;
		case 'pw_exp_hours':
			$h = (float) get_post_meta( $post_id, '_pw_duration_hours', true );
			echo $h > 0 ? esc_html( (string) $h ) : '—';
			break;
		case 'pw_event_start':
			echo esc_html( pw_admin_format_datetime_list_text( (string) get_post_meta( $post_id, '_pw_start_datetime', true ) ) );
			break;
		case 'pw_event_end':
			echo esc_html( pw_admin_format_datetime_list_text( (string) get_post_meta( $post_id, '_pw_end_datetime', true ) ) );
			break;
		case 'pw_event_venue':
			$vid = (int) get_post_meta( $post_id, '_pw_venue_id', true );
			if ( $vid <= 0 ) {
				echo '—';
				break;
			}
			$vp = get_post( $vid );
			if ( ! $vp || 'pw_meeting_room' !== $vp->post_type ) {
				echo '—';
				break;
			}
			$link = get_edit_post_link( $vid );
			if ( $link ) {
				echo '<a href="' . esc_url( $link ) . '">' . esc_html( get_the_title( $vid ) ) . '</a>';
			} else {
				echo esc_html( get_the_title( $vid ) );
			}
			break;
	}
}

function pw_admin_taxonomies_with_extra_columns() {
	return [
		'pw_bed_type',
		'pw_view_type',
		'pw_meal_period',
		'pw_treatment_type',
		'pw_av_equipment',
		'pw_feature_group',
		'pw_nearby_type',
		'pw_transport_mode',
		'pw_experience_category',
		'pw_event_type',
		'pw_policy_type',
		'pw_event_organiser',
		'pw_property_type',
	];
}

function pw_admin_taxonomy_columns( $columns, $taxonomy ) {
	$insert = [ 'pw_term_id' => __( 'ID', 'portico-webworks' ) ];
	if ( 'pw_event_organiser' === $taxonomy ) {
		$insert['pw_organiser_url'] = __( 'Organiser URL', 'portico-webworks' );
	}
	return pw_admin_insert_columns_after_key( $columns, $insert, 'name' );
}

function pw_admin_taxonomy_custom_column() {
	$a = func_get_args();
	if ( count( $a ) < 2 ) {
		return;
	}
	if ( count( $a ) >= 3 ) {
		if ( $a[0] === '' ) {
			$column  = $a[1];
			$term_id = (int) $a[2];
		} else {
			$column  = $a[0];
			$term_id = (int) $a[2];
		}
	} else {
		$column  = $a[0];
		$term_id = (int) $a[1];
	}
	if ( 'pw_term_id' === $column ) {
		echo $term_id > 0 ? esc_html( (string) $term_id ) : '—';
		return;
	}
	if ( 'pw_organiser_url' === $column ) {
		$url = (string) get_term_meta( $term_id, 'organiser_url', true );
		if ( $url === '' ) {
			echo '—';
			return;
		}
		echo '<a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a>';
	}
}

function pw_register_admin_list_columns() {
	foreach ( pw_admin_list_column_post_types() as $pt ) {
		add_filter(
			"manage_{$pt}_posts_columns",
			static function ( $columns ) use ( $pt ) {
				return pw_admin_posts_columns( $columns, $pt );
			},
			20
		);
		add_action(
			"manage_{$pt}_posts_custom_column",
			static function ( $column, $post_id ) {
				pw_admin_render_post_list_column( $column, $post_id );
			},
			10,
			2
		);
	}

	foreach ( pw_admin_post_types_with_property_column() as $pt ) {
		add_filter(
			"manage_edit-{$pt}_sortable_columns",
			static function ( $sortable ) {
				$sortable['pw_property'] = 'pw_property';
				return $sortable;
			}
		);
	}

	foreach ( pw_admin_taxonomies_with_extra_columns() as $tax ) {
		add_filter(
			"manage_edit-{$tax}_columns",
			static function ( $columns ) use ( $tax ) {
				return pw_admin_taxonomy_columns( $columns, $tax );
			},
			20
		);
		add_action(
			"manage_{$tax}_custom_column",
			'pw_admin_taxonomy_custom_column',
			10,
			3
		);
	}
}

add_action( 'admin_init', 'pw_register_admin_list_columns' );

function pw_admin_sort_posts_by_property( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'pw_property' !== $query->get( 'orderby' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit' !== $screen->base || ! $screen->post_type ) {
		return;
	}
	if ( ! in_array( $screen->post_type, pw_admin_post_types_with_property_column(), true ) ) {
		return;
	}
	$query->set( 'meta_key', '_pw_property_id' );
	$query->set( 'orderby', 'meta_value_num' );
}

add_action( 'pre_get_posts', 'pw_admin_sort_posts_by_property' );
