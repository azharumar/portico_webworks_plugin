<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$thumb_id = (int) get_post_thumbnail_id( $post_id );
if ( $thumb_id <= 0 ) {
	return;
}

$duration = (float) get_post_meta( $post_id, '_pw_duration_hours', true );

$terms = get_the_terms( $post_id, 'pw_experience_category' );
$term  = ( $terms && is_array( $terms ) ) ? ( $terms[0] ?? null ) : null;
if ( ! $term instanceof WP_Term ) {
	$term = null;
}
?>
<section class="pw-experience-hero" aria-labelledby="pw-experience-hero-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_experience_hero_content', $post_id ); ?>
	<h1 class="pw-experience-hero__heading" id="pw-experience-hero-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( get_the_title( $post_id ) ); ?>
	</h1>
	<div class="pw-experience-hero__meta">
		<?php if ( $term ) : ?>
			<span class="pw-experience-hero__category"><?php echo esc_html( $term->name ); ?></span>
		<?php endif; ?>
		<?php if ( $duration > 0 ) : ?>
			<span class="pw-experience-hero__duration">
				<?php echo esc_html( sprintf( '%s %s', number_format_i18n( $duration ), esc_html__( 'hours', 'portico-webworks' ) ) ); ?>
			</span>
		<?php endif; ?>
	</div>
	<div class="pw-experience-hero__image">
		<?php
		echo wp_get_attachment_image(
			$thumb_id,
			'large',
			false,
			[
				'class'    => 'pw-experience-hero__img',
				'loading'  => 'eager',
				'decoding' => 'async',
			]
		);
		?>
	</div>
	<?php do_action( 'pw_after_experience_hero_content', $post_id ); ?>
</section>

