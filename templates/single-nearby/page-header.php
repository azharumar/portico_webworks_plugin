<?php
defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : (int) get_the_ID();
if ( $post_id <= 0 ) {
	return;
}

$terms = get_the_terms( $post_id, 'pw_nearby_type' );
$term  = ( $terms && is_array( $terms ) ) ? ( $terms[0] ?? null ) : null;
if ( ! $term instanceof WP_Term ) {
	$term = null;
}
?>
<section class="pw-nearby-page-header" aria-labelledby="pw-nearby-page-header-heading-<?php echo esc_attr( (string) $post_id ); ?>">
	<?php do_action( 'pw_before_nearby_page_header_content', $post_id ); ?>
	<h1 class="pw-nearby-page-header__heading" id="pw-nearby-page-header-heading-<?php echo esc_attr( (string) $post_id ); ?>">
		<?php echo esc_html( get_the_title( $post_id ) ); ?>
	</h1>
	<?php if ( $term ) : ?>
		<p class="pw-nearby-page-header__category"><?php echo esc_html( $term->name ); ?></p>
	<?php endif; ?>
	<?php do_action( 'pw_after_nearby_page_header_content', $post_id ); ?>
</section>

