<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$booking_url = (string) get_post_meta( $post_id, '_pw_booking_url', true );
if ( $booking_url === '' ) {
	return;
}
?>
<section class="pw-offer-cta" aria-labelledby="pw-offer-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_offer_cta_content', $post_id ); ?>
	<h2 class="pw-offer-cta__heading" id="pw-offer-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_cta_label( 'pw_offer', $post_id ) ); ?>
	</h2>
	<p class="pw-offer-cta__action">
		<a class="pw-offer-cta__link" href="<?php echo esc_url( $booking_url ); ?>">
			<?php echo esc_html( pw_get_cta_label( 'pw_offer', $post_id ) ); ?>
		</a>
	</p>
	<?php do_action( 'pw_after_offer_cta_content', $post_id ); ?>
</section>

