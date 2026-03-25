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

$offer_type = (string) get_post_meta( $post_id, '_pw_offer_type', true );
if ( trim( $offer_type ) === '' ) {
	$offer_type = '';
}
?>
<section class="pw-offer-hero" aria-labelledby="pw-offer-hero-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_offer_hero_content', $post_id ); ?>
	<h1 class="pw-offer-hero__heading" id="pw-offer-hero-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( get_the_title( $post_id ) ); ?>
	</h1>
	<div class="pw-offer-hero__meta">
		<?php if ( $offer_type !== '' ) : ?>
			<span class="pw-offer-hero__type"><?php echo esc_html( $offer_type ); ?></span>
		<?php endif; ?>
	</div>
	<div class="pw-offer-hero__image">
		<?php
		echo wp_get_attachment_image(
			$thumb_id,
			'large',
			false,
			[
				'class'    => 'pw-offer-hero__img',
				'loading'  => 'eager',
				'decoding' => 'async',
			]
		);
		?>
	</div>
	<?php do_action( 'pw_after_offer_hero_content', $post_id ); ?>
</section>

