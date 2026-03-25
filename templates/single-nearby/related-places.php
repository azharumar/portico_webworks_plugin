<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$related = pw_nearby_get_related( $post_id, 3 );
if ( ! is_array( $related ) || $related === [] ) {
	return;
}
?>
<section class="pw-nearby-related-places" aria-labelledby="pw-nearby-related-places-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_nearby_related_places_content', $post_id ); ?>
	<h2 class="pw-nearby-related-places__heading" id="pw-nearby-related-places-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Related places', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-nearby-related-places__list">
		<?php foreach ( $related as $p ) : ?>
			<?php if ( ! $p instanceof WP_Post ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<?php $rid = (int) $p->ID; ?>
			<li class="pw-nearby-related-places__item">
				<a class="pw-nearby-related-places__link" href="<?php echo esc_url( get_permalink( $rid ) ); ?>">
					<?php echo esc_html( get_the_title( $rid ) ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'pw_after_nearby_related_places_content', $post_id ); ?>
</section>

