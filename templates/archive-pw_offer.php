<?php
defined( 'ABSPATH' ) || exit;

$property_id = (int) pw_get_current_property_id();
if ( $property_id <= 0 ) {
	return;
}

$intro = (string) get_post_meta( $property_id, '_pw_offers_section_intro', true );
$intro = trim( $intro ) !== '' ? $intro : '';

$book_url = (string) get_post_meta( $property_id, '_pw_booking_engine_url', true );

$direct_benefits_raw = get_post_meta( $property_id, '_pw_direct_benefits', true );
$direct_benefits = is_array( $direct_benefits_raw ) ? $direct_benefits_raw : [];

$now_ts = current_time( 'timestamp' );

$all_offers = get_posts(
	[
		'post_type'              => 'pw_offer',
		'post_status'            => 'publish',
		'posts_per_page'         => 50,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'meta_query'             => [
			[
				'key'   => '_pw_property_id',
				'value' => $property_id,
			],
		],
		'orderby'                => 'title',
		'order'                  => 'ASC',
	]
);

$offers = [];
foreach ( $all_offers as $p ) {
	if ( ! $p instanceof WP_Post ) {
		continue;
	}
	$vf_raw = (string) get_post_meta( $p->ID, '_pw_valid_from', true );
	$vt_raw = (string) get_post_meta( $p->ID, '_pw_valid_to', true );

	$vf_ok = true;
	if ( trim( $vf_raw ) !== '' ) {
		$vf_ts = is_numeric( $vf_raw ) ? (int) $vf_raw : strtotime( $vf_raw );
		$vf_ok = is_int( $vf_ts ) && $vf_ts > 0 ? $now_ts >= $vf_ts : true;
	}
	$vt_ok = true;
	if ( trim( $vt_raw ) !== '' ) {
		$vt_ts = is_numeric( $vt_raw ) ? (int) $vt_raw : strtotime( $vt_raw );
		$vt_ok = is_int( $vt_ts ) && $vt_ts > 0 ? $now_ts <= $vt_ts : true;
	}

	if ( $vf_ok && $vt_ok ) {
		$offers[] = $p;
	}
}

usort(
	$offers,
	static function ( $a, $b ) {
		$ad = (int) get_post_meta( $a->ID, '_pw_display_order', true );
		$bd = (int) get_post_meta( $b->ID, '_pw_display_order', true );
		if ( $ad === $bd ) {
			return strcmp( $a->post_title, $b->post_title );
		}
		return $ad <=> $bd;
	}
);

