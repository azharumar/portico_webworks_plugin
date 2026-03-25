<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pw_property_post_id = get_the_ID();
	$property_id          = (int) $pw_property_post_id;
	if ( $property_id <= 0 ) {
		continue;
	}

	do_action( 'pw_before_single_property', $property_id );

	$gallery_raw = get_post_meta( $property_id, '_pw_gallery', true );
	$gallery_ids = [];
	if ( is_array( $gallery_raw ) ) {
		foreach ( $gallery_raw as $key => $val ) {
			$id = is_numeric( $key ) ? (int) $key : ( ( is_numeric( $val ) ) ? (int) $val : 0 );
			if ( $id > 0 ) {
				$gallery_ids[] = $id;
			}
		}
	}
	$gallery_ids = array_values( array_unique( array_filter( $gallery_ids ) ) );
	$hero_id      = $gallery_ids[0] ?? 0;

	$booking_engine_url = (string) get_post_meta( $property_id, '_pw_booking_engine_url', true );
	$intro              = (string) get_post_field( 'post_excerpt', $property_id, 'raw' );
	$highlights_raw     = get_post_meta( $property_id, '_pw_highlights', true );
	$highlights         = is_array( $highlights_raw ) ? $highlights_raw : [];
	$currency           = pw_get_property_currency( $property_id );

	do_action( 'pw_before_property_hero', $property_id );
	?>
	<section class="pw-property-hero">
		<?php if ( $hero_id > 0 ) : ?>
			<div class="pw-property-hero__image">
				<?php
				echo wp_get_attachment_image(
					$hero_id,
					'large',
					false,
					[
						'class'    => 'pw-property-hero__img',
						'loading'  => 'eager',
						'decoding' => 'async',
					]
				);
				?>
			</div>
		<?php endif; ?>
		<div class="pw-property-hero__content">
			<h1 class="pw-property-hero__title"><?php echo esc_html( get_the_title( $property_id ) ); ?></h1>
			<?php if ( trim( $intro ) !== '' ) : ?>
				<p class="pw-property-hero__positioning"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	do_action( 'pw_after_property_hero', $property_id );

	do_action( 'pw_before_property_booking_widget', $property_id );
	?>
	<section class="pw-property-booking-widget">
		<h2 class="pw-property-booking-widget__heading"><?php echo esc_html__( 'Check availability', 'portico-webworks' ); ?></h2>
		<?php if ( $booking_engine_url !== '' ) : ?>
			<p class="pw-property-booking-widget__action">
				<a class="pw-property-booking-widget__link" href="<?php echo esc_url( $booking_engine_url ); ?>">
					<?php echo esc_html( pw_get_cta_label( 'pw_room_type' ) ); ?>
				</a>
			</p>
		<?php endif; ?>
	</section>
	<?php
	do_action( 'pw_after_property_booking_widget', $property_id );

	do_action( 'pw_before_property_introduction', $property_id );
	?>
	<section class="pw-property-introduction" aria-label="<?php echo esc_attr__( 'Property introduction', 'portico-webworks' ); ?>">
		<h2 class="pw-property-introduction__heading"><?php echo esc_html__( 'Why stay with us', 'portico-webworks' ); ?></h2>
		<?php if ( trim( $intro ) !== '' ) : ?>
			<div class="pw-property-introduction__body">
				<?php echo esc_html( wp_trim_words( wp_strip_all_tags( $intro ), 60 ) ); ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
	do_action( 'pw_after_property_introduction', $property_id );

	do_action( 'pw_before_property_highlights', $property_id );
	if ( $highlights !== [] ) :
		?>
		<section class="pw-property-highlights" aria-label="<?php echo esc_attr__( 'Highlights', 'portico-webworks' ); ?>">
			<h2 class="pw-property-highlights__heading"><?php echo esc_html__( 'Highlights', 'portico-webworks' ); ?></h2>
			<ul class="pw-property-highlights__list">
				<?php foreach ( array_slice( $highlights, 0, 4 ) as $row ) : ?>
					<?php
					if ( ! is_array( $row ) ) {
						continue;
					}
					$ht = isset( $row['title'] ) ? (string) $row['title'] : '';
					$hd = isset( $row['description'] ) ? (string) $row['description'] : '';
					if ( trim( $ht . $hd ) === '' ) {
						continue;
					}
					?>
					<li class="pw-property-highlights__item">
						<?php if ( $ht !== '' ) : ?>
							<h3 class="pw-property-highlights__title"><?php echo esc_html( $ht ); ?></h3>
						<?php endif; ?>
						<?php if ( trim( $hd ) !== '' ) : ?>
							<p class="pw-property-highlights__desc"><?php echo esc_html( $hd ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
	endif;
	do_action( 'pw_after_property_highlights', $property_id );

	do_action( 'pw_before_property_rooms_preview', $property_id );
	$rooms_preview = pw_property_get_room_preview( $property_id, 4 );
	$rooms_all_url = pw_get_section_listing_url( $property_id, 'pw_room_type' );
	if ( $rooms_preview !== [] ) :
		?>
		<section class="pw-property-rooms-preview" aria-label="<?php echo esc_attr__( 'Rooms preview', 'portico-webworks' ); ?>">
			<h2 class="pw-property-rooms-preview__heading"><?php echo esc_html__( 'Rooms', 'portico-webworks' ); ?></h2>
			<ul class="pw-property-rooms-preview__list">
				<?php foreach ( $rooms_preview as $r ) : ?>
					<?php
					if ( ! $r instanceof WP_Post ) {
						continue;
					}
					$rid = (int) $r->ID;
					$thumb_id = (int) get_post_thumbnail_id( $rid );
					$occ = (int) get_post_meta( $rid, '_pw_max_occupancy', true );
					$size_sqm = (int) get_post_meta( $rid, '_pw_size_sqm', true );
					$rate_from = (float) get_post_meta( $rid, '_pw_rate_from', true );
					$booking_url = (string) get_post_meta( $rid, '_pw_booking_url', true );
					$bed_terms = get_the_terms( $rid, 'pw_bed_type' );
					$bed_label = ( is_array( $bed_terms ) && $bed_terms !== [] && $bed_terms[0] instanceof WP_Term ) ? (string) $bed_terms[0]->name : '';
					?>
					<li class="pw-property-rooms-preview__item">
						<article class="pw-property-room-mini">
							<?php if ( $thumb_id > 0 ) : ?>
								<?php if ( $booking_url !== '' ) : ?>
									<a class="pw-property-room-mini__image-link" href="<?php echo esc_url( $booking_url ); ?>">
										<?php echo wp_get_attachment_image( $thumb_id, 'large', false, [ 'class' => 'pw-property-room-mini__image', 'loading' => 'lazy' ] ); ?>
									</a>
								<?php endif; ?>
							<?php endif; ?>
							<h3 class="pw-property-room-mini__title">
								<?php if ( $booking_url !== '' ) : ?>
									<a href="<?php echo esc_url( $booking_url ); ?>"><?php echo esc_html( get_the_title( $rid ) ); ?></a>
								<?php else : ?>
									<?php echo esc_html( get_the_title( $rid ) ); ?>
								<?php endif; ?>
							</h3>
							<ul class="pw-property-room-mini__meta">
								<?php if ( $occ > 0 ) : ?>
									<li><?php echo esc_html( sprintf( '%d %s', $occ, esc_html__( 'guests', 'portico-webworks' ) ) ); ?></li>
								<?php endif; ?>
								<?php if ( $bed_label !== '' ) : ?>
									<li><?php echo esc_html( $bed_label ); ?></li>
								<?php endif; ?>
								<?php if ( $size_sqm > 0 ) : ?>
									<li><?php echo esc_html( (string) $size_sqm . ' ' . pw_get_room_size_sqm_suffix( $rid ) ); ?></li>
								<?php endif; ?>
								<?php if ( $rate_from > 0 ) : ?>
									<li><?php echo esc_html( sprintf( '%s %s', number_format_i18n( $rate_from ), $currency ) ); ?></li>
								<?php endif; ?>
							</ul>
							<?php if ( $booking_url !== '' ) : ?>
								<p class="pw-property-room-mini__cta">
									<a class="pw-property-room-mini__cta-link" href="<?php echo esc_url( $booking_url ); ?>">
										<?php echo esc_html( pw_get_cta_label( 'pw_room_type', $rid ) ); ?>
									</a>
								</p>
							<?php endif; ?>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $rooms_all_url !== '' ) : ?>
				<p class="pw-property-rooms-preview__view-all">
					<a class="pw-property-rooms-preview__view-all-link" href="<?php echo esc_url( $rooms_all_url ); ?>">
						<?php echo esc_html__( 'View all rooms', 'portico-webworks' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</section>
	<?php
	endif;
	do_action( 'pw_after_property_rooms_preview', $property_id );

	do_action( 'pw_before_property_dining_preview', $property_id );
	$restaurants_preview = pw_property_get_restaurant_preview( $property_id, 3 );
	$dining_url = pw_get_section_listing_url( $property_id, 'pw_restaurant' );
	if ( $restaurants_preview !== [] ) :
		?>
		<section class="pw-property-dining-preview" aria-label="<?php echo esc_attr__( 'Dining preview', 'portico-webworks' ); ?>">
			<h2 class="pw-property-dining-preview__heading"><?php echo esc_html__( 'Dining', 'portico-webworks' ); ?></h2>
			<ul class="pw-property-dining-preview__list">
				<?php foreach ( $restaurants_preview as $r ) : ?>
					<?php
					if ( ! $r instanceof WP_Post ) {
						continue;
					}
					$rid = (int) $r->ID;
					$thumb_id = (int) get_post_thumbnail_id( $rid );
					$reservation_url = (string) get_post_meta( $rid, '_pw_reservation_url', true );
					$cuisine = (string) get_post_meta( $rid, '_pw_cuisine_type', true );
					?>
					<li class="pw-property-dining-preview__item">
						<article class="pw-property-dining-mini">
							<?php if ( $thumb_id > 0 ) : ?>
								<a class="pw-property-dining-mini__image-link" href="<?php echo esc_url( $reservation_url !== '' ? $reservation_url : get_permalink( $rid ) ); ?>">
									<?php echo wp_get_attachment_image( $thumb_id, 'large', false, [ 'class' => 'pw-property-dining-mini__image', 'loading' => 'lazy' ] ); ?>
								</a>
							<?php endif; ?>
							<h3 class="pw-property-dining-mini__title">
								<a class="pw-property-dining-mini__title-link" href="<?php echo esc_url( $reservation_url !== '' ? $reservation_url : get_permalink( $rid ) ); ?>">
									<?php echo esc_html( get_the_title( $rid ) ); ?>
								</a>
							</h3>
							<?php if ( $cuisine !== '' ) : ?>
								<p class="pw-property-dining-mini__cuisine"><?php echo esc_html( $cuisine ); ?></p>
							<?php endif; ?>
							<?php if ( $reservation_url !== '' ) : ?>
								<p class="pw-property-dining-mini__cta">
									<a class="pw-property-dining-mini__cta-link" href="<?php echo esc_url( $reservation_url ); ?>">
										<?php echo esc_html( pw_get_cta_label( 'pw_restaurant', $rid ) ); ?>
									</a>
								</p>
							<?php endif; ?>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $dining_url !== '' ) : ?>
				<p class="pw-property-dining-preview__view-all">
					<a class="pw-property-dining-preview__view-all-link" href="<?php echo esc_url( $dining_url ); ?>">
						<?php echo esc_html__( 'View all dining', 'portico-webworks' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</section>
	<?php
	endif;
	do_action( 'pw_after_property_dining_preview', $property_id );

	do_action( 'pw_before_property_experiences_preview', $property_id );
	$amenities_preview = pw_property_get_experience_preview( $property_id );
	if ( $amenities_preview !== [] ) :
		?>
		<section class="pw-property-experiences-preview" aria-label="<?php echo esc_attr__( 'Experiences & amenities', 'portico-webworks' ); ?>">
			<h2 class="pw-property-experiences-preview__heading"><?php echo esc_html__( 'Experiences & amenities', 'portico-webworks' ); ?></h2>
			<ul class="pw-property-experiences-preview__list">
				<?php foreach ( $amenities_preview as $row ) : ?>
					<?php
					if ( ! is_array( $row ) || ! isset( $row['type'], $row['post'] ) || ! ( $row['post'] instanceof WP_Post ) ) {
						continue;
					}
					$tp = (string) $row['type'];
					$p  = $row['post'];
					$id = (int) $p->ID;
					$thumb_id = (int) get_post_thumbnail_id( $id );
					$cta_url = '';
					if ( in_array( $tp, [ 'pw_spa', 'pw_experience', 'pw_event', 'pw_meeting_room', 'pw_offer' ], true ) ) {
						$cta_url = (string) get_post_meta( $id, '_pw_booking_url', true );
					}
					?>
					<li class="pw-property-experiences-preview__item">
						<article class="pw-property-amenity-mini">
							<?php if ( $thumb_id > 0 ) : ?>
								<a class="pw-property-amenity-mini__image-link" href="<?php echo esc_url( $cta_url !== '' ? $cta_url : get_permalink( $id ) ); ?>">
									<?php echo wp_get_attachment_image( $thumb_id, 'large', false, [ 'class' => 'pw-property-amenity-mini__image', 'loading' => 'lazy' ] ); ?>
								</a>
							<?php endif; ?>
							<h3 class="pw-property-amenity-mini__title">
								<a class="pw-property-amenity-mini__title-link" href="<?php echo esc_url( $cta_url !== '' ? $cta_url : get_permalink( $id ) ); ?>">
									<?php echo esc_html( get_the_title( $id ) ); ?>
								</a>
							</h3>
							<p class="pw-property-amenity-mini__meta">
								<?php echo esc_html( pw_get_cta_label( $tp, $id ) ); ?>
							</p>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php
	endif;
	do_action( 'pw_after_property_experiences_preview', $property_id );

	do_action( 'pw_before_property_offers_strip', $property_id );
	$offers = pw_property_get_active_offers( $property_id, 3 );
	if ( $offers !== [] ) :
		?>
		<section class="pw-property-offers-strip" aria-label="<?php echo esc_attr__( 'Offers', 'portico-webworks' ); ?>">
			<h2 class="pw-property-offers-strip__heading"><?php echo esc_html__( 'Special offers', 'portico-webworks' ); ?></h2>
			<ul class="pw-property-offers-strip__list">
				<?php foreach ( $offers as $offer ) : ?>
					<?php
					if ( ! $offer instanceof WP_Post ) {
						continue;
					}
					$oid = (int) $offer->ID;
					$thumb_id = (int) get_post_thumbnail_id( $oid );
					$booking_url = (string) get_post_meta( $oid, '_pw_booking_url', true );
					$excerpt = (string) get_post_field( 'post_excerpt', $oid, 'raw' );
					$vf = (string) get_post_meta( $oid, '_pw_valid_from', true );
					$vt = (string) get_post_meta( $oid, '_pw_valid_to', true );
					?>
					<li class="pw-property-offers-strip__item">
						<article class="pw-property-offer-mini">
							<?php if ( $thumb_id > 0 ) : ?>
								<?php if ( $booking_url !== '' ) : ?>
									<a class="pw-property-offer-mini__image-link" href="<?php echo esc_url( $booking_url ); ?>">
										<?php echo wp_get_attachment_image( $thumb_id, 'large', false, [ 'class' => 'pw-property-offer-mini__image', 'loading' => 'lazy' ] ); ?>
									</a>
								<?php endif; ?>
							<?php endif; ?>
							<h3 class="pw-property-offer-mini__title">
								<?php if ( $booking_url !== '' ) : ?>
									<a href="<?php echo esc_url( $booking_url ); ?>"><?php echo esc_html( get_the_title( $oid ) ); ?></a>
								<?php else : ?>
									<?php echo esc_html( get_the_title( $oid ) ); ?>
								<?php endif; ?>
							</h3>
							<?php if ( trim( $excerpt ) !== '' ) : ?>
								<p class="pw-property-offer-mini__benefit"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 28 ) ); ?></p>
							<?php endif; ?>
							<?php if ( trim( $vf . $vt ) !== '' ) : ?>
								<p class="pw-property-offer-mini__validity">
									<?php echo esc_html( trim( $vf . ( $vt !== '' ? ' – ' . $vt : '' ) ) ); ?>
								</p>
							<?php endif; ?>
							<?php if ( $booking_url !== '' ) : ?>
								<p class="pw-property-offer-mini__cta">
									<a class="pw-property-offer-mini__cta-link" href="<?php echo esc_url( $booking_url ); ?>">
										<?php echo esc_html( pw_get_cta_label( 'pw_offer', $oid ) ); ?>
									</a>
								</p>
							<?php endif; ?>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php
	endif;
	do_action( 'pw_after_property_offers_strip', $property_id );

	$review_embed_code = (string) get_post_meta( $property_id, '_pw_review_embed_code', true );

	do_action( 'pw_before_property_reviews_social', $property_id );
	?>
	<?php if ( trim( $review_embed_code ) !== '' ) : ?>
		<section class="pw-property-reviews" aria-label="<?php echo esc_attr__( 'Reviews', 'portico-webworks' ); ?>">
			<h2 class="pw-property-reviews__heading"><?php echo esc_html__( 'Reviews', 'portico-webworks' ); ?></h2>
			<div class="pw-property-reviews__embed">
				<?php echo wp_kses_post( $review_embed_code ); ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
	do_action( 'pw_after_property_reviews_social', $property_id );

	do_action( 'pw_before_property_location', $property_id );
	$plats_lat = get_post_meta( $property_id, '_pw_lat', true );
	$plats_lng = get_post_meta( $property_id, '_pw_lng', true );
	$plats_lat_f = is_numeric( $plats_lat ) ? (float) $plats_lat : 0.0;
	$plats_lng_f = is_numeric( $plats_lng ) ? (float) $plats_lng : 0.0;
	?>
	<section class="pw-property-location" aria-label="<?php echo esc_attr__( 'Location', 'portico-webworks' ); ?>">
		<h2 class="pw-property-location__heading"><?php echo esc_html__( 'Location', 'portico-webworks' ); ?></h2>
		<?php if ( $plats_lat_f !== 0.0 || $plats_lng_f !== 0.0 ) : ?>
			<iframe
				class="pw-property-location__map"
				title="<?php echo esc_attr( __( 'Property map', 'portico-webworks' ) ); ?>"
				loading="lazy"
				decoding="async"
				src="<?php echo esc_url( 'https://www.google.com/maps?q=' . rawurlencode( (string) $plats_lat_f . ',' . (string) $plats_lng_f ) . '&z=13&output=embed' ); ?>"
				referrerpolicy="no-referrer-when-downgrade"
			></iframe>
		<?php endif; ?>
	</section>
	<?php
	do_action( 'pw_after_property_location', $property_id );

	do_action( 'pw_before_property_bottom_booking_cta', $property_id );
	?>
	<section class="pw-property-bottom-booking-cta">
		<h2 class="pw-property-bottom-booking-cta__heading"><?php echo esc_html__( 'Book direct', 'portico-webworks' ); ?></h2>
		<?php if ( $booking_engine_url !== '' ) : ?>
			<p class="pw-property-bottom-booking-cta__action">
				<a class="pw-property-bottom-booking-cta__link" href="<?php echo esc_url( $booking_engine_url ); ?>">
					<?php echo esc_html( pw_get_cta_label( 'pw_room_type' ) ); ?>
				</a>
			</p>
		<?php endif; ?>
	</section>
	<?php
	do_action( 'pw_after_property_bottom_booking_cta', $property_id );

	do_action( 'pw_after_single_property', $property_id );
endwhile;

get_footer();

