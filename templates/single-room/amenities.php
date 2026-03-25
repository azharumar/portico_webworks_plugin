<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$beds  = get_the_terms( $post_id, 'pw_bed_type' );
$views = get_the_terms( $post_id, 'pw_view_type' );

$feature_ids = get_post_meta( $post_id, '_pw_features', true );
if ( ! is_array( $feature_ids ) ) {
	$feature_ids = [];
}
$feature_ids = array_filter( array_map( 'intval', $feature_ids ) );

$has_terms    = ( is_array( $beds ) && $beds !== [] ) || ( is_array( $views ) && $views !== [] );
$has_features = $feature_ids !== [];

if ( ! $has_terms && ! $has_features ) {
	return;
}
?>
<section class="pw-room-amenities" aria-labelledby="pw-room-amenities-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_room_amenities_content', $post_id ); ?>
	<h2 class="pw-room-amenities__heading" id="pw-room-amenities-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_room_amenities_heading( $post_id ) ); ?>
	</h2>
	<?php if ( $has_terms ) : ?>
		<ul class="pw-room-amenities__taxonomy pw-room-amenities__taxonomy--terms">
			<?php
			if ( is_array( $beds ) ) {
				foreach ( $beds as $term ) {
					if ( ! $term instanceof WP_Term ) {
						continue;
					}
					echo '<li class="pw-room-amenities__term">' . esc_html( $term->name ) . '</li>';
				}
			}
			if ( is_array( $views ) ) {
				foreach ( $views as $term ) {
					if ( ! $term instanceof WP_Term ) {
						continue;
					}
					echo '<li class="pw-room-amenities__term">' . esc_html( $term->name ) . '</li>';
				}
			}
			?>
		</ul>
	<?php endif; ?>
	<?php if ( $has_features ) : ?>
		<ul class="pw-room-amenities__features">
			<?php
			foreach ( $feature_ids as $fid ) {
				$title = get_the_title( $fid );
				if ( ! is_string( $title ) || $title === '' ) {
					continue;
				}
				echo '<li class="pw-room-amenities__feature">' . esc_html( $title ) . '</li>';
			}
			?>
		</ul>
	<?php endif; ?>
	<?php do_action( 'pw_after_room_amenities_content', $post_id ); ?>
</section>
