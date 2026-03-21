<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$property_id = isset( $property_id ) ? (int) $property_id : 0;
if ( $property_id <= 0 ) {
	return;
}
$excerpt = get_post_field( 'post_excerpt', $property_id, 'raw' );
if ( is_string( $excerpt ) && $excerpt !== '' ) {
	echo '<p class="pw-fact-sheet-excerpt"><strong>' . esc_html( 'Excerpt' ) . '</strong> — ' . esc_html( $excerpt ) . '</p>';
}
$content = get_post_field( 'post_content', $property_id, 'raw' );
if ( is_string( $content ) && trim( $content ) !== '' ) {
	echo '<section class="pw-fact-sheet-description"><h2>' . esc_html( 'Description' ) . '</h2><div class="pw-fact-sheet-editor">' . wp_kses_post( wpautop( $content ) ) . '</div></section>';
}
