<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$note = (string) get_post_meta( $post_id, '_pw_catering_note', true );
if ( trim( $note ) === '' ) {
	return;
}
?>
<section class="pw-meeting-room-catering-note" aria-labelledby="pw-meeting-room-catering-note-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_meeting_room_catering_note_content', $post_id ); ?>
	<h2 class="pw-meeting-room-catering-note__heading" id="pw-meeting-room-catering-note-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Catering options', 'portico-webworks' ); ?>
	</h2>
	<div class="pw-meeting-room-catering-note__body">
		<?php echo wp_kses_post( $note ); ?>
	</div>
	<?php do_action( 'pw_after_meeting_room_catering_note_content', $post_id ); ?>
</section>

