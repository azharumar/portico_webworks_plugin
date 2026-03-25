<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$title_id = 'pw-restaurant-hero-title-' . $post_id;
?>
<section class="pw-restaurant-hero" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<?php do_action( 'pw_before_restaurant_hero_content', $post_id ); ?>
	<h1 class="pw-restaurant-hero__title" id="<?php echo esc_attr( $title_id ); ?>">
		<?php echo esc_html( pw_get_restaurant_hero_title( $post_id ) ); ?>
	</h1>
	<?php
	$excerpt = (string) get_post_field( 'post_excerpt', $post_id );
	if ( $excerpt !== '' ) :
		?>
		<p class="pw-restaurant-hero__excerpt"><?php echo esc_html( $excerpt ); ?></p>
	<?php endif; ?>
	<?php if ( has_post_thumbnail( $post_id ) ) : ?>
		<div class="pw-restaurant-hero__media">
			<?php
			echo get_the_post_thumbnail(
				$post_id,
				'large',
				[
					'class'    => 'pw-restaurant-hero__image',
					'loading'  => 'eager',
					'decoding' => 'async',
				]
			);
			?>
		</div>
	<?php endif; ?>
	<?php do_action( 'pw_after_restaurant_hero_content', $post_id ); ?>
</section>

