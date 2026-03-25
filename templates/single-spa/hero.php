<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$title_id = 'pw-spa-hero-title-' . $post_id;
?>
<section class="pw-spa-hero" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<?php do_action( 'pw_before_spa_hero_content', $post_id ); ?>

	<h1 class="pw-spa-hero__title" id="<?php echo esc_attr( $title_id ); ?>">
		<?php echo esc_html( get_the_title( $post_id ) ); ?>
	</h1>

	<?php
	$subtitle = (string) get_post_field( 'post_excerpt', $post_id );
	if ( $subtitle !== '' ) :
		?>
		<p class="pw-spa-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
	<?php endif; ?>

	<?php if ( has_post_thumbnail( $post_id ) ) : ?>
		<div class="pw-spa-hero__media">
			<?php
			echo get_the_post_thumbnail(
				$post_id,
				'large',
				[
					'class'    => 'pw-spa-hero__image',
					'loading'  => 'eager',
					'decoding' => 'async',
				]
			);
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'pw_after_spa_hero_content', $post_id ); ?>
</section>

