<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$raw  = get_post_meta( $post_id, '_pw_gallery', true );
$ids  = [];
$first = 0;

if ( is_array( $raw ) ) {
	foreach ( $raw as $key => $val ) {
		$id = is_numeric( $key ) ? (int) $key : ( ( is_numeric( $val ) ) ? (int) $val : 0 );
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}
}
$ids   = array_values( array_unique( $ids ) );
$first = $ids[0] ?? 0;

if ( $first <= 0 ) {
	return;
}
?>
<section class="pw-meeting-room-hero" aria-labelledby="pw-meeting-room-hero-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_meeting_room_hero_content', $post_id ); ?>
	<h1 class="pw-meeting-room-hero__heading" id="pw-meeting-room-hero-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( get_the_title( $post_id ) ); ?>
	</h1>
	<div class="pw-meeting-room-hero__image">
		<?php
		echo wp_get_attachment_image(
			$first,
			'large',
			false,
			[
				'class'    => 'pw-meeting-room-hero__img',
				'loading'  => 'eager',
				'decoding' => 'async',
			]
		);
		?>
	</div>
	<?php do_action( 'pw_after_meeting_room_hero_content', $post_id ); ?>
</section>

