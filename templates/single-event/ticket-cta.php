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
<section class="pw-event-ticket-cta" aria-labelledby="pw-event-ticket-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_event_ticket_cta_content', $post_id ); ?>
	<h2 class="pw-event-ticket-cta__heading" id="pw-event-ticket-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Tickets / RSVP', 'portico-webworks' ); ?>
	</h2>
	<p class="pw-event-ticket-cta__action">
		<a class="pw-event-ticket-cta__link" href="<?php echo esc_url( $booking_url ); ?>">
			<?php echo esc_html( pw_get_cta_label( 'pw_event', $post_id ) ); ?>
		</a>
	</p>
	<?php do_action( 'pw_after_event_ticket_cta_content', $post_id ); ?>
</section>

