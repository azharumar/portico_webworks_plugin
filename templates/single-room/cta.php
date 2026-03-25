<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$book_url = (string) get_post_meta( $post_id, '_pw_booking_url', true );
if ( $book_url === '' ) {
	return;
}
?>
<section class="pw-room-cta" aria-labelledby="pw-room-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_room_cta_content', $post_id ); ?>
	<h2 class="pw-room-cta__heading" id="pw-room-cta-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_room_cta_heading( $post_id ) ); ?>
	</h2>
	<p class="pw-room-cta__action">
		<a class="pw-room-cta__link" href="<?php echo esc_url( $book_url ); ?>">
			<?php echo esc_html( pw_get_cta_label( 'pw_room_type', $post_id ) ); ?>
		</a>
	</p>
	<?php do_action( 'pw_after_room_cta_content', $post_id ); ?>
</section>
