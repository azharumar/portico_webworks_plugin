<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pw_register_contact_post_type() {
	$defaults = pw_child_cpt_defaults();

	register_post_type(
		'pw_contact',
		array_merge(
			$defaults,
			[
				'labels'             => pw_cpt_labels( 'Contact', 'Contacts' ),
				'menu_icon'          => 'dashicons-email',
				'show_in_menu'       => false,
				'show_in_rest'       => true,
				'rest_base'          => 'pw-contacts',
				'supports'           => [ 'title', 'custom-fields' ],
				'publicly_queryable' => false,
			]
		)
	);
}

add_action( 'init', 'pw_register_contact_post_type' );

/**
 * When an outlet is deleted, demote outlet-specific contacts to group-level and flag the label.
 *
 * @param int     $post_id Post ID being deleted.
 * @param WP_Post $post    Post object.
 */
function pw_contact_handle_outlet_deleted( $post_id, $post ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	$types = [ 'pw_restaurant', 'pw_spa', 'pw_meeting_room', 'pw_experience' ];
	if ( ! in_array( $post->post_type, $types, true ) ) {
		return;
	}
	$deleted_id = (int) $post_id;
	$contacts   = get_posts(
		[
			'post_type'              => 'pw_contact',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
			'meta_query'             => [
				[
					'key'     => '_pw_scope_id',
					'value'   => $deleted_id,
					'type'    => 'NUMERIC',
					'compare' => '=',
				],
			],
		]
	);
	$prefix = '[Unlinked] ';
	foreach ( $contacts as $c ) {
		update_post_meta( $c->ID, '_pw_scope_id', 0 );
		$label = (string) get_post_meta( $c->ID, '_pw_label', true );
		if ( $label !== '' && ! str_starts_with( $label, $prefix ) ) {
			update_post_meta( $c->ID, '_pw_label', $prefix . $label );
		}
	}
}

add_action( 'before_delete_post', 'pw_contact_handle_outlet_deleted', 10, 2 );
