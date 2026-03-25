<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$reservation_url = (string) get_post_meta( $post_id, '_pw_reservation_url', true );
if ( $reservation_url === '' ) {
	return;
}

?>
<section class="pw-restaurant-cta" aria-labelledby="pw-restaurant-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_restaurant_cta_content', $post_id ); ?>
	<h2 class="pw-restaurant-cta__heading" id="pw-restaurant-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_restaurant_cta_heading( $post_id ) ); ?>
	</h2>
	<p class="pw-restaurant-cta__action">
		<a class="pw-restaurant-cta__link" href="<?php echo esc_url( $reservation_url ); ?>">
			<?php echo esc_html( pw_get_cta_label( 'pw_restaurant', $post_id ) ); ?>
		</a>
	</p>
	<?php do_action( 'pw_after_restaurant_cta_content', $post_id ); ?>
</section>

