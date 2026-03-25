<?php
defined( 'ABSPATH' ) || exit;

$property_id = (int) pw_get_current_property_id();
if ( $property_id <= 0 ) {
	return;
}

$intro = (string) get_post_meta( $property_id, '_pw_places_section_intro', true );
$intro = trim( $intro ) !== '' ? $intro : '';

$lat = get_post_meta( $property_id, '_pw_lat', true );
$lng = get_post_meta( $property_id, '_pw_lng', true );
$lat_f = is_numeric( $lat ) ? (float) $lat : 0.0;
$lng_f = is_numeric( $lng ) ? (float) $lng : 0.0;

$query = new WP_Query(
	[
		'post_type'              => 'pw_nearby',
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

$getting_here_raw = get_post_meta( $property_id, '_pw_getting_here', true );
$getting_here = is_array( $getting_here_raw ) ? $getting_here_raw : [];

get_header();
?>
<main class="pw-archive-nearby">
	<?php do_action( 'pw_before_archive_pw_nearby', $property_id ); ?>
	<header class="pw-archive-nearby__header">
		<h1 class="pw-archive-nearby__title"><?php echo esc_html__( 'Around the property', 'portico-webworks' ); ?></h1>
		<?php if ( $intro !== '' ) : ?>
			<p class="pw-archive-nearby__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
	</header>

	<section class="pw-archive-nearby__map" aria-label="<?php echo esc_attr__( 'Map', 'portico-webworks' ); ?>">
		<h2 class="pw-archive-nearby__map-heading"><?php echo esc_html__( 'Map', 'portico-webworks' ); ?></h2>
		<?php if ( $lat_f !== 0.0 || $lng_f !== 0.0 ) : ?>
			<iframe
				class="pw-archive-nearby__map-iframe"
				title="<?php echo esc_attr( __( 'Nearby map', 'portico-webworks' ) ); ?>"
				loading="lazy"
				decoding="async"
				src="<?php echo esc_url( 'https://www.google.com/maps?q=' . rawurlencode( (string) $lat_f . ',' . (string) $lng_f ) . '&z=12&output=embed' ); ?>"
				referrerpolicy="no-referrer-when-downgrade"
			></iframe>
		<?php endif; ?>
	</section>

	<section class="pw-archive-nearby__grid" aria-label="<?php echo esc_attr__( 'Nearby places', 'portico-webworks' ); ?>">
		<h2 class="pw-archive-nearby__grid-heading"><?php echo esc_html__( 'Nearby places', 'portico-webworks' ); ?></h2>
		<?php if ( $query->have_posts() ) : ?>
			<ul class="pw-nearby-places-list">
				<?php foreach ( $query->posts as $p ) : ?>
					<?php if ( ! $p instanceof WP_Post ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<?php
					$pid = (int) $p->ID;
					$thumb_id = (int) get_post_thumbnail_id( $pid );
					$type_terms = get_the_terms( $pid, 'pw_nearby_type' );
					$type_name = ( is_array( $type_terms ) && $type_terms !== [] && $type_terms[0] instanceof WP_Term ) ? (string) $type_terms[0]->name : '';
					$trans_terms = get_the_terms( $pid, 'pw_transport_mode' );
					$trans_names = [];
					if ( is_array( $trans_terms ) ) {
						foreach ( $trans_terms as $t ) {
							if ( $t instanceof WP_Term ) {
								$trans_names[] = (string) $t->name;
							}
						}
					}
					$trans_names = array_values( array_unique( $trans_names ) );
					$distance_km = (float) get_post_meta( $pid, '_pw_distance_km', true );
					$travel_min  = (int) get_post_meta( $pid, '_pw_travel_time_min', true );
					$place_url   = (string) get_post_meta( $pid, '_pw_place_url', true );
					$link         = get_permalink( $pid );
					?>
					<li class="pw-nearby-place-card">
						<article class="pw-nearby-place-card__inner">
							<?php if ( $thumb_id > 0 ) : ?>
								<a class="pw-nearby-place-card__image-link" href="<?php echo esc_url( $link ); ?>">
									<?php echo wp_get_attachment_image( $thumb_id, 'large', false, [ 'class' => 'pw-nearby-place-card__image', 'loading' => 'lazy' ] ); ?>
								</a>
							<?php endif; ?>
							<h2 class="pw-nearby-place-card__title">
								<a class="pw-nearby-place-card__title-link" href="<?php echo esc_url( $link ); ?>">
									<?php echo esc_html( get_the_title( $pid ) ); ?>
								</a>
							</h2>
							<ul class="pw-nearby-place-card__meta">
								<?php if ( $type_name !== '' ) : ?>
									<li class="pw-nearby-place-card__meta-item">
										<span class="pw-nearby-place-card__meta-label"><?php echo esc_html__( 'Category', 'portico-webworks' ); ?>:</span>
										<span class="pw-nearby-place-card__meta-value"><?php echo esc_html( $type_name ); ?></span>
									</li>
								<?php endif; ?>
								<?php if ( $distance_km > 0 ) : ?>
									<li class="pw-nearby-place-card__meta-item">
										<span class="pw-nearby-place-card__meta-label"><?php echo esc_html__( 'Distance', 'portico-webworks' ); ?>:</span>
										<span class="pw-nearby-place-card__meta-value"><?php echo esc_html( sprintf( '%s km', number_format_i18n( $distance_km ) ) ); ?></span>
									</li>
								<?php endif; ?>
								<?php if ( $travel_min > 0 ) : ?>
									<li class="pw-nearby-place-card__meta-item">
										<span class="pw-nearby-place-card__meta-label"><?php echo esc_html__( 'Travel time', 'portico-webworks' ); ?>:</span>
										<span class="pw-nearby-place-card__meta-value"><?php echo esc_html( sprintf( '%d min', $travel_min ) ); ?></span>
									</li>
								<?php endif; ?>
								<?php if ( $trans_names !== [] ) : ?>
									<li class="pw-nearby-place-card__meta-item">
										<span class="pw-nearby-place-card__meta-label"><?php echo esc_html__( 'Transport', 'portico-webworks' ); ?>:</span>
										<span class="pw-nearby-place-card__meta-value"><?php echo esc_html( implode( ', ', $trans_names ) ); ?></span>
									</li>
								<?php endif; ?>
							</ul>
							<?php if ( $place_url !== '' ) : ?>
								<p class="pw-nearby-place-card__external">
									<a class="pw-nearby-place-card__external-link" href="<?php echo esc_url( $place_url ); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html__( 'Open in maps', 'portico-webworks' ); ?>
									</a>
								</p>
							<?php endif; ?>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="pw-archive-nearby__empty"><?php echo esc_html__( 'No nearby places found for this property.', 'portico-webworks' ); ?></p>
		<?php endif; ?>
	</section>

	<?php if ( is_array( $getting_here ) && $getting_here !== [] ) : ?>
		<section class="pw-archive-nearby__getting-there" aria-label="<?php echo esc_attr__( 'Getting there', 'portico-webworks' ); ?>">
			<h2 class="pw-archive-nearby__getting-there-heading"><?php echo esc_html__( 'Getting there', 'portico-webworks' ); ?></h2>
			<ul class="pw-archive-nearby__getting-there-list">
				<?php foreach ( $getting_here as $row ) : ?>
					<?php
					if ( ! is_array( $row ) ) {
						continue;
					}
					$from_location = isset( $row['from_location'] ) ? (string) $row['from_location'] : '';
					$duration = isset( $row['duration'] ) ? (string) $row['duration'] : '';
					$transport_mode = isset( $row['transport_mode'] ) ? (string) $row['transport_mode'] : '';
					$description = isset( $row['description'] ) ? (string) $row['description'] : '';
					if ( trim( $from_location . $duration . $transport_mode . $description ) === '' ) {
						continue;
					}
					?>
					<li class="pw-archive-nearby__getting-there-item">
						<h3 class="pw-archive-nearby__getting-there-item-title"><?php echo esc_html( $from_location ); ?></h3>
						<?php if ( trim( $duration ) !== '' || trim( $transport_mode ) !== '' ) : ?>
							<p class="pw-archive-nearby__getting-there-item-meta">
								<?php
								$meta = '';
								if ( trim( $duration ) !== '' ) {
									$meta = $duration;
								}
								if ( trim( $transport_mode ) !== '' ) {
									$meta = $meta !== '' ? $meta . ' · ' . $transport_mode : $transport_mode;
								}
								?>
								<?php if ( $meta !== '' ) : ?>
									<?php echo esc_html( $meta ); ?>
								<?php endif; ?>
							</p>
						<?php endif; ?>
						<?php if ( trim( $description ) !== '' ) : ?>
							<p class="pw-archive-nearby__getting-there-item-desc"><?php echo wp_kses_post( $description ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php do_action( 'pw_after_archive_pw_nearby', $property_id ); ?>
</main>
<?php
get_footer();
?>

