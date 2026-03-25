<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$theatre   = (int) get_post_meta( $post_id, '_pw_capacity_theatre', true );
$classroom = (int) get_post_meta( $post_id, '_pw_capacity_classroom', true );
$boardroom = (int) get_post_meta( $post_id, '_pw_capacity_boardroom', true );
$ushape    = (int) get_post_meta( $post_id, '_pw_capacity_ushape', true );

$has_any = $theatre > 0 || $classroom > 0 || $boardroom > 0 || $ushape > 0;
if ( ! $has_any ) {
	return;
}
?>
<section class="pw-meeting-capacity-table" aria-labelledby="pw-meeting-capacity-table-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_meeting_room_capacity_table_content', $post_id ); ?>
	<h2 class="pw-meeting-capacity-table__heading" id="pw-meeting-capacity-table-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Capacity table', 'portico-webworks' ); ?>
	</h2>
	<table class="pw-meeting-capacity-table__table">
		<tbody>
			<?php if ( $theatre > 0 ) : ?>
				<tr class="pw-meeting-capacity-table__row">
					<th scope="row" class="pw-meeting-capacity-table__label"><?php echo esc_html__( 'Theatre', 'portico-webworks' ); ?></th>
					<td class="pw-meeting-capacity-table__value"><?php echo esc_html( (string) $theatre ); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ( $classroom > 0 ) : ?>
				<tr class="pw-meeting-capacity-table__row">
					<th scope="row" class="pw-meeting-capacity-table__label"><?php echo esc_html__( 'Classroom', 'portico-webworks' ); ?></th>
					<td class="pw-meeting-capacity-table__value"><?php echo esc_html( (string) $classroom ); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ( $boardroom > 0 ) : ?>
				<tr class="pw-meeting-capacity-table__row">
					<th scope="row" class="pw-meeting-capacity-table__label"><?php echo esc_html__( 'Boardroom', 'portico-webworks' ); ?></th>
					<td class="pw-meeting-capacity-table__value"><?php echo esc_html( (string) $boardroom ); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ( $ushape > 0 ) : ?>
				<tr class="pw-meeting-capacity-table__row">
					<th scope="row" class="pw-meeting-capacity-table__label"><?php echo esc_html__( 'U-Shape', 'portico-webworks' ); ?></th>
					<td class="pw-meeting-capacity-table__value"><?php echo esc_html( (string) $ushape ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<?php do_action( 'pw_after_meeting_room_capacity_table_content', $post_id ); ?>
</section>

