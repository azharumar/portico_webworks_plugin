<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$thumb_id = (int) get_post_thumbnail_id( $post_id );
if ( $thumb_id <= 0 ) {
	return;
}

$start_iso = (string) get_post_meta( $post_id, '_pw_start_datetime_iso8601', true );
$venue_id  = (int) get_post_meta( $post_id, '_pw_venue_id', true );
$venue_title = $venue_id > 0 ? get_the_title( $venue_id ) : '';
$venue_link  = $venue_id > 0 ? get_permalink( $venue_id ) : '';

$date_text = '';
if ( trim( $start_iso ) !== '' ) {
	$ts = strtotime( $start_iso );
	if ( is_int( $ts ) && $ts > 0 ) {
		$date_text = date_i18n( 'j F Y', $ts );
	}
}
?>
<section class="pw-event-hero" aria-labelledby="pw-event-hero-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_event_hero_content', $post_id ); ?>
	<h1 class="pw-event-hero__heading" id="pw-event-hero-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( get_the_title( $post_id ) ); ?>
	</h1>
	<div class="pw-event-hero__meta">
		<?php if ( $date_text !== '' ) : ?>
			<span class="pw-event-hero__date"><?php echo esc_html( $date_text ); ?></span>
		<?php endif; ?>
		<?php if ( $venue_id > 0 && $venue_link !== '' && $venue_title !== '' ) : ?>
			<span class="pw-event-hero__venue">
				<a class="pw-event-hero__venue-link" href="<?php echo esc_url( $venue_link ); ?>">
					<?php echo esc_html( $venue_title ); ?>
				</a>
			</span>
		<?php endif; ?>
	</div>
	<div class="pw-event-hero__image">
		<?php
		echo wp_get_attachment_image(
			$thumb_id,
			'large',
			false,
			[
				'class'    => 'pw-event-hero__img',
				'loading'  => 'eager',
				'decoding' => 'async',
			]
		);
		?>
	</div>
	<?php do_action( 'pw_after_event_hero_content', $post_id ); ?>
</section>