get_header();
?>
<main class="pw-archive-offers">
	<?php do_action( 'pw_before_archive_pw_offer', $property_id ); ?>
	<header class="pw-archive-offers__header">
		<h1 class="pw-archive-offers__title"><?php echo esc_html__( 'Special offers', 'portico-webworks' ); ?></h1>
		<?php if ( $intro !== '' ) : ?>
			<p class="pw-archive-offers__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( $direct_benefits !== [] ) : ?>
		<section class="pw-archive-offers__direct-benefits" aria-label="<?php echo esc_attr__( 'Direct booking benefits', 'portico-webworks' ); ?>">
			<h2 class="pw-archive-offers__direct-title"><?php echo esc_html__( 'Book direct for added value', 'portico-webworks' ); ?></h2>
			<ul class="pw-archive-offers__direct-list">
				<?php foreach ( $direct_benefits as $row ) : ?>
					<?php
					if ( ! is_array( $row ) ) {
						continue;
					}
					$bt = isset( $row['title'] ) ? (string) $row['title'] : '';
					$bd = isset( $row['description'] ) ? (string) $row['description'] : '';
					if ( trim( $bt . $bd ) === '' ) {
						continue;
					}
					?>
					<li class="pw-archive-offers__direct-item">
						<?php if ( $bt !== '' ) : ?>
							<h3 class="pw-archive-offers__direct-item-title"><?php echo esc_html( $bt ); ?></h3>
						<?php endif; ?>
						<?php if ( trim( $bd ) !== '' ) : ?>
							<p class="pw-archive-offers__direct-item-desc"><?php echo esc_html( $bd ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<section class="pw-archive-offers__grid" aria-label="<?php echo esc_attr__( 'Offers', 'portico-webworks' ); ?>">
		<?php if ( $offers !== [] ) : ?>
			<ul class="pw-offers-list">
				<?php foreach ( $offers as $p ) : ?>
					<?php
					if ( ! $p instanceof WP_Post ) {
						continue;
					}
					$oid = (int) $p->ID;
					$thumb_id = (int) get_post_thumbnail_id( $oid );
					$excerpt = (string) get_post_field( 'post_excerpt', $oid, 'raw' );
					$terms = (string) get_post_field( 'post_content', $oid, 'raw' );
					$vf = (string) get_post_meta( $oid, '_pw_valid_from', true );
					$vt = (string) get_post_meta( $oid, '_pw_valid_to', true );
					$discount_type = (string) get_post_meta( $oid, '_pw_discount_type', true );
					$discount_value = (float) get_post_meta( $oid, '_pw_discount_value', true );
					$booking_url = (string) get_post_meta( $oid, '_pw_booking_url', true );
					$terms_summary = '';
					if ( trim( $terms ) !== '' ) {
						$terms_summary = wp_trim_words( wp_strip_all_tags( $terms ), 30 );
					}
					?>
					<li class="pw-offer-card">
						<article class="pw-offer-card__inner">
							<?php if ( $thumb_id > 0 ) : ?>
								<a class="pw-offer-card__image-link" href="<?php echo esc_url( $booking_url !== '' ? $booking_url : get_permalink( $oid ) ); ?>">
									<?php echo wp_get_attachment_image( $thumb_id, 'large', false, [ 'class' => 'pw-offer-card__image', 'loading' => 'lazy' ] ); ?>
								</a>
							<?php endif; ?>
							<h2 class="pw-offer-card__title">
								<a class="pw-offer-card__title-link" href="<?php echo esc_url( $booking_url !== '' ? $booking_url : get_permalink( $oid ) ); ?>">
									<?php echo esc_html( get_the_title( $oid ) ); ?>
								</a>
							</h2>
							<?php if ( trim( $excerpt ) !== '' ) : ?>
								<p class="pw-offer-card__benefit"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 30 ) ); ?></p>
							<?php else : ?>
								<?php if ( $discount_type !== '' && $discount_value > 0 ) : ?>
									<p class="pw-offer-card__benefit">
										<?php
										$benefit = '';
										if ( $discount_type === 'percentage' ) {
											$benefit = (string) $discount_value . '% off';
										} elseif ( $discount_type === 'flat' ) {
											$benefit = number_format_i18n( $discount_value ) . ' ' . pw_get_property_currency( $property_id ) . ' off';
										} elseif ( $discount_type === 'value_add' ) {
											$benefit = (string) __( 'Value added benefit', 'portico-webworks' );
										}
										echo esc_html( $benefit );
										?>
									</p>
								<?php endif; ?>
							<?php endif; ?>
							<?php if ( trim( $vf . $vt ) !== '' ) : ?>
								<p class="pw-offer-card__validity">
									<?php
									$valid_text = '';
									if ( trim( $vf ) !== '' && trim( $vt ) !== '' ) {
										$valid_text = $vf . ' – ' . $vt;
									} elseif ( trim( $vf ) !== '' ) {
										$valid_text = $vf;
									} elseif ( trim( $vt ) !== '' ) {
										$valid_text = $vt;
									}
									?>
									<?php if ( $valid_text !== '' ) : ?>
										<?php echo esc_html( $valid_text ); ?>
									<?php endif; ?>
								</p>
							<?php endif; ?>
							<?php if ( $terms_summary !== '' ) : ?>
								<p class="pw-offer-card__terms-summary"><?php echo esc_html( $terms_summary ); ?></p>
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
		<?php else : ?>
			<p class="pw-archive-offers__empty"><?php echo esc_html__( 'No offers found for this property.', 'portico-webworks' ); ?></p>
		<?php endif; ?>
	</section>

	<?php if ( is_string( $book_url ) && $book_url !== '' ) : ?>
		<section class="pw-archive-offers__bottom-booking">
			<p class="pw-archive-offers__bottom-action">
				<a class="pw-archive-offers__bottom-link" href="<?php echo esc_url( $book_url ); ?>">
					<?php echo esc_html__( 'Check availability', 'portico-webworks' ); ?>
				</a>
			</p>
		</section>
	<?php endif; ?>

	<?php do_action( 'pw_after_archive_pw_offer', $property_id ); ?>
</main>
<?php
get_footer();
?>

