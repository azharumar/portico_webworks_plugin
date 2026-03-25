<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$content = get_post_field( 'post_content', $post_id, 'raw' );
if ( ! is_string( $content ) || trim( $content ) === '' ) {
	return;
}
?>
<section class="pw-room-description" aria-labelledby="pw-room-description-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_room_description_content', $post_id ); ?>
	<h2 class="pw-room-description__heading" id="pw-room-description-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_room_description_heading( $post_id ) ); ?>
	</h2>
	<div class="pw-room-description__body">
		<?php echo apply_filters( 'the_content', $content ); ?>
	</div>
	<?php do_action( 'pw_after_room_description_content', $post_id ); ?>
</section>
