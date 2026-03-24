<?php
/**
 * Idempotent installer for plugin-managed pages and GeneratePress Elements (_pw_generated).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PW_FACT_SHEET_PAGE_SLUG' ) ) {
	define( 'PW_FACT_SHEET_PAGE_SLUG', 'fact-sheet' );
}

add_action( 'init', 'pw_register_page_installer_meta', 8 );

/**
 * Register page meta used by property-scoped routing and the installer.
 */
function pw_register_page_installer_meta() {
	$registered = static function ( string $key ): bool {
		return function_exists( 'wp_is_post_meta_registered' ) && wp_is_post_meta_registered( 'page', $key );
	};

	if ( ! $registered( '_pw_property_id' ) ) {
		register_post_meta(
			'page',
			'_pw_property_id',
			[
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => 0,
			]
		);
	}

	if ( ! $registered( '_pw_generated' ) ) {
		register_post_meta(
			'page',
			'_pw_generated',
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => '',
			]
		);
	}

	if ( ! $registered( '_pw_section_cpt' ) ) {
		register_post_meta(
			'page',
			'_pw_section_cpt',
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => '',
			]
		);
	}

	if ( ! $registered( '_pw_static_url_segment' ) ) {
		register_post_meta(
			'page',
			'_pw_static_url_segment',
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => '',
			]
		);
	}
}

/**
 * @return array<int, array{title: string, slug: string, property_id: int, cpt: string, type: string, kind?: string}>
 */
function pw_get_required_pages( int $property_id = 0 ): array {
	$mode = pw_get_setting( 'pw_property_mode', 'single' );
	$slug = PW_FACT_SHEET_PAGE_SLUG;
	$def  = [
		'title'       => __( 'Fact Sheet', 'portico-webworks' ),
		'slug'        => $slug,
		'property_id' => 0,
		'cpt'         => '',
		'type'        => '',
		'kind'        => 'fact_sheet',
	];

	if ( $mode === 'single' ) {
		if ( $property_id !== 0 ) {
			return [];
		}
		$def['property_id'] = 0;

		return [ $def ];
	}

	if ( $property_id <= 0 ) {
		return [];
	}
	$def['property_id'] = $property_id;

	return [ $def ];
}

/**
 * @return string Block markup for a new Fact Sheet page; empty if the sample file is missing (installer still creates the page).
 */
function pw_get_fact_sheet_starter_markup(): string {
	$path = plugin_dir_path( PW_PLUGIN_FILE ) . 'gb-pro-markup-samples.html';
	if ( ! is_readable( $path ) ) {
		return '';
	}
	$raw = file_get_contents( $path );
	if ( ! is_string( $raw ) || $raw === '' ) {
		return '';
	}

	return $raw;
}

/**
 * @return array<int, array{title: string, cpt: string, slug: string, type: string}>
 */
function pw_get_required_elements(): array {
	$archive_titles = [
		'pw_room_type'    => __( 'Rooms Archive', 'portico-webworks' ),
		'pw_restaurant'   => __( 'Restaurants Archive', 'portico-webworks' ),
		'pw_spa'          => __( 'Spas Archive', 'portico-webworks' ),
		'pw_meeting_room' => __( 'Meetings Archive', 'portico-webworks' ),
		'pw_experience'   => __( 'Experiences Archive', 'portico-webworks' ),
		'pw_event'        => __( 'Events Archive', 'portico-webworks' ),
		'pw_offer'        => __( 'Offers Archive', 'portico-webworks' ),
		'pw_nearby'       => __( 'Places Archive', 'portico-webworks' ),
	];

	$singular_titles = [
		'pw_room_type'    => __( 'Room', 'portico-webworks' ),
		'pw_restaurant'   => __( 'Restaurant', 'portico-webworks' ),
		'pw_spa'          => __( 'Spa', 'portico-webworks' ),
		'pw_meeting_room' => __( 'Meeting Room', 'portico-webworks' ),
		'pw_experience'   => __( 'Experience', 'portico-webworks' ),
		'pw_event'        => __( 'Event', 'portico-webworks' ),
		'pw_offer'        => __( 'Offer', 'portico-webworks' ),
		'pw_nearby'       => __( 'Place', 'portico-webworks' ),
	];

	$singular_slugs = [
		'pw_room_type'    => 'pw-room-singular',
		'pw_restaurant'   => 'pw-restaurant-singular',
		'pw_spa'          => 'pw-spa-singular',
		'pw_meeting_room' => 'pw-meeting-singular',
		'pw_experience'   => 'pw-experience-singular',
		'pw_event'        => 'pw-event-singular',
		'pw_offer'        => 'pw-offer-singular',
		'pw_nearby'       => 'pw-place-singular',
	];

	$out = [];

	$out[] = [
		'title' => __( 'Property', 'portico-webworks' ),
		'cpt'   => 'pw_property',
		'slug'  => 'pw-property-singular',
		'type'  => 'singular',
	];

	foreach ( pw_url_section_cpts() as $cpt ) {
		$pl = pw_get_section_base( $cpt, 'plural' );
		if ( $pl === '' ) {
			continue;
		}
		$out[] = [
			'title' => $archive_titles[ $cpt ] ?? $cpt,
			'cpt'   => $cpt,
			'slug'  => 'pw-' . $pl . '-archive',
			'type'  => 'archive',
		];
		$out[] = [
			'title' => $singular_titles[ $cpt ] ?? $cpt,
			'cpt'   => $cpt,
			'slug'  => $singular_slugs[ $cpt ] ?? 'pw-' . $pl . '-singular',
			'type'  => 'singular',
		];
	}

	return $out;
}

/**
 * Repair _generate_block_type, `_generate_element_display_conditions`, and legacy hash / section hrefs on plugin-generated gp_elements.
 */
function pw_repair_element_block_types(): void {
	if ( ! post_type_exists( 'gp_elements' ) ) {
		return;
	}

	$elements = get_posts(
		[
			'post_type'        => 'gp_elements',
			'post_status'      => [ 'publish', 'draft', 'private' ],
			'posts_per_page'   => -1,
			'meta_key'         => '_pw_generated',
			'meta_value'       => '1',
			'no_found_rows'    => true,
			'suppress_filters' => true,
		]
	);

	$section_hashes = [ '#rooms', '#restaurants', '#spas', '#meetings', '#experiences', '#events', '#offers', '#places' ];

	$property_hash_map = [
		'"#rooms"'        => '"{{pw_section_url:pw_room_type}}"',
		'"#restaurants"'  => '"{{pw_section_url:pw_restaurant}}"',
		'"#spas"'         => '"{{pw_section_url:pw_spa}}"',
		'"#meetings"'     => '"{{pw_section_url:pw_meeting_room}}"',
		'"#experiences"'  => '"{{pw_section_url:pw_experience}}"',
		'"#events"'       => '"{{pw_section_url:pw_event}}"',
		'"#offers"'       => '"{{pw_section_url:pw_offer}}"',
		'"#places"'       => '"{{pw_section_url:pw_nearby}}"',
	];

	foreach ( $elements as $el ) {
		if ( ! $el instanceof WP_Post ) {
			continue;
		}

		$id            = (int) $el->ID;
		$element_type  = (string) get_post_meta( $id, '_pw_element_type', true );
		$section_cpt   = (string) get_post_meta( $id, '_pw_section_cpt', true );

		if ( $element_type === 'singular' ) {
			$current = get_post_meta( $id, '_generate_block_type', true );
			if ( $current !== 'content-template' ) {
				update_post_meta( $id, '_generate_block_type', 'content-template' );
			}
		}

		if ( $element_type === 'archive' ) {
			$current = get_post_meta( $id, '_generate_block_type', true );
			if ( $current !== 'loop-template' ) {
				update_post_meta( $id, '_generate_block_type', 'loop-template' );
			}
		}

		if ( ( $element_type === 'singular' || $element_type === 'archive' ) && $section_cpt !== '' ) {
			$display_rows = pw_build_element_display_conditions(
				[
					'cpt'  => $section_cpt,
					'type' => $element_type,
				]
			);
			if ( $display_rows !== [] ) {
				update_post_meta( $id, '_generate_element_display_conditions', $display_rows );
			}
		}

		if ( $element_type !== 'singular' ) {
			continue;
		}

		$content      = (string) get_post_field( 'post_content', $id, 'raw' );
		$new_content  = $content;
		$needs_update = false;

		if ( $section_cpt === 'pw_property' ) {
			$new_content = str_replace( array_keys( $property_hash_map ), array_values( $property_hash_map ), $new_content );
			if ( $new_content !== $content ) {
				$needs_update = true;
			}
		} else {
			foreach ( $section_hashes as $hash ) {
				$quoted = '"' . $hash . '"';
				if ( strpos( $new_content, $quoted ) !== false ) {
					$new_content = str_replace( $quoted, '"{{post_type_archive_link}}"', $new_content );
					$needs_update = true;
				}
			}
		}

		if ( $needs_update ) {
			wp_update_post(
				wp_slash(
					[
						'ID'           => $id,
						'post_content' => $new_content,
					]
				)
			);
		}
	}
}

/**
 * Generated GP Element for a section CPT + type (_pw_generated, _pw_section_cpt, _pw_element_type).
 */
function pw_find_generated_element( string $cpt, string $type = 'archive' ): ?WP_Post {
	$cpt = sanitize_key( $cpt );
	if ( $cpt === '' ) {
		return null;
	}

	$slug = '';
	foreach ( pw_get_required_elements() as $def ) {
		if ( ( $def['cpt'] ?? '' ) === $cpt && ( $def['type'] ?? 'archive' ) === $type ) {
			$slug = sanitize_title( (string) ( $def['slug'] ?? '' ) );
			break;
		}
	}
	if ( $slug === '' || ! post_type_exists( 'gp_elements' ) ) {
		return null;
	}

	$meta_query = [
		[
			'key'   => '_pw_generated',
			'value' => '1',
		],
		[
			'key'   => '_pw_section_cpt',
			'value' => $cpt,
		],
	];

	if ( $type === 'archive' ) {
		$meta_query[] = [
			'relation' => 'OR',
			[
				'key'   => '_pw_element_type',
				'value' => 'archive',
			],
			[
				'key'     => '_pw_element_type',
				'compare' => 'NOT EXISTS',
			],
		];
	} else {
		$meta_query[] = [
			'key'   => '_pw_element_type',
			'value' => $type,
		];
	}

	$posts = get_posts(
		[
			'post_type'        => 'gp_elements',
			'name'             => $slug,
			'post_status'      => [ 'publish', 'draft', 'private' ],
			'meta_query'       => $meta_query,
			'posts_per_page'   => 1,
			'no_found_rows'    => true,
			'suppress_filters' => true,
		]
	);

	if ( empty( $posts ) ) {
		return null;
	}

	$p = $posts[0];
	return $p instanceof WP_Post ? $p : null;
}

/**
 * @return array<int, array{type: string, rule: string, object: string}>
 */
function pw_build_element_conditions( array $element_def ): array {
	$cpt  = (string) ( $element_def['cpt'] ?? '' );
	$type = (string) ( $element_def['type'] ?? 'archive' );

	if ( $type === 'singular' ) {
		return [
			[
				'type'   => 'basic',
				'rule'   => 'is_singular',
				'object' => $cpt,
			],
		];
	}

	return [
		[
			'type'   => 'basic',
			'rule'   => 'is_post_type_archive',
			'object' => $cpt,
		],
	];
}

