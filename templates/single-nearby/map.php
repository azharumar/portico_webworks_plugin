<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$lat = get_post_meta( $post_id, '_pw_lat', true );
$lng = get_post_meta( $post_id, '_pw_lng', true );

$place_url = (string) get_post_meta( $post_id, '_pw_place_url', true );

$lat_f = is_numeric( $lat ) ? (float) $lat : 0.0;
$lng_f = is_numeric( $lng ) ? (float) $lng : 0.0;

if ( $lat_f === 0.0 && $lng_f === 0.0 && trim( $place_url ) === '' ) {
	return;
}

$iframe_src = '';
if ( $lat_f !== 0.0 || $lng_f !== 0.0 ) {
	$iframe_src = 'https://www.google.com/maps?q=' . rawurlencode( (string) $lat_f . ',' . (string) $lng_f ) . '&z=15&output=embed';
}
?>
<section class="pw-nearby-map" aria-labelledby="pw-nearby-map-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_nearby_map_content', $post_id ); ?>
	<h2 class="pw-nearby-map__heading" id="pw-nearby-map-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Map', 'portico-webworks' ); ?>
	</h2>
	<div class="pw-nearby-map__body">
		<?php if ( $iframe_src !== '' ) : ?>
			<iframe class="pw-nearby-map__iframe" title="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy" decoding="async" src="<?php echo esc_url( $iframe_src ); ?>" referrerpolicy="no-referrer-when-downgrade"></iframe>
		<?php endif; ?>
		<?php if ( trim( $place_url ) !== '' ) : ?>
			<p class="pw-nearby-map__link">
				<a href="<?php echo esc_url( $place_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html__( 'Open in Google Maps', 'portico-webworks' ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
	<?php do_action( 'pw_after_nearby_map_content', $post_id ); ?>
</section>

