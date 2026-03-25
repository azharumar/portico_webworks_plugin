<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$raw = get_post_meta( $post_id, '_pw_gallery', true );
$ids = [];

if ( is_array( $raw ) ) {
	foreach ( $raw as $key => $val ) {
		$id = is_numeric( $key ) ? (int) $key : ( ( is_numeric( $val ) ) ? (int) $val : 0 );
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}
}

$ids = array_values( array_unique( $ids ) );
$ids = array_slice( $ids, 1 ); // Use the first image as the hero, rest here.

if ( $ids === [] ) {
	return;
}
?>
<section class="pw-meeting-room-setup-gallery" aria-labelledby="pw-meeting-room-setup-gallery-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_meeting_room_setup_gallery_content', $post_id ); ?>
	<h2 class="pw-meeting-room-setup-gallery__heading" id="pw-meeting-room-setup-gallery-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Setup gallery', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-meeting-room-setup-gallery__list">
		<?php foreach ( $ids as $attachment_id ) : ?>
			<li class="pw-meeting-room-setup-gallery__item">
				<?php
				echo wp_get_attachment_image(
					$attachment_id,
					'large',
					false,
					[
						'class'    => 'pw-meeting-room-setup-gallery__image',
						'loading'  => 'lazy',
						'decoding' => 'async',
					]
				);
				?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'pw_after_meeting_room_setup_gallery_content', $post_id ); ?>
</section>

