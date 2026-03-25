<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$content = (string) get_post_field( 'post_content', $post_id, 'raw' );
if ( trim( $content ) === '' ) {
	return;
}
?>
<section class="pw-spa-introduction" aria-labelledby="pw-spa-introduction-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_spa_introduction_content', $post_id ); ?>

	<h2 class="pw-spa-introduction__heading" id="pw-spa-introduction-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Wellness philosophy', 'portico-webworks' ); ?>
	</h2>

	<div class="pw-spa-introduction__body">
		<?php echo apply_filters( 'the_content', $content ); ?>
	</div>

	<?php do_action( 'pw_after_spa_introduction_content', $post_id ); ?>
</section>

