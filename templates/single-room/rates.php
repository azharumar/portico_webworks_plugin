<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$rows = get_post_meta( $post_id, '_pw_rates', true );
if ( ! is_array( $rows ) || $rows === [] ) {
	return;
}

$prop_id  = (int) get_post_meta( $post_id, '_pw_property_id', true );
$currency = $prop_id > 0 ? pw_get_property_currency( $prop_id ) : pw_get_property_currency();
?>
<section class="pw-room-rates" aria-labelledby="pw-room-rates-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_room_rates_content', $post_id ); ?>
	<h2 class="pw-room-rates__heading" id="pw-room-rates-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_room_rates_heading( $post_id ) ); ?>
	</h2>
	<table class="pw-room-rates__table">
		<thead class="pw-room-rates__thead">
			<tr class="pw-room-rates__row pw-room-rates__row--head">
				<th class="pw-room-rates__cell pw-room-rates__cell--label" scope="col">
					<?php echo esc_html( pw_get_room_rates_plan_label( $post_id ) ); ?>
				</th>
				<th class="pw-room-rates__cell pw-room-rates__cell--price" scope="col">
					<?php echo esc_html( pw_get_room_rates_price_label( $post_id ) ); ?>
				</th>
			</tr>
		</thead>
		<tbody class="pw-room-rates__tbody">
			<?php
			foreach ( $rows as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$label = isset( $row['rate_label'] ) ? (string) $row['rate_label'] : '';
				$price = isset( $row['price'] ) ? (float) $row['price'] : 0;
				if ( $label === '' && $price <= 0 ) {
					continue;
				}
				echo '<tr class="pw-room-rates__row">';
				echo '<td class="pw-room-rates__cell">' . esc_html( $label !== '' ? $label : '—' ) . '</td>';
				echo '<td class="pw-room-rates__cell pw-room-rates__cell--numeric">';
				if ( $price > 0 ) {
					echo esc_html( sprintf( '%s %s', number_format_i18n( $price ), $currency ) );
				} else {
					echo esc_html( '—' );
				}
				echo '</td></tr>';
			}
			?>
		</tbody>
	</table>
	<?php do_action( 'pw_after_room_rates_content', $post_id ); ?>
</section>
