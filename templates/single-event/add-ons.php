<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$raw = get_post_meta( $post_id, '_pw_event_addons', true );
if ( ! is_array( $raw ) || $raw === [] ) {
	return;
}

$rows = [];
foreach ( $raw as $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	$label = isset( $row['label'] ) ? (string) $row['label'] : '';
	$desc  = isset( $row['description'] ) ? (string) $row['description'] : '';
	$price = isset( $row['price'] ) ? (float) $row['price'] : 0;
	if ( $label === '' && trim( $desc ) === '' && $price <= 0 ) {
		continue;
	}
	$rows[] = [
		'label' => $label,
		'desc'  => $desc,
		'price' => $price,
	];
}

if ( $rows === [] ) {
	return;
}

$property_id = (int) get_post_meta( $post_id, '_pw_property_id', true );
$currency = $property_id > 0 ? pw_get_property_currency( $property_id ) : pw_get_property_currency();
?>
<section class="pw-event-add-ons" aria-labelledby="pw-event-add-ons-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_event_add_ons_content', $post_id ); ?>
	<h2 class="pw-event-add-ons__heading" id="pw-event-add-ons-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Add-on options', 'portico-webworks' ); ?>
	</h2>
	<ul class="pw-event-add-ons__list">
		<?php foreach ( $rows as $row ) : ?>
			<li class="pw-event-add-ons__item">
				<?php if ( $row['label'] !== '' ) : ?>
					<h3 class="pw-event-add-ons__label"><?php echo esc_html( $row['label'] ); ?></h3>
				<?php endif; ?>
				<?php if ( trim( $row['desc'] ) !== '' ) : ?>
					<div class="pw-event-add-ons__description">
						<?php echo wp_kses_post( $row['desc'] ); ?>
					</div>
				<?php endif; ?>
				<?php if ( $row['price'] > 0 ) : ?>
					<p class="pw-event-add-ons__price">
						<?php echo esc_html( sprintf( '%s %s', number_format_i18n( $row['price'] ), $currency ) ); ?>
					</p>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'pw_after_event_add_ons_content', $post_id ); ?>
</section>

