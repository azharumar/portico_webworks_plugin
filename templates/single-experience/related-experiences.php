<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$related = pw_experience_get_related( $post_id, 3 );
if ( ! is_array( $related ) || $related === [] ) {
	return;
}
?>
<section class="pw-experience-related-experiences" aria-labelledby="pw-experience-related-experiences-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_experience_related_experiences_content', $post_id ); ?>
	<h2 class="pw-experience-related-experiences__heading" id="pw-experience-related-experiences-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'More experiences', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-experience-related-experiences__list">
		<?php foreach ( $related as $p ) : ?>
			<?php if ( ! $p instanceof WP_Post ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<?php
			$rid = (int) $p->ID;
			$duration = (float) get_post_meta( $rid, '_pw_duration_hours', true );
			$price_from = (float) get_post_meta( $rid, '_pw_price_from', true );
			$property_id = (int) get_post_meta( $rid, '_pw_property_id', true );
			$currency = $property_id > 0 ? pw_get_property_currency( $property_id ) : pw_get_property_currency();
			?>
			<li class="pw-experience-related-experiences__item">
				<a class="pw-experience-related-experiences__link" href="<?php echo esc_url( get_permalink( $rid ) ); ?>">
					<span class="pw-experience-related-experiences__title"><?php echo esc_html( get_the_title( $rid ) ); ?></span>
					<?php if ( $duration > 0 ) : ?>
						<span class="pw-experience-related-experiences__meta">
							<?php echo esc_html( sprintf( '%s %s', number_format_i18n( $duration ), esc_html__( 'hours', 'portico-webworks' ) ) ); ?>
						</span>
					<?php endif; ?>
					<?php if ( $price_from > 0 ) : ?>
						<span class="pw-experience-related-experiences__price">
							<?php echo esc_html( sprintf( '%s %s', number_format_i18n( $price_from ), $currency ) ); ?>
						</span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'pw_after_experience_related_experiences_content', $post_id ); ?>
</section>

