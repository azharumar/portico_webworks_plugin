<?php
/**
 * Admin metabox: per-image category and caption for `_pw_gallery_meta` (JSON).
 *
 * Files touched: this file; loaded from portico_webworks_plugin.php.
 * Not touched: contact-post-type.php, property-rewrites.php, permalink-config.php.
 *
 * @package Portico_Webworks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post types that use `_pw_gallery` + `_pw_gallery_meta`.
 *
 * @return string[]
 */
function pw_gallery_meta_metabox_post_types() {
	return [
		'pw_property',
		'pw_room_type',
		'pw_restaurant',
		'pw_spa',
		'pw_meeting_room',
		'pw_experience',
		'pw_event',
	];
}

/**
 * @param WP_Post $post Post.
 */
function pw_render_gallery_meta_metabox( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$post_type = $post->post_type;
	wp_nonce_field( 'pw_save_gallery_meta', 'pw_gallery_meta_nonce' );

	$gallery = get_post_meta( $post->ID, '_pw_gallery', true );
	$ids     = [];
	foreach ( pw_normalize_gallery_attachment_ids( $gallery ) as $i ) {
		if ( get_post( $i ) && get_post_type( $i ) === 'attachment' ) {
			$ids[] = $i;
		}
	}

	$raw_meta = get_post_meta( $post->ID, '_pw_gallery_meta', true );
	$parsed   = [];
	if ( is_string( $raw_meta ) && $raw_meta !== '' ) {
		$d = json_decode( $raw_meta, true );
		if ( is_array( $d ) ) {
			$parsed = $d;
		}
	}

	$cats = pw_get_gallery_categories( $post_type );

	if ( $ids === [] ) {
		echo '<p class="description">' . esc_html__( 'Add images in the Gallery field above, then save the post to set categories and captions.', 'portico-webworks' ) . '</p>';
		return;
	}

	echo '<div class="pw-gallery-meta-rows" style="display:flex;flex-direction:column;gap:12px;">';
	foreach ( $ids as $aid ) {
		$key   = (string) $aid;
		$row   = isset( $parsed[ $key ] ) && is_array( $parsed[ $key ] ) ? $parsed[ $key ] : [];
		$cat   = isset( $row['category'] ) ? (string) $row['category'] : '';
		$cap   = isset( $row['caption'] ) ? (string) $row['caption'] : '';
		$thumb = wp_get_attachment_image( $aid, 'thumbnail', false, [ 'style' => 'max-width:80px;height:auto;vertical-align:top;' ] );
		echo '<div class="pw-gallery-meta-row" style="display:flex;gap:12px;align-items:flex-start;border:1px solid #c3c4c7;padding:8px;background:#fff;">';
		echo '<div style="flex-shrink:0;">' . $thumb . '</div>';
		echo '<div style="flex:1;min-width:0;">';
		echo '<p style="margin:0 0 6px;"><strong>' . esc_html( sprintf( /* translators: %d: attachment ID */ __( 'Image ID %d', 'portico-webworks' ), $aid ) ) . '</strong></p>';
		echo '<label style="display:block;margin-bottom:6px;"><span class="screen-reader-text">' . esc_html__( 'Category', 'portico-webworks' ) . '</span>';
		echo '<select name="pw_gallery_meta[' . esc_attr( $key ) . '][category]" style="max-width:100%;">';
		echo '<option value="">' . esc_html__( '— Category —', 'portico-webworks' ) . '</option>';
		foreach ( $cats as $slug => $label ) {
			echo '<option value="' . esc_attr( $slug ) . '"' . selected( $cat, $slug, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select></label>';
		echo '<label style="display:block;"><span class="screen-reader-text">' . esc_html__( 'Caption', 'portico-webworks' ) . '</span>';
		echo '<input type="text" class="widefat" name="pw_gallery_meta[' . esc_attr( $key ) . '][caption]" value="' . esc_attr( $cap ) . '" placeholder="' . esc_attr__( 'Caption', 'portico-webworks' ) . '" />';
		echo '</label>';
		echo '</div></div>';
	}
	echo '</div>';
}

/**
 * @param string $post_type Post type.
 */
function pw_register_gallery_meta_metabox_for_type( $post_type ) {
	add_meta_box(
		'pw_gallery_meta_details',
		__( 'Gallery details', 'portico-webworks' ),
		'pw_render_gallery_meta_metabox',
		$post_type,
		'normal',
		'default',
		[ '__block_editor_compatible_meta_box' => true ]
	);
}

add_action(
	'add_meta_boxes',
	static function () {
		foreach ( pw_gallery_meta_metabox_post_types() as $pt ) {
			pw_register_gallery_meta_metabox_for_type( $pt );
		}
	}
);

/**
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function pw_save_gallery_meta_metabox( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	if ( ! in_array( $post->post_type, pw_gallery_meta_metabox_post_types(), true ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['pw_gallery_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pw_gallery_meta_nonce'] ) ), 'pw_save_gallery_meta' ) ) {
		return;
	}

	$gallery = get_post_meta( $post_id, '_pw_gallery', true );
	$allowed = [];
	foreach ( pw_normalize_gallery_attachment_ids( $gallery ) as $i ) {
		$allowed[ (string) $i ] = true;
	}

	$incoming = isset( $_POST['pw_gallery_meta'] ) && is_array( $_POST['pw_gallery_meta'] ) ? wp_unslash( $_POST['pw_gallery_meta'] ) : [];
	$payload  = [];
	foreach ( $allowed as $key => $_true ) {
		if ( ! isset( $incoming[ $key ] ) || ! is_array( $incoming[ $key ] ) ) {
			$payload[ $key ] = [ 'category' => '', 'caption' => '' ];
			continue;
		}
		$payload[ $key ] = [
			'category' => isset( $incoming[ $key ]['category'] ) ? sanitize_text_field( (string) $incoming[ $key ]['category'] ) : '',
			'caption'  => isset( $incoming[ $key ]['caption'] ) ? sanitize_text_field( (string) $incoming[ $key ]['caption'] ) : '',
		];
	}

	$json = pw_sanitize_pw_gallery_meta_json( wp_json_encode( $payload ), $post->post_type );
	update_post_meta( $post_id, '_pw_gallery_meta', $json );
}

add_action( 'save_post', 'pw_save_gallery_meta_metabox', 25, 2 );