/**
 * GP Premium `GeneratePress_Block_Element` uses `_generate_element_display_conditions` (Location rules), not `_generate_element_conditions`.
 *
 * @return array<int, array{rule: string, object: string}>
 */
function pw_build_element_display_conditions( array $element_def ): array {
	$cpt  = sanitize_key( (string) ( $element_def['cpt'] ?? '' ) );
	$type = (string) ( $element_def['type'] ?? 'archive' );

	if ( $cpt === '' ) {
		return [];
	}

	if ( $type === 'singular' ) {
		return [
			[
				'rule'   => 'post:' . $cpt,
				'object' => '',
			],
		];
	}

	return [
		[
			'rule'   => 'archive:' . $cpt,
			'object' => '',
		],
	];
}

/**
 * @return array{action: 'created'|'skipped', post_id: int, message: string}
 */
function pw_install_element( array $element_def ): array {
	$cpt   = sanitize_key( (string) ( $element_def['cpt'] ?? '' ) );
	$title = (string) ( $element_def['title'] ?? '' );
	$slug  = sanitize_title( (string) ( $element_def['slug'] ?? '' ) );

	if ( $cpt === '' || $slug === '' ) {
		return [
			'action'  => 'skipped',
			'post_id' => 0,
			'message' => __( 'Invalid element definition.', 'portico-webworks' ),
		];
	}

	$type = (string) ( $element_def['type'] ?? 'archive' );

	$existing = pw_find_generated_element( $cpt, $type );
	if ( $existing ) {
		return [
			'action'  => 'skipped',
			'post_id' => (int) $existing->ID,
			'message' => __( 'Already exists, no changes needed.', 'portico-webworks' ),
		];
	}

	$content = pw_get_section_starter_markup( $cpt, $type );
	if ( $content === '' ) {
		return [
			'action'  => 'skipped',
			'post_id' => 0,
			'message' => sprintf(
				/* translators: %s: CPT slug */
				__( 'No starter markup for %s.', 'portico-webworks' ),
				$cpt
			),
		];
	}

	$post_id = wp_insert_post(
		wp_slash(
			[
				'post_title'   => $title !== '' ? $title : $slug,
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'gp_elements',
				'post_content' => $content,
			]
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return [
			'action'  => 'skipped',
			'post_id' => 0,
			'message' => $post_id->get_error_message(),
		];
	}

	$block_type = $type === 'singular' ? 'content-template' : 'loop-template';

	$post_id = (int) $post_id;
	update_post_meta( $post_id, '_generate_element_type', 'block' );
	update_post_meta( $post_id, '_generate_block_type', $block_type );
	update_post_meta( $post_id, '_pw_generated', '1' );
	update_post_meta( $post_id, '_pw_section_cpt', $cpt );
	update_post_meta( $post_id, '_pw_element_type', $type );
	update_post_meta( $post_id, '_generate_element_conditions', pw_build_element_conditions( $element_def ) );
	update_post_meta( $post_id, '_generate_element_display_conditions', pw_build_element_display_conditions( $element_def ) );
	update_post_meta( $post_id, '_generate_element_is_content', '' );

	return [
		'action'  => 'created',
		'post_id' => $post_id,
		'message' => sprintf(
			/* translators: 1: element title, 2: slug */
			__( "Created element '%1\$s' (%2\$s)", 'portico-webworks' ),
			$title !== '' ? $title : $slug,
			$slug
		),
	];
}

/**
 * @return array<int, array{action: string, post_id: int, message: string}>
 */
function pw_run_elements_installer(): array {
	if ( ! post_type_exists( 'gp_elements' ) ) {
		set_transient(
			'pw_installer_gp_elements_notice',
			__( 'GeneratePress Elements not found. Section archive templates were not created. Activate GeneratePress Premium to generate them.', 'portico-webworks' ),
			120
		);

		return [];
	}

	delete_transient( 'pw_installer_gp_elements_notice' );

	$results = [];
	foreach ( pw_get_required_elements() as $def ) {
		$results[] = pw_install_element( $def );
	}

	return $results;
}

/**
 * @return array<int, array{title: string, slug: string, property_id: int, cpt: string, type: string, property_label: string}>
 */
function pw_get_page_structure_display_rows(): array {
	$rows = [];
	$mode = pw_get_setting( 'pw_property_mode', 'single' );

	if ( $mode === 'single' ) {
		foreach ( pw_get_required_pages( 0 ) as $def ) {
			$def['property_label'] = __( '(site-level)', 'portico-webworks' );
			$rows[]                = $def;
		}
		return $rows;
	}

	foreach ( pw_get_required_pages( 0 ) as $def ) {
		$def['property_label'] = __( '(site-level)', 'portico-webworks' );
		$rows[]                = $def;
	}

	$prop_ids = get_posts(
		[
			'post_type'      => 'pw_property',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'title',
			'order'          => 'ASC',
		]
	);

	foreach ( $prop_ids as $pid ) {
		$pid = (int) $pid;
		$label = get_the_title( $pid );
		foreach ( pw_get_required_pages( $pid ) as $def ) {
			$def['property_label'] = $label !== '' ? $label : '#' . $pid;
			$rows[]                = $def;
		}
	}

	return $rows;
}

/**
 * Installer page: published/draft/private, slug match, _pw_generated === '1', _pw_property_id match.
 * Fact Sheet: if `post_name` was uniquified (multi-property), falls back to `_pw_static_url_segment` + same metas.
 * Room-type starter omits pw_bed_type / pw_view_type (no stock GB Pro term-list tag in query loops).
 * Meeting-room starter uses _pw_sales_email / _pw_sales_phone (registered on pw_meeting_room).
 */
function pw_find_generated_page( string $slug, int $property_id ): ?WP_Post {
	$slug = sanitize_title( $slug );
	if ( $slug === '' ) {
		return null;
	}

	$posts = get_posts(
		[
			'post_type'        => 'page',
			'name'             => $slug,
			'post_status'      => [ 'publish', 'draft', 'private' ],
			'meta_query'       => [
				[
					'key'   => '_pw_generated',
					'value' => '1',
				],
				[
					'key'   => '_pw_property_id',
					'value' => (int) $property_id,
				],
			],
			'posts_per_page'   => 1,
			'no_found_rows'    => true,
			'suppress_filters' => true,
		]
	);

	if ( empty( $posts ) && defined( 'PW_FACT_SHEET_PAGE_SLUG' ) && $slug === PW_FACT_SHEET_PAGE_SLUG ) {
		$posts = get_posts(
			[
				'post_type'        => 'page',
				'post_status'      => [ 'publish', 'draft', 'private' ],
				'meta_query'       => [
					[
						'key'   => '_pw_generated',
						'value' => '1',
					],
					[
						'key'   => '_pw_property_id',
						'value' => (int) $property_id,
					],
					[
						'key'   => '_pw_static_url_segment',
						'value' => $slug,
					],
				],
				'posts_per_page'   => 1,
				'no_found_rows'    => true,
				'suppress_filters' => true,
			]
		);
	}

	if ( empty( $posts ) ) {
		return null;
	}

	$p = $posts[0];
	return $p instanceof WP_Post ? $p : null;
}

/**
 * @return array{action: 'created'|'updated'|'skipped'|'conflict', post_id: int, message: string}
 */
function pw_install_page( array $page_def ): array {
	$slug        = sanitize_title( (string) ( $page_def['slug'] ?? '' ) );
	$prop_id     = (int) ( $page_def['property_id'] ?? 0 );
	$cpt         = (string) ( $page_def['cpt'] ?? '' );
	$title       = (string) ( $page_def['title'] ?? $slug );

	if ( $slug === '' ) {
		return [
			'action'  => 'skipped',
			'post_id' => 0,
			'message' => __( 'Empty slug.', 'portico-webworks' ),
		];
	}

	$existing = pw_find_generated_page( $slug, $prop_id );

	if ( ! $existing ) {
		// Conflict check only — not used for front-end routing.
		// get_page_by_path() is permitted here per installer design.
		$conflict = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $conflict instanceof WP_Post && get_post_meta( $conflict->ID, '_pw_generated', true ) !== '1' ) {
			return [
				'action'  => 'conflict',
				'post_id' => 0,
				'message' => sprintf(
					/* translators: 1: slug, 2: page title, 3: post ID */
					__( "Slug '%1\$s' is already used by page '%2\$s' (ID %3\$d). Resolve this conflict before the installer can create this page.", 'portico-webworks' ),
					$slug,
					$conflict->post_title,
					(int) $conflict->ID
				),
			];
		}

		$kind = (string) ( $page_def['kind'] ?? '' );
		if ( $kind === 'fact_sheet' ) {
			$content = pw_get_fact_sheet_starter_markup();
		} elseif ( $cpt !== '' && in_array( $cpt, pw_url_section_cpts(), true ) ) {
			$content = pw_get_section_starter_markup( $cpt );
		} else {
			$content = '';
		}

		$post_id = wp_insert_post(
			wp_slash(
				[
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => $content,
				]
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return [
				'action'  => 'skipped',
				'post_id' => 0,
				'message' => $post_id->get_error_message(),
			];
		}

		$post_id = (int) $post_id;
		update_post_meta( $post_id, '_pw_property_id', $prop_id );
		update_post_meta( $post_id, '_pw_generated', '1' );
		update_post_meta( $post_id, '_pw_section_cpt', $cpt );
		if ( $kind === 'fact_sheet' ) {
			update_post_meta( $post_id, '_pw_static_url_segment', PW_FACT_SHEET_PAGE_SLUG );
		}

		return [
			'action'  => 'created',
			'post_id' => $post_id,
			'message' => sprintf(
				/* translators: 1: title, 2: slug */
				__( "Created '%1\$s' at /%2\$s", 'portico-webworks' ),
				$title,
				$slug
			),
		];
	}

	if ( $existing->post_name !== $slug ) {
		$updated = wp_update_post(
			wp_slash(
				[
					'ID'        => $existing->ID,
					'post_name' => $slug,
				]
			),
			true
		);
		if ( is_wp_error( $updated ) ) {
			return [
				'action'  => 'skipped',
				'post_id' => (int) $existing->ID,
				'message' => $updated->get_error_message(),
			];
		}
		return [
			'action'  => 'updated',
			'post_id' => (int) $existing->ID,
			'message' => sprintf(
				/* translators: %s: new slug */
				__( "Updated slug to '%s'", 'portico-webworks' ),
				$slug
			),
		];
	}

	return [
		'action'  => 'skipped',
		'post_id' => (int) $existing->ID,
		'message' => __( 'Already exists, no changes needed.', 'portico-webworks' ),
	];
}

/**
 * @return array<int, array{action: string, post_id: int, message: string}>
 */
function pw_run_page_installer( int $property_id = 0 ): array {
	$results = [];
	foreach ( pw_get_required_pages( $property_id ) as $def ) {
		$results[] = pw_install_page( $def );
	}

	delete_transient( 'pw_flush_rewrites' );
	set_transient( 'pw_flush_rewrites', '1', 60 );

	return $results;
}

/**
 * @return array<int, array{action: string, post_id: int, message: string}>
 */
function pw_run_page_installer_all_scopes(): array {
	$all = pw_run_page_installer( 0 );
	if ( pw_get_setting( 'pw_property_mode', 'single' ) === 'multi' ) {
		$ids = get_posts(
			[
				'post_type'      => 'pw_property',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);
		foreach ( $ids as $pid ) {
			$all = array_merge( $all, pw_run_page_installer( (int) $pid ) );
		}
	}

	return array_merge( $all, pw_run_elements_installer() );
}

/**
 * @param array<int, array{action: string, post_id: int, message: string}> $results
 * @return array{created: int, updated: int, unchanged: int, conflict: int, conflict_messages: string[]}
 */
function pw_summarize_installer_results( array $results ): array {
	$out = [
		'created'            => 0,
		'updated'            => 0,
		'unchanged'          => 0,
		'conflict'           => 0,
		'conflict_messages'  => [],
	];

	foreach ( $results as $r ) {
		if ( ! is_array( $r ) || ! isset( $r['action'] ) ) {
			continue;
		}
		$msg = isset( $r['message'] ) ? (string) $r['message'] : '';
		if ( $r['action'] === 'created' ) {
			++$out['created'];
		} elseif ( $r['action'] === 'updated' ) {
			++$out['updated'];
		} elseif ( $r['action'] === 'conflict' ) {
			++$out['conflict'];
			if ( $msg !== '' ) {
				$out['conflict_messages'][] = $msg;
			}
		} elseif ( $r['action'] === 'skipped' ) {
			++$out['unchanged'];
		}
	}

	return $out;
}

function pw_on_property_published( $new_status, $old_status, $post ) {
	if ( ! $post instanceof WP_Post || $post->post_type !== 'pw_property' ) {
		return;
	}
	if ( $new_status !== 'publish' || $old_status === 'publish' ) {
		return;
	}

	$mode = pw_get_setting( 'pw_property_mode', 'single' );
	if ( $mode === 'multi' ) {
		pw_run_page_installer( (int) $post->ID );
	} else {
		pw_run_page_installer( 0 );
	}
	pw_run_elements_installer();

	$titles = [];
	foreach ( pw_get_required_elements() as $d ) {
		if ( isset( $d['title'] ) ) {
			$titles[] = (string) $d['title'];
		}
	}

	set_transient(
		'pw_installer_last_run',
		[
			'property_id'    => (int) $post->ID,
			'property_title' => get_the_title( $post ),
			'page_titles'    => $titles,
		],
		300
	);
}

/**
 * GenerateBlocks starter post_content for section CPT archive loop templates (insert only).
 * Derived from gb-pro-markup-samples.html. Room type: pw_bed_type / pw_view_type omitted (no verified GB Pro term-list tag in loops).
 *
 * @param string $cpt Section CPT or empty.
 */
function pw_get_section_starter_markup( string $cpt, string $type = 'archive' ): string {
	if ( $type === 'singular' ) {
		return _pw_get_singular_starter_markup( $cpt );
	}

	if ( $cpt === '' || ! in_array( $cpt, pw_url_section_cpts(), true ) ) {
		return '';
	}

	return match ( $cpt ) {
		'pw_room_type' => <<<'PW_ST_ROOM_TYPE'
<!-- wp:generateblocks/query {"uniqueId":"rmq","tagName":"div","query":{"post_type":["pw_room_type"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/looper {"uniqueId":"rm-loop","tagName":"div","className":"gb-loop-rm-loop"} -->
<div class="gb-looper-rm-loop gb-loop-rm-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"rm-item","tagName":"div","className":"gb-li-rm-item"} -->
<div class="gb-loop-item gb-loop-item-rm-item gb-li-rm-item">
<!-- wp:generateblocks/element {"uniqueId":"rtlsp","tagName":"div","styles":{"marginBottom":"32px","paddingBottom":"8px"},"css":".gb-element-rtlsp{margin-bottom:32px;padding-bottom:8px}","className":"gb-el gb-el-rtlsp"} -->
<div class="gb-element-rtlsp gb-el gb-el-rtlsp"><!-- wp:generateblocks/text {"uniqueId":"rt_t","tagName":"h3","styles":{"marginBottom":"12px","fontSize":"18px","fontWeight":"600"},"css":".gb-text-rt_t{margin-bottom:12px;font-size:18px;font-weight:600}","className":"gb-t-rt_t"} -->
<h3 class="gb-text gb-text-rt_t gb-t-rt_t">{{post_title}}</h3>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rt_e","tagName":"p","styles":{"marginBottom":"12px"},"css":".gb-text-rt_e{margin-bottom:12px}","className":"gb-t-rt_e"} -->
<p class="gb-text gb-text-rt_e gb-t-rt_e">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"rtgr","tagName":"div","styles":{"display":"flex","flexDirection":"column","width":"100%"},"css":".gb-element-rtgr{display:flex;flex-direction:column;width:100%}","className":"gb-el gb-el-rtgr"} -->
<div class="gb-element-rtgr gb-el gb-el-rtgr"><!-- wp:generateblocks/element {"uniqueId":"rtrw0","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-rtrw0{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-rtrw0{grid-template-columns:1fr}}","className":"gb-el gb-el-rtrw0"} -->
<div class="gb-element-rtrw0 gb-el gb-el-rtrw0"><!-- wp:generateblocks/text {"uniqueId":"rtlk0","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-rtlk0{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-rtlk0"} -->
<div class="gb-text gb-text-rtlk0 gb-t-rtlk0">Rate from</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rtvk0","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-rtvk0{margin-bottom:0px;font-size:14px}","className":"gb-t-rtvk0"} -->
<div class="gb-text gb-text-rtvk0 gb-t-rtvk0">{{post_meta key:_pw_rate_from}} __PW_PROPERTY_CURRENCY__</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"rtrw1","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-rtrw1{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-rtrw1{grid-template-columns:1fr}}","className":"gb-el gb-el-rtrw1"} -->
<div class="gb-element-rtrw1 gb-el gb-el-rtrw1"><!-- wp:generateblocks/text {"uniqueId":"rtlk1","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-rtlk1{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-rtlk1"} -->
<div class="gb-text gb-text-rtlk1 gb-t-rtlk1">Rate to</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rtvk1","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-rtvk1{margin-bottom:0px;font-size:14px}","className":"gb-t-rtvk1"} -->
<div class="gb-text gb-text-rtvk1 gb-t-rtvk1">{{post_meta key:_pw_rate_to}} __PW_PROPERTY_CURRENCY__</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"rtrw2","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-rtrw2{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-rtrw2{grid-template-columns:1fr}}","className":"gb-el gb-el-rtrw2"} -->
<div class="gb-element-rtrw2 gb-el gb-el-rtrw2"><!-- wp:generateblocks/text {"uniqueId":"rtlk2","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-rtlk2{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-rtlk2"} -->
<div class="gb-text gb-text-rtlk2 gb-t-rtlk2">Max occupancy</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rtvk2","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-rtvk2{margin-bottom:0px;font-size:14px}","className":"gb-t-rtvk2"} -->
<div class="gb-text gb-text-rtvk2 gb-t-rtvk2">{{post_meta key:_pw_max_occupancy}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"rtrw3","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-rtrw3{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-rtrw3{grid-template-columns:1fr}}","className":"gb-el gb-el-rtrw3"} -->
<div class="gb-element-rtrw3 gb-el gb-el-rtrw3"><!-- wp:generateblocks/text {"uniqueId":"rtlk3","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-rtlk3{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-rtlk3"} -->
<div class="gb-text gb-text-rtlk3 gb-t-rtlk3">Max adults</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rtvk3","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-rtvk3{margin-bottom:0px;font-size:14px}","className":"gb-t-rtvk3"} -->
<div class="gb-text gb-text-rtvk3 gb-t-rtvk3">{{post_meta key:_pw_max_adults}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"rtrw4","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-rtrw4{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-rtrw4{grid-template-columns:1fr}}","className":"gb-el gb-el-rtrw4"} -->
<div class="gb-element-rtrw4 gb-el gb-el-rtrw4"><!-- wp:generateblocks/text {"uniqueId":"rtlk4","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-rtlk4{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-rtlk4"} -->
<div class="gb-text gb-text-rtlk4 gb-t-rtlk4">Max children</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rtvk4","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-rtvk4{margin-bottom:0px;font-size:14px}","className":"gb-t-rtvk4"} -->
<div class="gb-text gb-text-rtvk4 gb-t-rtvk4">{{post_meta key:_pw_max_children}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"rtrw5","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-rtrw5{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-rtrw5{grid-template-columns:1fr}}","className":"gb-el gb-el-rtrw5"} -->
<div class="gb-element-rtrw5 gb-el gb-el-rtrw5"><!-- wp:generateblocks/text {"uniqueId":"rtlk5","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-rtlk5{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-rtlk5"} -->
<div class="gb-text gb-text-rtlk5 gb-t-rtlk5">Size (m²)</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rtvk5","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-rtvk5{margin-bottom:0px;font-size:14px}","className":"gb-t-rtvk5"} -->
<div class="gb-text gb-text-rtvk5 gb-t-rtvk5">{{post_meta key:_pw_size_sqm}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"rtrw6","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-rtrw6{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-rtrw6{grid-template-columns:1fr}}","className":"gb-el gb-el-rtrw6"} -->
<div class="gb-element-rtrw6 gb-el gb-el-rtrw6"><!-- wp:generateblocks/text {"uniqueId":"rtlk6","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-rtlk6{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-rtlk6"} -->
<div class="gb-text gb-text-rtlk6 gb-t-rtlk6">Size (ft²)</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rtvk6","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-rtvk6{margin-bottom:0px;font-size:14px}","className":"gb-t-rtvk6"} -->
<div class="gb-text gb-text-rtvk6 gb-t-rtvk6">{{post_meta key:_pw_size_sqft}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"rtrw7","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-rtrw7{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-rtrw7{grid-template-columns:1fr}}","className":"gb-el gb-el-rtrw7"} -->
<div class="gb-element-rtrw7 gb-el gb-el-rtrw7"><!-- wp:generateblocks/text {"uniqueId":"rtlk7","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-rtlk7{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-rtlk7"} -->
<div class="gb-text gb-text-rtlk7 gb-t-rtlk7">Extra beds</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rtvk7","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-rtvk7{margin-bottom:0px;font-size:14px}","className":"gb-t-rtvk7"} -->
<div class="gb-text gb-text-rtvk7 gb-t-rtvk7">{{post_meta key:_pw_max_extra_beds}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"rm-nr","tagName":"p","styles":{"marginBottom":"0px"},"css":".gb-text-rm-nr{margin-bottom:0px}","className":"gb-t-rm-nr"} -->
<p class="gb-text gb-text-rm-nr gb-t-rm-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_ROOM_TYPE,
		'pw_restaurant' => <<<'PW_ST_RESTAURANT'
<!-- wp:generateblocks/query {"uniqueId":"rstq","tagName":"div","query":{"post_type":["pw_restaurant"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/looper {"uniqueId":"rst-loop","tagName":"div","className":"gb-loop-rst-loop"} -->
<div class="gb-looper-rst-loop gb-loop-rst-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"rst-item","tagName":"div","className":"gb-li-rst-item"} -->
<div class="gb-loop-item gb-loop-item-rst-item gb-li-rst-item">
<!-- wp:generateblocks/element {"uniqueId":"rslsp","tagName":"div","styles":{"marginBottom":"32px","paddingBottom":"8px"},"css":".gb-element-rslsp{margin-bottom:32px;padding-bottom:8px}","className":"gb-el gb-el-rslsp"} -->
<div class="gb-element-rslsp gb-el gb-el-rslsp"><!-- wp:generateblocks/element {"uniqueId":"rsbd","tagName":"div","styles":{"paddingLeft":"18px","borderLeftWidth":"3px","borderLeftStyle":"solid","borderLeftColor":"#c5c5c5"},"css":".gb-element-rsbd{border-left:3px solid #c5c5c5;padding-left:18px}","className":"gb-el gb-el-rsbd"} -->
<div class="gb-element-rsbd gb-el gb-el-rsbd"><!-- wp:generateblocks/text {"uniqueId":"rs_t","tagName":"div","styles":{"marginBottom":"6px","fontSize":"17px","fontWeight":"600"},"css":".gb-text-rs_t{margin-bottom:6px;font-size:17px;font-weight:600}","className":"gb-t-rs_t"} -->
<div class="gb-text gb-text-rs_t gb-t-rs_t">{{post_title}}</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rs_d","tagName":"div","styles":{"marginBottom":"8px","fontSize":"14px"},"css":".gb-text-rs_d{margin-bottom:8px;font-size:14px}","className":"gb-t-rs_d"} -->
<div class="gb-text gb-text-rs_d gb-t-rs_d">{{post_meta key:_pw_cuisine_type}} · {{post_meta key:_pw_location}} · {{post_meta key:_pw_seating_capacity}} seats</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rs_e","tagName":"div","styles":{"marginBottom":"8px","fontSize":"14px"},"css":".gb-text-rs_e{margin-bottom:8px;font-size:14px}","className":"gb-t-rs_e"} -->
<div class="gb-text gb-text-rs_e gb-t-rs_e">{{post_excerpt}}</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rs_ru","tagName":"a","styles":{"marginBottom":"4px","display":"inline-block"},"css":".gb-text-rs_ru{display:inline-block;margin-bottom:4px}","htmlAttributes":{"href":"{{post_meta key:_pw_reservation_url}}"},"className":"gb-t-rs_ru"} -->
<a class="gb-text gb-text-rs_ru gb-t-rs_ru" href="{{post_meta key:_pw_reservation_url}}">Reservations</a>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rs_mu","tagName":"a","styles":{"marginBottom":"0px","display":"inline-block"},"css":".gb-text-rs_mu{display:inline-block;margin-bottom:0px}","htmlAttributes":{"href":"{{post_meta key:_pw_menu_url}}"},"className":"gb-t-rs_mu"} -->
<a class="gb-text gb-text-rs_mu gb-t-rs_mu" href="{{post_meta key:_pw_menu_url}}">Menu</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"rst-nr","tagName":"p","styles":{"marginBottom":"0px"},"css":".gb-text-rst-nr{margin-bottom:0px}","className":"gb-t-rst-nr"} -->
<p class="gb-text gb-text-rst-nr gb-t-rst-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_RESTAURANT,
		'pw_spa' => <<<'PW_ST_SPA'
<!-- wp:generateblocks/query {"uniqueId":"spaq","tagName":"div","query":{"post_type":["pw_spa"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/looper {"uniqueId":"spa-loop","tagName":"div","className":"gb-loop-spa-loop"} -->
<div class="gb-looper-spa-loop gb-loop-spa-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"spa-item","tagName":"div","className":"gb-li-spa-item"} -->
<div class="gb-loop-item gb-loop-item-spa-item gb-li-spa-item">
<!-- wp:generateblocks/element {"uniqueId":"splsp","tagName":"div","styles":{"marginBottom":"32px","paddingBottom":"8px"},"css":".gb-element-splsp{margin-bottom:32px;padding-bottom:8px}","className":"gb-el gb-el-splsp"} -->
<div class="gb-element-splsp gb-el gb-el-splsp"><!-- wp:generateblocks/text {"uniqueId":"sp_t","tagName":"h3","styles":{"marginBottom":"12px","fontSize":"18px","fontWeight":"600"},"css":".gb-text-sp_t{margin-bottom:12px;font-size:18px;font-weight:600}","className":"gb-t-sp_t"} -->
<h3 class="gb-text gb-text-sp_t gb-t-sp_t">{{post_title}}</h3>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"sp_e","tagName":"p","styles":{"marginBottom":"12px"},"css":".gb-text-sp_e{margin-bottom:12px}","className":"gb-t-sp_e"} -->
<p class="gb-text gb-text-sp_e gb-t-sp_e">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"spgr","tagName":"div","styles":{"display":"flex","flexDirection":"column","width":"100%"},"css":".gb-element-spgr{display:flex;flex-direction:column;width:100%}","className":"gb-el gb-el-spgr"} -->
<div class="gb-element-spgr gb-el gb-el-spgr"><!-- wp:generateblocks/element {"uniqueId":"sprw0","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-sprw0{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-sprw0{grid-template-columns:1fr}}","className":"gb-el gb-el-sprw0"} -->
<div class="gb-element-sprw0 gb-el gb-el-sprw0"><!-- wp:generateblocks/text {"uniqueId":"splk0","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-splk0{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-splk0"} -->
<div class="gb-text gb-text-splk0 gb-t-splk0">Min age</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"spvk0","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-spvk0{margin-bottom:0px;font-size:14px}","className":"gb-t-spvk0"} -->
<div class="gb-text gb-text-spvk0 gb-t-spvk0">{{post_meta key:_pw_min_age}} years</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"sprw1","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-sprw1{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-sprw1{grid-template-columns:1fr}}","className":"gb-el gb-el-sprw1"} -->
<div class="gb-element-sprw1 gb-el gb-el-sprw1"><!-- wp:generateblocks/text {"uniqueId":"splk1","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-splk1{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-splk1"} -->
<div class="gb-text gb-text-splk1 gb-t-splk1">Treatment rooms</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"spvk1","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-spvk1{margin-bottom:0px;font-size:14px}","className":"gb-t-spvk1"} -->
<div class="gb-text gb-text-spvk1 gb-t-spvk1">{{post_meta key:_pw_number_of_treatment_rooms}} rooms</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"sp_bu","tagName":"a","styles":{"marginBottom":"4px","display":"inline-block"},"css":".gb-text-sp_bu{display:inline-block;margin-bottom:4px}","htmlAttributes":{"href":"{{post_meta key:_pw_booking_url}}"},"className":"gb-t-sp_bu"} -->
<a class="gb-text gb-text-sp_bu gb-t-sp_bu" href="{{post_meta key:_pw_booking_url}}">Book spa</a>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"sp_mu","tagName":"a","styles":{"marginBottom":"0px","display":"inline-block"},"css":".gb-text-sp_mu{display:inline-block;margin-bottom:0px}","htmlAttributes":{"href":"{{post_meta key:_pw_menu_url}}"},"className":"gb-t-sp_mu"} -->
<a class="gb-text gb-text-sp_mu gb-t-sp_mu" href="{{post_meta key:_pw_menu_url}}">Spa menu</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"spa-nr","tagName":"p","styles":{"marginBottom":"0px"},"css":".gb-text-spa-nr{margin-bottom:0px}","className":"gb-t-spa-nr"} -->
<p class="gb-text gb-text-spa-nr gb-t-spa-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_SPA,
		'pw_meeting_room' => <<<'PW_ST_MEETING_ROOM'
<!-- wp:generateblocks/query {"uniqueId":"mtq","tagName":"div","query":{"post_type":["pw_meeting_room"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/looper {"uniqueId":"mt-loop","tagName":"div","className":"gb-loop-mt-loop"} -->
<div class="gb-looper-mt-loop gb-loop-mt-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"mt-item","tagName":"div","className":"gb-li-mt-item"} -->
<div class="gb-loop-item gb-loop-item-mt-item gb-li-mt-item">
<!-- wp:generateblocks/element {"uniqueId":"ch_mrrow","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"repeat(8, minmax(5.5rem, 1fr))","columnGap":"10px","rowGap":"6px","alignItems":"start","paddingTop":"10px","paddingBottom":"10px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-ch_mrrow{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:10px;display:grid;grid-template-columns:repeat(8,minmax(5.5rem,1fr));padding:10px 0;row-gap:6px;width:100%}","className":"gb-el gb-el-ch_mrrow"} -->
<div class="gb-element-ch_mrrow gb-el gb-el-ch_mrrow"><!-- wp:generateblocks/text {"uniqueId":"ch_mrrowc0","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_mrrowc0{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_mrrowc0"} -->
<div class="gb-text gb-text-ch_mrrowc0 gb-t-ch_mrrowc0">{{post_title}}</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ch_mrrowc1","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_mrrowc1{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_mrrowc1"} -->
<div class="gb-text gb-text-ch_mrrowc1 gb-t-ch_mrrowc1">{{post_meta key:_pw_capacity_theatre}} guests</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ch_mrrowc2","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_mrrowc2{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_mrrowc2"} -->
<div class="gb-text gb-text-ch_mrrowc2 gb-t-ch_mrrowc2">{{post_meta key:_pw_capacity_classroom}} guests</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ch_mrrowc3","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_mrrowc3{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_mrrowc3"} -->
<div class="gb-text gb-text-ch_mrrowc3 gb-t-ch_mrrowc3">{{post_meta key:_pw_capacity_boardroom}} guests</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ch_mrrowc4","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_mrrowc4{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_mrrowc4"} -->
<div class="gb-text gb-text-ch_mrrowc4 gb-t-ch_mrrowc4">{{post_meta key:_pw_capacity_ushape}} guests</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ch_mrrowc5","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_mrrowc5{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_mrrowc5"} -->
<div class="gb-text gb-text-ch_mrrowc5 gb-t-ch_mrrowc5">{{post_meta key:_pw_area_sqm}} m²</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ch_mrrowc6","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_mrrowc6{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_mrrowc6"} -->
<div class="gb-text gb-text-ch_mrrowc6 gb-t-ch_mrrowc6">{{post_meta key:_pw_area_sqft}} ft²</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ch_mrrowc7","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_mrrowc7{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_mrrowc7"} -->
<div class="gb-text gb-text-ch_mrrowc7 gb-t-ch_mrrowc7">{{post_meta key:_pw_natural_light}}</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"mt-srw0","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-mt-srw0{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-mt-srw0{grid-template-columns:1fr}}","className":"gb-el gb-el-mt-srw0"} -->
<div class="gb-element-mt-srw0 gb-el gb-el-mt-srw0"><!-- wp:generateblocks/text {"uniqueId":"mt-slk0","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-mt-slk0{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-mt-slk0"} -->
<div class="gb-text gb-text-mt-slk0 gb-t-mt-slk0">Sales email</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"mt-svk0","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-mt-svk0{margin-bottom:0px;font-size:14px}","className":"gb-t-mt-svk0"} -->
<div class="gb-text gb-text-mt-svk0 gb-t-mt-svk0">{{post_meta key:_pw_sales_email}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"mt-srw1","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-mt-srw1{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-mt-srw1{grid-template-columns:1fr}}","className":"gb-el gb-el-mt-srw1"} -->
<div class="gb-element-mt-srw1 gb-el gb-el-mt-srw1"><!-- wp:generateblocks/text {"uniqueId":"mt-slk1","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-mt-slk1{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-mt-slk1"} -->
<div class="gb-text gb-text-mt-slk1 gb-t-mt-slk1">Sales phone</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"mt-svk1","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-mt-svk1{margin-bottom:0px;font-size:14px}","className":"gb-t-mt-svk1"} -->
<div class="gb-text gb-text-mt-svk1 gb-t-mt-svk1">{{post_meta key:_pw_sales_phone}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"mt-nr","tagName":"p","styles":{"marginBottom":"0px"},"css":".gb-text-mt-nr{margin-bottom:0px}","className":"gb-t-mt-nr"} -->
<p class="gb-text gb-text-mt-nr gb-t-mt-nr">No meeting spaces.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_MEETING_ROOM,
		'pw_experience' => <<<'PW_ST_EXPERIENCE'
<!-- wp:generateblocks/query {"uniqueId":"exq","tagName":"div","query":{"post_type":["pw_experience"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/looper {"uniqueId":"ex-loop","tagName":"div","className":"gb-loop-ex-loop"} -->
<div class="gb-looper-ex-loop gb-loop-ex-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"ex-item","tagName":"div","className":"gb-li-ex-item"} -->
<div class="gb-loop-item gb-loop-item-ex-item gb-li-ex-item">
<!-- wp:generateblocks/element {"uniqueId":"exlsp","tagName":"div","styles":{"marginBottom":"32px","paddingBottom":"8px"},"css":".gb-element-exlsp{margin-bottom:32px;padding-bottom:8px}","className":"gb-el gb-el-exlsp"} -->
<div class="gb-element-exlsp gb-el gb-el-exlsp"><!-- wp:generateblocks/text {"uniqueId":"ex_t","tagName":"h3","styles":{"marginBottom":"12px","fontSize":"18px","fontWeight":"600"},"css":".gb-text-ex_t{margin-bottom:12px;font-size:18px;font-weight:600}","className":"gb-t-ex_t"} -->
<h3 class="gb-text gb-text-ex_t gb-t-ex_t">{{post_title}}</h3>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ex_d","tagName":"p","styles":{"marginBottom":"12px"},"css":".gb-text-ex_d{margin-bottom:12px}","className":"gb-t-ex_d"} -->
<p class="gb-text gb-text-ex_d gb-t-ex_d">{{post_meta key:_pw_description}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ex_ex","tagName":"p","styles":{"marginBottom":"12px"},"css":".gb-text-ex_ex{margin-bottom:12px}","className":"gb-t-ex_ex"} -->
<p class="gb-text gb-text-ex_ex gb-t-ex_ex">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"exgr","tagName":"div","styles":{"display":"flex","flexDirection":"column","width":"100%"},"css":".gb-element-exgr{display:flex;flex-direction:column;width:100%}","className":"gb-el gb-el-exgr"} -->
<div class="gb-element-exgr gb-el gb-el-exgr"><!-- wp:generateblocks/element {"uniqueId":"exrw0","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-exrw0{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-exrw0{grid-template-columns:1fr}}","className":"gb-el gb-el-exrw0"} -->
<div class="gb-element-exrw0 gb-el gb-el-exrw0"><!-- wp:generateblocks/text {"uniqueId":"exlk0","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-exlk0{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-exlk0"} -->
<div class="gb-text gb-text-exlk0 gb-t-exlk0">Duration</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"exvk0","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-exvk0{margin-bottom:0px;font-size:14px}","className":"gb-t-exvk0"} -->
<div class="gb-text gb-text-exvk0 gb-t-exvk0">{{post_meta key:_pw_duration_hours}} h</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"exrw1","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-exrw1{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-exrw1{grid-template-columns:1fr}}","className":"gb-el gb-el-exrw1"} -->
<div class="gb-element-exrw1 gb-el gb-el-exrw1"><!-- wp:generateblocks/text {"uniqueId":"exlk1","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-exlk1{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-exlk1"} -->
<div class="gb-text gb-text-exlk1 gb-t-exlk1">Price from</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"exvk1","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-exvk1{margin-bottom:0px;font-size:14px}","className":"gb-t-exvk1"} -->
<div class="gb-text gb-text-exvk1 gb-t-exvk1">{{post_meta key:_pw_price_from}} __PW_PROPERTY_CURRENCY__</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"exrw2","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-exrw2{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-exrw2{grid-template-columns:1fr}}","className":"gb-el gb-el-exrw2"} -->
<div class="gb-element-exrw2 gb-el gb-el-exrw2"><!-- wp:generateblocks/text {"uniqueId":"exlk2","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-exlk2{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-exlk2"} -->
<div class="gb-text gb-text-exlk2 gb-t-exlk2">Complimentary</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"exvk2","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-exvk2{margin-bottom:0px;font-size:14px}","className":"gb-t-exvk2"} -->
<div class="gb-text gb-text-exvk2 gb-t-exvk2">{{post_meta key:_pw_is_complimentary}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"ex_b","tagName":"a","styles":{"marginBottom":"0px","display":"inline-block"},"css":".gb-text-ex_b{display:inline-block;margin-bottom:0px}","htmlAttributes":{"href":"{{post_meta key:_pw_booking_url}}"},"className":"gb-t-ex_b"} -->
<a class="gb-text gb-text-ex_b gb-t-ex_b" href="{{post_meta key:_pw_booking_url}}">Book</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"ex-nr","tagName":"p","styles":{"marginBottom":"0px"},"css":".gb-text-ex-nr{margin-bottom:0px}","className":"gb-t-ex-nr"} -->
<p class="gb-text gb-text-ex-nr gb-t-ex-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_EXPERIENCE,
		'pw_nearby' => <<<'PW_ST_NEARBY'
<!-- wp:generateblocks/query {"uniqueId":"nbq","tagName":"div","query":{"post_type":["pw_nearby"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/looper {"uniqueId":"nb-loop","tagName":"div","className":"gb-loop-nb-loop"} -->
<div class="gb-looper-nb-loop gb-loop-nb-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"nb-item","tagName":"div","className":"gb-li-nb-item"} -->
<div class="gb-loop-item gb-loop-item-nb-item gb-li-nb-item">
<!-- wp:generateblocks/element {"uniqueId":"ch_nrrow","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"repeat(5, minmax(5.5rem, 1fr))","columnGap":"10px","rowGap":"6px","alignItems":"start","paddingTop":"10px","paddingBottom":"10px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-ch_nrrow{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:10px;display:grid;grid-template-columns:repeat(5,minmax(5.5rem,1fr));padding:10px 0;row-gap:6px;width:100%}","className":"gb-el gb-el-ch_nrrow"} -->
<div class="gb-element-ch_nrrow gb-el gb-el-ch_nrrow"><!-- wp:generateblocks/text {"uniqueId":"ch_nrrowc0","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_nrrowc0{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_nrrowc0"} -->
<div class="gb-text gb-text-ch_nrrowc0 gb-t-ch_nrrowc0">{{post_title}}</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ch_nrrowc1","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_nrrowc1{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_nrrowc1"} -->
<div class="gb-text gb-text-ch_nrrowc1 gb-t-ch_nrrowc1">{{post_excerpt}}</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ch_nrrowc2","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_nrrowc2{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_nrrowc2"} -->
<div class="gb-text gb-text-ch_nrrowc2 gb-t-ch_nrrowc2">{{post_meta key:_pw_distance_km}} km</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ch_nrrowc3","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ch_nrrowc3{margin-bottom:0px;font-size:14px}","className":"gb-t-ch_nrrowc3"} -->
<div class="gb-text gb-text-ch_nrrowc3 gb-t-ch_nrrowc3">{{post_meta key:_pw_travel_time_min}} min</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ch_nrplink","tagName":"a","styles":{"marginBottom":"0px","display":"inline-block"},"css":".gb-text-ch_nrplink{display:inline-block;margin-bottom:0px;word-break:break-all}","htmlAttributes":{"href":"{{post_meta key:_pw_place_url}}"},"className":"gb-t-ch_nrplink"} -->
<a class="gb-text gb-text-ch_nrplink gb-t-ch_nrplink" href="{{post_meta key:_pw_place_url}}">{{post_meta key:_pw_place_url}}</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"nb-nr","tagName":"p","styles":{"marginBottom":"0px"},"css":".gb-text-nb-nr{margin-bottom:0px}","className":"gb-t-nb-nr"} -->
<p class="gb-text gb-text-nb-nr gb-t-nb-nr">No nearby places.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_NEARBY,
		'pw_event' => <<<'PW_ST_EVENT'
<!-- wp:generateblocks/query {"uniqueId":"evq","tagName":"div","query":{"post_type":["pw_event"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/looper {"uniqueId":"ev-loop","tagName":"div","className":"gb-loop-ev-loop"} -->
<div class="gb-looper-ev-loop gb-loop-ev-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"ev-item","tagName":"div","className":"gb-li-ev-item"} -->
<div class="gb-loop-item gb-loop-item-ev-item gb-li-ev-item">
<!-- wp:generateblocks/element {"uniqueId":"evlsp","tagName":"div","styles":{"marginBottom":"32px","paddingBottom":"8px"},"css":".gb-element-evlsp{margin-bottom:32px;padding-bottom:8px}","className":"gb-el gb-el-evlsp"} -->
<div class="gb-element-evlsp gb-el gb-el-evlsp"><!-- wp:generateblocks/text {"uniqueId":"ev_t","tagName":"h3","styles":{"marginBottom":"12px","fontSize":"18px","fontWeight":"600"},"css":".gb-text-ev_t{margin-bottom:12px;font-size:18px;font-weight:600}","className":"gb-t-ev_t"} -->
<h3 class="gb-text gb-text-ev_t gb-t-ev_t">{{post_title}}</h3>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ev_e","tagName":"p","styles":{"marginBottom":"12px"},"css":".gb-text-ev_e{margin-bottom:12px}","className":"gb-t-ev_e"} -->
<p class="gb-text gb-text-ev_e gb-t-ev_e">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"evgr","tagName":"div","styles":{"display":"flex","flexDirection":"column","width":"100%"},"css":".gb-element-evgr{display:flex;flex-direction:column;width:100%}","className":"gb-el gb-el-evgr"} -->
<div class="gb-element-evgr gb-el gb-el-evgr"><!-- wp:generateblocks/element {"uniqueId":"evrw0","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-evrw0{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-evrw0{grid-template-columns:1fr}}","className":"gb-el gb-el-evrw0"} -->
<div class="gb-element-evrw0 gb-el gb-el-evrw0"><!-- wp:generateblocks/text {"uniqueId":"evlk0","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-evlk0{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-evlk0"} -->
<div class="gb-text gb-text-evlk0 gb-t-evlk0">Start (local)</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"evvk0","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-evvk0{margin-bottom:0px;font-size:14px}","className":"gb-t-evvk0"} -->
<div class="gb-text gb-text-evvk0 gb-t-evvk0">{{post_meta key:_pw_start_datetime}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"evrw1","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-evrw1{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-evrw1{grid-template-columns:1fr}}","className":"gb-el gb-el-evrw1"} -->
<div class="gb-element-evrw1 gb-el gb-el-evrw1"><!-- wp:generateblocks/text {"uniqueId":"evlk1","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-evlk1{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-evlk1"} -->
<div class="gb-text gb-text-evlk1 gb-t-evlk1">End (local)</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"evvk1","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-evvk1{margin-bottom:0px;font-size:14px}","className":"gb-t-evvk1"} -->
<div class="gb-text gb-text-evvk1 gb-t-evvk1">{{post_meta key:_pw_end_datetime}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"evrw2","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-evrw2{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-evrw2{grid-template-columns:1fr}}","className":"gb-el gb-el-evrw2"} -->
<div class="gb-element-evrw2 gb-el gb-el-evrw2"><!-- wp:generateblocks/text {"uniqueId":"evlk2","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-evlk2{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-evlk2"} -->
<div class="gb-text gb-text-evlk2 gb-t-evlk2">Capacity</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"evvk2","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-evvk2{margin-bottom:0px;font-size:14px}","className":"gb-t-evvk2"} -->
<div class="gb-text gb-text-evvk2 gb-t-evvk2">{{post_meta key:_pw_capacity}} guests</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"evrw3","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-evrw3{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-evrw3{grid-template-columns:1fr}}","className":"gb-el gb-el-evrw3"} -->
<div class="gb-element-evrw3 gb-el gb-el-evrw3"><!-- wp:generateblocks/text {"uniqueId":"evlk3","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-evlk3{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-evlk3"} -->
<div class="gb-text gb-text-evlk3 gb-t-evlk3">Price from</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"evvk3","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-evvk3{margin-bottom:0px;font-size:14px}","className":"gb-t-evvk3"} -->
<div class="gb-text gb-text-evvk3 gb-t-evvk3">{{post_meta key:_pw_price_from}} __PW_PROPERTY_CURRENCY__</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"evrw5","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-evrw5{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-evrw5{grid-template-columns:1fr}}","className":"gb-el gb-el-evrw5"} -->
<div class="gb-element-evrw5 gb-el gb-el-evrw5"><!-- wp:generateblocks/text {"uniqueId":"evlk5","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-evlk5{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-evlk5"} -->
<div class="gb-text gb-text-evlk5 gb-t-evlk5">Status</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"evvk5","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-evvk5{margin-bottom:0px;font-size:14px}","className":"gb-t-evvk5"} -->
<div class="gb-text gb-text-evvk5 gb-t-evvk5">{{post_meta key:_pw_event_status}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"ev_b","tagName":"a","styles":{"marginBottom":"0px","display":"inline-block"},"css":".gb-text-ev_b{display:inline-block;margin-bottom:0px}","htmlAttributes":{"href":"{{post_meta key:_pw_booking_url}}"},"className":"gb-t-ev_b"} -->
<a class="gb-text gb-text-ev_b gb-t-ev_b" href="{{post_meta key:_pw_booking_url}}">Booking</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"ev-nr","tagName":"p","styles":{"marginBottom":"0px"},"css":".gb-text-ev-nr{margin-bottom:0px}","className":"gb-t-ev-nr"} -->
<p class="gb-text gb-text-ev-nr gb-t-ev-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_EVENT,
		'pw_offer' => <<<'PW_ST_OFFER'
<!-- wp:generateblocks/query {"uniqueId":"ofq","tagName":"div","query":{"post_type":["pw_offer"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/looper {"uniqueId":"of-loop","tagName":"div","className":"gb-loop-of-loop"} -->
<div class="gb-looper-of-loop gb-loop-of-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"of-item","tagName":"div","className":"gb-li-of-item"} -->
<div class="gb-loop-item gb-loop-item-of-item gb-li-of-item">
<!-- wp:generateblocks/element {"uniqueId":"oflsp","tagName":"div","styles":{"marginBottom":"32px","paddingBottom":"8px"},"css":".gb-element-oflsp{margin-bottom:32px;padding-bottom:8px}","className":"gb-el gb-el-oflsp"} -->
<div class="gb-element-oflsp gb-el gb-el-oflsp"><!-- wp:generateblocks/text {"uniqueId":"of_t","tagName":"h3","styles":{"marginBottom":"12px","fontSize":"18px","fontWeight":"600"},"css":".gb-text-of_t{margin-bottom:12px;font-size:18px;font-weight:600}","className":"gb-t-of_t"} -->
<h3 class="gb-text gb-text-of_t gb-t-of_t">{{post_title}}</h3>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"of_e","tagName":"p","styles":{"marginBottom":"12px"},"css":".gb-text-of_e{margin-bottom:12px}","className":"gb-t-of_e"} -->
<p class="gb-text gb-text-of_e gb-t-of_e">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"ofgr","tagName":"div","styles":{"display":"flex","flexDirection":"column","width":"100%"},"css":".gb-element-ofgr{display:flex;flex-direction:column;width:100%}","className":"gb-el gb-el-ofgr"} -->
<div class="gb-element-ofgr gb-el gb-el-ofgr"><!-- wp:generateblocks/element {"uniqueId":"ofrw0","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-ofrw0{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-ofrw0{grid-template-columns:1fr}}","className":"gb-el gb-el-ofrw0"} -->
<div class="gb-element-ofrw0 gb-el gb-el-ofrw0"><!-- wp:generateblocks/text {"uniqueId":"oflk0","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-oflk0{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-oflk0"} -->
<div class="gb-text gb-text-oflk0 gb-t-oflk0">Offer type</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ofvk0","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ofvk0{margin-bottom:0px;font-size:14px}","className":"gb-t-ofvk0"} -->
<div class="gb-text gb-text-ofvk0 gb-t-ofvk0">{{post_meta key:_pw_offer_type}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"ofrw1","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-ofrw1{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-ofrw1{grid-template-columns:1fr}}","className":"gb-el gb-el-ofrw1"} -->
<div class="gb-element-ofrw1 gb-el gb-el-ofrw1"><!-- wp:generateblocks/text {"uniqueId":"oflk1","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-oflk1{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-oflk1"} -->
<div class="gb-text gb-text-oflk1 gb-t-oflk1">Valid from</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ofvk1","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ofvk1{margin-bottom:0px;font-size:14px}","className":"gb-t-ofvk1"} -->
<div class="gb-text gb-text-ofvk1 gb-t-ofvk1">{{post_meta key:_pw_valid_from}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"ofrw2","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-ofrw2{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-ofrw2{grid-template-columns:1fr}}","className":"gb-el gb-el-ofrw2"} -->
<div class="gb-element-ofrw2 gb-el gb-el-ofrw2"><!-- wp:generateblocks/text {"uniqueId":"oflk2","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-oflk2{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-oflk2"} -->
<div class="gb-text gb-text-oflk2 gb-t-oflk2">Valid to</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ofvk2","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ofvk2{margin-bottom:0px;font-size:14px}","className":"gb-t-ofvk2"} -->
<div class="gb-text gb-text-ofvk2 gb-t-ofvk2">{{post_meta key:_pw_valid_to}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"ofrw3","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-ofrw3{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-ofrw3{grid-template-columns:1fr}}","className":"gb-el gb-el-ofrw3"} -->
<div class="gb-element-ofrw3 gb-el gb-el-ofrw3"><!-- wp:generateblocks/text {"uniqueId":"oflk3","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-oflk3{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-oflk3"} -->
<div class="gb-text gb-text-oflk3 gb-t-oflk3">Discount</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ofvk3","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ofvk3{margin-bottom:0px;font-size:14px}","className":"gb-t-ofvk3"} -->
<div class="gb-text gb-text-ofvk3 gb-t-ofvk3">{{post_meta key:_pw_discount_value}} ({{post_meta key:_pw_discount_type}})</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"ofrw4","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-ofrw4{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-ofrw4{grid-template-columns:1fr}}","className":"gb-el gb-el-ofrw4"} -->
<div class="gb-element-ofrw4 gb-el gb-el-ofrw4"><!-- wp:generateblocks/text {"uniqueId":"oflk4","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-oflk4{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-oflk4"} -->
<div class="gb-text gb-text-oflk4 gb-t-oflk4">Min. nights</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ofvk4","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ofvk4{margin-bottom:0px;font-size:14px}","className":"gb-t-ofvk4"} -->
<div class="gb-text gb-text-ofvk4 gb-t-ofvk4">{{post_meta key:_pw_minimum_stay_nights}} nights</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"ofrw5","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-ofrw5{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media (max-width:640px){.gb-element-ofrw5{grid-template-columns:1fr}}","className":"gb-el gb-el-ofrw5"} -->
<div class="gb-element-ofrw5 gb-el gb-el-ofrw5"><!-- wp:generateblocks/text {"uniqueId":"oflk5","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-oflk5{margin-bottom:0px;font-size:14px;font-weight:600}","className":"gb-t-oflk5"} -->
<div class="gb-text gb-text-oflk5 gb-t-oflk5">Featured</div>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ofvk5","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-ofvk5{margin-bottom:0px;font-size:14px}","className":"gb-t-ofvk5"} -->
<div class="gb-text gb-text-ofvk5 gb-t-ofvk5">{{post_meta key:_pw_is_featured}}</div>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"of_b","tagName":"a","styles":{"marginBottom":"0px","display":"inline-block"},"css":".gb-text-of_b{display:inline-block;margin-bottom:0px}","htmlAttributes":{"href":"{{post_meta key:_pw_booking_url}}"},"className":"gb-t-of_b"} -->
<a class="gb-text gb-text-of_b gb-t-of_b" href="{{post_meta key:_pw_booking_url}}">Book / details</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"of-nr","tagName":"p","styles":{"marginBottom":"0px"},"css":".gb-text-of-nr{margin-bottom:0px}","className":"gb-t-of-nr"} -->
<p class="gb-text gb-text-of-nr gb-t-of-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_OFFER,
		default => '',
	};
}

