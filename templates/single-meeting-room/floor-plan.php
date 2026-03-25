<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$attachment_id = (int) get_post_meta( $post_id, '_pw_floor_plan', true );
if ( $attachment_id <= 0 ) {
	return;
}

$url = (string) wp_get_attachment_url( $attachment_id );
if ( $url === '' ) {
	return;
}
?>
<section class="pw-meeting-room-floor-plan" aria-labelledby="pw-meeting-room-floor-plan-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_meeting_room_floor_plan_content', $post_id ); ?>
	<h2 class="pw-meeting-room-floor-plan__heading" id="pw-meeting-room-floor-plan-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Floor plan', 'portico-webworks' ); ?>
	</h2>
	<div class="pw-meeting-room-floor-plan__body">
		<?php
		echo wp_get_attachment_image(
			$attachment_id,
			'large',
			false,
			[
				'class'    => 'pw-meeting-room-floor-plan__image',
				'loading'  => 'lazy',
				'decoding' => 'async',
			]
		);
		?>
		<p class="pw-meeting-room-floor-plan__download">
			<a class="pw-meeting-room-floor-plan__download-link" href="<?php echo esc_url( $url ); ?>" download>
				<?php echo esc_html__( 'Download floor plan', 'portico-webworks' ); ?>
			</a>
		</p>
	</div>
	<?php do_action( 'pw_after_meeting_room_floor_plan_content', $post_id ); ?>
</section>

