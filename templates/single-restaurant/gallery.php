<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$raw = get_post_meta( $post_id, '_pw_gallery', true );
$ids = [];
if ( is_array( $raw ) ) {
	foreach ( $raw as $key => $val ) {
		$id = is_numeric( $key ) ? (int) $key : ( ( is_numeric( $val ) ) ? (int) $val : 0 );
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}
}
$ids = array_values( array_unique( $ids ) );
if ( $ids === [] ) {
	return;
}

?>
<section class="pw-restaurant-gallery" aria-labelledby="pw-restaurant-gallery-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_restaurant_gallery_content', $post_id ); ?>
	<h2 class="pw-restaurant-gallery__heading" id="pw-restaurant-gallery-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_restaurant_gallery_heading( $post_id ) ); ?>
	</h2>
	<ul class="pw-restaurant-gallery__list">
		<?php foreach ( $ids as $attachment_id ) : ?>
			<li class="pw-restaurant-gallery__item">
				<?php
				echo wp_get_attachment_image(
					$attachment_id,
					'large',
					false,
					[
						'class'    => 'pw-restaurant-gallery__image',
						'loading'  => 'lazy',
						'decoding' => 'async',
					]
				);
				?>
			</li>
		<?php endforeach; ?>
</ul>
	<?php do_action( 'pw_after_restaurant_gallery_content', $post_id ); ?>
</section>