/* ────────────────────────────────────────────────────────────────────────
 * Singular markup helpers (private — used by _pw_get_singular_starter_markup)
 * ──────────────────────────────────────────────────────────────────────── */

function _pw_gb_row( string $uid, string $label, string $value ): string {
	return '<!-- wp:generateblocks/element {"uniqueId":"' . $uid . '","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(36%, 12rem)) 1fr","columnGap":"14px","alignItems":"start","paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0","width":"100%"},"css":".gb-element-' . $uid . '{align-items:start;border-bottom:1px solid #e0e0e0;column-gap:14px;display:grid;grid-template-columns:minmax(0,min(36%,12rem)) 1fr;padding:8px 0;width:100%}@media(max-width:640px){.gb-element-' . $uid . '{grid-template-columns:1fr}}","className":"gb-el gb-el-' . $uid . '"} -->' . "\n"
		. '<div class="gb-element-' . $uid . ' gb-el gb-el-' . $uid . '">'
		. '<!-- wp:generateblocks/text {"uniqueId":"' . $uid . 'l","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"14px"},"css":".gb-text-' . $uid . 'l{margin-bottom:0;font-size:14px;font-weight:600}","className":"gb-t-' . $uid . 'l"} -->' . "\n"
		. '<div class="gb-text gb-text-' . $uid . 'l gb-t-' . $uid . 'l">' . $label . '</div>' . "\n"
		. '<!-- /wp:generateblocks/text -->' . "\n"
		. '<!-- wp:generateblocks/text {"uniqueId":"' . $uid . 'v","tagName":"div","styles":{"marginBottom":"0px","fontSize":"14px"},"css":".gb-text-' . $uid . 'v{margin-bottom:0;font-size:14px}","className":"gb-t-' . $uid . 'v"} -->' . "\n"
		. '<div class="gb-text gb-text-' . $uid . 'v gb-t-' . $uid . 'v">' . $value . '</div>' . "\n"
		. '<!-- /wp:generateblocks/text -->' . "\n"
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/element -->';
}

