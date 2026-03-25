<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$menu_url = (string) get_post_meta( $post_id, '_pw_menu_url', true );
if ( $menu_url === '' ) {
	return;
}

$cuisine = (string) get_post_meta( $post_id, '_pw_cuisine_type', true );

?>
<section class="pw-restaurant-menu-preview" aria-labelledby="pw-restaurant-menu-preview-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_restaurant_menu_preview_content', $post_id ); ?>
	<h2 class="pw-restaurant-menu-preview__heading" id="pw-restaurant-menu-preview-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( pw_get_restaurant_menu_preview_heading( $post_id ) ); ?>
	</h2>
	<div class="pw-restaurant-menu-preview__body">
		<p class="pw-restaurant-menu-preview__link">
			<a class="pw-restaurant-menu-preview__anchor" href="<?php echo esc_url( $menu_url ); ?>">
				<?php echo esc_html( pw_get_restaurant_menu_preview_menu_label( $post_id ) ); ?>
			</a>
		</p>
		<?php if ( $cuisine !== '' ) : ?>
			<p class="pw-restaurant-menu-preview__meta">
				<span class="pw-restaurant-menu-preview__meta-label"><?php echo esc_html( pw_get_restaurant_cuisine_label( $post_id ) ); ?>:</span>
				<span class="pw-restaurant-menu-preview__meta-value"><?php echo esc_html( $cuisine ); ?></span>
			</p>
		<?php endif; ?>
</div>
	<?php do_action( 'pw_after_restaurant_menu_preview_content', $post_id ); ?>
</section>

