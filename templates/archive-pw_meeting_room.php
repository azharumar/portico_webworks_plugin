<?php
defined( 'ABSPATH' ) || exit;

$property_id = (int) pw_get_current_property_id();
if ( $property_id <= 0 ) {
	return;
}

$title = (string) get_post_meta( $property_id, '_pw_meetings_section_title', true );
$intro = (string) get_post_meta( $property_id, '_pw_meetings_section_intro', true );
$title = trim( $title ) !== '' ? $title : (string) __( 'Meetings & events', 'portico-webworks' );
$intro = trim( $intro ) !== '' ? $intro : '';

$services = (string) get_post_meta( $property_id, '_pw_meetings_services_text', true );
$services = trim( $services ) !== '' ? $services : '';

$contact = pw_meeting_room_get_archive_primary_contact( $property_id );
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
		'post_type'              => 'pw_meeting_room',
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
<main class="pw-archive-meetings">
	<?php do_action( 'pw_before_archive_pw_meeting_room', $property_id ); ?>
	<header class="pw-archive-meetings__header">
		<h1 class="pw-archive-meetings__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro !== '' ) : ?>
			<p class="pw-archive-meetings__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
		<?php if ( $services !== '' ) : ?>
			<p class="pw-archive-meetings__services"><?php echo esc_html( $services ); ?></p>
		<?php endif; ?>
	</header>

	<section class="pw-archive-meetings__grid" aria-label="<?php echo esc_attr__( 'Meeting rooms', 'portico-webworks' ); ?>">
		<?php if ( $query->have_posts() ) : ?>
			<?php foreach ( $query->posts as $m ) : ?>
				<?php if ( ! $m instanceof WP_Post ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php
				$mid = (int) $m->ID;
				$thumb_id = (int) get_post_thumbnail_id( $mid );
				$theatre = (int) get_post_meta( $mid, '_pw_capacity_theatre', true );
				$area_sqm = (int) get_post_meta( $mid, '_pw_area_sqm', true );
				$terms = get_the_terms( $mid, 'pw_av_equipment' );
				$av = [];
				if ( is_array( $terms ) ) {
					foreach ( $terms as $t ) {
						if ( $t instanceof WP_Term ) {
							$av[] = (string) $t->name;
						}
					}
				}
				$av = array_values( array_unique( $av ) );
				$av_preview = $av !== [] ? implode( ', ', array_slice( $av, 0, 3 ) ) : '';
				?>
				<article class="pw-meeting-room-card">
					<?php if ( $thumb_id > 0 ) : ?>
						<a class="pw-meeting-room-card__image-link" href="<?php echo esc_url( get_permalink( $mid ) ); ?>">
							<?php
							echo wp_get_attachment_image(
								$thumb_id,
								'large',
								false,
								[
									'class'   => 'pw-meeting-room-card__image',
									'loading' => 'lazy',
								]
							);
							?>
						</a>
					<?php endif; ?>
					<h2 class="pw-meeting-room-card__title">
						<a class="pw-meeting-room-card__title-link" href="<?php echo esc_url( get_permalink( $mid ) ); ?>">
							<?php echo esc_html( get_the_title( $mid ) ); ?>
						</a>
					</h2>
					<ul class="pw-meeting-room-card__meta">
						<?php if ( $theatre > 0 ) : ?>
							<li class="pw-meeting-room-card__meta-item">
								<span class="pw-meeting-room-card__meta-label"><?php echo esc_html__( 'Theatre capacity', 'portico-webworks' ); ?>:</span>
								<span class="pw-meeting-room-card__meta-value"><?php echo esc_html( (string) $theatre ); ?></span>
							</li>
						<?php endif; ?>
						<?php if ( $area_sqm > 0 ) : ?>
							<li class="pw-meeting-room-card__meta-item">
								<span class="pw-meeting-room-card__meta-label"><?php echo esc_html__( 'Floor area', 'portico-webworks' ); ?>:</span>
								<span class="pw-meeting-room-card__meta-value">
									<?php echo esc_html( (string) $area_sqm . ' ' . pw_get_room_size_sqm_suffix( $mid ) ); ?>
								</span>
							</li>
						<?php endif; ?>
						<?php if ( $av_preview !== '' ) : ?>
							<li class="pw-meeting-room-card__meta-item">
								<span class="pw-meeting-room-card__meta-label"><?php echo esc_html__( 'AV', 'portico-webworks' ); ?>:</span>
								<span class="pw-meeting-room-card__meta-value"><?php echo esc_html( $av_preview ); ?></span>
							</li>
						<?php endif; ?>
					</ul>
				</article>
			<?php endforeach; ?>
		<?php else : ?>
			<p class="pw-archive-meetings__empty"><?php echo esc_html__( 'No meeting rooms found for this property.', 'portico-webworks' ); ?></p>
		<?php endif; ?>
	</section>

	<?php if ( $href !== '' ) : ?>
		<section class="pw-archive-meetings__rfp-cta" aria-label="<?php echo esc_attr__( 'Proposal request', 'portico-webworks' ); ?>">
			<h2 class="pw-archive-meetings__rfp-heading"><?php echo esc_html( pw_get_cta_label( 'pw_meeting_room' ) ); ?></h2>
			<p class="pw-archive-meetings__rfp-action">
				<a class="pw-archive-meetings__rfp-link" href="<?php echo esc_url( $href ); ?>">
					<?php echo esc_html__( 'Send a proposal request', 'portico-webworks' ); ?>
				</a>
			</p>
		</section>

		<section class="pw-archive-meetings__bottom-cta">
			<p class="pw-archive-meetings__bottom-action">
				<a class="pw-archive-meetings__bottom-link" href="<?php echo esc_url( $href ); ?>">
					<?php echo esc_html( pw_get_cta_label( 'pw_meeting_room' ) ); ?>
				</a>
			</p>
		</section>
	<?php endif; ?>

	<?php do_action( 'pw_after_archive_pw_meeting_room', $property_id ); ?>
</main>
<?php
get_footer();
?>

