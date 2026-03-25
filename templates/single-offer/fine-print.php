<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$terms = (string) get_post_meta( $post_id, '_pw_terms_and_conditions', true );
if ( trim( $terms ) === '' ) {
	return;
}
?>
<section class="pw-offer-fine-print" aria-labelledby="pw-offer-fine-print-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_offer_fine_print_content', $post_id ); ?>
	<h2 class="pw-offer-fine-print__heading" id="pw-offer-fine-print-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Fine print', 'portico-webworks' ); ?>
	</h2>
	<details class="pw-offer-fine-print__details">
		<summary class="pw-offer-fine-print__summary">
			<?php echo esc_html__( 'Terms & conditions', 'portico-webworks' ); ?>
		</summary>
		<div class="pw-offer-fine-print__body">
			<?php echo wp_kses_post( $terms ); ?>
		</div>
	</details>
	<?php do_action( 'pw_after_offer_fine_print_content', $post_id ); ?>
</section>

