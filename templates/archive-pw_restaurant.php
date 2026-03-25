<?php
defined( 'ABSPATH' ) || exit;

$property_id = (int) pw_get_current_property_id();
if ( $property_id <= 0 ) {
	return;
}

$title   = (string) get_post_meta( $property_id, '_pw_dining_section_title', true );
$intro   = (string) get_post_meta( $property_id, '_pw_dining_section_intro', true );
$title   = trim( $title ) !== '' ? $title : (string) __( 'Dining', 'portico-webworks' );
$intro   = trim( $intro ) !== '' ? $intro : '';
$highlite = (string) get_post_meta( $property_id, '_pw_dining_highlight_text', true );
$highlite = trim( $highlite ) !== '' ? $highlite : '';

$contact = pw_restaurant_get_archive_primary_contact( $property_id );
$email   = is_array( $contact ) && isset( $contact['email'] ) ? (string) $contact['email'] : '';
$phone   = is_array( $contact ) && isset( $contact['phone'] ) ? (string) $contact['phone'] : '';
$mobile  = is_array( $contact ) && isset( $contact['mobile'] ) ? (string) $contact['mobile'] : '';
$whatsapp = is_array( $contact ) && isset( $contact['whatsapp'] ) ? (string) $contact['whatsapp'] : '';

$href = '';
if ( $email !== '' ) {
	$href = 'mailto:' . rawurlencode( $email );
} elseif ( $phone !== '' ) {
	$tel  = preg_replace( '/[^0-9+]/', '', $phone );
	$href = $tel !== '' ? 'tel:' . rawurlencode( $tel ) : '';
} elseif ( $mobile !== '' ) {
	$tel  = preg_replace( '/[^0-9+]/', '', $mobile );
	$href = $tel !== '' ? 'tel:' . rawurlencode( $tel ) : '';
} elseif ( $whatsapp !== '' ) {
	$tel  = preg_replace( '/[^0-9+]/', '', $whatsapp );
	$href = $tel !== '' ? 'tel:' . rawurlencode( $tel ) : '';
}

$query = new WP_Query(
	[
		'post_type'              => 'pw_restaurant',
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
<main class="pw-archive-restaurants">
	<?php do_action( 'pw_before_archive_pw_restaurant', $property_id ); ?>
	<header class="pw-archive-restaurants__header">
		<h1 class="pw-archive-restaurants__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro !== '' ) : ?>
			<p class="pw-archive-restaurants__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
		<?php if ( $highlite !== '' ) : ?>
			<p class="pw-archive-restaurants__highlight"><?php echo esc_html( $highlite ); ?></p>
		<?php endif; ?>
	</header>

	<section class="pw-archive-restaurants__grid" aria-label="<?php echo esc_attr__( 'Restaurants', 'portico-webworks' ); ?>">
		<?php if ( $query->have_posts() ) : ?>
			<?php foreach ( $query->posts as $r ) : ?>
				<?php if ( ! $r instanceof WP_Post ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php
				$rid = (int) $r->ID;
				$thumb_id = (int) get_post_thumbnail_id( $rid );
				$cuisine = (string) get_post_meta( $rid, '_pw_cuisine_type', true );
				$res_url = (string) get_post_meta( $rid, '_pw_reservation_url', true );
				$excerpt = (string) get_post_field( 'post_excerpt', $rid, 'raw' );
				$terms   = get_the_terms( $rid, 'pw_meal_period' );
				$meal_names = [];
				if ( is_array( $terms ) ) {
					foreach ( $terms as $t ) {
						if ( $t instanceof WP_Term ) {
							$meal_names[] = (string) $t->name;
						}
					}
				}
				$meal_names = array_values( array_unique( array_filter( $meal_names ) ) );
				$cta_link  = $res_url !== '' ? $res_url : get_permalink( $rid );
				?>
				<article class="pw-restaurant-card">
					<?php if ( $thumb_id > 0 ) : ?>
						<a class="pw-restaurant-card__image-link" href="<?php echo esc_url( $cta_link ); ?>">
							<?php
							echo wp_get_attachment_image(
								$thumb_id,
								'large',
								false,
								[
									'class'   => 'pw-restaurant-card__image',
									'loading' => 'lazy',
								]
							);
							?>
						</a>
					<?php endif; ?>
					<h2 class="pw-restaurant-card__title">
						<a class="pw-restaurant-card__title-link" href="<?php echo esc_url( $cta_link ); ?>">
							<?php echo esc_html( get_the_title( $rid ) ); ?>
						</a>
					</h2>
					<?php if ( $cuisine !== '' ) : ?>
						<p class="pw-restaurant-card__cuisine">
							<?php echo esc_html( $cuisine ); ?>
						</p>
					<?php endif; ?>
					<?php if ( $meal_names !== [] ) : ?>
						<p class="pw-restaurant-card__meal-periods">
							<?php echo esc_html( implode( ', ', $meal_names ) ); ?>
						</p>
					<?php endif; ?>
					<?php if ( trim( $excerpt ) !== '' ) : ?>
						<p class="pw-restaurant-card__excerpt">
							<?php echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 26 ) ); ?>
						</p>
					<?php endif; ?>
					<?php if ( $res_url !== '' ) : ?>
						<p class="pw-restaurant-card__cta">
							<a class="pw-restaurant-card__cta-link" href="<?php echo esc_url( $res_url ); ?>">
								<?php echo esc_html( pw_get_cta_label( 'pw_restaurant', $rid ) ); ?>
							</a>
						</p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		<?php else : ?>
			<p class="pw-archive-restaurants__empty"><?php echo esc_html__( 'No restaurants found for this property.', 'portico-webworks' ); ?></p>
		<?php endif; ?>
	</section>

	<?php if ( $href !== '' ) : ?>
		<section class="pw-archive-restaurants__bottom-cta" aria-label="<?php echo esc_attr__( 'Reservations', 'portico-webworks' ); ?>">
			<p class="pw-archive-restaurants__bottom-action">
				<a class="pw-archive-restaurants__bottom-link" href="<?php echo esc_url( $href ); ?>">
					<?php echo esc_html( pw_get_cta_label( 'pw_restaurant' ) ); ?>
				</a>
			</p>
		</section>
	<?php endif; ?>

	<?php do_action( 'pw_after_archive_pw_restaurant', $property_id ); ?>
</main>
<?php
get_footer();
?>

