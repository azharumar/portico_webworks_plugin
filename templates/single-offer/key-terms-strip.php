<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$valid_from = (string) get_post_meta( $post_id, '_pw_valid_from', true );
$valid_to   = (string) get_post_meta( $post_id, '_pw_valid_to', true );
$min_stay    = (int) get_post_meta( $post_id, '_pw_minimum_stay_nights', true );
$offer_type  = (string) get_post_meta( $post_id, '_pw_offer_type', true );
$advance_days = (int) get_post_meta( $post_id, '_pw_advance_days', true );

$room_ids = get_post_meta( $post_id, '_pw_room_types', true );
if ( ! is_array( $room_ids ) ) {
	$room_ids = [];
}
$room_ids = array_values( array_unique( array_map( 'intval', $room_ids ) ) );
$room_ids = array_values( array_filter( $room_ids ) );

$room_names = [];
foreach ( $room_ids as $rid ) {
	$name = get_the_title( $rid );
	if ( $name !== '' ) {
		$room_names[] = $name;
	}
}
$room_names_preview = $room_names !== [] ? implode( ', ', $room_names ) : '';

$has_any = trim( $valid_from ) !== '' || trim( $valid_to ) !== '' || $min_stay > 0 || $advance_days > 0 || $room_names_preview !== '';
if ( ! $has_any ) {
	return;
}
?>
<section class="pw-offer-key-terms-strip" aria-labelledby="pw-offer-key-terms-strip-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_offer_key_terms_strip_content', $post_id ); ?>
	<h2 class="pw-offer-key-terms-strip__heading" id="pw-offer-key-terms-strip-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Key terms', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-offer-key-terms-strip__list">
		<?php if ( trim( $valid_from ) !== '' || trim( $valid_to ) !== '' ) : ?>
			<li class="pw-offer-key-terms-strip__item">
				<span class="pw-offer-key-terms-strip__label"><?php echo esc_html__( 'Validity', 'portico-webworks' ); ?></span>
				<span class="pw-offer-key-terms-strip__value">
					<?php echo esc_html( trim( $valid_from . ( $valid_to !== '' ? ' – ' . $valid_to : '' ) ) ); ?>
					<?php if ( $valid_from === '' && $valid_to === '' ) : ?>
						<?php echo esc_html__( '—', 'portico-webworks' ); ?>
					<?php endif; ?>
				</span>
			</li>
		<?php endif; ?>

		<?php if ( $min_stay > 0 ) : ?>
			<li class="pw-offer-key-terms-strip__item">
				<span class="pw-offer-key-terms-strip__label"><?php echo esc_html__( 'Minimum stay', 'portico-webworks' ); ?></span>
				<span class="pw-offer-key-terms-strip__value">
					<?php echo esc_html( sprintf( '%d %s', $min_stay, esc_html__( 'nights', 'portico-webworks' ) ) ); ?>
				</span>
			</li>
		<?php endif; ?>

		<?php if ( $advance_days > 0 ) : ?>
			<li class="pw-offer-key-terms-strip__item">
				<span class="pw-offer-key-terms-strip__label"><?php echo esc_html__( 'Advance purchase', 'portico-webworks' ); ?></span>
				<span class="pw-offer-key-terms-strip__value">
					<?php echo esc_html( sprintf( '%d %s', $advance_days, esc_html__( 'days', 'portico-webworks' ) ) ); ?>
				</span>
			</li>
		<?php elseif ( $offer_type === 'advance' ) : ?>
			<li class="pw-offer-key-terms-strip__item">
				<span class="pw-offer-key-terms-strip__label"><?php echo esc_html__( 'Advance purchase', 'portico-webworks' ); ?></span>
				<span class="pw-offer-key-terms-strip__value"><?php echo esc_html__( 'Required', 'portico-webworks' ); ?></span>
			</li>
		<?php endif; ?>

		<?php if ( $room_names_preview !== '' ) : ?>
			<li class="pw-offer-key-terms-strip__item">
				<span class="pw-offer-key-terms-strip__label"><?php echo esc_html__( 'Applicable room types', 'portico-webworks' ); ?></span>
				<span class="pw-offer-key-terms-strip__value"><?php echo esc_html( $room_names_preview ); ?></span>
			</li>
		<?php endif; ?>
	</ul>
	<?php do_action( 'pw_after_offer_key_terms_strip_content', $post_id ); ?>
</section>

