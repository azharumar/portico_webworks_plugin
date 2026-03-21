<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'pw_fact_kv_table' ) ) {

	/**
	 * @param array<int, array{l: string, v: string}> $rows Label + value HTML (caller escapes text; $v may contain safe HTML).
	 */
	function pw_fact_kv_table( $caption, array $rows ) {
		if ( ! $rows ) {
			return;
		}
		echo '<table class="pw-fact-kv"><caption>' . esc_html( $caption ) . '</caption>';
		echo '<thead><tr><th scope="col">' . esc_html( 'Field' ) . '</th><th scope="col">' . esc_html( 'Value' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $row ) {
			echo '<tr><th scope="row">' . esc_html( $row['l'] ) . '</th><td>' . $row['v'] . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	function pw_fact_esc( $s ) {
		return esc_html( is_scalar( $s ) ? (string) $s : '' );
	}

	function pw_fact_bool_cell( $v ) {
		return esc_html( ! empty( $v ) ? 'Yes' : 'No' );
	}

	function pw_fact_url_cell( $url ) {
		$url = is_string( $url ) ? trim( $url ) : '';
		if ( $url === '' ) {
			return '';
		}
		return '<a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a>';
	}

	function pw_fact_hours_block( $post_id ) {
		$days  = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
		$found = false;
		foreach ( $days as $day ) {
			$h = get_post_meta( (int) $post_id, '_pw_hours_' . $day, true );
			if ( ! is_array( $h ) || ! $h ) {
				continue;
			}
			$found = true;
			$cap = 'Operating hours: ' . ucfirst( $day );
			echo '<table class="pw-fact-hours"><caption>' . esc_html( $cap ) . '</caption>';
			echo '<thead><tr><th scope="col">' . esc_html( 'Detail' ) . '</th><th scope="col">' . esc_html( 'Value' ) . '</th></tr></thead><tbody>';
			echo '<tr><th scope="row">' . esc_html( 'Closed all day' ) . '</th><td>' . pw_fact_bool_cell( ! empty( $h['is_closed'] ) ) . '</td></tr>';
			if ( ! empty( $h['sessions'] ) && is_array( $h['sessions'] ) ) {
				$parts = array();
				foreach ( $h['sessions'] as $sess ) {
					if ( ! is_array( $sess ) ) {
						continue;
					}
					$lb = isset( $sess['label'] ) ? (string) $sess['label'] : '';
					$ot = isset( $sess['open_time'] ) ? (string) $sess['open_time'] : '';
					$ct = isset( $sess['close_time'] ) ? (string) $sess['close_time'] : '';
					$parts[] = trim( $lb . ' ' . $ot . '–' . $ct );
				}
				echo '<tr><th scope="row">' . esc_html( 'Sessions' ) . '</th><td>' . esc_html( implode( '; ', array_filter( $parts ) ) ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}
		return $found;
	}

	function pw_fact_gallery_table( $attachment_ids ) {
		if ( ! is_array( $attachment_ids ) || ! $attachment_ids ) {
			return;
		}
		echo '<table class="pw-fact-gallery"><caption>' . esc_html( 'Gallery (attachments)' ) . '</caption>';
		echo '<thead><tr><th scope="col">' . esc_html( 'ID' ) . '</th><th scope="col">' . esc_html( 'URL' ) . '</th></tr></thead><tbody>';
		foreach ( $attachment_ids as $aid ) {
			$aid = (int) $aid;
			if ( $aid <= 0 ) {
				continue;
			}
			$u = wp_get_attachment_url( $aid );
			echo '<tr><th scope="row">' . esc_html( (string) $aid ) . '</th><td>';
			echo $u ? '<a href="' . esc_url( $u ) . '">' . esc_html( $u ) . '</a>' : '—';
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}
}

function pw_fact_sheet_buffer_property_id() {
	$pid = pw_get_current_property_id();
	if ( is_wp_error( $pid ) || ! $pid ) {
		return 0;
	}
	return (int) $pid;
}

function pw_fact_sheet_empty_message_html() {
	return '<p class="pw-fact-sheet-empty">' . esc_html( 'No property found.' ) . '</p>';
}

function pw_fact_sheet_render_tag( $tag ) {
	$tag = is_string( $tag ) ? preg_replace( '/[^a-z0-9_]/', '', strtolower( $tag ) ) : '';
	if ( $tag === '' ) {
		return '';
	}

	$property_id = pw_fact_sheet_buffer_property_id();
	$bad         = $property_id <= 0;

	if ( $tag === 'pw_fact_error' ) {
		return $bad ? pw_fact_sheet_empty_message_html() : '';
	}

	if ( $bad ) {
		return '';
	}

	switch ( $tag ) {
		case 'pw_fact_title':
			return esc_html( get_the_title( $property_id ) );
		case 'pw_fact_lead':
			return esc_html( 'Reference listing of property record and linked content (Portico Webworks).' );
		case 'pw_fact_header':
			ob_start();
			include __DIR__ . '/fact-sheet-fragments/header-body.php';
			return ob_get_clean();
		case 'pw_fact_property':
			ob_start();
			include __DIR__ . '/fact-sheet-fragments/property-section.php';
			return ob_get_clean();
		case 'pw_fact_linked':
			ob_start();
			include __DIR__ . '/fact-sheet-fragments/linked-sections.php';
			return ob_get_clean();
		default:
			return '';
	}
}

function pw_fact_sheet_replace_content_tokens( $content ) {
	if ( ! is_singular( 'page' ) ) {
		return $content;
	}
	$page_id = (int) get_option( 'pw_fact_sheet_page_id', 0 );
	if ( $page_id <= 0 || (int) get_the_ID() !== $page_id ) {
		return $content;
	}

	return preg_replace_callback(
		'/\{\{\s*(?:portico:)?(pw_fact_[a-z0-9_]+)\s*\}\}/',
		static function ( $m ) {
			return pw_fact_sheet_render_tag( $m[1] );
		},
		$content
	);
}
