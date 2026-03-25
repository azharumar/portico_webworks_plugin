<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$rate_from = (float) get_post_meta( $post_id, '_pw_rate_from', true );
$rate_to   = (float) get_post_meta( $post_id, '_pw_rate_to', true );
$book_url  = (string) get_post_meta( $post_id, '_pw_booking_url', true );
$prop_id   = (int) get_post_meta( $post_id, '_pw_property_id', true );

$show_rates = ( $rate_from > 0 || $rate_to > 0 );
$show_link  = $book_url !== '';

if ( ! $show_rates && ! $show_link ) {
	return;
}

$currency = $prop_id > 0 ? pw_get_property_currency( $prop_id ) : pw_get_property_currency();
?>
<section class="pw-room-booking-widget">
	<?php do_action( 'pw_before_room_booking_widget_content', $post_id ); ?>
	<h2 class="pw-room-booking-widget__heading">
		<?php echo esc_html( pw_get_room_booking_heading( $post_id ) ); ?>
	</h2>
	<?php if ( $show_rates ) : ?>
		<ul class="pw-room-booking-widget__rates">
			<?php if ( $rate_from > 0 ) : ?>
				<li class="pw-room-booking-widget__rate">
					<span class="pw-room-booking-widget__label"><?php echo esc_html( pw_get_room_rate_from_label( $post_id ) ); ?></span>
					<span class="pw-room-booking-widget__value">
						<?php echo esc_html( sprintf( '%s %s', number_format_i18n( $rate_from ), $currency ) ); ?>
					</span>
				</li>
			<?php endif; ?>
			<?php if ( $rate_to > 0 ) : ?>
				<li class="pw-room-booking-widget__rate">
					<span class="pw-room-booking-widget__label"><?php echo esc_html( pw_get_room_rate_to_label( $post_id ) ); ?></span>
					<span class="pw-room-booking-widget__value">
						<?php echo esc_html( sprintf( '%s %s', number_format_i18n( $rate_to ), $currency ) ); ?>
					</span>
				</li>
			<?php endif; ?>
		</ul>
	<?php endif; ?>
	<?php if ( $show_link ) : ?>
		<p class="pw-room-booking-widget__cta">
			<a class="pw-room-booking-widget__link" href="<?php echo esc_url( $book_url ); ?>">
				<?php echo esc_html( pw_get_cta_label( 'pw_room_type', $post_id ) ); ?>
			</a>
		</p>
	<?php endif; ?>
	<?php do_action( 'pw_after_room_booking_widget_content', $post_id ); ?>
</section>