function _pw_gb_h( string $uid, string $text, string $tag = 'h2' ): string {
	$fs = $tag === 'h1' ? '28px' : '20px';
	$fw = $tag === 'h1' ? '700' : '600';
	return '<!-- wp:generateblocks/text {"uniqueId":"' . $uid . '","tagName":"' . $tag . '","styles":{"marginBottom":"12px","fontSize":"' . $fs . '","fontWeight":"' . $fw . '"},"css":".gb-text-' . $uid . '{margin-bottom:12px;font-size:' . $fs . ';font-weight:' . $fw . '}","className":"gb-t-' . $uid . '"} -->' . "\n"
		. '<' . $tag . ' class="gb-text gb-text-' . $uid . ' gb-t-' . $uid . '">' . $text . '</' . $tag . '>' . "\n"
		. '<!-- /wp:generateblocks/text -->';
}

function _pw_gb_p( string $uid, string $text ): string {
	return '<!-- wp:generateblocks/text {"uniqueId":"' . $uid . '","tagName":"p","styles":{"marginBottom":"12px","fontSize":"14px"},"css":".gb-text-' . $uid . '{margin-bottom:12px;font-size:14px}","className":"gb-t-' . $uid . '"} -->' . "\n"
		. '<p class="gb-text gb-text-' . $uid . ' gb-t-' . $uid . '">' . $text . '</p>' . "\n"
		. '<!-- /wp:generateblocks/text -->';
}

