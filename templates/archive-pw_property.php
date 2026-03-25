<?php
defined( 'ABSPATH' ) || exit;

if ( pw_get_setting( 'pw_property_mode', 'single' ) !== 'multi' ) {
	return;
}

$properties = get_posts(
	[
		'post_type'              => 'pw_property',
		'post_status'            => 'publish',
		'posts_per_page'         => -1,
		'orderby'                => 'title',
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	]
);

get_header();
?>
<main class="pw-archive-properties">
	<?php do_action( 'pw_before_archive_pw_property' ); ?>
	<header class="pw-archive-properties__header">
		<h1 class="pw-archive-properties__title"><?php echo esc_html__( 'Hotels', 'portico-webworks' ); ?></h1>
		<p class="pw-archive-properties__intro"><?php echo esc_html__( 'Browse our properties', 'portico-webworks' ); ?></p>
	</header>

	<section class="pw-archive-properties__grid" aria-label="<?php echo esc_attr__( 'Properties', 'portico-webworks' ); ?>">
		<?php if ( is_array( $properties ) && $properties !== [] ) : ?>
			<ul class="pw-properties-list">
				<?php foreach ( $properties as $p ) : ?>
					<?php if ( ! $p instanceof WP_Post ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<?php
					$pid = (int) $p->ID;
					$thumb_id = (int) get_post_thumbnail_id( $pid );
					$city = (string) get_post_meta( $pid, '_pw_city', true );
					$country = (string) get_post_meta( $pid, '_pw_country', true );
					$star = (string) get_post_meta( $pid, '_pw_star_rating', true );
					$excerpt = (string) get_post_field( 'post_excerpt', $pid, 'raw' );
					$link = pw_get_property_url( $pid );
					?>
					<li class="pw-property-card">
						<article class="pw-property-card__inner">
							<?php if ( $thumb_id > 0 && $link !== '' ) : ?>
								<a class="pw-property-card__image-link" href="<?php echo esc_url( $link ); ?>">
									<?php echo wp_get_attachment_image( $thumb_id, 'large', false, [ 'class' => 'pw-property-card__image', 'loading' => 'lazy' ] ); ?>
								</a>
							<?php endif; ?>
							<h2 class="pw-property-card__title">
								<?php if ( $link !== '' ) : ?>
									<a class="pw-property-card__title-link" href="<?php echo esc_url( $link ); ?>">
										<?php echo esc_html( get_the_title( $pid ) ); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html( get_the_title( $pid ) ); ?>
								<?php endif; ?>
							</h2>
							<ul class="pw-property-card__meta">
								<?php if ( $star !== '' ) : ?>
									<li class="pw-property-card__meta-item">
										<span class="pw-property-card__meta-label"><?php echo esc_html__( 'Star rating', 'portico-webworks' ); ?>:</span>
										<span class="pw-property-card__meta-value"><?php echo esc_html( $star ); ?></span>
									</li>
								<?php endif; ?>
								<?php if ( trim( $city . $country ) !== '' ) : ?>
									<li class="pw-property-card__meta-item">
										<span class="pw-property-card__meta-label"><?php echo esc_html__( 'Location', 'portico-webworks' ); ?>:</span>
										<span class="pw-property-card__meta-value">
											<?php echo esc_html( trim( $city . ( $country !== '' ? ', ' . $country : '' ) ) ); ?>
										</span>
									</li>
								<?php endif; ?>
							</ul>
							<?php if ( trim( $excerpt ) !== '' ) : ?>
								<p class="pw-property-card__excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 26 ) ); ?></p>
							<?php endif; ?>
							<?php if ( $link !== '' ) : ?>
								<p class="pw-property-card__cta">
									<a class="pw-property-card__cta-link" href="<?php echo esc_url( $link ); ?>">
										<?php echo esc_html__( 'Enquire for group travel', 'portico-webworks' ); ?>
									</a>
								</p>
							<?php endif; ?>
						</article>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="pw-archive-properties__empty"><?php echo esc_html__( 'No properties found.', 'portico-webworks' ); ?></p>
		<?php endif; ?>
	</section>

	<?php do_action( 'pw_after_archive_pw_property' ); ?>
</main>
<?php
get_footer();
?>

