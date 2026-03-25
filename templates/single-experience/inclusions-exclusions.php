<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$inclusions = (string) get_post_meta( $post_id, '_pw_inclusions', true );
$exclusions = (string) get_post_meta( $post_id, '_pw_exclusions', true );

$has_any = trim( $inclusions ) !== '' || trim( $exclusions ) !== '';
if ( ! $has_any ) {
	return;
}
?>
<section class="pw-experience-inclusions-exclusions" aria-labelledby="pw-experience-inclusions-exclusions-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_experience_inclusions_exclusions_content', $post_id ); ?>
	<h2 class="pw-experience-inclusions-exclusions__heading" id="pw-experience-inclusions-exclusions-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Inclusions & exclusions', 'portico-webworks' ); ?>
	</h2>
	<div class="pw-experience-inclusions-exclusions__grid">
		<?php if ( trim( $inclusions ) !== '' ) : ?>
			<div class="pw-experience-inclusions-exclusions__block">
				<h3 class="pw-experience-inclusions-exclusions__subheading">
					<?php echo esc_html__( 'What is included', 'portico-webworks' ); ?>
				</h3>
				<div class="pw-experience-inclusions-exclusions__body">
					<?php echo wp_kses_post( $inclusions ); ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( trim( $exclusions ) !== '' ) : ?>
			<div class="pw-experience-inclusions-exclusions__block">
				<h3 class="pw-experience-inclusions-exclusions__subheading">
					<?php echo esc_html__( 'What is not included', 'portico-webworks' ); ?>
				</h3>
				<div class="pw-experience-inclusions-exclusions__body">
					<?php echo wp_kses_post( $exclusions ); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php do_action( 'pw_after_experience_inclusions_exclusions_content', $post_id ); ?>
</section>

