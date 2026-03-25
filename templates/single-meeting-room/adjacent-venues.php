<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$prefunction_sqm = (int) get_post_meta( $post_id, '_pw_prefunction_area_sqm', true );
$adjacent        = pw_meeting_room_get_adjacent_venues( $post_id, 3 );
$has_any         = $prefunction_sqm > 0 || ( is_array( $adjacent ) && $adjacent !== [] );
if ( ! $has_any ) {
	return;
}
?>
<section class="pw-meeting-room-adjacent-venues" aria-labelledby="pw-meeting-room-adjacent-venues-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_meeting_room_adjacent_venues_content', $post_id ); ?>
	<h2 class="pw-meeting-room-adjacent-venues__heading" id="pw-meeting-room-adjacent-venues-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Adjacent venues', 'portico-webworks' ); ?>
	</h2>
	<div class="pw-meeting-room-adjacent-venues__body">
		<?php if ( $prefunction_sqm > 0 ) : ?>
			<p class="pw-meeting-room-adjacent-venues__prefunction">
				<?php echo esc_html__( 'Pre-function area', 'portico-webworks' ); ?>:
				<?php echo esc_html( (string) $prefunction_sqm . ' ' . esc_html__( 'm²', 'portico-webworks' ) ); ?>
			</p>
		<?php endif; ?>

		<?php if ( is_array( $adjacent ) && $adjacent !== [] ) : ?>
			<ul class="pw-meeting-room-adjacent-venues__list">
				<?php foreach ( $adjacent as $venue ) : ?>
					<?php
					if ( ! $venue instanceof WP_Post ) {
						continue;
					}
					$venue_id = (int) $venue->ID;
					$theatre  = (int) get_post_meta( $venue_id, '_pw_capacity_theatre', true );
					$link     = get_permalink( $venue_id );
					?>
					<li class="pw-meeting-room-adjacent-venues__item">
						<a class="pw-meeting-room-adjacent-venues__link" href="<?php echo esc_url( $link ); ?>">
							<span class="pw-meeting-room-adjacent-venues__name"><?php echo esc_html( get_the_title( $venue_id ) ); ?></span>
							<?php if ( $theatre > 0 ) : ?>
								<span class="pw-meeting-room-adjacent-venues__capacity"><?php echo esc_html( (string) $theatre ); ?></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php do_action( 'pw_after_meeting_room_adjacent_venues_content', $post_id ); ?>
</section>

