<?php
/**
 * Fact sheet markup: direct get_post_meta / get_posts only (no aggregate helpers).
 *
 * @var int $property_id Current property post ID.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$property_id = isset( $property_id ) ? (int) $property_id : 0;
if ( $property_id <= 0 ) {
	return;
}

?>
<div class="pw-fact-sheet">
	<section class="pw-fact-sheet-property">
		<h2><?php echo esc_html( get_the_title( $property_id ) ); ?></h2>
		<?php
		$excerpt = get_post_field( 'post_excerpt', $property_id, 'raw' );
		if ( is_string( $excerpt ) && $excerpt !== '' ) {
			echo '<p>' . esc_html( $excerpt ) . '</p>';
		}
		$content = get_post_field( 'post_content', $property_id, 'raw' );
		if ( is_string( $content ) && trim( $content ) !== '' ) {
			echo '<div class="pw-fact-sheet-editor">' . wp_kses_post( wpautop( $content ) ) . '</div>';
		}
		?>
		<h3><?php echo esc_html( 'General' ); ?></h3>
		<dl>
			<?php
			$v = get_post_meta( $property_id, '_pw_legal_name', true );
			if ( is_string( $v ) && $v !== '' ) {
				echo '<dt>' . esc_html( 'Legal name' ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
			}
			$v = (int) get_post_meta( $property_id, '_pw_star_rating', true );
			if ( $v > 0 ) {
				echo '<dt>' . esc_html( 'Star rating' ) . '</dt><dd>' . esc_html( (string) $v ) . '</dd>';
			}
			$v = get_post_meta( $property_id, '_pw_currency', true );
			if ( is_string( $v ) && $v !== '' ) {
				echo '<dt>' . esc_html( 'Currency' ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
			}
			$v = get_post_meta( $property_id, '_pw_check_in_time', true );
			if ( is_string( $v ) && $v !== '' ) {
				echo '<dt>' . esc_html( 'Check-in time' ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
			}
			$v = get_post_meta( $property_id, '_pw_check_out_time', true );
			if ( is_string( $v ) && $v !== '' ) {
				echo '<dt>' . esc_html( 'Check-out time' ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
			}
			$v = (int) get_post_meta( $property_id, '_pw_year_established', true );
			if ( $v > 0 ) {
				echo '<dt>' . esc_html( 'Year established' ) . '</dt><dd>' . esc_html( (string) $v ) . '</dd>';
			}
			$v = (int) get_post_meta( $property_id, '_pw_total_rooms', true );
			if ( $v > 0 ) {
				echo '<dt>' . esc_html( 'Total rooms' ) . '</dt><dd>' . esc_html( (string) $v ) . '</dd>';
			}
			?>
		</dl>
		<h3><?php echo esc_html( 'Address' ); ?></h3>
		<dl>
			<?php
			foreach (
				array(
					'_pw_address_line_1' => 'Address line 1',
					'_pw_address_line_2' => 'Address line 2',
					'_pw_city'            => 'City',
					'_pw_state'           => 'State / province',
					'_pw_postal_code'     => 'Postal code',
					'_pw_country'         => 'Country',
					'_pw_country_code'    => 'Country code',
				) as $mk => $lab
			) {
				$v = get_post_meta( $property_id, $mk, true );
				if ( is_string( $v ) && $v !== '' ) {
					echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
				}
			}
			?>
		</dl>
		<h3><?php echo esc_html( 'Geo' ); ?></h3>
		<dl>
			<?php
			$lat = (float) get_post_meta( $property_id, '_pw_lat', true );
			$lng = (float) get_post_meta( $property_id, '_pw_lng', true );
			if ( $lat !== 0.0 || $lng !== 0.0 ) {
				echo '<dt>' . esc_html( 'Latitude' ) . '</dt><dd>' . esc_html( (string) $lat ) . '</dd>';
				echo '<dt>' . esc_html( 'Longitude' ) . '</dt><dd>' . esc_html( (string) $lng ) . '</dd>';
			}
			$v = get_post_meta( $property_id, '_pw_google_place_id', true );
			if ( is_string( $v ) && $v !== '' ) {
				echo '<dt>' . esc_html( 'Google Place ID' ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
			}
			$v = get_post_meta( $property_id, '_pw_timezone', true );
			if ( is_string( $v ) && $v !== '' ) {
				echo '<dt>' . esc_html( 'Timezone' ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
			}
			?>
		</dl>
		<h3><?php echo esc_html( 'Social' ); ?></h3>
		<dl>
			<?php
			foreach (
				array(
					'_pw_social_facebook'    => 'Facebook',
					'_pw_social_instagram'   => 'Instagram',
					'_pw_social_twitter'     => 'Twitter',
					'_pw_social_youtube'     => 'YouTube',
					'_pw_social_linkedin'    => 'LinkedIn',
					'_pw_social_tripadvisor' => 'TripAdvisor',
				) as $mk => $lab
			) {
				$v = get_post_meta( $property_id, $mk, true );
				if ( is_string( $v ) && $v !== '' ) {
					echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
				}
			}
			?>
		</dl>
		<h3><?php echo esc_html( 'Contacts' ); ?></h3>
		<?php
		$contacts = get_post_meta( $property_id, '_pw_contacts', true );
		if ( is_array( $contacts ) && $contacts ) {
			echo '<ul>';
			foreach ( $contacts as $c ) {
				if ( ! is_array( $c ) ) {
					continue;
				}
				echo '<li><dl>';
				foreach ( array( 'label', 'phone', 'mobile', 'whatsapp', 'email' ) as $f ) {
					$cv = isset( $c[ $f ] ) ? (string) $c[ $f ] : '';
					if ( $cv !== '' ) {
						echo '<dt>' . esc_html( $f ) . '</dt><dd>' . esc_html( $cv ) . '</dd>';
					}
				}
				echo '</dl></li>';
			}
			echo '</ul>';
		}
		?>
		<h3><?php echo esc_html( 'Pools' ); ?></h3>
		<?php
		$pools = get_post_meta( $property_id, '_pw_pools', true );
		if ( is_array( $pools ) && $pools ) {
			echo '<ul>';
			foreach ( $pools as $p ) {
				if ( ! is_array( $p ) ) {
					continue;
				}
				echo '<li><dl>';
				foreach (
					array(
						'name'        => 'name',
						'length_m'    => 'length_m',
						'width_m'     => 'width_m',
						'depth_m'     => 'depth_m',
						'open_time'   => 'open_time',
						'close_time'  => 'close_time',
						'is_heated'   => 'is_heated',
						'is_kids'     => 'is_kids',
						'is_indoor'   => 'is_indoor',
						'is_infinity' => 'is_infinity',
					) as $f => $lab
				) {
					if ( ! array_key_exists( $f, $p ) ) {
						continue;
					}
					$pv = $p[ $f ];
					if ( is_bool( $pv ) ) {
						echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( $pv ? 'yes' : 'no' ) . '</dd>';
					} elseif ( is_numeric( $pv ) && (float) $pv !== 0.0 ) {
						echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( (string) $pv ) . '</dd>';
					} elseif ( is_string( $pv ) && $pv !== '' ) {
						echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( $pv ) . '</dd>';
					}
				}
				echo '</dl></li>';
			}
			echo '</ul>';
		}
		?>
		<h3><?php echo esc_html( 'Direct booking benefits' ); ?></h3>
		<?php
		$dbs = get_post_meta( $property_id, '_pw_direct_benefits', true );
		if ( is_array( $dbs ) && $dbs ) {
			echo '<ul>';
			foreach ( $dbs as $b ) {
				if ( ! is_array( $b ) ) {
					continue;
				}
				echo '<li><dl>';
				foreach ( array( 'title', 'description', 'icon' ) as $f ) {
					$bv = isset( $b[ $f ] ) ? (string) $b[ $f ] : '';
					if ( $bv !== '' ) {
						echo '<dt>' . esc_html( $f ) . '</dt><dd>' . esc_html( $bv ) . '</dd>';
					}
				}
				echo '</dl></li>';
			}
			echo '</ul>';
		}
		?>
		<h3><?php echo esc_html( 'Certifications' ); ?></h3>
		<?php
		$certs = get_post_meta( $property_id, '_pw_certifications', true );
		if ( is_array( $certs ) && $certs ) {
			echo '<ul>';
			foreach ( $certs as $c ) {
				if ( ! is_array( $c ) ) {
					continue;
				}
				echo '<li><dl>';
				foreach ( array( 'name', 'issuer', 'year', 'url' ) as $f ) {
					if ( ! isset( $c[ $f ] ) ) {
						continue;
					}
					$cv = $c[ $f ];
					if ( $f === 'url' && is_string( $cv ) && $cv !== '' ) {
						echo '<dt>' . esc_html( $f ) . '</dt><dd><a href="' . esc_url( $cv ) . '">' . esc_html( $cv ) . '</a></dd>';
					} elseif ( ( is_string( $cv ) && $cv !== '' ) || ( is_numeric( $cv ) && (int) $cv > 0 ) ) {
						echo '<dt>' . esc_html( $f ) . '</dt><dd>' . esc_html( (string) $cv ) . '</dd>';
					}
				}
				echo '</dl></li>';
			}
			echo '</ul>';
		}
		?>
		<h3><?php echo esc_html( 'Sustainability' ); ?></h3>
		<?php
		$sus_items = get_post_meta( $property_id, '_pw_sustainability_items', true );
		$sus_defs  = pw_get_sustainability_facet_definitions();
		$sus_label = array();
		foreach ( $sus_defs as $d ) {
			$sus_label[ $d['key'] ] = $d['label'];
		}
		if ( is_array( $sus_items ) && $sus_items ) {
			echo '<ul>';
			foreach ( $sus_items as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$st = isset( $row['status'] ) ? (string) $row['status'] : 'unknown';
				if ( $st === 'unknown' ) {
					continue;
				}
				$key = isset( $row['key'] ) ? (string) $row['key'] : '';
				$lab = isset( $sus_label[ $key ] ) ? $sus_label[ $key ] : $key;
				$note = isset( $row['note'] ) ? (string) $row['note'] : '';
				echo '<li><strong>' . esc_html( $lab ) . '</strong>: ' . esc_html( $st );
				if ( $note !== '' ) {
					echo ' — ' . esc_html( $note );
				}
				echo '</li>';
			}
			echo '</ul>';
		}
		?>
		<h3><?php echo esc_html( 'Accessibility' ); ?></h3>
		<?php
		$acc_items = get_post_meta( $property_id, '_pw_accessibility_items', true );
		$acc_defs  = pw_get_accessibility_facet_definitions();
		$acc_label = array();
		foreach ( $acc_defs as $d ) {
			$acc_label[ $d['key'] ] = $d['label'];
		}
		if ( is_array( $acc_items ) && $acc_items ) {
			echo '<ul>';
			foreach ( $acc_items as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$st = isset( $row['status'] ) ? (string) $row['status'] : 'unknown';
				if ( $st === 'unknown' ) {
					continue;
				}
				$key = isset( $row['key'] ) ? (string) $row['key'] : '';
				$lab = isset( $acc_label[ $key ] ) ? $acc_label[ $key ] : $key;
				$note = isset( $row['note'] ) ? (string) $row['note'] : '';
				echo '<li><strong>' . esc_html( $lab ) . '</strong>: ' . esc_html( $st );
				if ( $note !== '' ) {
					echo ' — ' . esc_html( $note );
				}
				echo '</li>';
			}
			echo '</ul>';
		}
		?>
		<h3><?php echo esc_html( 'SEO' ); ?></h3>
		<dl>
			<?php
			$v = get_post_meta( $property_id, '_pw_meta_title', true );
			if ( is_string( $v ) && $v !== '' ) {
				echo '<dt>' . esc_html( 'Meta title' ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
			}
			$v = get_post_meta( $property_id, '_pw_meta_description', true );
			if ( is_string( $v ) && $v !== '' ) {
				echo '<dt>' . esc_html( 'Meta description' ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
			}
			$og = (int) get_post_meta( $property_id, '_pw_og_image', true );
			if ( $og > 0 ) {
				$url = wp_get_attachment_url( $og );
				echo '<dt>' . esc_html( 'OG image ID' ) . '</dt><dd>' . esc_html( (string) $og ) . '</dd>';
				if ( $url ) {
					echo '<dt>' . esc_html( 'OG image URL' ) . '</dt><dd><a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a></dd>';
				}
			}
			?>
		</dl>
	</section>
	<?php
	$pw_fact_meta_query = array(
		array(
			'key'   => '_pw_property_id',
			'value' => (int) $property_id,
		),
	);
	$pw_fact_child_types = array(
		'pw_room_type'    => 'Room types',
		'pw_restaurant'   => 'Restaurants',
		'pw_spa'          => 'Spas',
		'pw_meeting_room' => 'Meeting rooms',
		'pw_amenity'      => 'Amenities',
		'pw_policy'       => 'Policies',
		'pw_nearby'       => 'Nearby',
		'pw_event'        => 'Events',
		'pw_experience'   => 'Experiences',
		'pw_faq'          => 'FAQs',
		'pw_offer'        => 'Offers',
	);
	foreach ( $pw_fact_child_types as $pt => $heading ) {
		$posts = get_posts(
			array(
				'post_type'      => $pt,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_query'     => $pw_fact_meta_query,
			)
		);
		if ( ! $posts ) {
			continue;
		}
		echo '<section class="pw-fact-sheet-' . esc_attr( str_replace( '_', '-', $pt ) ) . '">';
		echo '<h2>' . esc_html( $heading ) . '</h2>';
		foreach ( $posts as $post_obj ) {
			$pid = (int) $post_obj->ID;
			echo '<article><h3>' . esc_html( get_the_title( $pid ) ) . '</h3>';
			if ( $pt === 'pw_room_type' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p>' . esc_html( $ex ) . '</p>';
				}
				echo '<dl>';
				$rf = (float) get_post_meta( $pid, '_pw_rate_from', true );
				$rt = (float) get_post_meta( $pid, '_pw_rate_to', true );
				if ( $rf > 0 || $rt > 0 ) {
					echo '<dt>rate_from</dt><dd>' . esc_html( (string) $rf ) . '</dd>';
					echo '<dt>rate_to</dt><dd>' . esc_html( (string) $rt ) . '</dd>';
				}
				$rates = get_post_meta( $pid, '_pw_rates', true );
				if ( is_array( $rates ) && $rates ) {
					echo '<dt>_pw_rates</dt><dd><ul>';
					foreach ( $rates as $r ) {
						if ( ! is_array( $r ) ) {
							continue;
						}
						echo '<li><dl>';
						foreach ( array( 'rate_label', 'rate_type', 'price', 'valid_from', 'valid_to', 'advance_days', 'includes_breakfast' ) as $rk ) {
							if ( ! array_key_exists( $rk, $r ) ) {
								continue;
							}
							$rv = $r[ $rk ];
							if ( is_bool( $rv ) ) {
								echo '<dt>' . esc_html( $rk ) . '</dt><dd>' . esc_html( $rv ? 'yes' : 'no' ) . '</dd>';
							} elseif ( $rv !== '' && $rv !== null && $rv !== 0 && $rv !== 0.0 && $rv !== '0' ) {
								echo '<dt>' . esc_html( $rk ) . '</dt><dd>' . esc_html( is_scalar( $rv ) ? (string) $rv : '' ) . '</dd>';
							}
						}
						echo '</dl></li>';
					}
					echo '</ul></dd>';
				}
				foreach (
					array(
						'_pw_max_occupancy'  => 'max_occupancy',
						'_pw_max_adults'     => 'max_adults',
						'_pw_max_children'   => 'max_children',
						'_pw_size_sqft'      => 'size_sqft',
						'_pw_size_sqm'       => 'size_sqm',
						'_pw_max_extra_beds' => 'max_extra_beds',
						'_pw_display_order'  => 'display_order',
					) as $mk => $lab
				) {
					$iv = (int) get_post_meta( $pid, $mk, true );
					if ( $iv > 0 ) {
						echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( (string) $iv ) . '</dd>';
					}
				}
				$feat_ids = get_post_meta( $pid, '_pw_features', true );
				if ( is_array( $feat_ids ) && $feat_ids ) {
					echo '<dt>features</dt><dd><ul>';
					foreach ( $feat_ids as $fid ) {
						$fid = (int) $fid;
						if ( $fid <= 0 ) {
							continue;
						}
						echo '<li>' . esc_html( get_the_title( $fid ) ) . '</li>';
					}
					echo '</ul></dd>';
				}
				$gids = get_post_meta( $pid, '_pw_gallery', true );
				if ( is_array( $gids ) && $gids ) {
					echo '<dt>gallery</dt><dd><ul>';
					foreach ( $gids as $aid ) {
						$aid = (int) $aid;
						$u   = $aid ? wp_get_attachment_url( $aid ) : '';
						if ( $u ) {
							echo '<li><a href="' . esc_url( $u ) . '">' . esc_html( (string) $aid ) . '</a></li>';
						} else {
							echo '<li>' . esc_html( (string) $aid ) . '</li>';
						}
					}
					echo '</ul></dd>';
				}
				$t1 = get_the_terms( $pid, 'pw_bed_type' );
				if ( $t1 && ! is_wp_error( $t1 ) ) {
					echo '<dt>pw_bed_type</dt><dd>' . esc_html( implode( ', ', wp_list_pluck( $t1, 'name' ) ) ) . '</dd>';
				}
				$t2 = get_the_terms( $pid, 'pw_view_type' );
				if ( $t2 && ! is_wp_error( $t2 ) ) {
					echo '<dt>pw_view_type</dt><dd>' . esc_html( implode( ', ', wp_list_pluck( $t2, 'name' ) ) ) . '</dd>';
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					echo '<dt>_pw_meta_title</dt><dd>' . esc_html( $mt ) . '</dd>';
				}
				if ( is_string( $md ) && $md !== '' ) {
					echo '<dt>_pw_meta_description</dt><dd>' . esc_html( $md ) . '</dd>';
				}
				echo '</dl>';
			} elseif ( $pt === 'pw_restaurant' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p>' . esc_html( $ex ) . '</p>';
				}
				echo '<dl>';
				foreach (
					array(
						'_pw_location'         => 'location',
						'_pw_cuisine_type'     => 'cuisine_type',
						'_pw_seating_capacity' => 'seating_capacity',
						'_pw_reservation_url'  => 'reservation_url',
						'_pw_menu_url'         => 'menu_url',
					) as $mk => $lab
				) {
					$v = get_post_meta( $pid, $mk, true );
					if ( $mk === '_pw_seating_capacity' ) {
						$iv = (int) $v;
						if ( $iv > 0 ) {
							echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( (string) $iv ) . '</dd>';
						}
					} elseif ( is_string( $v ) && $v !== '' ) {
						if ( strpos( $mk, '_url' ) !== false ) {
							echo '<dt>' . esc_html( $lab ) . '</dt><dd><a href="' . esc_url( $v ) . '">' . esc_html( $v ) . '</a></dd>';
						} else {
							echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
						}
					}
				}
				$gids = get_post_meta( $pid, '_pw_gallery', true );
				if ( is_array( $gids ) && $gids ) {
					echo '<dt>gallery</dt><dd><ul>';
					foreach ( $gids as $aid ) {
						$aid = (int) $aid;
						$u   = $aid ? wp_get_attachment_url( $aid ) : '';
						if ( $u ) {
							echo '<li><a href="' . esc_url( $u ) . '">' . esc_html( (string) $aid ) . '</a></li>';
						}
					}
					echo '</ul></dd>';
				}
				$days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
				foreach ( $days as $day ) {
					$h = get_post_meta( $pid, '_pw_hours_' . $day, true );
					if ( ! is_array( $h ) || ! $h ) {
						continue;
					}
					echo '<dt>_pw_hours_' . esc_html( $day ) . '</dt><dd>';
					echo '<dl><dt>is_closed</dt><dd>' . esc_html( ! empty( $h['is_closed'] ) ? 'yes' : 'no' ) . '</dd>';
					if ( ! empty( $h['sessions'] ) && is_array( $h['sessions'] ) ) {
						echo '<dt>sessions</dt><dd><ul>';
						foreach ( $h['sessions'] as $sess ) {
							if ( ! is_array( $sess ) ) {
								continue;
							}
							$lb = isset( $sess['label'] ) ? (string) $sess['label'] : '';
							$ot = isset( $sess['open_time'] ) ? (string) $sess['open_time'] : '';
							$ct = isset( $sess['close_time'] ) ? (string) $sess['close_time'] : '';
							echo '<li>' . esc_html( $lb . ' ' . $ot . '–' . $ct ) . '</li>';
						}
						echo '</ul></dd>';
					}
					echo '</dl></dd>';
				}
				$tm = get_the_terms( $pid, 'pw_meal_period' );
				if ( $tm && ! is_wp_error( $tm ) ) {
					echo '<dt>pw_meal_period</dt><dd>' . esc_html( implode( ', ', wp_list_pluck( $tm, 'name' ) ) ) . '</dd>';
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					echo '<dt>_pw_meta_title</dt><dd>' . esc_html( $mt ) . '</dd>';
				}
				if ( is_string( $md ) && $md !== '' ) {
					echo '<dt>_pw_meta_description</dt><dd>' . esc_html( $md ) . '</dd>';
				}
				echo '</dl>';
			} elseif ( $pt === 'pw_spa' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p>' . esc_html( $ex ) . '</p>';
				}
				echo '<dl>';
				foreach ( array( '_pw_booking_url' => 'booking_url', '_pw_menu_url' => 'menu_url' ) as $mk => $lab ) {
					$v = get_post_meta( $pid, $mk, true );
					if ( is_string( $v ) && $v !== '' ) {
						echo '<dt>' . esc_html( $lab ) . '</dt><dd><a href="' . esc_url( $v ) . '">' . esc_html( $v ) . '</a></dd>';
					}
				}
				foreach ( array( '_pw_min_age' => 'min_age', '_pw_number_of_treatment_rooms' => 'treatment_rooms' ) as $mk => $lab ) {
					$iv = (int) get_post_meta( $pid, $mk, true );
					if ( $iv > 0 ) {
						echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( (string) $iv ) . '</dd>';
					}
				}
				$gids = get_post_meta( $pid, '_pw_gallery', true );
				if ( is_array( $gids ) && $gids ) {
					echo '<dt>gallery</dt><dd><ul>';
					foreach ( $gids as $aid ) {
						$aid = (int) $aid;
						$u   = $aid ? wp_get_attachment_url( $aid ) : '';
						if ( $u ) {
							echo '<li><a href="' . esc_url( $u ) . '">' . esc_html( (string) $aid ) . '</a></li>';
						}
					}
					echo '</ul></dd>';
				}
				$days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
				foreach ( $days as $day ) {
					$h = get_post_meta( $pid, '_pw_hours_' . $day, true );
					if ( ! is_array( $h ) || ! $h ) {
						continue;
					}
					echo '<dt>_pw_hours_' . esc_html( $day ) . '</dt><dd><dl>';
					echo '<dt>is_closed</dt><dd>' . esc_html( ! empty( $h['is_closed'] ) ? 'yes' : 'no' ) . '</dd>';
					if ( ! empty( $h['sessions'] ) && is_array( $h['sessions'] ) ) {
						echo '<dt>sessions</dt><dd><ul>';
						foreach ( $h['sessions'] as $sess ) {
							if ( ! is_array( $sess ) ) {
								continue;
							}
							$lb = isset( $sess['label'] ) ? (string) $sess['label'] : '';
							$ot = isset( $sess['open_time'] ) ? (string) $sess['open_time'] : '';
							$ct = isset( $sess['close_time'] ) ? (string) $sess['close_time'] : '';
							echo '<li>' . esc_html( $lb . ' ' . $ot . '–' . $ct ) . '</li>';
						}
						echo '</ul></dd>';
					}
					echo '</dl></dd>';
				}
				$tt = get_the_terms( $pid, 'pw_treatment_type' );
				if ( $tt && ! is_wp_error( $tt ) ) {
					echo '<dt>pw_treatment_type</dt><dd>' . esc_html( implode( ', ', wp_list_pluck( $tt, 'name' ) ) ) . '</dd>';
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					echo '<dt>_pw_meta_title</dt><dd>' . esc_html( $mt ) . '</dd>';
				}
				if ( is_string( $md ) && $md !== '' ) {
					echo '<dt>_pw_meta_description</dt><dd>' . esc_html( $md ) . '</dd>';
				}
				echo '</dl>';
			} elseif ( $pt === 'pw_meeting_room' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p>' . esc_html( $ex ) . '</p>';
				}
				echo '<dl>';
				foreach (
					array(
						'_pw_capacity_theatre'   => 'capacity_theatre',
						'_pw_capacity_classroom' => 'capacity_classroom',
						'_pw_capacity_boardroom' => 'capacity_boardroom',
						'_pw_capacity_ushape'    => 'capacity_ushape',
						'_pw_area_sqft'          => 'area_sqft',
						'_pw_area_sqm'           => 'area_sqm',
						'_pw_prefunction_area_sqft' => 'prefunction_sqft',
						'_pw_prefunction_area_sqm'  => 'prefunction_sqm',
					) as $mk => $lab
				) {
					$iv = (int) get_post_meta( $pid, $mk, true );
					if ( $iv > 0 ) {
						echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( (string) $iv ) . '</dd>';
					}
				}
				$nl = get_post_meta( $pid, '_pw_natural_light', true );
				echo '<dt>natural_light</dt><dd>' . esc_html( ! empty( $nl ) ? 'yes' : 'no' ) . '</dd>';
				$fp = (int) get_post_meta( $pid, '_pw_floor_plan', true );
				if ( $fp > 0 ) {
					$fu = wp_get_attachment_url( $fp );
					echo '<dt>floor_plan</dt><dd>' . esc_html( (string) $fp );
					if ( $fu ) {
						echo ' <a href="' . esc_url( $fu ) . '">' . esc_html( $fu ) . '</a>';
					}
					echo '</dd>';
				}
				foreach (
					array(
						'_pw_sales_phone'    => 'sales_phone',
						'_pw_sales_mobile'   => 'sales_mobile',
						'_pw_sales_whatsapp' => 'sales_whatsapp',
						'_pw_sales_email'    => 'sales_email',
					) as $mk => $lab
				) {
					$v = get_post_meta( $pid, $mk, true );
					if ( is_string( $v ) && $v !== '' ) {
						echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
					}
				}
				$gids = get_post_meta( $pid, '_pw_gallery', true );
				if ( is_array( $gids ) && $gids ) {
					echo '<dt>gallery</dt><dd><ul>';
					foreach ( $gids as $aid ) {
						$aid = (int) $aid;
						$u   = $aid ? wp_get_attachment_url( $aid ) : '';
						if ( $u ) {
							echo '<li><a href="' . esc_url( $u ) . '">' . esc_html( (string) $aid ) . '</a></li>';
						}
					}
					echo '</ul></dd>';
				}
				$av = get_the_terms( $pid, 'pw_av_equipment' );
				if ( $av && ! is_wp_error( $av ) ) {
					echo '<dt>pw_av_equipment</dt><dd>' . esc_html( implode( ', ', wp_list_pluck( $av, 'name' ) ) ) . '</dd>';
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					echo '<dt>_pw_meta_title</dt><dd>' . esc_html( $mt ) . '</dd>';
				}
				if ( is_string( $md ) && $md !== '' ) {
					echo '<dt>_pw_meta_description</dt><dd>' . esc_html( $md ) . '</dd>';
				}
				echo '</dl>';
			} elseif ( $pt === 'pw_amenity' ) {
				echo '<dl>';
				$typ = get_post_meta( $pid, '_pw_type', true );
				if ( is_string( $typ ) && $typ !== '' ) {
					echo '<dt>type</dt><dd>' . esc_html( $typ ) . '</dd>';
				}
				foreach ( array( '_pw_category' => 'category', '_pw_icon' => 'icon', '_pw_description' => 'description' ) as $mk => $lab ) {
					$v = get_post_meta( $pid, $mk, true );
					if ( is_string( $v ) && $v !== '' ) {
						echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
					}
				}
				$ic = get_post_meta( $pid, '_pw_is_complimentary', true );
				echo '<dt>is_complimentary</dt><dd>' . esc_html( $ic ? 'yes' : 'no' ) . '</dd>';
				$do = (int) get_post_meta( $pid, '_pw_display_order', true );
				if ( $do > 0 ) {
					echo '<dt>display_order</dt><dd>' . esc_html( (string) $do ) . '</dd>';
				}
				echo '</dl>';
			} elseif ( $pt === 'pw_policy' ) {
				echo '<dl>';
				$pc = get_post_meta( $pid, '_pw_content', true );
				if ( is_string( $pc ) && $pc !== '' ) {
					echo '<dt>content</dt><dd>' . wp_kses_post( $pc ) . '</dd>';
				}
				$do = (int) get_post_meta( $pid, '_pw_display_order', true );
				if ( $do > 0 ) {
					echo '<dt>display_order</dt><dd>' . esc_html( (string) $do ) . '</dd>';
				}
				echo '<dt>is_highlighted</dt><dd>' . esc_html( get_post_meta( $pid, '_pw_is_highlighted', true ) ? 'yes' : 'no' ) . '</dd>';
				echo '<dt>active</dt><dd>' . esc_html( get_post_meta( $pid, '_pw_active', true ) ? 'yes' : 'no' ) . '</dd>';
				$ptt = get_the_terms( $pid, 'pw_policy_type' );
				if ( $ptt && ! is_wp_error( $ptt ) ) {
					echo '<dt>pw_policy_type</dt><dd>' . esc_html( implode( ', ', wp_list_pluck( $ptt, 'name' ) ) ) . '</dd>';
				}
				echo '</dl>';
			} elseif ( $pt === 'pw_nearby' ) {
				echo '<dl>';
				$dk = (float) get_post_meta( $pid, '_pw_distance_km', true );
				if ( $dk > 0 ) {
					echo '<dt>distance_km</dt><dd>' . esc_html( (string) $dk ) . '</dd>';
				}
				$ttm = (int) get_post_meta( $pid, '_pw_travel_time_min', true );
				if ( $ttm > 0 ) {
					echo '<dt>travel_time_min</dt><dd>' . esc_html( (string) $ttm ) . '</dd>';
				}
				$nlat = (float) get_post_meta( $pid, '_pw_lat', true );
				$nlng = (float) get_post_meta( $pid, '_pw_lng', true );
				if ( $nlat !== 0.0 || $nlng !== 0.0 ) {
					echo '<dt>lat</dt><dd>' . esc_html( (string) $nlat ) . '</dd>';
					echo '<dt>lng</dt><dd>' . esc_html( (string) $nlng ) . '</dd>';
				}
				$pu = get_post_meta( $pid, '_pw_place_url', true );
				if ( is_string( $pu ) && $pu !== '' ) {
					echo '<dt>place_url</dt><dd><a href="' . esc_url( $pu ) . '">' . esc_html( $pu ) . '</a></dd>';
				}
				$do = (int) get_post_meta( $pid, '_pw_display_order', true );
				if ( $do > 0 ) {
					echo '<dt>display_order</dt><dd>' . esc_html( (string) $do ) . '</dd>';
				}
				$t1 = get_the_terms( $pid, 'pw_nearby_type' );
				if ( $t1 && ! is_wp_error( $t1 ) ) {
					echo '<dt>pw_nearby_type</dt><dd>' . esc_html( implode( ', ', wp_list_pluck( $t1, 'name' ) ) ) . '</dd>';
				}
				$t2 = get_the_terms( $pid, 'pw_transport_mode' );
				if ( $t2 && ! is_wp_error( $t2 ) ) {
					echo '<dt>pw_transport_mode</dt><dd>' . esc_html( implode( ', ', wp_list_pluck( $t2, 'name' ) ) ) . '</dd>';
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					echo '<dt>_pw_meta_title</dt><dd>' . esc_html( $mt ) . '</dd>';
				}
				if ( is_string( $md ) && $md !== '' ) {
					echo '<dt>_pw_meta_description</dt><dd>' . esc_html( $md ) . '</dd>';
				}
				echo '</dl>';
			} elseif ( $pt === 'pw_event' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p>' . esc_html( $ex ) . '</p>';
				}
				$ev_prop = (int) get_post_meta( $pid, '_pw_property_id', true );
				echo '<dl>';
				$vid = (int) get_post_meta( $pid, '_pw_venue_id', true );
				if ( $vid > 0 ) {
					echo '<dt>venue_id</dt><dd>' . esc_html( (string) $vid ) . ' — ' . esc_html( get_the_title( $vid ) ) . '</dd>';
				}
				$ed = get_post_meta( $pid, '_pw_description', true );
				if ( is_string( $ed ) && $ed !== '' ) {
					echo '<dt>description</dt><dd>' . esc_html( $ed ) . '</dd>';
				}
				$sd = get_post_meta( $pid, '_pw_start_datetime', true );
				$edt = get_post_meta( $pid, '_pw_end_datetime', true );
				if ( is_string( $sd ) && $sd !== '' ) {
					echo '<dt>start_datetime</dt><dd>' . esc_html( $sd ) . '</dd>';
					if ( function_exists( 'pw_event_local_datetime_to_iso8601' ) ) {
						$iso = pw_event_local_datetime_to_iso8601( $sd, $ev_prop );
						if ( $iso !== '' ) {
							echo '<dt>start_datetime_iso8601</dt><dd>' . esc_html( $iso ) . '</dd>';
						}
					}
				}
				if ( is_string( $edt ) && $edt !== '' ) {
					echo '<dt>end_datetime</dt><dd>' . esc_html( $edt ) . '</dd>';
					if ( function_exists( 'pw_event_local_datetime_to_iso8601' ) ) {
						$iso = pw_event_local_datetime_to_iso8601( $edt, $ev_prop );
						if ( $iso !== '' ) {
							echo '<dt>end_datetime_iso8601</dt><dd>' . esc_html( $iso ) . '</dd>';
						}
					}
				}
				$cap = (int) get_post_meta( $pid, '_pw_capacity', true );
				if ( $cap > 0 ) {
					echo '<dt>capacity</dt><dd>' . esc_html( (string) $cap ) . '</dd>';
				}
				$pf = (float) get_post_meta( $pid, '_pw_price_from', true );
				if ( $pf > 0 ) {
					echo '<dt>price_from</dt><dd>' . esc_html( (string) $pf ) . '</dd>';
				}
				$bu = get_post_meta( $pid, '_pw_booking_url', true );
				if ( is_string( $bu ) && $bu !== '' ) {
					echo '<dt>booking_url</dt><dd><a href="' . esc_url( $bu ) . '">' . esc_html( $bu ) . '</a></dd>';
				}
				$rr = get_post_meta( $pid, '_pw_recurrence_rule', true );
				if ( is_string( $rr ) && $rr !== '' ) {
					echo '<dt>recurrence_rule</dt><dd>' . esc_html( $rr ) . '</dd>';
				}
				$es = get_post_meta( $pid, '_pw_event_status', true );
				if ( is_string( $es ) && $es !== '' ) {
					echo '<dt>event_status</dt><dd>' . esc_html( $es ) . '</dd>';
				}
				$am = get_post_meta( $pid, '_pw_event_attendance_mode', true );
				if ( is_string( $am ) && $am !== '' ) {
					echo '<dt>event_attendance_mode</dt><dd>' . esc_html( $am ) . '</dd>';
				}
				$gids = get_post_meta( $pid, '_pw_gallery', true );
				if ( is_array( $gids ) && $gids ) {
					echo '<dt>gallery</dt><dd><ul>';
					foreach ( $gids as $aid ) {
						$aid = (int) $aid;
						$u   = $aid ? wp_get_attachment_url( $aid ) : '';
						if ( $u ) {
							echo '<li><a href="' . esc_url( $u ) . '">' . esc_html( (string) $aid ) . '</a></li>';
						}
					}
					echo '</ul></dd>';
				}
				$et = get_the_terms( $pid, 'pw_event_type' );
				if ( $et && ! is_wp_error( $et ) ) {
					echo '<dt>pw_event_type</dt><dd>' . esc_html( implode( ', ', wp_list_pluck( $et, 'name' ) ) ) . '</dd>';
				}
				$eo = get_the_terms( $pid, 'pw_event_organiser' );
				if ( $eo && ! is_wp_error( $eo ) ) {
					echo '<dt>pw_event_organiser</dt><dd><ul>';
					foreach ( $eo as $term ) {
						$ou = get_term_meta( $term->term_id, 'organiser_url', true );
						echo '<li>' . esc_html( $term->name );
						if ( is_string( $ou ) && $ou !== '' ) {
							echo ' — <a href="' . esc_url( $ou ) . '">' . esc_html( $ou ) . '</a>';
						}
						echo '</li>';
					}
					echo '</ul></dd>';
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					echo '<dt>_pw_meta_title</dt><dd>' . esc_html( $mt ) . '</dd>';
				}
				if ( is_string( $md ) && $md !== '' ) {
					echo '<dt>_pw_meta_description</dt><dd>' . esc_html( $md ) . '</dd>';
				}
				echo '</dl>';
			} elseif ( $pt === 'pw_experience' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p>' . esc_html( $ex ) . '</p>';
				}
				echo '<dl>';
				$ed = get_post_meta( $pid, '_pw_description', true );
				if ( is_string( $ed ) && $ed !== '' ) {
					echo '<dt>description</dt><dd>' . esc_html( $ed ) . '</dd>';
				}
				$dh = (float) get_post_meta( $pid, '_pw_duration_hours', true );
				if ( $dh > 0 ) {
					echo '<dt>duration_hours</dt><dd>' . esc_html( (string) $dh ) . '</dd>';
				}
				$pf = (float) get_post_meta( $pid, '_pw_price_from', true );
				if ( $pf > 0 ) {
					echo '<dt>price_from</dt><dd>' . esc_html( (string) $pf ) . '</dd>';
				}
				$bu = get_post_meta( $pid, '_pw_booking_url', true );
				if ( is_string( $bu ) && $bu !== '' ) {
					echo '<dt>booking_url</dt><dd><a href="' . esc_url( $bu ) . '">' . esc_html( $bu ) . '</a></dd>';
				}
				echo '<dt>is_complimentary</dt><dd>' . esc_html( get_post_meta( $pid, '_pw_is_complimentary', true ) ? 'yes' : 'no' ) . '</dd>';
				$gids = get_post_meta( $pid, '_pw_gallery', true );
				if ( is_array( $gids ) && $gids ) {
					echo '<dt>gallery</dt><dd><ul>';
					foreach ( $gids as $aid ) {
						$aid = (int) $aid;
						$u   = $aid ? wp_get_attachment_url( $aid ) : '';
						if ( $u ) {
							echo '<li><a href="' . esc_url( $u ) . '">' . esc_html( (string) $aid ) . '</a></li>';
						}
					}
					echo '</ul></dd>';
				}
				$do = (int) get_post_meta( $pid, '_pw_display_order', true );
				if ( $do > 0 ) {
					echo '<dt>display_order</dt><dd>' . esc_html( (string) $do ) . '</dd>';
				}
				$ec = get_the_terms( $pid, 'pw_experience_category' );
				if ( $ec && ! is_wp_error( $ec ) ) {
					echo '<dt>pw_experience_category</dt><dd>' . esc_html( implode( ', ', wp_list_pluck( $ec, 'name' ) ) ) . '</dd>';
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					echo '<dt>_pw_meta_title</dt><dd>' . esc_html( $mt ) . '</dd>';
				}
				if ( is_string( $md ) && $md !== '' ) {
					echo '<dt>_pw_meta_description</dt><dd>' . esc_html( $md ) . '</dd>';
				}
				echo '</dl>';
			} elseif ( $pt === 'pw_faq' ) {
				echo '<dl>';
				$do = (int) get_post_meta( $pid, '_pw_display_order', true );
				if ( $do > 0 ) {
					echo '<dt>display_order</dt><dd>' . esc_html( (string) $do ) . '</dd>';
				}
				$ans = get_post_meta( $pid, '_pw_answer', true );
				if ( is_string( $ans ) && $ans !== '' ) {
					echo '<dt>answer</dt><dd>' . wp_kses_post( $ans ) . '</dd>';
				}
				echo '</dl>';
			} elseif ( $pt === 'pw_offer' ) {
				$ex = get_post_field( 'post_excerpt', $pid, 'raw' );
				if ( is_string( $ex ) && $ex !== '' ) {
					echo '<p>' . esc_html( $ex ) . '</p>';
				}
				echo '<dl>';
				$ot = get_post_meta( $pid, '_pw_offer_type', true );
				if ( is_string( $ot ) && $ot !== '' ) {
					echo '<dt>offer_type</dt><dd>' . esc_html( $ot ) . '</dd>';
				}
				foreach ( array( '_pw_valid_from' => 'valid_from', '_pw_valid_to' => 'valid_to' ) as $mk => $lab ) {
					$v = get_post_meta( $pid, $mk, true );
					if ( is_string( $v ) && $v !== '' ) {
						echo '<dt>' . esc_html( $lab ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
					}
				}
				$bu = get_post_meta( $pid, '_pw_booking_url', true );
				if ( is_string( $bu ) && $bu !== '' ) {
					echo '<dt>booking_url</dt><dd><a href="' . esc_url( $bu ) . '">' . esc_html( $bu ) . '</a></dd>';
				}
				echo '<dt>is_featured</dt><dd>' . esc_html( get_post_meta( $pid, '_pw_is_featured', true ) ? 'yes' : 'no' ) . '</dd>';
				$dt = get_post_meta( $pid, '_pw_discount_type', true );
				if ( is_string( $dt ) && $dt !== '' ) {
					echo '<dt>discount_type</dt><dd>' . esc_html( $dt ) . '</dd>';
				}
				$dv = (float) get_post_meta( $pid, '_pw_discount_value', true );
				if ( $dv > 0 ) {
					echo '<dt>discount_value</dt><dd>' . esc_html( (string) $dv ) . '</dd>';
				}
				$msn = (int) get_post_meta( $pid, '_pw_minimum_stay_nights', true );
				if ( $msn > 0 ) {
					echo '<dt>minimum_stay_nights</dt><dd>' . esc_html( (string) $msn ) . '</dd>';
				}
				$rts = get_post_meta( $pid, '_pw_room_types', true );
				if ( is_array( $rts ) && $rts ) {
					echo '<dt>room_types</dt><dd><ul>';
					foreach ( $rts as $rid ) {
						$rid = (int) $rid;
						if ( $rid <= 0 ) {
							continue;
						}
						echo '<li>' . esc_html( get_the_title( $rid ) ) . '</li>';
					}
					echo '</ul></dd>';
				}
				$do = (int) get_post_meta( $pid, '_pw_display_order', true );
				if ( $do > 0 ) {
					echo '<dt>display_order</dt><dd>' . esc_html( (string) $do ) . '</dd>';
				}
				$mt = get_post_meta( $pid, '_pw_meta_title', true );
				$md = get_post_meta( $pid, '_pw_meta_description', true );
				if ( is_string( $mt ) && $mt !== '' ) {
					echo '<dt>_pw_meta_title</dt><dd>' . esc_html( $mt ) . '</dd>';
				}
				if ( is_string( $md ) && $md !== '' ) {
					echo '<dt>_pw_meta_description</dt><dd>' . esc_html( $md ) . '</dd>';
				}
				echo '</dl>';
			}
			echo '</article>';
		}
		echo '</section>';
	}
	?>
</div>
