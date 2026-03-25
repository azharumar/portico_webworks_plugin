<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$markup = apply_filters( 'pw_room_upgrade_prompt_markup', '', $post_id );
$markup = is_string( $markup ) ? $markup : '';

ob_start();
do_action( 'pw_room_upgrade_prompt_body', $post_id );
$buffer = ob_get_clean();
$buffer = is_string( $buffer ) ? $buffer : '';

if ( $markup === '' && trim( $buffer ) === '' ) {
	return;
}
?>
<section class="pw-room-upgrade-prompt" aria-labelledby="pw-room-upgrade-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_room_upgrade_prompt_content', $post_id ); ?>
	<h2 class="pw-room-upgrade-prompt__heading" id="pw-room-upgrade-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_room_upgrade_prompt_heading( $post_id ) ); ?>
	</h2>
	<?php
	if ( $markup !== '' ) {
		echo wp_kses_post( $markup );
	} else {
		echo wp_kses_post( $buffer );
	}
	?>
	<?php do_action( 'pw_after_room_upgrade_prompt_content', $post_id ); ?>
</section>
