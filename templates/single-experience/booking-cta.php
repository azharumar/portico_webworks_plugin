<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$booking_url = (string) get_post_meta( $post_id, '_pw_booking_url', true );
if ( $booking_url !== '' ) {
	$href = $booking_url;
} else {
	$contact = pw_experience_get_primary_contact( $post_id );
	if ( ! is_array( $contact ) || $contact === [] ) {
		return;
	}

	$email    = isset( $contact['email'] ) ? (string) $contact['email'] : '';
	$phone    = isset( $contact['phone'] ) ? (string) $contact['phone'] : '';
	$mobile   = isset( $contact['mobile'] ) ? (string) $contact['mobile'] : '';
	$whatsapp = isset( $contact['whatsapp'] ) ? (string) $contact['whatsapp'] : '';

	$href = '';
	if ( $email !== '' ) {
		$href = 'mailto:' . rawurlencode( $email );
	} elseif ( $phone !== '' ) {
		$tel  = preg_replace( '/[^0-9+]/', '', $phone );
		$href = $tel !== '' ? 'tel:' . rawurlencode( $tel ) : '';
	} elseif ( $mobile !== '' ) {
		$tel  = preg_replace( '/[^0-9+]/', '', $mobile );
		$href = $tel !== '' ? 'tel:' . rawurlencode( $tel ) : '';
	} elseif ( $whatsapp !== '' ) {
		$tel  = preg_replace( '/[^0-9+]/', '', $whatsapp );
		$href = $tel !== '' ? 'tel:' . rawurlencode( $tel ) : '';
	}
}

if ( ! is_string( $href ) || $href === '' ) {
	return;
}
?>
<section class="pw-experience-booking-cta" aria-labelledby="pw-experience-booking-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_experience_booking_cta_content', $post_id ); ?>
	<h2 class="pw-experience-booking-cta__heading" id="pw-experience-booking-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Book this experience', 'portico-webworks' ); ?>
	</h2>
	<p class="pw-experience-booking-cta__action">
		<a class="pw-experience-booking-cta__link" href="<?php echo esc_url( $href ); ?>">
			<?php echo esc_html( pw_get_cta_label( 'pw_experience', $post_id ) ); ?>
		</a>
	</p>
	<?php do_action( 'pw_after_experience_booking_cta_content', $post_id ); ?>
</section>

