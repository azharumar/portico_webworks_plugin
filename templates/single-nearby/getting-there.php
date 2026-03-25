<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$travel_min = (int) get_post_meta( $post_id, '_pw_travel_time_min', true );
$terms = get_the_terms( $post_id, 'pw_transport_mode' );
$modes = [];
if ( is_array( $terms ) ) {
	foreach ( $terms as $t ) {
		if ( $t instanceof WP_Term ) {
			$modes[] = $t->name;
		}
	}
}
$modes = array_values( array_unique( $modes ) );

$has_any = $travel_min > 0 || $modes !== [];
if ( ! $has_any ) {
	return;
}
?>
<section class="pw-nearby-getting-there" aria-labelledby="pw-nearby-getting-there-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_nearby_getting_there_content', $post_id ); ?>
	<h2 class="pw-nearby-getting-there__heading" id="pw-nearby-getting-there-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Getting there', 'portico-webworks' ); ?>
	</h2>
	<div class="pw-nearby-getting-there__body">
		<?php if ( $travel_min > 0 ) : ?>
			<p class="pw-nearby-getting-there__travel-time">
				<?php echo esc_html( sprintf( '%d %s', $travel_min, esc_html__( 'minutes', 'portico-webworks' ) ) ); ?>
			</p>
		<?php endif; ?>
		<?php if ( $modes !== [] ) : ?>
			<ul class="pw-nearby-getting-there__modes">
				<?php foreach ( $modes as $m ) : ?>
					<li class="pw-nearby-getting-there__mode"><?php echo esc_html( $m ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php do_action( 'pw_after_nearby_getting_there_content', $post_id ); ?>
</section>

