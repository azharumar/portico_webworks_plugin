<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$items = get_post_meta( $post_id, '_pw_signature_treatments', true );
if ( ! is_array( $items ) || $items === [] ) {
	return;
}

do_action( 'pw_before_spa_signature_treatments_content', $post_id );
?>
<section class="pw-spa-signature-treatments" aria-labelledby="pw-spa-signature-treatments-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<h2 class="pw-spa-signature-treatments__heading" id="pw-spa-signature-treatments-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Signature treatments', 'portico-webworks' ); ?>
	</h2>

	<ul class="pw-spa-signature-treatments__list">
		<?php foreach ( $items as $row ) : ?>
			<?php
			if ( ! is_array( $row ) ) {
				continue;
			}
			$name        = isset( $row['name'] ) ? (string) $row['name'] : '';
			$duration_min = isset( $row['duration_min'] ) ? (string) $row['duration_min'] : '';
			$description = isset( $row['description'] ) ? (string) $row['description'] : '';
			if ( trim( $name ) === '' && trim( $description ) === '' ) {
				continue;
			}
			?>
			<li class="pw-spa-signature-treatments__item">
				<?php if ( $name !== '' ) : ?>
					<div class="pw-spa-signature-treatments__name"><?php echo esc_html( $name ); ?></div>
				<?php endif; ?>
				<?php if ( $duration_min !== '' ) : ?>
					<div class="pw-spa-signature-treatments__duration"><?php echo esc_html( $duration_min ); ?> <?php echo esc_html__( 'min', 'portico-webworks' ); ?></div>
				<?php endif; ?>
				<?php if ( $description !== '' ) : ?>
					<div class="pw-spa-signature-treatments__description"><?php echo wp_kses_post( $description ); ?></div>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>

<?php
do_action( 'pw_after_spa_signature_treatments_content', $post_id );

