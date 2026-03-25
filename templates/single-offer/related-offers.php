<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$related = pw_offer_get_related( $post_id, 3 );
if ( ! is_array( $related ) || $related === [] ) {
	return;
}
?>
<section class="pw-offer-related-offers" aria-labelledby="pw-offer-related-offers-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_offer_related_offers_content', $post_id ); ?>
	<h2 class="pw-offer-related-offers__heading" id="pw-offer-related-offers-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Related offers', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-offer-related-offers__list">
		<?php foreach ( $related as $p ) : ?>
			<?php if ( ! $p instanceof WP_Post ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<?php $rid = (int) $p->ID; ?>
			<li class="pw-offer-related-offers__item">
				<a class="pw-offer-related-offers__link" href="<?php echo esc_url( get_permalink( $rid ) ); ?>">
					<span class="pw-offer-related-offers__title"><?php echo esc_html( get_the_title( $rid ) ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'pw_after_offer_related_offers_content', $post_id ); ?>
</section>

