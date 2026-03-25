<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$occupancy = (int) get_post_meta( $post_id, '_pw_max_occupancy', true );
$adults    = (int) get_post_meta( $post_id, '_pw_max_adults', true );
$children  = (int) get_post_meta( $post_id, '_pw_max_children', true );
$extra     = (int) get_post_meta( $post_id, '_pw_max_extra_beds', true );
$sqm       = (int) get_post_meta( $post_id, '_pw_size_sqm', true );
$sqft      = (int) get_post_meta( $post_id, '_pw_size_sqft', true );

$has_any = $occupancy > 0 || $adults > 0 || $children > 0 || $extra > 0 || $sqm > 0 || $sqft > 0;
if ( ! $has_any ) {
	return;
}
?>
<section class="pw-room-overview" aria-labelledby="pw-room-overview-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_room_overview_content', $post_id ); ?>
	<h2 class="pw-room-overview__heading" id="pw-room-overview-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_room_overview_heading( $post_id ) ); ?>
	</h2>
	<ul class="pw-room-overview__specs">
		<?php if ( $occupancy > 0 ) : ?>
			<li class="pw-room-overview__spec">
				<span class="pw-room-overview__label"><?php echo esc_html( pw_get_room_overview_occupancy_label( $post_id ) ); ?></span>
				<span class="pw-room-overview__value"><?php echo esc_html( (string) $occupancy ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( $adults > 0 ) : ?>
			<li class="pw-room-overview__spec">
				<span class="pw-room-overview__label"><?php echo esc_html( pw_get_room_overview_adults_label( $post_id ) ); ?></span>
				<span class="pw-room-overview__value"><?php echo esc_html( (string) $adults ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( $children > 0 ) : ?>
			<li class="pw-room-overview__spec">
				<span class="pw-room-overview__label"><?php echo esc_html( pw_get_room_overview_children_label( $post_id ) ); ?></span>
				<span class="pw-room-overview__value"><?php echo esc_html( (string) $children ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( $extra > 0 ) : ?>
			<li class="pw-room-overview__spec">
				<span class="pw-room-overview__label"><?php echo esc_html( pw_get_room_overview_extra_beds_label( $post_id ) ); ?></span>
				<span class="pw-room-overview__value"><?php echo esc_html( (string) $extra ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( $sqm > 0 ) : ?>
			<li class="pw-room-overview__spec">
				<span class="pw-room-overview__label"><?php echo esc_html( pw_get_room_overview_size_label( $post_id ) ); ?></span>
				<span class="pw-room-overview__value">
					<?php
					echo esc_html( (string) $sqm . ' ' . pw_get_room_size_sqm_suffix( $post_id ) );
					?>
				</span>
			</li>
		<?php elseif ( $sqft > 0 ) : ?>
			<li class="pw-room-overview__spec">
				<span class="pw-room-overview__label"><?php echo esc_html( pw_get_room_overview_size_label( $post_id ) ); ?></span>
				<span class="pw-room-overview__value">
					<?php
					echo esc_html( (string) $sqft . ' ' . pw_get_room_size_sqft_suffix( $post_id ) );
					?>
				</span>
			</li>
		<?php endif; ?>
	</ul>
	<?php do_action( 'pw_after_room_overview_content', $post_id ); ?>
</section>