function _pw_gb_link( string $uid, string $text, string $href ): string {
	return '<!-- wp:generateblocks/text {"uniqueId":"' . $uid . '","tagName":"a","styles":{"marginBottom":"4px","display":"inline-block","marginRight":"16px"},"css":".gb-text-' . $uid . '{display:inline-block;margin-bottom:4px;margin-right:16px}","htmlAttributes":[{"key":"href","value":"' . $href . '"}],"className":"gb-t-' . $uid . '"} -->' . "\n"
		. '<a class="gb-text gb-text-' . $uid . ' gb-t-' . $uid . '" href="' . $href . '">' . $text . '</a>' . "\n"
		. '<!-- /wp:generateblocks/text -->';
}

function _pw_gb_img( string $uid ): string {
	return '<!-- wp:generateblocks/image {"uniqueId":"' . $uid . '","styles":{"display":"block","height":"auto","maxWidth":"100%","marginBottom":"24px"},"css":".gb-image-' . $uid . '{display:block;height:auto;max-width:100%;margin-bottom:24px}","dynamicImage":"featured-image","className":"gb-img-' . $uid . '"} -->' . "\n"
		. '<img class="gb-image gb-image-' . $uid . ' gb-img-' . $uid . '" />' . "\n"
		. '<!-- /wp:generateblocks/image -->';
}

