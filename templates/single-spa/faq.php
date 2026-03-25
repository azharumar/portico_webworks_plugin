<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$faqs = pw_spa_get_faqs( $post_id );
if ( ! is_array( $faqs ) || $faqs === [] ) {
	return;
}
?>
<section class="pw-spa-faq" aria-labelledby="pw-spa-faq-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_spa_faq_content', $post_id ); ?>
	<h2 class="pw-spa-faq__heading" id="pw-spa-faq-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Frequently asked questions', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-spa-faq__list">
		<?php foreach ( $faqs as $faq ) : ?>
			<?php if ( ! $faq instanceof WP_Post ) : ?>
				<?php continue; ?>
			<?php endif; ?>

			<?php
			$faq_id  = (int) $faq->ID;
			$ans_raw = (string) get_post_meta( $faq_id, '_pw_answer', true );
			?>

			<li class="pw-spa-faq__item">
				<details class="pw-spa-faq__details">
					<summary class="pw-spa-faq__question">
						<?php echo esc_html( get_the_title( $faq_id ) ); ?>
					</summary>
					<div class="pw-spa-faq__answer">
						<?php
						if ( $ans_raw !== '' ) {
							echo wp_kses_post( $ans_raw );
						}
						?>
					</div>
				</details>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'pw_after_spa_faq_content', $post_id ); ?>
</section>

