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
?>
<section class="pw-spa-treatments-menu" aria-labelledby="pw-spa-treatments-menu-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_spa_treatments_menu_content', $post_id ); ?>

	<h2 class="pw-spa-treatments-menu__heading" id="pw-spa-treatments-menu-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html__( 'Full treatments menu', 'portico-webworks' ); ?>
	</h2>

	<p class="pw-spa-treatments-menu__action">
		<a class="pw-spa-treatments-menu__link" href="<?php echo esc_url( $menu_url ); ?>">
			<?php echo esc_html__( 'View treatments', 'portico-webworks' ); ?>
		</a>
	</p>

	<?php do_action( 'pw_after_spa_treatments_menu_content', $post_id ); ?>
</section>

