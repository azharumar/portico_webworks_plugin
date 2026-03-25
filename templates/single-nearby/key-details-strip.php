<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$distance_km = (float) get_post_meta( $post_id, '_pw_distance_km', true );
$travel_min  = (int) get_post_meta( $post_id, '_pw_travel_time_min', true );

$terms = get_the_terms( $post_id, 'pw_transport_mode' );
$modes = [];
if ( is_array( $terms ) ) {
	foreach ( $terms as $t ) {
		if ( $t instanceof WP_Term ) {
			$modes[] = $t->name;
		}
	}
}
$modes = array_values( array_unique( $modes ) );

$has_any = $distance_km > 0 || $travel_min > 0 || $modes !== [];
if ( ! $has_any ) {
	return;
}
?>
<section class="pw-nearby-key-details-strip" aria-labelledby="pw-nearby-key-details-strip-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_nearby_key_details_strip_content', $post_id ); ?>
	<h2 class="pw-nearby-key-details-strip__heading" id="pw-nearby-key-details-strip-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Key details', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-nearby-key-details-strip__list">
		<?php if ( $distance_km > 0 ) : ?>
			<li class="pw-nearby-key-details-strip__item">
				<span class="pw-nearby-key-details-strip__label"><?php echo esc_html__( 'Distance', 'portico-webworks' ); ?></span>
				<span class="pw-nearby-key-details-strip__value">
					<?php echo esc_html( sprintf( '%s %s', number_format_i18n( $distance_km ), esc_html__( 'km', 'portico-webworks' ) ) ); ?>
				</span>
			</li>
		<?php endif; ?>
		<?php if ( $travel_min > 0 ) : ?>
			<li class="pw-nearby-key-details-strip__item">
				<span class="pw-nearby-key-details-strip__label"><?php echo esc_html__( 'Travel time', 'portico-webworks' ); ?></span>
				<span class="pw-nearby-key-details-strip__value">
					<?php echo esc_html( sprintf( '%d %s', $travel_min, esc_html__( 'min', 'portico-webworks' ) ) ); ?>
				</span>
			</li>
		<?php endif; ?>
		<?php if ( $modes !== [] ) : ?>
			<li class="pw-nearby-key-details-strip__item">
				<span class="pw-nearby-key-details-strip__label"><?php echo esc_html__( 'Transport', 'portico-webworks' ); ?></span>
				<span class="pw-nearby-key-details-strip__value"><?php echo esc_html( implode( ', ', $modes ) ); ?></span>
			</li>
		<?php endif; ?>
	</ul>
	<?php do_action( 'pw_after_nearby_key_details_strip_content', $post_id ); ?>
</section>

