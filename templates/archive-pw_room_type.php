<?php
defined( 'ABSPATH' ) || exit;

$property_id = (int) pw_get_current_property_id();
if ( $property_id <= 0 ) {
	return;
}

$title   = (string) get_post_meta( $property_id, '_pw_rooms_section_title', true );
$intro   = (string) get_post_meta( $property_id, '_pw_rooms_section_intro', true );
$title   = trim( $title ) !== '' ? $title : (string) __( 'Rooms', 'portico-webworks' );
$intro   = trim( $intro ) !== '' ? $intro : '';
$book_url = (string) get_post_meta( $property_id, '_pw_booking_engine_url', true );

$bed_filter = isset( $_GET['pw_bed_type'] ) ? sanitize_title( (string) wp_unslash( $_GET['pw_bed_type'] ) ) : '';

$query_args = [
	'post_type'              => 'pw_room_type',
	'post_status'            => 'publish',
	'posts_per_page'         => -1,
	'orderby'                => 'title',
	'order'                  => 'ASC',
	'no_found_rows'          => true,
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
	'meta_query'             => [
		[
			'key'   => '_pw_property_id',
			'value' => $property_id,
		],
	],
];

if ( $bed_filter !== '' ) {
	$query_args['tax_query'] = [
		[
			'taxonomy' => 'pw_bed_type',
			'field'    => 'slug',
			'terms'    => [ $bed_filter ],
		],
	];
}

$rooms_query = new WP_Query( $query_args );

