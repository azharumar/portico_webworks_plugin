<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$excerpt = (string) get_post_field( 'post_excerpt', $post_id );
$content = (string) get_post_field( 'post_content', $post_id, 'raw' );
$body    = $excerpt !== '' ? $excerpt : $content;

if ( trim( $body ) === '' ) {
	return;
}

?>
<section class="pw-restaurant-introduction" aria-labelledby="pw-restaurant-introduction-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_restaurant_introduction_content', $post_id ); ?>
	<h2 class="pw-restaurant-introduction__heading" id="pw-restaurant-introduction-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_restaurant_introduction_heading( $post_id ) ); ?>
	</h2>
	<div class="pw-restaurant-introduction__body">
		<?php if ( $excerpt !== '' ) : ?>
			<p><?php echo esc_html( $excerpt ); ?></p>
		<?php else : ?>
			<?php echo apply_filters( 'the_content', $content ); ?>
		<?php endif; ?>
</div>
	<?php do_action( 'pw_after_restaurant_introduction_content', $post_id ); ?>
</section>

