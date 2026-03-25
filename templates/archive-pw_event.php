<?php
defined( 'ABSPATH' ) || exit;

$property_id = (int) pw_get_current_property_id();
if ( $property_id <= 0 ) {
	return;
}

$title = (string) __( 'Events', 'portico-webworks' );
$intro = (string) get_post_meta( $property_id, '_pw_events_section_intro', true );
$intro = trim( $intro ) !== '' ? $intro : '';

$upcoming = pw_archive_get_upcoming_events( $property_id, 8 );
$past     = pw_archive_get_past_events( $property_id, 3 );

$contact = pw_meeting_room_get_archive_primary_contact( $property_id );
$email   = is_array( $contact ) && isset( $contact['email'] ) ? (string) $contact['email'] : '';
$phone   = is_array( $contact ) && isset( $contact['phone'] ) ? (string) $contact['phone'] : '';
$mobile  = is_array( $contact ) && isset( $contact['mobile'] ) ? (string) $contact['mobile'] : '';
$whatsapp = is_array( $contact ) && isset( $contact['whatsapp'] ) ? (string) $contact['whatsapp'] : '';

$contact_href = '';
if ( $email !== '' ) {
	$contact_href = 'mailto:' . rawurlencode( $email );
} elseif ( $phone !== '' ) {
	$tel  = preg_replace( '/[^0-9+]/', '', $phone );
	$contact_href = $tel !== '' ? 'tel:' . rawurlencode( $tel ) : '';
} elseif ( $mobile !== '' ) {
	$tel  = preg_replace( '/[^0-9+]/', '', $mobile );
	$contact_href = $tel !== '' ? 'tel:' . rawurlencode( $tel ) : '';
} elseif ( $whatsapp !== '' ) {
	$tel  = preg_replace( '/[^0-9+]/', '', $whatsapp );
	$contact_href = $tel !== '' ? 'tel:' . rawurlencode( $tel ) : '';
}

$cur = pw_get_property_currency( $property_id );

