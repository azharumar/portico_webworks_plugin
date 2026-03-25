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
<section class="pw-restaurant-opening-hours" aria-labelledby="pw-restaurant-opening-hours-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_restaurant_opening_hours_content', $post_id ); ?>
	<h2 class="pw-restaurant-opening-hours__heading" id="pw-restaurant-opening-hours-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_restaurant_opening_hours_heading( $post_id ) ); ?>
	</h2>
	<ul class="pw-restaurant-opening-hours__list">
		<?php foreach ( $rows as $row ) : ?>
			<?php if ( ! is_array( $row ) ) : ?>
				<?php continue; ?>
			<?php endif; ?>

			<?php
			$label     = isset( $row['label'] ) ? (string) $row['label'] : '';
			$open_time = isset( $row['open_time'] ) ? (string) $row['open_time'] : '';
			$close_time = isset( $row['close_time'] ) ? (string) $row['close_time'] : '';
			if ( $label === '' && $open_time === '' && $close_time === '' ) {
				continue;
			}
			?>

			<li class="pw-restaurant-opening-hours__row">
				<?php if ( $label !== '' ) : ?>
					<div class="pw-restaurant-opening-hours__label-block">
						<span class="pw-restaurant-opening-hours__label"><?php echo esc_html( pw_get_restaurant_opening_hours_day_label( $post_id ) ); ?>:</span>
						<span class="pw-restaurant-opening-hours__value"><?php echo esc_html( $label ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $open_time !== '' || $close_time !== '' ) : ?>
					<div class="pw-restaurant-opening-hours__time-block">
						<span class="pw-restaurant-opening-hours__label"><?php echo esc_html( pw_get_restaurant_opening_hours_time_label( $post_id ) ); ?>:</span>
						<span class="pw-restaurant-opening-hours__value">
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
	<?php do_action( 'pw_after_restaurant_opening_hours_content', $post_id ); ?>
</section>

