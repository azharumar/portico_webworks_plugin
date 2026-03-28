<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pw_register_child_post_types() {
}

function pw_register_section_cpt_permastructs(): void {
	$mode = pw_get_setting( 'pw_property_mode', 'single' );
	foreach ( pw_url_section_cpts() as $cpt ) {
		$singular = pw_get_section_base( $cpt, 'singular' );
		if ( $singular === '' ) {
			continue;
		}
		$slug_tag = '%' . $cpt . '%';
		if ( $mode === 'single' ) {
			$struct = '/' . $singular . '/' . $slug_tag;
		} else {
			$struct = '/%pw_property_slug%/' . $singular . '/' . $slug_tag;
		}
		add_permastruct(
			$cpt,
			$struct,
			[
				'with_front' => false,
				'ep_mask'    => EP_NONE,
				'paged'      => false,
				'feed'       => false,
				'forpage'    => false,
				'walk_dirs'  => false,
			]
		);
	}
}

add_action( 'init', 'pw_register_section_cpt_permastructs', 12 );

function pw_register_child_taxonomies() {
	$shared = [
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => false,
	];

	register_taxonomy( 'pw_property_type', 'pw_property', array_merge( $shared, [
		'label' => 'Property Types',
	] ) );
}

function pw_register_child_post_meta() {
	$facet_item_schema = [
		'type'       => 'object',
		'properties' => [
			'key'    => [ 'type' => 'string' ],
			'status' => [ 'type' => 'string', 'enum' => [ 'unknown', 'available', 'not_available' ] ],
			'note'   => [ 'type' => 'string' ],
		],
	];

	register_post_meta( 'pw_property', PW_SUSTAINABILITY_ITEMS_META_KEY, [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => $facet_item_schema,
			],
		],
	] );

	register_post_meta( 'pw_property', PW_ACCESSIBILITY_ITEMS_META_KEY, [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => $facet_item_schema,
			],
		],
	] );

	register_post_meta( 'pw_property', '_pw_certifications', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [
					'type'       => 'object',
					'properties' => [
						'name'   => [ 'type' => 'string' ],
						'issuer' => [ 'type' => 'string' ],
						'year'   => [ 'type' => 'integer' ],
						'url'    => [ 'type' => 'string' ],
					],
				],
			],
		],
	] );

	register_post_meta( 'pw_property', '_pw_gallery', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [ 'type' => 'integer' ],
			],
		],
	] );

	register_post_meta( 'pw_property', '_pw_pools', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [
					'type'       => 'object',
					'properties' => [
						'name'          => [ 'type' => 'string' ],
						'length_m'      => [ 'type' => 'number' ],
						'width_m'       => [ 'type' => 'number' ],
						'depth_m'       => [ 'type' => 'number' ],
						'open_time'     => [ 'type' => 'string' ],
						'close_time'    => [ 'type' => 'string' ],
						'is_heated'     => [ 'type' => 'boolean' ],
						'is_kids'       => [ 'type' => 'boolean' ],
						'is_indoor'     => [ 'type' => 'boolean' ],
						'is_infinity'   => [ 'type' => 'boolean' ],
						'attachment_id' => [ 'type' => 'integer' ],
					],
				],
			],
		],
	] );

	register_post_meta( 'pw_property', '_pw_direct_benefits', [
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [
				'type'  => 'array',
				'items' => [
					'type'       => 'object',
					'properties' => [
						'title'       => [ 'type' => 'string' ],
						'description' => [ 'type' => 'string' ],
						'icon'        => [ 'type' => 'string' ],
					],
				],
			],
		],
	] );
}

add_action( 'init', 'pw_register_child_taxonomies', 5 );
add_action( 'init', 'pw_register_child_post_types' );
add_action( 'init', 'pw_register_child_post_meta' );

function pw_remove_cpt_submenus() {
	foreach ( [ 'pw_property' ] as $cpt ) {
		remove_submenu_page( pw_admin_page_slug(), 'edit.php?post_type=' . $cpt );
		remove_submenu_page( pw_admin_page_slug(), 'post-new.php?post_type=' . $cpt );
	}
}

add_action( 'admin_menu', 'pw_remove_cpt_submenus', 999 );