get_header();
?>
<main class="pw-archive-events">
	<?php do_action( 'pw_before_archive_pw_event', $property_id ); ?>
	<header class="pw-archive-events__header">
		<h1 class="pw-archive-events__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro !== '' ) : ?>
			<p class="pw-archive-events__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
	</header>

	<section class="pw-archive-events__upcoming" aria-label="<?php echo esc_attr__( 'Upcoming events', 'portico-webworks' ); ?>">
		<h2 class="pw-archive-events__section-title"><?php echo esc_html__( 'Upcoming', 'portico-webworks' ); ?></h2>
		<?php if ( is_array( $upcoming ) && $upcoming !== [] ) : ?>
			<ul class="pw-events-list">
				<?php foreach ( $upcoming as $e ) : ?>
					<?php if ( ! $e instanceof WP_Post ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<?php
					$eid = (int) $e->ID;
					$thumb_id = (int) get_post_thumbnail_id( $eid );
					$start_iso = (string) get_post_meta( $eid, '_pw_start_datetime_iso8601', true );
					$ts = strtotime( $start_iso );
					$date_text = '';
					$time_text = '';
					if ( is_int( $ts ) && $ts > 0 ) {
						$date_text = date_i18n( 'j F Y', $ts );
						$time_text = date_i18n( 'g:i A', $ts );
					}
					$venue_id = (int) get_post_meta( $eid, '_pw_venue_id', true );
					$venue_title = $venue_id > 0 ? get_the_title( $venue_id ) : '';
					$venue_link = $venue_id > 0 ? get_permalink( $venue_id ) : '';
					$price_from = (float) get_post_meta( $eid, '_pw_price_from', true );
					$book_url = (string) get_post_meta( $eid, '_pw_booking_url', true );
					?>
					<li class="pw-event-card">
						<article class="pw-event-card__inner">
							<?php if ( $thumb_id > 0 ) : ?>
								<?php echo wp_get_attachment_image( $thumb_id, 'large', false, [ 'class' => 'pw-event-card__image', 'loading' => 'lazy' ] ); ?>
							<?php endif; ?>
							<h2 class="pw-event-card__title"><?php echo esc_html( get_the_title( $eid ) ); ?></h2>
							<ul class="pw-event-card__meta">
								<?php if ( $date_text !== '' ) : ?>
									<li class="pw-event-card__meta-item">
										<span class="pw-event-card__meta-label"><?php echo esc_html__( 'Date', 'portico-webworks' ); ?>:</span>
										<span class="pw-event-card__meta-value"><?php echo esc_html( $date_text ); ?></span>
									</li>
								<?php endif; ?>
								<?php if ( $time_text !== '' ) : ?>
									<li class="pw-event-card__meta-item">
										<span class="pw-event-card__meta-label"><?php echo esc_html__( 'Time', 'portico-webworks' ); ?>:</span>
										<span class="pw-event-card__meta-value"><?php echo esc_html( $time_text ); ?></span>
									</li>
								<?php endif; ?>
								<?php if ( $venue_title !== '' ) : ?>
									<li class="pw-event-card__meta-item">
										<span class="pw-event-card__meta-label"><?php echo esc_html__( 'Venue', 'portico-webworks' ); ?>:</span>
										<span class="pw-event-card__meta-value">
											<?php if ( $venue_link !== '' ) : ?>
												<a href="<?php echo esc_url( $venue_link ); ?>"><?php echo esc_html( $venue_title ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $venue_title ); ?>
											<?php endif; ?>
										</span>
									</li>
								<?php endif; ?>
								<?php if ( $price_from > 0 ) : ?>
									<li class="pw-event-card__meta-item">
										<span class="pw-event-card__meta-label"><?php echo esc_html__( 'Price', 'portico-webworks' ); ?>:</span>
										<span class="pw-event-card__meta-value"><?php echo esc_html( sprintf( '%s %s', number_format_i18n( $price_from ), $cur ) ); ?></span>
									</li>
								<?php endif; ?>
							</ul>
							<?php if ( $book_url !== '' ) : ?>
								<p class="pw-event-card__cta">
									<a class="pw-event-card__cta-link" href="<?php echo esc_url( $book_url ); ?>">
										<?php echo esc_html( pw_get_cta_label( 'pw_event', $eid ) ); ?>
									</a>
								</p>
							<?php endif; ?>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="pw-archive-events__empty"><?php echo esc_html__( 'No upcoming events found.', 'portico-webworks' ); ?></p>
		<?php endif; ?>
	</section>

	<?php if ( is_array( $past ) && $past !== [] ) : ?>
		<section class="pw-archive-events__past" aria-label="<?php echo esc_attr__( 'Past events', 'portico-webworks' ); ?>">
			<details>
				<summary><?php echo esc_html__( 'Past events', 'portico-webworks' ); ?></summary>
				<ul class="pw-events-list">
					<?php foreach ( $past as $e ) : ?>
						<?php if ( ! $e instanceof WP_Post ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<?php
						$eid = (int) $e->ID;
						$start_iso = (string) get_post_meta( $eid, '_pw_start_datetime_iso8601', true );
						$ts = strtotime( $start_iso );
						$date_text = '';
						if ( is_int( $ts ) && $ts > 0 ) {
							$date_text = date_i18n( 'j F Y', $ts );
						}
						$venue_id = (int) get_post_meta( $eid, '_pw_venue_id', true );
						$venue_title = $venue_id > 0 ? get_the_title( $venue_id ) : '';
						$book_url = (string) get_post_meta( $eid, '_pw_booking_url', true );
						?>
						<li class="pw-event-card">
							<article class="pw-event-card__inner">
								<h2 class="pw-event-card__title"><?php echo esc_html( get_the_title( $eid ) ); ?></h2>
								<ul class="pw-event-card__meta">
									<?php if ( $date_text !== '' ) : ?>
										<li class="pw-event-card__meta-item">
											<span class="pw-event-card__meta-label"><?php echo esc_html__( 'Date', 'portico-webworks' ); ?>:</span>
											<span class="pw-event-card__meta-value"><?php echo esc_html( $date_text ); ?></span>
										</li>
									<?php endif; ?>
									<?php if ( $venue_title !== '' ) : ?>
										<li class="pw-event-card__meta-item">
											<span class="pw-event-card__meta-label"><?php echo esc_html__( 'Venue', 'portico-webworks' ); ?>:</span>
											<span class="pw-event-card__meta-value"><?php echo esc_html( $venue_title ); ?></span>
										</li>
									<?php endif; ?>
								</ul>
								<?php if ( $book_url !== '' ) : ?>
									<p class="pw-event-card__cta">
										<a class="pw-event-card__cta-link" href="<?php echo esc_url( $book_url ); ?>">
											<?php echo esc_html( pw_get_cta_label( 'pw_event', $eid ) ); ?>
										</a>
									</p>
								<?php endif; ?>
							</article>
						</li>
					<?php endforeach; ?>
				</ul>
			</details>
		</section>
	<?php endif; ?>

	<?php if ( $contact_href !== '' ) : ?>
		<section class="pw-archive-events__host-cta" aria-label="<?php echo esc_attr__( 'Host an event', 'portico-webworks' ); ?>">
			<h2 class="pw-archive-events__host-heading"><?php echo esc_html__( 'Host an event', 'portico-webworks' ); ?></h2>
			<p class="pw-archive-events__host-action">
				<a class="pw-archive-events__host-link" href="<?php echo esc_url( $contact_href ); ?>">
					<?php echo esc_html__( 'Private event enquiries', 'portico-webworks' ); ?>
				</a>
			</p>
		</section>

		<section class="pw-archive-events__bottom-cta">
			<p class="pw-archive-events__bottom-action">
				<a class="pw-archive-events__bottom-link" href="<?php echo esc_url( $contact_href ); ?>">
					<?php echo esc_html( pw_get_cta_label( 'pw_event' ) ); ?>
				</a>
			</p>
		</section>
	<?php endif; ?>

	<?php do_action( 'pw_after_archive_pw_event', $property_id ); ?>
</main>
<?php
get_footer();
?>

