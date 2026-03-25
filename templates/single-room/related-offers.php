<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$offers = pw_room_get_related_offers( $post_id );
if ( $offers === [] ) {
	return;
}
?>
<section class="pw-room-related-offers" aria-labelledby="pw-room-related-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_room_related_offers_content', $post_id ); ?>
	<h2 class="pw-room-related-offers__heading" id="pw-room-related-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_room_related_offers_heading( $post_id ) ); ?>
	</h2>
	<ul class="pw-room-related-offers__list">
		<?php foreach ( $offers as $offer ) : ?>
			<?php if ( ! $offer instanceof WP_Post ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<li class="pw-room-related-offers__item">
				<a class="pw-room-related-offers__link" href="<?php echo esc_url( get_permalink( $offer ) ); ?>">
					<?php echo esc_html( get_the_title( $offer ) ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'pw_after_room_related_offers_content', $post_id ); ?>
</section>
