<?php
defined( 'ABSPATH' ) || exit;

$property_id = (int) pw_get_current_property_id();
if ( $property_id <= 0 ) {
	return;
}

$text = (string) pw_property_get_announcement_bar( $property_id );
if ( trim( $text ) === '' ) {
	return;
}
?>
<div class="pw-announcement-bar">
	<p class="pw-announcement-bar__text">
		<?php echo wp_kses_post( $text ); ?>
	</p>
</div>
