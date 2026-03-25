<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$start_iso = (string) get_post_meta( $post_id, '_pw_start_datetime_iso8601', true );
$end_iso   = (string) get_post_meta( $post_id, '_pw_end_datetime_iso8601', true );
$dress_code = (string) get_post_meta( $post_id, '_pw_dress_code', true );
$price_from = (float) get_post_meta( $post_id, '_pw_price_from', true );
$venue_id   = (int) get_post_meta( $post_id, '_pw_venue_id', true );

$property_id = (int) get_post_meta( $post_id, '_pw_property_id', true );
$currency = $property_id > 0 ? pw_get_property_currency( $property_id ) : pw_get_property_currency();

$date_text = '';
$time_text = '';
if ( trim( $start_iso ) !== '' ) {
	$ts = strtotime( $start_iso );
	if ( is_int( $ts ) && $ts > 0 ) {
		$date_text = date_i18n( 'j F Y', $ts );
		$time_text = date_i18n( 'g:i A', $ts );
	}
}

if ( trim( $end_iso ) !== '' && trim( $start_iso ) !== '' ) {
	$ts_end = strtotime( $end_iso );
	if ( is_int( $ts_end ) && $ts_end > 0 && $ts_end > 0 && trim( $time_text ) !== '' ) {
		$end_time = date_i18n( 'g:i A', $ts_end );
		$time_text = $time_text . ' – ' . $end_time;
	}
}

$venue_title = $venue_id > 0 ? get_the_title( $venue_id ) : '';

$has_any = $date_text !== '' || $time_text !== '' || $dress_code !== '' || $price_from > 0 || $venue_title !== '';
if ( ! $has_any ) {
	return;
}
?>
<section class="pw-event-key-details-strip" aria-labelledby="pw-event-key-details-strip-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_event_key_details_strip_content', $post_id ); ?>
	<h2 class="pw-event-key-details-strip__heading" id="pw-event-key-details-strip-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Key details', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-event-key-details-strip__list">
		<?php if ( $date_text !== '' ) : ?>
			<li class="pw-event-key-details-strip__item">
				<span class="pw-event-key-details-strip__label"><?php echo esc_html__( 'Date', 'portico-webworks' ); ?></span>
				<span class="pw-event-key-details-strip__value"><?php echo esc_html( $date_text ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( $time_text !== '' ) : ?>
			<li class="pw-event-key-details-strip__item">
				<span class="pw-event-key-details-strip__label"><?php echo esc_html__( 'Time', 'portico-webworks' ); ?></span>
				<span class="pw-event-key-details-strip__value"><?php echo esc_html( $time_text ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( $venue_title !== '' ) : ?>
			<li class="pw-event-key-details-strip__item">
				<span class="pw-event-key-details-strip__label"><?php echo esc_html__( 'Venue', 'portico-webworks' ); ?></span>
				<span class="pw-event-key-details-strip__value"><?php echo esc_html( $venue_title ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( trim( $dress_code ) !== '' ) : ?>
			<li class="pw-event-key-details-strip__item">
				<span class="pw-event-key-details-strip__label"><?php echo esc_html__( 'Dress code', 'portico-webworks' ); ?></span>
				<span class="pw-event-key-details-strip__value"><?php echo esc_html( $dress_code ); ?></span>
			</li>
		<?php endif; ?>
		<?php if ( $price_from > 0 ) : ?>
			<li class="pw-event-key-details-strip__item">
				<span class="pw-event-key-details-strip__label"><?php echo esc_html__( 'Price', 'portico-webworks' ); ?></span>
				<span class="pw-event-key-details-strip__value">
					<?php echo esc_html( sprintf( '%s %s', number_format_i18n( $price_from ), $currency ) ); ?>
				</span>
			</li>
		<?php endif; ?>
	</ul>
	<?php do_action( 'pw_after_event_key_details_strip_content', $post_id ); ?>
</section>

