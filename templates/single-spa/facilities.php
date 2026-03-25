<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$items = get_post_meta( $post_id, '_pw_spa_facilities', true );
if ( ! is_array( $items ) || $items === [] ) {
	return;
}

do_action( 'pw_before_spa_facilities_content', $post_id );
?>
<section class="pw-spa-facilities" aria-labelledby="pw-spa-facilities-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<h2 class="pw-spa-facilities__heading" id="pw-spa-facilities-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Facilities', 'portico-webworks' ); ?>
	</h2>

	<ul class="pw-spa-facilities__list">
		<?php foreach ( $items as $row ) : ?>
			<?php
			if ( ! is_array( $row ) ) {
				continue;
			}
			$name = isset( $row['name'] ) ? (string) $row['name'] : '';
			$icon = isset( $row['icon'] ) ? (string) $row['icon'] : '';
			if ( trim( $name ) === '' && trim( $icon ) === '' ) {
				continue;
			}
			?>
			<li class="pw-spa-facilities__item">
				<?php if ( $icon !== '' ) : ?>
					<span class="pw-spa-facilities__icon"><?php echo esc_html( $icon ); ?></span>
				<?php endif; ?>
				<?php if ( $name !== '' ) : ?>
					<span class="pw-spa-facilities__name"><?php echo esc_html( $name ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php do_action( 'pw_after_spa_facilities_content', $post_id ); ?>
</section>

