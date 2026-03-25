<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$sqm = (int) get_post_meta( $post_id, '_pw_area_sqm', true );

$natural_raw = get_post_meta( $post_id, '_pw_natural_light', true );
$natural     = $natural_raw === '1' || $natural_raw === 1 || $natural_raw === true || $natural_raw === 'true' || $natural_raw === 'on';

$terms = get_the_terms( $post_id, 'pw_av_equipment' );
$av     = [];
if ( is_array( $terms ) ) {
	foreach ( $terms as $t ) {
		if ( $t instanceof WP_Term ) {
			$av[] = $t->name;
		}
	}
}
$av = array_values( array_unique( $av ) );

$has_any = $sqm > 0 || $natural || $av !== [];
if ( ! $has_any ) {
	return;
}
?>
<section class="pw-meeting-room-specs" aria-labelledby="pw-meeting-room-specs-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_meeting_room_room_specs_content', $post_id ); ?>
	<h2 class="pw-meeting-room-specs__heading" id="pw-meeting-room-specs-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Room specs', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-meeting-room-specs__list">
		<?php if ( $sqm > 0 ) : ?>
			<li class="pw-meeting-room-specs__item">
				<span class="pw-meeting-room-specs__label"><?php echo esc_html__( 'Floor area', 'portico-webworks' ); ?></span>
				<span class="pw-meeting-room-specs__value">
					<?php echo esc_html( (string) $sqm . ' ' . esc_html__( 'm²', 'portico-webworks' ) ); ?>
				</span>
			</li>
		<?php endif; ?>
		<?php if ( $natural ) : ?>
			<li class="pw-meeting-room-specs__item">
				<span class="pw-meeting-room-specs__label"><?php echo esc_html__( 'Natural light', 'portico-webworks' ); ?></span>
				<span class="pw-meeting-room-specs__value"><?php echo esc_html__( 'Yes', 'portico-webworks' ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( $av !== [] ) : ?>
			<li class="pw-meeting-room-specs__item">
				<span class="pw-meeting-room-specs__label"><?php echo esc_html__( 'AV equipment', 'portico-webworks' ); ?></span>
				<span class="pw-meeting-room-specs__value">
					<?php echo esc_html( implode( ', ', $av ) ); ?>
				</span>
			</li>
		<?php endif; ?>
	</ul>
	<?php do_action( 'pw_after_meeting_room_room_specs_content', $post_id ); ?>
</section>