function _pw_gb_section( string $uid, string $inner ): string {
	return '<!-- wp:generateblocks/element {"uniqueId":"' . $uid . '","tagName":"section","styles":{"marginTop":"32px"},"css":".gb-element-' . $uid . '{margin-top:32px}","className":"gb-el gb-el-' . $uid . '"} -->' . "\n"
		. '<section class="gb-element-' . $uid . ' gb-el gb-el-' . $uid . '">' . "\n"
		. $inner . "\n"
		. '</section>' . "\n"
		. '<!-- /wp:generateblocks/element -->';
}

/* ────────────────────────────────────────────────────────────────────────
 * Singular markup dispatcher
 * ──────────────────────────────────────────────────────────────────────── */

function _pw_get_singular_starter_markup( string $cpt ): string {
	return match ( $cpt ) {
		'pw_property'     => _pw_markup_property_singular(),
		'pw_room_type'    => _pw_markup_room_singular(),
		'pw_restaurant'   => _pw_markup_restaurant_singular(),
		'pw_spa'          => _pw_markup_spa_singular(),
		'pw_meeting_room' => _pw_markup_meeting_singular(),
		'pw_experience'   => _pw_markup_experience_singular(),
		'pw_event'        => _pw_markup_event_singular(),
		'pw_offer'        => _pw_markup_offer_singular(),
		'pw_nearby'       => _pw_markup_nearby_singular(),
		default           => '',
	};
}

