<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$property_id = isset( $property_id ) ? (int) $property_id : 0;
if ( $property_id <= 0 ) {
	return;
}
?>
	<section class="pw-fact-sheet-property" aria-labelledby="pw-fact-property-heading">
		<h2 id="pw-fact-property-heading"><?php echo esc_html( 'Property details' ); ?></h2>

		<?php
		$rows = array();
		$v    = get_post_meta( $property_id, '_pw_legal_name', true );
		if ( is_string( $v ) && $v !== '' ) {
			$rows[] = array( 'l' => 'Legal name', 'v' => pw_fact_esc( $v ) );
		}
		$v = (int) get_post_meta( $property_id, '_pw_star_rating', true );
		if ( $v > 0 ) {
			$rows[] = array( 'l' => 'Star rating', 'v' => pw_fact_esc( (string) $v ) );
		}
		$v = get_post_meta( $property_id, '_pw_currency', true );
		if ( is_string( $v ) && $v !== '' ) {
			$rows[] = array( 'l' => 'Currency (ISO 4217)', 'v' => pw_fact_esc( $v ) );
		}
		$v = get_post_meta( $property_id, '_pw_check_in_time', true );
		if ( is_string( $v ) && $v !== '' ) {
			$rows[] = array( 'l' => 'Check-in time', 'v' => pw_fact_esc( $v ) );
		}
		$v = get_post_meta( $property_id, '_pw_check_out_time', true );
		if ( is_string( $v ) && $v !== '' ) {
			$rows[] = array( 'l' => 'Check-out time', 'v' => pw_fact_esc( $v ) );
		}
		$v = (int) get_post_meta( $property_id, '_pw_year_established', true );
		if ( $v > 0 ) {
			$rows[] = array( 'l' => 'Year established', 'v' => pw_fact_esc( (string) $v ) );
		}
		$v = (int) get_post_meta( $property_id, '_pw_total_rooms', true );
		if ( $v > 0 ) {
			$rows[] = array( 'l' => 'Total rooms', 'v' => pw_fact_esc( (string) $v ) );
		}
		pw_fact_kv_table( 'General', $rows );

		$rows = array();
		foreach (
			array(
				'_pw_address_line_1' => 'Address line 1',
				'_pw_address_line_2' => 'Address line 2',
				'_pw_city'           => 'City',
				'_pw_state'          => 'State / province',
				'_pw_postal_code'    => 'Postal code',
				'_pw_country'        => 'Country',
				'_pw_country_code'   => 'Country code (ISO 3166-1 alpha-2)',
			) as $mk => $lab
		) {
			$v = get_post_meta( $property_id, $mk, true );
			if ( is_string( $v ) && $v !== '' ) {
				$rows[] = array( 'l' => $lab, 'v' => pw_fact_esc( $v ) );
			}
		}
		pw_fact_kv_table( 'Address', $rows );

		$rows = array();
		$lat  = (float) get_post_meta( $property_id, '_pw_lat', true );
		$lng  = (float) get_post_meta( $property_id, '_pw_lng', true );
		if ( $lat !== 0.0 || $lng !== 0.0 ) {
			$rows[] = array( 'l' => 'Latitude', 'v' => pw_fact_esc( (string) $lat ) );
			$rows[] = array( 'l' => 'Longitude', 'v' => pw_fact_esc( (string) $lng ) );
		}
		$v = get_post_meta( $property_id, '_pw_google_place_id', true );
		if ( is_string( $v ) && $v !== '' ) {
			$rows[] = array( 'l' => 'Google Place ID', 'v' => pw_fact_esc( $v ) );
		}
		$v = get_post_meta( $property_id, '_pw_timezone', true );
		if ( is_string( $v ) && $v !== '' ) {
			$rows[] = array( 'l' => 'Timezone (IANA)', 'v' => pw_fact_esc( $v ) );
		}
		pw_fact_kv_table( 'Geo location', $rows );

		$rows = array();
		foreach (
			array(
				'_pw_social_facebook'    => 'Facebook',
				'_pw_social_instagram'   => 'Instagram',
				'_pw_social_twitter'     => 'Twitter / X',
				'_pw_social_youtube'     => 'YouTube',
				'_pw_social_linkedin'    => 'LinkedIn',
				'_pw_social_tripadvisor' => 'TripAdvisor',
			) as $mk => $lab
		) {
			$v = get_post_meta( $property_id, $mk, true );
			if ( is_string( $v ) && $v !== '' ) {
				$rows[] = array( 'l' => $lab, 'v' => pw_fact_url_cell( $v ) );
			}
		}
		pw_fact_kv_table( 'Social links', $rows );

		$contacts = get_post_meta( $property_id, '_pw_contacts', true );
		if ( is_array( $contacts ) && $contacts ) {
			echo '<table class="pw-fact-contacts"><caption>' . esc_html( 'Contacts' ) . '</caption>';
			echo '<thead><tr>';
			foreach ( array( 'Label', 'Phone', 'Mobile', 'WhatsApp', 'Email' ) as $col ) {
				echo '<th scope="col">' . esc_html( $col ) . '</th>';
			}
			echo '</tr></thead><tbody>';
			foreach ( $contacts as $c ) {
				if ( ! is_array( $c ) ) {
					continue;
				}
				echo '<tr>';
				foreach ( array( 'label', 'phone', 'mobile', 'whatsapp', 'email' ) as $f ) {
					$cv = isset( $c[ $f ] ) ? (string) $c[ $f ] : '';
					echo '<td>' . ( $cv !== '' ? esc_html( $cv ) : '—' ) . '</td>';
				}
				echo '</tr>';
			}
			echo '</tbody></table>';
		}

		$pools = get_post_meta( $property_id, '_pw_pools', true );
		if ( is_array( $pools ) && $pools ) {
			echo '<h3>' . esc_html( 'Pools' ) . '</h3>';
			$pi = 0;
			foreach ( $pools as $p ) {
				if ( ! is_array( $p ) ) {
					continue;
				}
				++$pi;
				$pname = isset( $p['name'] ) && (string) $p['name'] !== '' ? (string) $p['name'] : sprintf( 'Pool %d', $pi );
				echo '<h4>' . esc_html( $pname ) . '</h4>';
				$prows = array();
				foreach (
					array(
						'length_m'    => 'Length (m)',
						'width_m'     => 'Width (m)',
						'depth_m'     => 'Depth (m)',
						'open_time'   => 'Opens',
						'close_time'  => 'Closes',
						'is_heated'   => 'Heated',
						'is_kids'     => 'Kids pool',
						'is_indoor'   => 'Indoor',
						'is_infinity' => 'Infinity',
					) as $f => $lab
				) {
					if ( ! array_key_exists( $f, $p ) ) {
						continue;
					}
					$pv = $p[ $f ];
					if ( is_bool( $pv ) || in_array( $f, array( 'is_heated', 'is_kids', 'is_indoor', 'is_infinity' ), true ) ) {
						$prows[] = array( 'l' => $lab, 'v' => pw_fact_bool_cell( $pv ) );
					} elseif ( is_numeric( $pv ) && (float) $pv !== 0.0 ) {
						$prows[] = array( 'l' => $lab, 'v' => pw_fact_esc( (string) $pv ) );
					} elseif ( is_string( $pv ) && $pv !== '' ) {
						$prows[] = array( 'l' => $lab, 'v' => pw_fact_esc( $pv ) );
					}
				}
				pw_fact_kv_table( $pname, $prows );
			}
		}

		$dbs = get_post_meta( $property_id, '_pw_direct_benefits', true );
		if ( is_array( $dbs ) && $dbs ) {
			echo '<table class="pw-fact-benefits"><caption>' . esc_html( 'Direct booking benefits' ) . '</caption>';
			echo '<thead><tr><th scope="col">' . esc_html( 'Title' ) . '</th><th scope="col">' . esc_html( 'Description' ) . '</th><th scope="col">' . esc_html( 'Icon' ) . '</th></tr></thead><tbody>';
			foreach ( $dbs as $b ) {
				if ( ! is_array( $b ) ) {
					continue;
				}
				$t = isset( $b['title'] ) ? (string) $b['title'] : '';
				$d = isset( $b['description'] ) ? (string) $b['description'] : '';
				$i = isset( $b['icon'] ) ? (string) $b['icon'] : '';
				echo '<tr><th scope="row">' . ( $t !== '' ? esc_html( $t ) : '—' ) . '</th><td>' . ( $d !== '' ? esc_html( $d ) : '—' ) . '</td><td>' . ( $i !== '' ? esc_html( $i ) : '—' ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}

		$certs = get_post_meta( $property_id, '_pw_certifications', true );
		if ( is_array( $certs ) && $certs ) {
			echo '<table class="pw-fact-certs"><caption>' . esc_html( 'Certifications & awards' ) . '</caption>';
			echo '<thead><tr><th scope="col">' . esc_html( 'Name' ) . '</th><th scope="col">' . esc_html( 'Issuer' ) . '</th><th scope="col">' . esc_html( 'Year' ) . '</th><th scope="col">' . esc_html( 'URL' ) . '</th></tr></thead><tbody>';
			foreach ( $certs as $c ) {
				if ( ! is_array( $c ) ) {
					continue;
				}
				$n = isset( $c['name'] ) ? (string) $c['name'] : '';
				$i = isset( $c['issuer'] ) ? (string) $c['issuer'] : '';
				$y = isset( $c['year'] ) ? (string) $c['year'] : '';
				$u = isset( $c['url'] ) ? (string) $c['url'] : '';
				echo '<tr><th scope="row">' . ( $n !== '' ? esc_html( $n ) : '—' ) . '</th><td>' . ( $i !== '' ? esc_html( $i ) : '—' ) . '</td><td>' . ( $y !== '' ? esc_html( $y ) : '—' ) . '</td><td>' . ( $u !== '' ? pw_fact_url_cell( $u ) : '—' ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}

		$sus_items = get_post_meta( $property_id, '_pw_sustainability_items', true );
		$sus_defs  = pw_get_sustainability_facet_definitions();
		$sus_label = array();
		foreach ( $sus_defs as $d ) {
			$sus_label[ $d['key'] ] = $d['label'];
		}
		$sus_rows = array();
		if ( is_array( $sus_items ) ) {
			foreach ( $sus_items as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$st = isset( $row['status'] ) ? (string) $row['status'] : 'unknown';
				if ( $st === 'unknown' ) {
					continue;
				}
				$key  = isset( $row['key'] ) ? (string) $row['key'] : '';
				$lab  = isset( $sus_label[ $key ] ) ? $sus_label[ $key ] : $key;
				$note = isset( $row['note'] ) ? (string) $row['note'] : '';
				$sus_rows[] = array(
					'l' => $lab,
					'v' => pw_fact_esc( $st ) . ( $note !== '' ? ' — ' . pw_fact_esc( $note ) : '' ),
				);
			}
		}
		pw_fact_kv_table( 'Sustainability (non-unknown only)', $sus_rows );

		$acc_items = get_post_meta( $property_id, '_pw_accessibility_items', true );
		$acc_defs  = pw_get_accessibility_facet_definitions();
		$acc_label = array();
		foreach ( $acc_defs as $d ) {
			$acc_label[ $d['key'] ] = $d['label'];
		}
		$acc_rows = array();
		if ( is_array( $acc_items ) ) {
			foreach ( $acc_items as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$st = isset( $row['status'] ) ? (string) $row['status'] : 'unknown';
				if ( $st === 'unknown' ) {
					continue;
				}
				$key  = isset( $row['key'] ) ? (string) $row['key'] : '';
				$lab  = isset( $acc_label[ $key ] ) ? $acc_label[ $key ] : $key;
				$note = isset( $row['note'] ) ? (string) $row['note'] : '';
				$acc_rows[] = array(
					'l' => $lab,
					'v' => pw_fact_esc( $st ) . ( $note !== '' ? ' — ' . pw_fact_esc( $note ) : '' ),
				);
			}
		}
		pw_fact_kv_table( 'Accessibility (non-unknown only)', $acc_rows );

		$rows = array();
		$v    = get_post_meta( $property_id, '_pw_meta_title', true );
		if ( is_string( $v ) && $v !== '' ) {
			$rows[] = array( 'l' => 'Meta title', 'v' => pw_fact_esc( $v ) );
		}
		$v = get_post_meta( $property_id, '_pw_meta_description', true );
		if ( is_string( $v ) && $v !== '' ) {
			$rows[] = array( 'l' => 'Meta description', 'v' => pw_fact_esc( $v ) );
		}
		$og = (int) get_post_meta( $property_id, '_pw_og_image', true );
		if ( $og > 0 ) {
			$url = wp_get_attachment_url( $og );
			$rows[] = array( 'l' => 'Open Graph image (attachment ID)', 'v' => pw_fact_esc( (string) $og ) );
			if ( $url ) {
				$rows[] = array( 'l' => 'Open Graph image URL', 'v' => pw_fact_url_cell( $url ) );
			}
		}
		pw_fact_kv_table( 'SEO & social sharing', $rows );
		?>
	</section>

