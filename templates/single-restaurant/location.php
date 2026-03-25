<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$location   = (string) get_post_meta( $post_id, '_pw_location', true );
$cuisine    = (string) get_post_meta( $post_id, '_pw_cuisine_type', true );
$capacity   = (int) get_post_meta( $post_id, '_pw_seating_capacity', true );
$has_any    = $location !== '' || $cuisine !== '' || $capacity > 0;
if ( ! $has_any ) {
	return;
}

?>
<section class="pw-restaurant-location" aria-labelledby="pw-restaurant-location-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_restaurant_location_content', $post_id ); ?>
	<h2 class="pw-restaurant-location__heading" id="pw-restaurant-location-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_restaurant_location_heading( $post_id ) ); ?>
	</h2>
	<ul class="pw-restaurant-location__list">
		<?php if ( $location !== '' ) : ?>
			<li class="pw-restaurant-location__item">
				<span class="pw-restaurant-location__value"><?php echo esc_html( $location ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( $cuisine !== '' ) : ?>
			<li class="pw-restaurant-location__item">
				<span class="pw-restaurant-location__label"><?php echo esc_html( pw_get_restaurant_cuisine_label( $post_id ) ); ?>:</span>
				<span class="pw-restaurant-location__value"><?php echo esc_html( $cuisine ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( $capacity > 0 ) : ?>
			<li class="pw-restaurant-location__item">
				<span class="pw-restaurant-location__label"><?php echo esc_html( pw_get_restaurant_capacity_label( $post_id ) ); ?>:</span>
				<span class="pw-restaurant-location__value"><?php echo esc_html( (string) $capacity ); ?></span>
			</li>
		<?php endif; ?>
</ul>
	<?php do_action( 'pw_after_restaurant_location_content', $post_id ); ?>
</section>

