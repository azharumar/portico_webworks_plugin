<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$faqs = pw_restaurant_get_faqs( $post_id );
if ( ! is_array( $faqs ) || $faqs === [] ) {
	return;
}

?>
<section class="pw-restaurant-faq" aria-labelledby="pw-restaurant-faq-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_restaurant_faq_content', $post_id ); ?>
	<h2 class="pw-restaurant-faq__heading" id="pw-restaurant-faq-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_restaurant_faq_heading( $post_id ) ); ?>
	</h2>
	<ul class="pw-restaurant-faq__list">
		<?php foreach ( $faqs as $faq ) : ?>
			<?php if ( ! $faq instanceof WP_Post ) : ?>
				<?php continue; ?>
			<?php endif; ?>

			<?php
			$faq_id  = (int) $faq->ID;
			$ans_raw = (string) get_post_meta( $faq_id, '_pw_answer', true );
			?>

			<li class="pw-restaurant-faq__item">
				<details class="pw-restaurant-faq__details">
					<summary class="pw-restaurant-faq__question">
						<?php echo esc_html( get_the_title( $faq_id ) ); ?>
					</summary>
					<div class="pw-restaurant-faq__answer">
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
	<?php do_action( 'pw_after_restaurant_faq_content', $post_id ); ?>
</section>

