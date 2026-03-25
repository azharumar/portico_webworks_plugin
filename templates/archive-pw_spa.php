<?php
defined( 'ABSPATH' ) || exit;

$property_id = (int) pw_get_current_property_id();
if ( $property_id <= 0 ) {
	return;
}

$title = (string) get_post_meta( $property_id, '_pw_spa_section_title', true );
$intro = (string) get_post_meta( $property_id, '_pw_spa_section_intro', true );
$title = trim( $title ) !== '' ? $title : (string) __( 'Spas', 'portico-webworks' );
$intro = trim( $intro ) !== '' ? $intro : '';

$book_url = (string) get_post_meta( $property_id, '_pw_booking_engine_url', true );

$query = new WP_Query(
	[
		'post_type'              => 'pw_spa',
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
	]
);

get_header();
?>
<main class="pw-archive-spas">
	<?php do_action( 'pw_before_archive_pw_spa', $property_id ); ?>
	<header class="pw-archive-spas__header">
		<h1 class="pw-archive-spas__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro !== '' ) : ?>
			<p class="pw-archive-spas__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
	</header>

	<section class="pw-archive-spas__grid" aria-label="<?php echo esc_attr__( 'Spa outlets', 'portico-webworks' ); ?>">
		<?php if ( $query->have_posts() ) : ?>
			<?php foreach ( $query->posts as $s ) : ?>
				<?php if ( ! $s instanceof WP_Post ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php
				$sid       = (int) $s->ID;
				$thumb_id = (int) get_post_thumbnail_id( $sid );
				$excerpt  = (string) get_post_field( 'post_excerpt', $sid, 'raw' );
				$booking   = (string) get_post_meta( $sid, '_pw_booking_url', true );
				$treatments_raw = get_post_meta( $sid, '_pw_signature_treatments', true );
				$treatments = is_array( $treatments_raw ) ? $treatments_raw : [];
				$treatments = array_slice( $treatments, 0, 4 );

				$hours = pw_get_operating_hours( $sid );
				?>
				<article class="pw-spa-card">
					<?php if ( $thumb_id > 0 ) : ?>
						<a class="pw-spa-card__image-link" href="<?php echo esc_url( $booking !== '' ? $booking : get_permalink( $sid ) ); ?>">
							<?php
							echo wp_get_attachment_image(
								$thumb_id,
								'large',
								false,
								[
									'class'   => 'pw-spa-card__image',
									'loading' => 'lazy',
								]
							);
							?>
						</a>
					<?php endif; ?>
					<h2 class="pw-spa-card__title">
						<a class="pw-spa-card__title-link" href="<?php echo esc_url( $booking !== '' ? $booking : get_permalink( $sid ) ); ?>">
							<?php echo esc_html( get_the_title( $sid ) ); ?>
						</a>
					</h2>
					<?php if ( trim( $excerpt ) !== '' ) : ?>
						<p class="pw-spa-card__excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 28 ) ); ?></p>
					<?php endif; ?>

					<?php if ( is_array( $treatments ) && $treatments !== [] ) : ?>
						<div class="pw-spa-card__treatments">
							<h3 class="pw-spa-card__treatments-heading"><?php echo esc_html__( 'Signature treatments', 'portico-webworks' ); ?></h3>
							<ul class="pw-spa-card__treatments-list">
								<?php foreach ( $treatments as $row ) : ?>
									<?php
									if ( ! is_array( $row ) ) {
										continue;
									}
									$name = isset( $row['name'] ) ? (string) $row['name'] : '';
									$dur  = isset( $row['duration_min'] ) ? (int) $row['duration_min'] : 0;
									$desc  = isset( $row['description'] ) ? (string) $row['description'] : '';
									if ( $name === '' && $desc === '' ) {
										continue;
									}
									?>
									<li class="pw-spa-card__treatment">
										<span class="pw-spa-card__treatment-name"><?php echo esc_html( $name ); ?></span>
										<?php if ( $dur > 0 ) : ?>
											<span class="pw-spa-card__treatment-duration"><?php echo esc_html( sprintf( '%d %s', $dur, __( 'min', 'portico-webworks' ) ) ); ?></span>
										<?php endif; ?>
										<?php if ( trim( $desc ) !== '' ) : ?>
											<p class="pw-spa-card__treatment-desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $desc ), 24 ) ); ?></p>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( is_array( $hours ) && $hours !== [] ) : ?>
						<div class="pw-spa-card__hours">
							<h3 class="pw-spa-card__hours-heading"><?php echo esc_html__( 'Opening hours', 'portico-webworks' ); ?></h3>
							<ul class="pw-spa-card__hours-list">
								<?php foreach ( $hours as $h ) : ?>
									<?php
									if ( ! is_array( $h ) ) {
										continue;
									}
									$label = isset( $h['label'] ) ? (string) $h['label'] : '';
									$open  = isset( $h['open_time'] ) ? (string) $h['open_time'] : '';
									$close = isset( $h['close_time'] ) ? (string) $h['close_time'] : '';
									if ( $label === '' ) {
										continue;
									}
									$range = '';
									if ( $open !== '' && $close !== '' ) {
										$range = $open . ' – ' . $close;
									}
									?>
									<li class="pw-spa-card__hour">
										<span class="pw-spa-card__hour-label"><?php echo esc_html( $label ); ?></span>
										<?php if ( $range !== '' ) : ?>
											<span class="pw-spa-card__hour-range"><?php echo esc_html( $range ); ?></span>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( $booking !== '' ) : ?>
						<p class="pw-spa-card__cta">
							<a class="pw-spa-card__cta-link" href="<?php echo esc_url( $booking ); ?>">
								<?php echo esc_html( pw_get_cta_label( 'pw_spa', $sid ) ); ?>
							</a>
						</p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		<?php else : ?>
			<p class="pw-archive-spas__empty"><?php echo esc_html__( 'No spas found for this property.', 'portico-webworks' ); ?></p>
		<?php endif; ?>
	</section>

	<?php if ( is_string( $book_url ) && $book_url !== '' ) : ?>
		<section class="pw-archive-spas__bottom-cta" aria-label="<?php echo esc_attr__( 'Bookings', 'portico-webworks' ); ?>">
			<p class="pw-archive-spas__bottom-action">
				<a class="pw-archive-spas__bottom-link" href="<?php echo esc_url( $book_url ); ?>">
					<?php echo esc_html( pw_get_cta_label( 'pw_spa' ) ); ?>
				</a>
			</p>
		</section>
	<?php endif; ?>

	<?php do_action( 'pw_after_archive_pw_spa', $property_id ); ?>
</main>
<?php
get_footer();
?>