get_header();
?>
<main class="pw-archive-rooms">
	<?php do_action( 'pw_before_archive_pw_room_type', $property_id ); ?>
	<header class="pw-archive-rooms__header">
		<h1 class="pw-archive-rooms__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro !== '' ) : ?>
			<p class="pw-archive-rooms__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( is_string( $book_url ) && $book_url !== '' ) : ?>
		<section class="pw-archive-rooms__booking-widget">
			<h2 class="pw-archive-rooms__booking-heading">
				<?php echo esc_html( __( 'Check availability', 'portico-webworks' ) ); ?>
			</h2>
			<p class="pw-archive-rooms__booking-action">
				<a class="pw-archive-rooms__booking-link" href="<?php echo esc_url( $book_url ); ?>">
					<?php echo esc_html( pw_get_cta_label( 'pw_room_type' ) ); ?>
				</a>
			</p>
		</section>
	<?php endif; ?>

	<?php
	$rooms_posts = $rooms_query->posts;
	$count        = is_array( $rooms_posts ) ? count( $rooms_posts ) : 0;
	$bed_type_map = [];
	if ( $count >= 5 ) {
		foreach ( $rooms_posts as $p ) {
			if ( ! $p instanceof WP_Post ) {
				continue;
			}
			$terms = get_the_terms( $p->ID, 'pw_bed_type' );
			if ( ! is_array( $terms ) || $terms === [] ) {
				continue;
			}
			$t = $terms[0];
			if ( $t instanceof WP_Term ) {
				$bed_type_map[ (string) $t->slug ] = (string) $t->name;
			}
		}
	}
	$bed_type_keys = array_keys( $bed_type_map );
	sort( $bed_type_keys );
	?>

	<?php if ( $count >= 5 && $bed_type_keys !== [] ) : ?>
		<nav class="pw-archive-rooms__filters" aria-label="<?php echo esc_attr__( 'Room filters', 'portico-webworks' ); ?>">
			<ul class="pw-archive-rooms__filter-list">
				<li class="pw-archive-rooms__filter-item">
					<a class="pw-archive-rooms__filter-link" href="<?php echo esc_url( remove_query_arg( 'pw_bed_type' ) ); ?>">
						<?php echo esc_html__( 'All bed types', 'portico-webworks' ); ?>
					</a>
				</li>
				<?php foreach ( $bed_type_keys as $slug ) : ?>
					<?php $label = $bed_type_map[ $slug ] ?? ''; ?>
					<?php if ( $label === '' ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<?php $href = add_query_arg( 'pw_bed_type', $slug ); ?>
					<li class="pw-archive-rooms__filter-item">
						<a class="pw-archive-rooms__filter-link<?php echo $bed_filter === $slug ? ' pw-archive-rooms__filter-link--active' : ''; ?>" href="<?php echo esc_url( $href ); ?>">
							<?php echo esc_html( $label ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<section class="pw-archive-rooms__grid" aria-label="<?php echo esc_attr__( 'Room types', 'portico-webworks' ); ?>">
		<?php if ( $rooms_query->have_posts() ) : ?>
			<?php foreach ( $rooms_query->posts as $room ) : ?>
				<?php if ( ! $room instanceof WP_Post ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php
				$rid         = (int) $room->ID;
				$thumb_id   = (int) get_post_thumbnail_id( $rid );
				$occ         = (int) get_post_meta( $rid, '_pw_max_occupancy', true );
				$size_sqm    = (int) get_post_meta( $rid, '_pw_size_sqm', true );
				$rate_from   = (float) get_post_meta( $rid, '_pw_rate_from', true );
				$booking_url = (string) get_post_meta( $rid, '_pw_booking_url', true );
				$cook_terms  = get_the_terms( $rid, 'pw_bed_type' );
				$bed_label   = ( is_array( $cook_terms ) && ! empty( $cook_terms ) && $cook_terms[0] instanceof WP_Term ) ? (string) $cook_terms[0]->name : '';
				?>
				<article class="pw-room-card">
					<?php if ( $thumb_id > 0 ) : ?>
						<a class="pw-room-card__image-link" href="<?php echo esc_url( $booking_url !== '' ? $booking_url : get_permalink( $rid ) ); ?>">
							<?php
							echo wp_get_attachment_image(
								$thumb_id,
								'large',
								false,
								[
									'class'   => 'pw-room-card__image',
									'loading' => 'lazy',
								]
							);
							?>
						</a>
					<?php endif; ?>
					<h2 class="pw-room-card__title">
						<a class="pw-room-card__title-link" href="<?php echo esc_url( $booking_url !== '' ? $booking_url : get_permalink( $rid ) ); ?>">
							<?php echo esc_html( get_the_title( $rid ) ); ?>
						</a>
					</h2>
					<ul class="pw-room-card__meta">
						<?php if ( $occ > 0 ) : ?>
							<li class="pw-room-card__meta-item">
								<span class="pw-room-card__meta-label"><?php echo esc_html__( 'Occupancy', 'portico-webworks' ); ?>:</span>
								<span class="pw-room-card__meta-value"><?php echo esc_html( (string) $occ ); ?></span>
							</li>
						<?php endif; ?>
						<?php if ( $bed_label !== '' ) : ?>
							<li class="pw-room-card__meta-item">
								<span class="pw-room-card__meta-label"><?php echo esc_html__( 'Bed type', 'portico-webworks' ); ?>:</span>
								<span class="pw-room-card__meta-value"><?php echo esc_html( $bed_label ); ?></span>
							</li>
						<?php endif; ?>
						<?php if ( $size_sqm > 0 ) : ?>
							<li class="pw-room-card__meta-item">
								<span class="pw-room-card__meta-label"><?php echo esc_html__( 'Size', 'portico-webworks' ); ?>:</span>
								<span class="pw-room-card__meta-value">
									<?php echo esc_html( (string) $size_sqm . ' ' . pw_get_room_size_sqm_suffix( $rid ) ); ?>
								</span>
							</li>
						<?php endif; ?>
						<?php if ( $rate_from > 0 ) : ?>
							<li class="pw-room-card__meta-item">
								<span class="pw-room-card__meta-label"><?php echo esc_html__( 'From', 'portico-webworks' ); ?>:</span>
								<span class="pw-room-card__meta-value">
									<?php
									$currency = pw_get_property_currency( $property_id );
									echo esc_html( sprintf( '%s %s', number_format_i18n( $rate_from ), $currency ) );
									?>
								</span>
							</li>
						<?php endif; ?>
					</ul>

					<?php if ( $booking_url !== '' ) : ?>
						<p class="pw-room-card__cta">
							<a class="pw-room-card__cta-link" href="<?php echo esc_url( $booking_url ); ?>">
								<?php echo esc_html( pw_get_cta_label( 'pw_room_type', $rid ) ); ?>
							</a>
						</p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		<?php else : ?>
			<p class="pw-archive-rooms__empty"><?php echo esc_html__( 'No rooms found for this property.', 'portico-webworks' ); ?></p>
		<?php endif; ?>
	</section>

	<?php
	$offers = pw_archive_get_room_offers( $property_id, 3 );
	?>
	<?php if ( is_array( $offers ) && $offers !== [] ) : ?>
		<section class="pw-archive-rooms__offers-callout" aria-label="<?php echo esc_attr__( 'Offers', 'portico-webworks' ); ?>">
			<h2 class="pw-archive-rooms__offers-title"><?php echo esc_html__( 'Special offers', 'portico-webworks' ); ?></h2>
			<ul class="pw-archive-rooms__offers-list">
				<?php foreach ( $offers as $offer ) : ?>
					<?php if ( ! $offer instanceof WP_Post ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<?php
					$oid          = (int) $offer->ID;
					$thumb_id     = (int) get_post_thumbnail_id( $oid );
					$booking_url  = (string) get_post_meta( $oid, '_pw_booking_url', true );
					$discount_type = (string) get_post_meta( $oid, '_pw_discount_type', true );
					$discount_value = (float) get_post_meta( $oid, '_pw_discount_value', true );
					$valid_from    = (string) get_post_meta( $oid, '_pw_valid_from', true );
					$valid_to      = (string) get_post_meta( $oid, '_pw_valid_to', true );
					?>
					<li class="pw-archive-rooms__offers-item">
						<article class="pw-offer-card">
							<?php if ( $thumb_id > 0 ) : ?>
								<?php echo wp_get_attachment_image( $thumb_id, 'large', false, [ 'class' => 'pw-offer-card__image', 'loading' => 'lazy' ] ); ?>
							<?php endif; ?>
							<h3 class="pw-offer-card__title">
								<?php echo esc_html( get_the_title( $oid ) ); ?>
							</h3>
							<?php if ( $discount_type !== '' && $discount_value > 0 ) : ?>
								<p class="pw-offer-card__benefit">
									<?php
									$benefit = '';
									if ( $discount_type === 'percentage' ) {
										$benefit = (string) $discount_value . '% off';
									} elseif ( $discount_type === 'flat' ) {
										$cur = pw_get_property_currency( $property_id );
										$benefit = number_format_i18n( $discount_value ) . ' ' . $cur . ' off';
									} elseif ( $discount_type === 'value_add' ) {
										$benefit = (string) __( 'Value added benefit', 'portico-webworks' );
									}
									?>
									<?php if ( $benefit !== '' ) : ?>
										<?php echo esc_html( $benefit ); ?>
									<?php endif; ?>
								</p>
							<?php endif; ?>
							<?php if ( trim( $valid_from . $valid_to ) !== '' ) : ?>
								<p class="pw-offer-card__validity">
									<?php
									$valid_text = '';
									if ( trim( $valid_from ) !== '' && trim( $valid_to ) !== '' ) {
										$valid_text = $valid_from . ' – ' . $valid_to;
									} elseif ( trim( $valid_from ) !== '' ) {
										$valid_text = $valid_from;
									} elseif ( trim( $valid_to ) !== '' ) {
										$valid_text = $valid_to;
									}
									?>
									<?php if ( $valid_text !== '' ) : ?>
										<?php echo esc_html( $valid_text ); ?>
									<?php endif; ?>
								</p>
							<?php endif; ?>
							<?php if ( $booking_url !== '' ) : ?>
								<p class="pw-offer-card__cta">
									<a class="pw-offer-card__cta-link" href="<?php echo esc_url( $booking_url ); ?>">
										<?php echo esc_html( pw_get_cta_label( 'pw_offer', $oid ) ); ?>
									</a>
								</p>
							<?php endif; ?>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php if ( is_string( $book_url ) && $book_url !== '' ) : ?>
		<section class="pw-archive-rooms__bottom-cta">
			<p class="pw-archive-rooms__bottom-action">
				<a class="pw-archive-rooms__bottom-link" href="<?php echo esc_url( $book_url ); ?>">
					<?php echo esc_html( pw_get_cta_label( 'pw_room_type' ) ); ?>
				</a>
			</p>
		</section>
	<?php endif; ?>

	<?php do_action( 'pw_after_archive_pw_room_type', $property_id ); ?>
</main>
<?php
get_footer();
?>

