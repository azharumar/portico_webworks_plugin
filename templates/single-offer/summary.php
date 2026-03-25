<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$excerpt = (string) get_post_field( 'post_excerpt', $post_id, 'raw' );
if ( trim( $excerpt ) === '' ) {
	return;
}
?>
<section class="pw-offer-summary" aria-labelledby="pw-offer-summary-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_offer_summary_content', $post_id ); ?>
	<h2 class="pw-offer-summary__heading" id="pw-offer-summary-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Offer summary', 'portico-webworks' ); ?>
	</h2>
	<div class="pw-offer-summary__body">
		<?php echo wp_kses_post( $excerpt ); ?>
	</div>
	<?php do_action( 'pw_after_offer_summary_content', $post_id ); ?>
</section>

