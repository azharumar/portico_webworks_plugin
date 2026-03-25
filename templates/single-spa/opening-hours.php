<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$rows = pw_get_operating_hours( $post_id );
if ( ! is_array( $rows ) || $rows === [] ) {
	return;
}
?>
<section class="pw-spa-opening-hours" aria-labelledby="pw-spa-opening-hours-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_spa_opening_hours_content', $post_id ); ?>
	<h2 class="pw-spa-opening-hours__heading" id="pw-spa-opening-hours-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Opening hours', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-spa-opening-hours__list">
		<?php foreach ( $rows as $row ) : ?>
			<?php
			if ( ! is_array( $row ) ) {
				continue;
			}
			$label     = isset( $row['label'] ) ? (string) $row['label'] : '';
			$open_time = isset( $row['open_time'] ) ? (string) $row['open_time'] : '';
			$close_time = isset( $row['close_time'] ) ? (string) $row['close_time'] : '';
			if ( $label === '' && $open_time === '' && $close_time === '' ) {
				continue;
			}
			?>
			<li class="pw-spa-opening-hours__row">
				<?php if ( $label !== '' ) : ?>
					<div class="pw-spa-opening-hours__label-block">
						<span class="pw-spa-opening-hours__label"><?php echo esc_html( $label ); ?></span>
					</div>
				<?php endif; ?>
				<?php if ( $open_time !== '' || $close_time !== '' ) : ?>
					<div class="pw-spa-opening-hours__time-block">
						<span class="pw-spa-opening-hours__value">
							<?php
							if ( $open_time !== '' && $close_time !== '' ) {
								echo esc_html( $open_time . ' - ' . $close_time );
							} elseif ( $open_time !== '' ) {
								echo esc_html( $open_time );
							} else {
								echo esc_html( $close_time );
							}
							?>
						</span>
					</div>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'pw_after_spa_opening_hours_content', $post_id ); ?>
</section>

