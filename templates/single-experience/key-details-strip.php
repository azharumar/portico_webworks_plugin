<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$duration = (float) get_post_meta( $post_id, '_pw_duration_hours', true );
$group_max = (int) get_post_meta( $post_id, '_pw_group_size_max', true );
$price_from = (float) get_post_meta( $post_id, '_pw_price_from', true );
$inclusions_raw = (string) get_post_meta( $post_id, '_pw_inclusions', true );

$property_id = (int) get_post_meta( $post_id, '_pw_property_id', true );
$currency = $property_id > 0 ? pw_get_property_currency( $property_id ) : pw_get_property_currency();

$inclusions_preview = '';
if ( trim( $inclusions_raw ) !== '' ) {
	$inclusions_preview = wp_trim_words( wp_strip_all_tags( $inclusions_raw ), 28 );
}

$has_any = $duration > 0 || $group_max > 0 || $price_from > 0 || $inclusions_preview !== '';
if ( ! $has_any ) {
	return;
}
?>
<section class="pw-experience-key-details-strip" aria-labelledby="pw-experience-key-details-strip-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_experience_key_details_strip_content', $post_id ); ?>
	<h2 class="pw-experience-key-details-strip__heading" id="pw-experience-key-details-strip-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Key details', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-experience-key-details-strip__list">
		<?php if ( $duration > 0 ) : ?>
			<li class="pw-experience-key-details-strip__item">
				<span class="pw-experience-key-details-strip__label"><?php echo esc_html__( 'Duration', 'portico-webworks' ); ?></span>
				<span class="pw-experience-key-details-strip__value">
					<?php echo esc_html( sprintf( '%s %s', number_format_i18n( $duration ), esc_html__( 'hours', 'portico-webworks' ) ) ); ?>
				</span>
			</li>
		<?php endif; ?>
		<?php if ( $group_max > 0 ) : ?>
			<li class="pw-experience-key-details-strip__item">
				<span class="pw-experience-key-details-strip__label"><?php echo esc_html__( 'Group size', 'portico-webworks' ); ?></span>
				<span class="pw-experience-key-details-strip__value"><?php echo esc_html( (string) $group_max ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( $price_from > 0 ) : ?>
			<li class="pw-experience-key-details-strip__item">
				<span class="pw-experience-key-details-strip__label"><?php echo esc_html__( 'Price', 'portico-webworks' ); ?></span>
				<span class="pw-experience-key-details-strip__value">
					<?php echo esc_html( sprintf( '%s %s', number_format_i18n( $price_from ), $currency ) ); ?>
				</span>
			</li>
		<?php endif; ?>
		<?php if ( $inclusions_preview !== '' ) : ?>
			<li class="pw-experience-key-details-strip__item">
				<span class="pw-experience-key-details-strip__label"><?php echo esc_html__( 'What is included', 'portico-webworks' ); ?></span>
				<span class="pw-experience-key-details-strip__value"><?php echo esc_html( $inclusions_preview ); ?></span>
			</li>
		<?php endif; ?>
	</ul>
	<?php do_action( 'pw_after_experience_key_details_strip_content', $post_id ); ?>
</section>

