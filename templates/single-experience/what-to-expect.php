<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$what = (string) get_post_meta( $post_id, '_pw_what_to_expect', true );
if ( trim( $what ) === '' ) {
	return;
}
?>
<section class="pw-experience-what-to-expect" aria-labelledby="pw-experience-what-to-expect-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_experience_what_to_expect_content', $post_id ); ?>
	<h2 class="pw-experience-what-to-expect__heading" id="pw-experience-what-to-expect-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'What to expect', 'portico-webworks' ); ?>
	</h2>
	<div class="pw-experience-what-to-expect__body">
		<?php echo wp_kses_post( $what ); ?>
	</div>
	<?php do_action( 'pw_after_experience_what_to_expect_content', $post_id ); ?>
</section>

