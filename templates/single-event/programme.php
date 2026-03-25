<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$programme = (string) get_post_meta( $post_id, '_pw_programme', true );
if ( trim( $programme ) === '' ) {
	return;
}
?>
<section class="pw-event-programme" aria-labelledby="pw-event-programme-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_event_programme_content', $post_id ); ?>
	<h2 class="pw-event-programme__heading" id="pw-event-programme-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Programme', 'portico-webworks' ); ?>
	</h2>
	<div class="pw-event-programme__body">
		<?php echo wp_kses_post( $programme ); ?>
	</div>
	<?php do_action( 'pw_after_event_programme_content', $post_id ); ?>
</section>

