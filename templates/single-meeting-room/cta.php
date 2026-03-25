<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$contact = pw_meeting_room_get_primary_contact( $post_id );
if ( ! is_array( $contact ) || $contact === [] ) {
	return;
}

$email   = isset( $contact['email'] ) ? (string) $contact['email'] : '';
$phone   = isset( $contact['phone'] ) ? (string) $contact['phone'] : '';
$mobile  = isset( $contact['mobile'] ) ? (string) $contact['mobile'] : '';
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

if ( $href === '' ) {
	return;
}
?>
<section class="pw-meeting-room-cta" aria-labelledby="pw-meeting-room-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_meeting_room_cta_content', $post_id ); ?>
	<h2 class="pw-meeting-room-cta__heading" id="pw-meeting-room-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Enquire about this venue', 'portico-webworks' ); ?>
	</h2>
	<p class="pw-meeting-room-cta__action">
		<a class="pw-meeting-room-cta__link" href="<?php echo esc_url( $href ); ?>">
			<?php echo esc_html( pw_get_cta_label( 'pw_meeting_room', $post_id ) ); ?>
		</a>
	</p>
	<?php do_action( 'pw_after_meeting_room_cta_content', $post_id ); ?>
</section>

