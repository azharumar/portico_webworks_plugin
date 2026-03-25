<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$venue_id = (int) get_post_meta( $post_id, '_pw_venue_id', true );
if ( $venue_id <= 0 ) {
	return;
}

$theatre = (int) get_post_meta( $venue_id, '_pw_capacity_theatre', true );
$classroom = (int) get_post_meta( $venue_id, '_pw_capacity_classroom', true );
$boardroom = (int) get_post_meta( $venue_id, '_pw_capacity_boardroom', true );
$ushape = (int) get_post_meta( $venue_id, '_pw_capacity_ushape', true );

$has_any = $theatre > 0 || $classroom > 0 || $boardroom > 0 || $ushape > 0;
$room_title = get_the_title( $venue_id );
$room_link = get_permalink( $venue_id );
if ( $room_title === '' && ! $has_any ) {
	return;
}
?>
<section class="pw-event-venue-details" aria-labelledby="pw-event-venue-details-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_event_venue_details_content', $post_id ); ?>
	<h2 class="pw-event-venue-details__heading" id="pw-event-venue-details-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Venue details', 'portico-webworks' ); ?>
	</h2>

	<?php if ( $room_title !== '' && $room_link !== '' ) : ?>
		<p class="pw-event-venue-details__room">
			<a class="pw-event-venue-details__room-link" href="<?php echo esc_url( $room_link ); ?>">
				<?php echo esc_html( $room_title ); ?>
			</a>
		</p>
	<?php endif; ?>

	<?php if ( $has_any ) : ?>
		<ul class="pw-event-venue-details__capacity">
			<?php if ( $theatre > 0 ) : ?>
				<li class="pw-event-venue-details__capacity-item">
					<span class="pw-event-venue-details__capacity-label"><?php echo esc_html__( 'Theatre', 'portico-webworks' ); ?></span>
					<span class="pw-event-venue-details__capacity-value"><?php echo esc_html( (string) $theatre ); ?></span>
				</li>
			<?php endif; ?>
			<?php if ( $classroom > 0 ) : ?>
				<li class="pw-event-venue-details__capacity-item">
					<span class="pw-event-venue-details__capacity-label"><?php echo esc_html__( 'Classroom', 'portico-webworks' ); ?></span>
					<span class="pw-event-venue-details__capacity-value"><?php echo esc_html( (string) $classroom ); ?></span>
				</li>
			<?php endif; ?>
			<?php if ( $boardroom > 0 ) : ?>
				<li class="pw-event-venue-details__capacity-item">
					<span class="pw-event-venue-details__capacity-label"><?php echo esc_html__( 'Boardroom', 'portico-webworks' ); ?></span>
					<span class="pw-event-venue-details__capacity-value"><?php echo esc_html( (string) $boardroom ); ?></span>
				</li>
			<?php endif; ?>
			<?php if ( $ushape > 0 ) : ?>
				<li class="pw-event-venue-details__capacity-item">
					<span class="pw-event-venue-details__capacity-label"><?php echo esc_html__( 'U-Shape', 'portico-webworks' ); ?></span>
					<span class="pw-event-venue-details__capacity-value"><?php echo esc_html( (string) $ushape ); ?></span>
				</li>
			<?php endif; ?>
		</ul>
	<?php endif; ?>

	<?php do_action( 'pw_after_event_venue_details_content', $post_id ); ?>
</section>

