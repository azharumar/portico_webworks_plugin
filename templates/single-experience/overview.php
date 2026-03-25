<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$content = get_post_field( 'post_content', $post_id, 'raw' );
if ( ! is_string( $content ) || trim( $content ) === '' ) {
	return;
}
?>
<section class="pw-experience-overview" aria-labelledby="pw-experience-overview-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_experience_overview_content', $post_id ); ?>
	<h2 class="pw-experience-overview__heading" id="pw-experience-overview-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Overview', 'portico-webworks' ); ?>
	</h2>
	<div class="pw-experience-overview__body">
		<?php echo apply_filters( 'the_content', $content ); ?>
	</div>
	<?php do_action( 'pw_after_experience_overview_content', $post_id ); ?>
</section>

