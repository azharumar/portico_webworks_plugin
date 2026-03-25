<?php
defined( 'ABSPATH' ) || exit;

$property_id = (int) pw_get_current_property_id();
if ( $property_id <= 0 ) {
	return;
}

$title = (string) get_post_meta( $property_id, '_pw_experiences_section_title', true );
$intro = (string) get_post_meta( $property_id, '_pw_experiences_section_intro', true );
$title = trim( $title ) !== '' ? $title : (string) __( 'Experiences', 'portico-webworks' );
$intro = trim( $intro ) !== '' ? $intro : '';

$book_url = (string) get_post_meta( $property_id, '_pw_booking_engine_url', true );

$query = new WP_Query(
	[
		'post_type'              => 'pw_experience',
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
<main class="pw-archive-experiences">
	<?php do_action( 'pw_before_archive_pw_experience', $property_id ); ?>
	<header class="pw-archive-experiences__header">
		<h1 class="pw-archive-experiences__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro !== '' ) : ?>
			<p class="pw-archive-experiences__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
	</header>

	<section class="pw-archive-experiences__grid" aria-label="<?php echo esc_attr__( 'Experiences', 'portico-webworks' ); ?>">
		<?php if ( $query->have_posts() ) : ?>
			<?php foreach ( $query->posts as $e ) : ?>
				<?php if ( ! $e instanceof WP_Post ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php
				$eid = (int) $e->ID;
				$thumb_id = (int) get_post_thumbnail_id( $eid );
				$duration = (float) get_post_meta( $eid, '_pw_duration_hours', true );
				$price_from = (float) get_post_meta( $eid, '_pw_price_from', true );
				$booking_url = (string) get_post_meta( $eid, '_pw_booking_url', true );
				$excerpt = (string) get_post_field( 'post_excerpt', $eid, 'raw' );

				$terms = get_the_terms( $eid, 'pw_experience_category' );
				$cat = '';
				if ( is_array( $terms ) && $terms !== [] && $terms[0] instanceof WP_Term ) {
					$cat = (string) $terms[0]->name;
				}
				?>
				<article class="pw-experience-card">
					<?php if ( $thumb_id > 0 ) : ?>
						<a class="pw-experience-card__image-link" href="<?php echo esc_url( $booking_url !== '' ? $booking_url : get_permalink( $eid ) ); ?>">
							<?php
							echo wp_get_attachment_image(
								$thumb_id,
								'large',
								false,
								[
									'class'   => 'pw-experience-card__image',
									'loading' => 'lazy',
								]
							);
							?>
						</a>
					<?php endif; ?>
					<h2 class="pw-experience-card__title">
						<a class="pw-experience-card__title-link" href="<?php echo esc_url( $booking_url !== '' ? $booking_url : get_permalink( $eid ) ); ?>">
							<?php echo esc_html( get_the_title( $eid ) ); ?>
						</a>
					</h2>
					<ul class="pw-experience-card__meta">
						<?php if ( $cat !== '' ) : ?>
							<li class="pw-experience-card__meta-item">
								<span class="pw-experience-card__meta-label"><?php echo esc_html__( 'Category', 'portico-webworks' ); ?>:</span>
								<span class="pw-experience-card__meta-value"><?php echo esc_html( $cat ); ?></span>
							</li>
						<?php endif; ?>
						<?php if ( $duration > 0 ) : ?>
							<li class="pw-experience-card__meta-item">
								<span class="pw-experience-card__meta-label"><?php echo esc_html__( 'Duration', 'portico-webworks' ); ?>:</span>
								<span class="pw-experience-card__meta-value">
									<?php echo esc_html( sprintf( '%s %s', number_format_i18n( $duration ), esc_html__( 'hours', 'portico-webworks' ) ) ); ?>
								</span>
							</li>
						<?php endif; ?>
						<?php if ( $price_from > 0 ) : ?>
							<li class="pw-experience-card__meta-item">
								<span class="pw-experience-card__meta-label"><?php echo esc_html__( 'From', 'portico-webworks' ); ?>:</span>
								<span class="pw-experience-card__meta-value">
									<?php
									$cur = pw_get_property_currency( $property_id );
									echo esc_html( sprintf( '%s %s', number_format_i18n( $price_from ), $cur ) );
									?>
								</span>
							</li>
						<?php endif; ?>
					</ul>
					<?php if ( trim( $excerpt ) !== '' ) : ?>
						<p class="pw-experience-card__excerpt">
							<?php echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 26 ) ); ?>
						</p>
					<?php endif; ?>
					<?php if ( $booking_url !== '' ) : ?>
						<p class="pw-experience-card__cta">
							<a class="pw-experience-card__cta-link" href="<?php echo esc_url( $booking_url ); ?>">
								<?php echo esc_html( pw_get_cta_label( 'pw_experience', $eid ) ); ?>
							</a>
						</p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		<?php else : ?>
			<p class="pw-archive-experiences__empty"><?php echo esc_html__( 'No experiences found for this property.', 'portico-webworks' ); ?></p>
		<?php endif; ?>
	</section>

	<?php if ( is_string( $book_url ) && $book_url !== '' ) : ?>
		<section class="pw-archive-experiences__bottom-cta" aria-label="<?php echo esc_attr__( 'Booking', 'portico-webworks' ); ?>">
			<p class="pw-archive-experiences__bottom-action">
				<a class="pw-archive-experiences__bottom-link" href="<?php echo esc_url( $book_url ); ?>">
					<?php echo esc_html( pw_get_cta_label( 'pw_experience' ) ); ?>
				</a>
			</p>
		</section>
	<?php endif; ?>

	<?php do_action( 'pw_after_archive_pw_experience', $property_id ); ?>
</main>
<?php
get_footer();
?>

