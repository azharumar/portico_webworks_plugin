<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$tip = (string) get_post_meta( $post_id, '_pw_concierge_tip', true );
if ( trim( $tip ) === '' ) {
	return;
}
?>
<section class="pw-nearby-concierge-tip" aria-labelledby="pw-nearby-concierge-tip-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_nearby_concierge_tip_content', $post_id ); ?>
	<h2 class="pw-nearby-concierge-tip__heading" id="pw-nearby-concierge-tip-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Concierge tip', 'portico-webworks' ); ?>
	</h2>
	<div class="pw-nearby-concierge-tip__body">
		<?php echo wp_kses_post( $tip ); ?>
	</div>
	<?php do_action( 'pw_after_nearby_concierge_tip_content', $post_id ); ?>
</section>