/* ── Property singular ─────────────────────────────────────────────────── */

function _pw_markup_property_singular(): string {
	$identity = _pw_gb_section( 'prop-idn',
		_pw_gb_h( 'prop-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_row( 'prop-r0', 'Star rating',      '{{post_meta key:_pw_star_rating}}' )           . "\n"
		. _pw_gb_row( 'prop-r1', 'Year established',  '{{post_meta key:_pw_year_established}}' )      . "\n"
		. _pw_gb_row( 'prop-r2', 'Total rooms',       '{{post_meta key:_pw_total_rooms}}' )           . "\n"
		. _pw_gb_row( 'prop-r3', 'Check-in',          '{{post_meta key:_pw_check_in_time}}' )         . "\n"
		. _pw_gb_row( 'prop-r4', 'Check-out',         '{{post_meta key:_pw_check_out_time}}' )
	);

	$address = _pw_gb_section( 'prop-adr',
		_pw_gb_h( 'prop-ah', 'Address' ) . "\n"
		. _pw_gb_row( 'prop-a0', 'Address line 1', '{{post_meta key:_pw_address_line_1}}' )  . "\n"
		. _pw_gb_row( 'prop-a1', 'Address line 2', '{{post_meta key:_pw_address_line_2}}' )  . "\n"
		. _pw_gb_row( 'prop-a2', 'City / State / Postal',
			'{{post_meta key:_pw_city}}, {{post_meta key:_pw_state}} {{post_meta key:_pw_postal_code}}' ) . "\n"
		. _pw_gb_row( 'prop-a3', 'Country', '{{post_meta key:_pw_country}}' )
	);

	// Benefits — generateblocks/query with queryType post_meta.
	$benefits = _pw_gb_section( 'prop-bnf',
		_pw_gb_h( 'prop-bh', 'Direct Booking Benefits' ) . "\n"
		. '<!-- wp:generateblocks/query {"uniqueId":"prop-bnq","tagName":"div","query":{"post_type":["pw_property"],"queryType":"post_meta","metaKey":"_pw_direct_benefits","posts_per_page":100},"className":"gb-el gb-el-prop-bnq"} -->' . "\n"
		. '<div class="gb-el gb-el-prop-bnq">'
		. '<!-- wp:generateblocks/looper {"uniqueId":"prop-bnlp","tagName":"div","className":"gb-loop-prop-bnlp"} -->' . "\n"
		. '<div class="gb-looper-prop-bnlp gb-loop-prop-bnlp">'
		. '<!-- wp:generateblocks/loop-item {"uniqueId":"prop-bnli","tagName":"div","styles":{"paddingTop":"8px","paddingBottom":"8px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0"},"css":".gb-loop-item-prop-bnli{padding:8px 0;border-bottom:1px solid #e0e0e0}","className":"gb-li-prop-bnli"} -->' . "\n"
		. '<div class="gb-loop-item-prop-bnli gb-li-prop-bnli">'
		. _pw_gb_p( 'prop-bnt', '{{loop_item key:title}}' ) . "\n"
		. _pw_gb_p( 'prop-bnd', '{{loop_item key:description}}' )
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/loop-item -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/looper -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/query -->'
	);

	$links = _pw_gb_section( 'prop-slk',
		_pw_gb_h( 'prop-lh', 'Sections' ) . "\n"
		. _pw_gb_link( 'prop-l0', 'Rooms', '{{pw_section_url:pw_room_type}}' ) . "\n"
		. _pw_gb_link( 'prop-l1', 'Restaurants', '{{pw_section_url:pw_restaurant}}' ) . "\n"
		. _pw_gb_link( 'prop-l2', 'Spas', '{{pw_section_url:pw_spa}}' ) . "\n"
		. _pw_gb_link( 'prop-l3', 'Meetings', '{{pw_section_url:pw_meeting_room}}' ) . "\n"
		. _pw_gb_link( 'prop-l4', 'Experiences', '{{pw_section_url:pw_experience}}' ) . "\n"
		. _pw_gb_link( 'prop-l5', 'Events', '{{pw_section_url:pw_event}}' ) . "\n"
		. _pw_gb_link( 'prop-l6', 'Offers', '{{pw_section_url:pw_offer}}' ) . "\n"
		. _pw_gb_link( 'prop-l7', 'Places Nearby', '{{pw_section_url:pw_nearby}}' )
	);

	// Contacts — generateblocks/query for pw_contact scoped to property.
	$contacts = _pw_gb_section( 'prop-ct',
		_pw_gb_h( 'prop-ch', 'Contacts' ) . "\n"
		. '<!-- wp:generateblocks/query {"uniqueId":"prop-ctq","tagName":"div","query":{"post_type":["pw_contact"],"posts_per_page":20,"orderby":"title","order":"asc"},"className":"pw-gb-scope-property pw-gb-contact-filter-property"} -->' . "\n"
		. '<div class="pw-gb-scope-property pw-gb-contact-filter-property">'
		. '<!-- wp:generateblocks/looper {"uniqueId":"prop-ctlp","tagName":"div","className":"gb-loop-prop-ctlp"} -->' . "\n"
		. '<div class="gb-looper-prop-ctlp gb-loop-prop-ctlp">'
		. '<!-- wp:generateblocks/loop-item {"uniqueId":"prop-ctli","tagName":"div","styles":{"paddingTop":"12px","paddingBottom":"12px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#e0e0e0"},"css":".gb-loop-item-prop-ctli{padding:12px 0;border-bottom:1px solid #e0e0e0}","className":"gb-li-prop-ctli"} -->' . "\n"
		. '<div class="gb-loop-item-prop-ctli gb-li-prop-ctli">'
		. _pw_gb_row( 'prop-c0', 'Label',    '{{post_meta key:_pw_label}}' )    . "\n"
		. _pw_gb_row( 'prop-c1', 'Phone',    '{{post_meta key:_pw_phone}}' )    . "\n"
		. _pw_gb_row( 'prop-c2', 'Mobile',   '{{post_meta key:_pw_mobile}}' )   . "\n"
		. _pw_gb_row( 'prop-c3', 'WhatsApp', '{{post_meta key:_pw_whatsapp}}' ) . "\n"
		. _pw_gb_row( 'prop-c4', 'Email',    '{{post_meta key:_pw_email}}' )
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/loop-item -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/looper -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/query -->'
	);

	return $identity . "\n" . $address . "\n" . $benefits . "\n" . $links . "\n" . $contacts;
}

/* ── Room singular ─────────────────────────────────────────────────────── */

function _pw_markup_room_singular(): string {
	return _pw_gb_img( 'rms-img' ) . "\n"
		. _pw_gb_h( 'rms-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'rms-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_row( 'rms-r0', 'Rate from',         '{{post_meta key:_pw_rate_from}} __PW_PROPERTY_CURRENCY__' ) . "\n"
		. _pw_gb_row( 'rms-r1', 'Rate to',           '{{post_meta key:_pw_rate_to}} __PW_PROPERTY_CURRENCY__' )   . "\n"
		. _pw_gb_row( 'rms-r2', 'Max occupancy',     '{{post_meta key:_pw_max_occupancy}}' )   . "\n"
		. _pw_gb_row( 'rms-r3', 'Max adults',        '{{post_meta key:_pw_max_adults}}' )      . "\n"
		. _pw_gb_row( 'rms-r4', 'Max children',      '{{post_meta key:_pw_max_children}}' )    . "\n"
		. _pw_gb_row( 'rms-r5', 'Size (sqm)',        '{{post_meta key:_pw_size_sqm}}' )        . "\n"
		. _pw_gb_row( 'rms-r6', 'Size (sqft)',       '{{post_meta key:_pw_size_sqft}}' )       . "\n"
		. _pw_gb_row( 'rms-r7', 'Max extra beds',    '{{post_meta key:_pw_max_extra_beds}}' )  . "\n"
		. _pw_gb_row( 'rms-r8', 'Bed type',          '{{post_terms taxonomy:pw_bed_type}}' )   . "\n"
		. _pw_gb_row( 'rms-r9', 'View type',         '{{post_terms taxonomy:pw_view_type}}' )  . "\n"
		. _pw_gb_p( 'rms-ct', '{{post_content}}' ) . "\n"
		. _pw_gb_link( 'rms-bk', "\xE2\x86\x90 Back to Rooms", '{{post_type_archive_link}}' );
}

/* ── Restaurant singular ───────────────────────────────────────────────── */

function _pw_markup_restaurant_singular(): string {
	return _pw_gb_img( 'rsts-img' ) . "\n"
		. _pw_gb_h( 'rsts-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'rsts-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_row( 'rsts-r0', 'Cuisine type',      '{{post_meta key:_pw_cuisine_type}}' )      . "\n"
		. _pw_gb_row( 'rsts-r1', 'Location',          '{{post_meta key:_pw_location}}' )          . "\n"
		. _pw_gb_row( 'rsts-r2', 'Seating capacity',  '{{post_meta key:_pw_seating_capacity}}' )  . "\n"
		. _pw_gb_row( 'rsts-r3', 'Reservation URL',   '{{post_meta key:_pw_reservation_url}}' )   . "\n"
		. _pw_gb_row( 'rsts-r4', 'Menu URL',          '{{post_meta key:_pw_menu_url}}' )          . "\n"
		. _pw_gb_p( 'rsts-ct', '{{post_content}}' ) . "\n"
		. _pw_gb_link( 'rsts-bk', "\xE2\x86\x90 Back to Restaurants", '{{post_type_archive_link}}' );
}

/* ── Spa singular ──────────────────────────────────────────────────────── */

function _pw_markup_spa_singular(): string {
	return _pw_gb_img( 'spas-img' ) . "\n"
		. _pw_gb_h( 'spas-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'spas-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_row( 'spas-r0', 'Minimum age',           '{{post_meta key:_pw_min_age}}' )                    . "\n"
		. _pw_gb_row( 'spas-r1', 'Treatment rooms',       '{{post_meta key:_pw_number_of_treatment_rooms}}' )  . "\n"
		. _pw_gb_row( 'spas-r2', 'Booking URL',           '{{post_meta key:_pw_booking_url}}' )                . "\n"
		. _pw_gb_row( 'spas-r3', 'Menu URL',              '{{post_meta key:_pw_menu_url}}' )                   . "\n"
		. _pw_gb_p( 'spas-ct', '{{post_content}}' ) . "\n"
		. _pw_gb_link( 'spas-bk', "\xE2\x86\x90 Back to Spas", '{{post_type_archive_link}}' );
}

/* ── Meeting room singular ─────────────────────────────────────────────── */

function _pw_markup_meeting_singular(): string {
	return _pw_gb_img( 'mts-img' ) . "\n"
		. _pw_gb_h( 'mts-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'mts-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_row( 'mts-r0', 'Theatre capacity',   '{{post_meta key:_pw_capacity_theatre}}' )   . "\n"
		. _pw_gb_row( 'mts-r1', 'Classroom capacity',  '{{post_meta key:_pw_capacity_classroom}}' ) . "\n"
		. _pw_gb_row( 'mts-r2', 'Boardroom capacity',  '{{post_meta key:_pw_capacity_boardroom}}' ) . "\n"
		. _pw_gb_row( 'mts-r3', 'U-shape capacity',    '{{post_meta key:_pw_capacity_ushape}}' )    . "\n"
		. _pw_gb_row( 'mts-r4', 'Area (sqm)',          '{{post_meta key:_pw_area_sqm}}' )           . "\n"
		. _pw_gb_row( 'mts-r5', 'Area (sqft)',         '{{post_meta key:_pw_area_sqft}}' )          . "\n"
		. _pw_gb_row( 'mts-r6', 'Natural light',       '{{post_meta key:_pw_natural_light}}' )      . "\n"
		. _pw_gb_row( 'mts-r7', 'Sales email',         '{{post_meta key:_pw_sales_email}}' )        . "\n"
		. _pw_gb_row( 'mts-r8', 'Sales phone',         '{{post_meta key:_pw_sales_phone}}' )        . "\n"
		. _pw_gb_p( 'mts-ct', '{{post_content}}' ) . "\n"
		. _pw_gb_link( 'mts-bk', "\xE2\x86\x90 Back to Meetings", '{{post_type_archive_link}}' );
}

/* ── Experience singular ───────────────────────────────────────────────── */

function _pw_markup_experience_singular(): string {
	return _pw_gb_img( 'exs-img' ) . "\n"
		. _pw_gb_h( 'exs-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'exs-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_row( 'exs-r0', 'Duration (hours)',   '{{post_meta key:_pw_duration_hours}}' )   . "\n"
		. _pw_gb_row( 'exs-r1', 'Price from',         '{{post_meta key:_pw_price_from}} __PW_PROPERTY_CURRENCY__' ) . "\n"
		. _pw_gb_row( 'exs-r2', 'Complimentary',      '{{post_meta key:_pw_is_complimentary}}' ) . "\n"
		. _pw_gb_row( 'exs-r3', 'Booking URL',        '{{post_meta key:_pw_booking_url}}' )      . "\n"
		. _pw_gb_p( 'exs-ct', '{{post_content}}' ) . "\n"
		. _pw_gb_link( 'exs-bk', "\xE2\x86\x90 Back to Experiences", '{{post_type_archive_link}}' );
}

/* ── Event singular ────────────────────────────────────────────────────── */

function _pw_markup_event_singular(): string {
	return _pw_gb_img( 'evs-img' ) . "\n"
		. _pw_gb_h( 'evs-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'evs-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_row( 'evs-r0', 'Start',               '{{post_meta key:_pw_start_datetime}}' )        . "\n"
		. _pw_gb_row( 'evs-r1', 'End',                 '{{post_meta key:_pw_end_datetime}}' )          . "\n"
		. _pw_gb_row( 'evs-r2', 'Capacity',            '{{post_meta key:_pw_capacity}}' )              . "\n"
		. _pw_gb_row( 'evs-r3', 'Price from',          '{{post_meta key:_pw_price_from}} __PW_PROPERTY_CURRENCY__' ) . "\n"
		. _pw_gb_row( 'evs-r4', 'Status',              '{{post_meta key:_pw_event_status}}' )          . "\n"
		. _pw_gb_row( 'evs-r5', 'Attendance mode',     '{{post_meta key:_pw_event_attendance_mode}}' ) . "\n"
		. _pw_gb_row( 'evs-r6', 'Booking URL',         '{{post_meta key:_pw_booking_url}}' )           . "\n"
		. _pw_gb_p( 'evs-ct', '{{post_content}}' ) . "\n"
		. _pw_gb_link( 'evs-bk', "\xE2\x86\x90 Back to Events", '#events' );
}

/* ── Offer singular ────────────────────────────────────────────────────── */

function _pw_markup_offer_singular(): string {
	return _pw_gb_h( 'ofs-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'ofs-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_row( 'ofs-r0', 'Offer type',        '{{post_meta key:_pw_offer_type}}' )          . "\n"
		. _pw_gb_row( 'ofs-r1', 'Valid from',         '{{post_meta key:_pw_valid_from}}' )          . "\n"
		. _pw_gb_row( 'ofs-r2', 'Valid to',           '{{post_meta key:_pw_valid_to}}' )            . "\n"
		. _pw_gb_row( 'ofs-r3', 'Discount type',      '{{post_meta key:_pw_discount_type}}' )       . "\n"
		. _pw_gb_row( 'ofs-r4', 'Discount value',     '{{post_meta key:_pw_discount_value}}' )      . "\n"
		. _pw_gb_row( 'ofs-r5', 'Min. stay nights',   '{{post_meta key:_pw_minimum_stay_nights}}' ) . "\n"
		. _pw_gb_row( 'ofs-r6', 'Featured',           '{{post_meta key:_pw_is_featured}}' )         . "\n"
		. _pw_gb_row( 'ofs-r7', 'Booking URL',        '{{post_meta key:_pw_booking_url}}' )         . "\n"
		. _pw_gb_p( 'ofs-ct', '{{post_content}}' ) . "\n"
		. _pw_gb_link( 'ofs-bk', "\xE2\x86\x90 Back to Offers", '{{post_type_archive_link}}' );
}

/* ── Nearby place singular ─────────────────────────────────────────────── */

function _pw_markup_nearby_singular(): string {
	return _pw_gb_img( 'pls-img' ) . "\n"
		. _pw_gb_h( 'pls-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'pls-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_row( 'pls-r0', 'Distance (km)',    '{{post_meta key:_pw_distance_km}}' )    . "\n"
		. _pw_gb_row( 'pls-r1', 'Travel time (min)', '{{post_meta key:_pw_travel_time_min}}' ) . "\n"
		. _pw_gb_row( 'pls-r2', 'Latitude',          '{{post_meta key:_pw_lat}}' )             . "\n"
		. _pw_gb_row( 'pls-r3', 'Longitude',         '{{post_meta key:_pw_lng}}' )             . "\n"
		. _pw_gb_row( 'pls-r4', 'Place URL',         '{{post_meta key:_pw_place_url}}' )       . "\n"
		. _pw_gb_p( 'pls-ct', '{{post_content}}' ) . "\n"
		. _pw_gb_link( 'pls-bk', "\xE2\x86\x90 Back to Places Nearby", '{{post_type_archive_link}}' );
}

/**
 * True if any section plural base changed between two merged settings arrays.
 */
function pw_installer_section_plural_bases_changed( array $before, array $after ): bool {
	$ob = isset( $before['pw_section_bases'] ) && is_array( $before['pw_section_bases'] ) ? $before['pw_section_bases'] : [];
	$na = isset( $after['pw_section_bases'] ) && is_array( $after['pw_section_bases'] ) ? $after['pw_section_bases'] : [];

	foreach ( array_keys( pw_default_section_bases() ) as $cpt ) {
		$op = isset( $ob[ $cpt ]['plural'] ) ? (string) $ob[ $cpt ]['plural'] : '';
		$np = isset( $na[ $cpt ]['plural'] ) ? (string) $na[ $cpt ]['plural'] : '';
		if ( $op !== $np ) {
			return true;
		}
	}
	return false;
}

function pw_handle_admin_post_pw_run_page_installer() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to run this action.', 'portico-webworks' ) );
	}
	check_admin_referer( 'pw_run_page_installer' );

	pw_repair_element_block_types();

	$results = pw_run_page_installer_all_scopes();
	set_transient( 'pw_installer_manual_results', pw_summarize_installer_results( $results ), 120 );

	// Admin redirect — pw_redirect_with_qs() not required.
	wp_safe_redirect(
		add_query_arg(
			'pw_installer_ran',
			'1',
			pw_admin_permalinks_url()
		)
	);
	exit;
}

add_action( 'admin_post_pw_run_page_installer', 'pw_handle_admin_post_pw_run_page_installer' );
