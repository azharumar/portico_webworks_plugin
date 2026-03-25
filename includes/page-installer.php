<?php
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PW_FACT_SHEET_PAGE_SLUG' ) ) {
	define( 'PW_FACT_SHEET_PAGE_SLUG', 'fact-sheet' );
}

add_action( 'init', 'pw_register_page_installer_meta', 8 );
add_action( 'transition_post_status', 'pw_on_property_published', 10, 3 );
add_action( 'admin_post_pw_run_page_installer', 'pw_handle_admin_post_pw_run_page_installer' );

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

function pw_get_fact_sheet_starter_markup(): string {
	return '';
}

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
			'posts_per_page'   => 1,
			'no_found_rows'    => true,
			'suppress_filters' => true,
			'meta_query'       => [
				[
					'key'   => '_pw_generated',
					'value' => '1',
				],
				[
					'key'   => '_pw_property_id',
					'value' => (string) (int) $property_id,
				],
			],
		]
	);

	return ( $posts && $posts[0] instanceof WP_Post ) ? $posts[0] : null;
}

/**
 * @param array{title: string, slug: string, property_id: int, cpt: string, type: string, kind?: string} $page_def
 * @return array{action: string, post_id: int, message: string}
 */
function pw_install_page( array $page_def ): array {
	$slug    = sanitize_title( (string) ( $page_def['slug'] ?? '' ) );
	$prop_id = (int) ( $page_def['property_id'] ?? 0 );
	$title   = (string) ( $page_def['title'] ?? $slug );
	$kind    = (string) ( $page_def['kind'] ?? '' );

	if ( $slug === '' ) {
		return [ 'action' => 'skipped', 'post_id' => 0, 'message' => __( 'Missing slug.', 'portico-webworks' ) ];
	}

	$existing = pw_find_generated_page( $slug, $prop_id );
	if ( $existing instanceof WP_Post ) {
		return [
			'action'  => 'skipped',
			'post_id' => (int) $existing->ID,
			'message' => __( 'Already exists, no changes needed.', 'portico-webworks' ),
		];
	}

	$by_path = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $by_path instanceof WP_Post && get_post_meta( $by_path->ID, '_pw_generated', true ) !== '1' ) {
		return [
			'action'  => 'conflict',
			'post_id' => (int) $by_path->ID,
			'message' => __( 'Slug already used by an existing page.', 'portico-webworks' ),
		];
	}

	$content = '';
	if ( $kind === 'fact_sheet' ) {
		$content = pw_get_fact_sheet_starter_markup();
	}

	if ( function_exists( 'pw_reserved_slug_installer_active' ) ) {
		pw_reserved_slug_installer_active( true );
	}
	$post_id = wp_insert_post(
		wp_slash(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $content,
			]
		),
		true
	);
	if ( function_exists( 'pw_reserved_slug_installer_active' ) ) {
		pw_reserved_slug_installer_active( false );
	}

	if ( is_wp_error( $post_id ) ) {
		return [ 'action' => 'skipped', 'post_id' => 0, 'message' => $post_id->get_error_message() ];
	}

	update_post_meta( $post_id, '_pw_generated', '1' );
	update_post_meta( $post_id, '_pw_property_id', $prop_id );
	update_post_meta( $post_id, '_pw_static_url_segment', $slug );

	return [
		'action'  => 'created',
		'post_id' => (int) $post_id,
		'message' => __( 'Created.', 'portico-webworks' ),
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

	return $all;
}

/**
 * @param array<int, array{action: string, post_id: int, message: string}> $results
 * @return array{created: int, updated: int, unchanged: int, conflict: int, conflict_messages: string[]}
 */
function pw_summarize_installer_results( array $results ): array {
	$out = [
		'created'           => 0,
		'updated'           => 0,
		'unchanged'         => 0,
		'conflict'          => 0,
		'conflict_messages' => [],
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
}

function pw_handle_admin_post_pw_run_page_installer() {
	if (
		! current_user_can( 'manage_options' ) ||
		! isset( $_POST['pw_run_page_installer_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pw_run_page_installer_nonce'] ) ), 'pw_run_page_installer' )
	) {
		wp_die( 'Unauthorised' );
	}

	pw_run_page_installer_all_scopes();

	wp_safe_redirect( wp_get_referer() ?: admin_url( 'admin.php?page=' . urlencode( pw_admin_page_slug() ) ) );
	exit;
}

<?php
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PW_FACT_SHEET_PAGE_SLUG' ) ) {
	define( 'PW_FACT_SHEET_PAGE_SLUG', 'fact-sheet' );
}

add_action( 'init', 'pw_register_page_installer_meta', 8 );
add_action( 'transition_post_status', 'pw_on_property_published', 10, 3 );
add_action( 'admin_post_pw_run_page_installer', 'pw_handle_admin_post_pw_run_page_installer' );

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

function pw_get_fact_sheet_starter_markup(): string {
	return '';
}

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
			'posts_per_page'   => 1,
			'no_found_rows'    => true,
			'suppress_filters' => true,
			'meta_query'       => [
				[
					'key'   => '_pw_generated',
					'value' => '1',
				],
				[
					'key'   => '_pw_property_id',
					'value' => (string) (int) $property_id,
				],
			],
		]
	);

	return ( $posts && $posts[0] instanceof WP_Post ) ? $posts[0] : null;
}

/**
 * @param array{title: string, slug: string, property_id: int, cpt: string, type: string, kind?: string} $page_def
 * @return array{action: string, post_id: int, message: string}
 */
function pw_install_page( array $page_def ): array {
	$slug    = sanitize_title( (string) ( $page_def['slug'] ?? '' ) );
	$prop_id = (int) ( $page_def['property_id'] ?? 0 );
	$title   = (string) ( $page_def['title'] ?? $slug );
	$kind    = (string) ( $page_def['kind'] ?? '' );

	if ( $slug === '' ) {
		return [ 'action' => 'skipped', 'post_id' => 0, 'message' => __( 'Missing slug.', 'portico-webworks' ) ];
	}

	$existing = pw_find_generated_page( $slug, $prop_id );
	if ( $existing instanceof WP_Post ) {
		return [
			'action'  => 'skipped',
			'post_id' => (int) $existing->ID,
			'message' => __( 'Already exists, no changes needed.', 'portico-webworks' ),
		];
	}

	$by_path = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $by_path instanceof WP_Post && get_post_meta( $by_path->ID, '_pw_generated', true ) !== '1' ) {
		return [
			'action'  => 'conflict',
			'post_id' => (int) $by_path->ID,
			'message' => __( 'Slug already used by an existing page.', 'portico-webworks' ),
		];
	}

	$content = '';
	if ( $kind === 'fact_sheet' ) {
		$content = pw_get_fact_sheet_starter_markup();
	}

	if ( function_exists( 'pw_reserved_slug_installer_active' ) ) {
		pw_reserved_slug_installer_active( true );
	}
	$post_id = wp_insert_post(
		wp_slash(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $content,
			]
		),
		true
	);
	if ( function_exists( 'pw_reserved_slug_installer_active' ) ) {
		pw_reserved_slug_installer_active( false );
	}

	if ( is_wp_error( $post_id ) ) {
		return [ 'action' => 'skipped', 'post_id' => 0, 'message' => $post_id->get_error_message() ];
	}

	update_post_meta( $post_id, '_pw_generated', '1' );
	update_post_meta( $post_id, '_pw_property_id', $prop_id );
	update_post_meta( $post_id, '_pw_static_url_segment', $slug );

	return [
		'action'  => 'created',
		'post_id' => (int) $post_id,
		'message' => __( 'Created.', 'portico-webworks' ),
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

	return $all;
}

/**
 * @param array<int, array{action: string, post_id: int, message: string}> $results
 * @return array{created: int, updated: int, unchanged: int, conflict: int, conflict_messages: string[]}
 */
function pw_summarize_installer_results( array $results ): array {
	$out = [
		'created'           => 0,
		'updated'           => 0,
		'unchanged'         => 0,
		'conflict'          => 0,
		'conflict_messages' => [],
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
}

function pw_handle_admin_post_pw_run_page_installer() {
	if (
		! current_user_can( 'manage_options' ) ||
		! isset( $_POST['pw_run_page_installer_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pw_run_page_installer_nonce'] ) ), 'pw_run_page_installer' )
	) {
		wp_die( 'Unauthorised' );
	}

	pw_run_page_installer_all_scopes();

	wp_safe_redirect( wp_get_referer() ?: admin_url( 'admin.php?page=' . urlencode( pw_admin_page_slug() ) ) );
	exit;
}

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
	return '';
}

/**
 * GenerateBlocks starter for GP Premium site header element (GB Pro-style utility bar, main row, breadcrumbs).
 */
function pw_get_site_header_starter_markup(): string {
	return '';
}

/**
 * GenerateBlocks starter for GP Premium site footer element (GB Pro-style three-column + separator).
 */
function pw_get_site_footer_starter_markup(): string {
	return '';
}

/**
 * @return array<int, array{title: string, cpt: string, slug: string, type: string}>
 */
function pw_get_required_elements(): array {
	return [];
}

/**
 * Repair _generate_block_type, `_generate_element_display_conditions`, and legacy hash / section hrefs on plugin-generated gp_elements.
 */
function pw_repair_element_block_types(): void {
	return;
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
 * Installer-managed site header Block Element (slug pw-site-header).
 */
function pw_find_generated_site_header_element(): ?WP_Post {
	if ( ! post_type_exists( 'gp_elements' ) ) {
		return null;
	}
	$slug = 'pw-site-header';
	$posts = get_posts(
		[
			'post_type'        => 'gp_elements',
			'name'             => $slug,
			'post_status'      => [ 'publish', 'draft', 'private' ],
			'posts_per_page'   => 1,
			'meta_key'         => '_pw_generated',
			'meta_value'       => '1',
			'no_found_rows'    => true,
			'suppress_filters' => true,
		]
	);
	if ( empty( $posts ) || ! $posts[0] instanceof WP_Post ) {
		return null;
	}

	return $posts[0];
}

/**
 * @return array{action: 'created'|'skipped', post_id: int, message: string}
 */
function pw_install_site_header_element(): array {
	if ( ! post_type_exists( 'gp_elements' ) ) {
		return [
			'action'  => 'skipped',
			'post_id' => 0,
			'message' => __( 'GeneratePress Elements not found.', 'portico-webworks' ),
		];
	}

	$existing = pw_find_generated_site_header_element();
	if ( $existing ) {
		return [
			'action'  => 'skipped',
			'post_id' => (int) $existing->ID,
			'message' => __( 'Site header element already exists.', 'portico-webworks' ),
		];
	}

	$content = pw_get_site_header_starter_markup();
	if ( $content === '' ) {
		return [
			'action'  => 'skipped',
			'post_id' => 0,
			'message' => __( 'Site header starter markup missing.', 'portico-webworks' ),
		];
	}

	$post_id = wp_insert_post(
		wp_slash(
			[
				'post_title'   => __( 'Portico Site Header', 'portico-webworks' ),
				'post_name'    => 'pw-site-header',
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

	$post_id = (int) $post_id;
	update_post_meta( $post_id, '_generate_element_type', 'block' );
	update_post_meta( $post_id, '_generate_block_type', 'site-header' );
	update_post_meta( $post_id, '_pw_generated', '1' );
	update_post_meta( $post_id, '_pw_section_cpt', '' );
	update_post_meta( $post_id, '_pw_element_type', 'site_header' );
	update_post_meta(
		$post_id,
		'_generate_element_display_conditions',
		[
			[
				'rule'   => 'general:site',
				'object' => '',
			],
		]
	);
	update_post_meta( $post_id, '_generate_element_conditions', [] );
	update_post_meta( $post_id, '_generate_element_is_content', '' );

	if ( function_exists( 'pw_maybe_seed_portico_nav_menus' ) ) {
		pw_maybe_seed_portico_nav_menus();
	}

	return [
		'action'  => 'created',
		'post_id' => $post_id,
		'message' => __( "Created element 'Portico Site Header' (pw-site-header)", 'portico-webworks' ),
	];
}

/**
 * Installer-managed site footer Block Element (slug pw-site-footer).
 */
function pw_find_generated_site_footer_element(): ?WP_Post {
	if ( ! post_type_exists( 'gp_elements' ) ) {
		return null;
	}
	$slug  = 'pw-site-footer';
	$posts = get_posts(
		[
			'post_type'        => 'gp_elements',
			'name'             => $slug,
			'post_status'      => [ 'publish', 'draft', 'private' ],
			'posts_per_page'   => 1,
			'meta_key'         => '_pw_generated',
			'meta_value'       => '1',
			'no_found_rows'    => true,
			'suppress_filters' => true,
		]
	);
	if ( empty( $posts ) || ! $posts[0] instanceof WP_Post ) {
		return null;
	}

	return $posts[0];
}

/**
 * @return array{action: 'created'|'skipped', post_id: int, message: string}
 */
function pw_install_site_footer_element(): array {
	if ( ! post_type_exists( 'gp_elements' ) ) {
		return [
			'action'  => 'skipped',
			'post_id' => 0,
			'message' => __( 'GeneratePress Elements not found.', 'portico-webworks' ),
		];
	}

	$existing = pw_find_generated_site_footer_element();
	if ( $existing ) {
		return [
			'action'  => 'skipped',
			'post_id' => (int) $existing->ID,
			'message' => __( 'Site footer element already exists.', 'portico-webworks' ),
		];
	}

	$content = pw_get_site_footer_starter_markup();
	if ( $content === '' ) {
		return [
			'action'  => 'skipped',
			'post_id' => 0,
			'message' => __( 'Site footer starter markup missing.', 'portico-webworks' ),
		];
	}

	$post_id = wp_insert_post(
		wp_slash(
			[
				'post_title'   => __( 'Portico Site Footer', 'portico-webworks' ),
				'post_name'    => 'pw-site-footer',
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

	$post_id = (int) $post_id;
	update_post_meta( $post_id, '_generate_element_type', 'block' );
	update_post_meta( $post_id, '_generate_block_type', 'site-footer' );
	update_post_meta( $post_id, '_pw_generated', '1' );
	update_post_meta( $post_id, '_pw_section_cpt', '' );
	update_post_meta( $post_id, '_pw_element_type', 'site_footer' );
	update_post_meta(
		$post_id,
		'_generate_element_display_conditions',
		[
			[
				'rule'   => 'general:site',
				'object' => '',
			],
		]
	);
	update_post_meta( $post_id, '_generate_element_conditions', [] );
	update_post_meta( $post_id, '_generate_element_is_content', '' );

	return [
		'action'  => 'created',
		'post_id' => $post_id,
		'message' => __( "Created element 'Portico Site Footer' (pw-site-footer)", 'portico-webworks' ),
	];
}

/**
 * @return array<int, array{action: string, post_id: int, message: string}>
 */
function pw_run_elements_installer(): array {
	return [];
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

	return $all;
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
 * Guest-facing card grids and singular heroes; repeatable meta rendered via HTML placeholders (see _pw_gb_needs_shortcode_placeholder).
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

	$body = match ( $cpt ) {
		'pw_room_type' => <<<'PW_ST_ROOM_TYPE'
<!-- wp:generateblocks/query {"uniqueId":"rmq","tagName":"div","query":{"post_type":["pw_room_type"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/element {"uniqueId":"rm-starter-root","tagName":"div","styles":{"width":"100%"},"css":".gb-element-rm-starter-root{width:100%}","className":"pw-hotel-starter-root gb-el gb-el-rm-starter-root"} -->
<div class="gb-element-rm-starter-root gb-el gb-el-rm-starter-root pw-hotel-starter-root">
<!-- wp:generateblocks/element {"uniqueId":"rm-grid","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"1fr","columnGap":"24px","rowGap":"24px","width":"100%"},"css":".gb-element-rm-grid{display:grid;grid-template-columns:1fr;column-gap:24px;row-gap:24px;width:100%}@media(min-width:640px){.gb-element-rm-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(min-width:1000px){.gb-element-rm-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}","className":"pw-hotel-card-grid gb-el gb-el-rm-grid"} -->
<div class="gb-element-rm-grid gb-el gb-el-rm-grid pw-hotel-card-grid">
<!-- wp:generateblocks/looper {"uniqueId":"rm-loop","tagName":"div","className":"gb-loop-rm-loop"} -->
<div class="gb-looper-rm-loop gb-loop-rm-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"rm-item","tagName":"div","className":"gb-li-rm-item"} -->
<div class="gb-loop-item gb-loop-item-rm-item gb-li-rm-item">
<!-- wp:generateblocks/element {"uniqueId":"rm-card","tagName":"article","styles":{"overflow":"hidden","borderTopLeftRadius":"12px","borderTopRightRadius":"12px","borderBottomLeftRadius":"12px","borderBottomRightRadius":"12px","borderTopWidth":"1px","borderRightWidth":"1px","borderBottomWidth":"1px","borderLeftWidth":"1px","borderTopStyle":"solid","borderRightStyle":"solid","borderBottomStyle":"solid","borderLeftStyle":"solid","borderTopColor":"#e8e8e8","borderRightColor":"#e8e8e8","borderBottomColor":"#e8e8e8","borderLeftColor":"#e8e8e8","boxShadow":"0 8px 24px rgba(0,0,0,0.06)","backgroundColor":"#ffffff","height":"100%","display":"flex","flexDirection":"column"},"css":".gb-element-rm-card{background-color:#fff;border:1px solid #e8e8e8;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);display:flex;flex-direction:column;height:100%;overflow:hidden}","className":"pw-hotel-card gb-el gb-el-rm-card"} -->
<article class="gb-element-rm-card gb-el gb-el-rm-card pw-hotel-card">
<!-- wp:generateblocks/element {"uniqueId":"rm-card-media","tagName":"div","styles":{"overflow":"hidden","width":"100%","maxHeight":"220px"},"css":".gb-element-rm-card-media{max-height:220px;overflow:hidden;width:100%}","className":"gb-el gb-el-rm-card-media"} -->
<div class="gb-element-rm-card-media gb-el gb-el-rm-card-media">
<!-- wp:generateblocks/image {"uniqueId":"rm-fi","styles":{"display":"block","width":"100%","maxWidth":"100%","minHeight":"200px","marginBottom":"0px","objectFit":"cover"},"css":".gb-image-rm-fi{display:block;margin-bottom:0;max-width:100%;min-height:200px;object-fit:cover;width:100%}","dynamicImage":"featured-image","className":"gb-img-rm-fi"} -->
<img class="gb-image gb-image-rm-fi gb-img-rm-fi" />
<!-- /wp:generateblocks/image -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"rm-card-body","tagName":"div","styles":{"display":"flex","flexDirection":"column","flexGrow":"1","paddingTop":"24px","paddingRight":"24px","paddingBottom":"24px","paddingLeft":"24px"},"css":".gb-element-rm-card-body{display:flex;flex-direction:column;flex-grow:1;padding:24px}","className":"gb-el gb-el-rm-card-body"} -->
<div class="gb-element-rm-card-body gb-el gb-el-rm-card-body">
<!-- wp:generateblocks/text {"uniqueId":"rt_t","tagName":"a","styles":{"marginBottom":"12px","fontSize":"22px","fontWeight":"600","display":"inline-block","textDecoration":"none","lineHeight":"1.25"},"css":".gb-text-rt_t{display:inline-block;font-size:22px;font-weight:600;line-height:1.25;margin-bottom:12px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-rt_t"} -->
<a class="gb-text gb-text-rt_t gb-t-rt_t" href="{{post_permalink}}">{{post_title}}</a>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rt_e","tagName":"p","styles":{"marginBottom":"16px","fontSize":"16px","lineHeight":"1.55","color":"#4a4a4a"},"css":".gb-text-rt_e{color:#4a4a4a;font-size:16px;line-height:1.55;margin-bottom:16px}","className":"gb-t-rt_e"} -->
<p class="gb-text gb-text-rt_e gb-t-rt_e">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"rm-specs","tagName":"div","styles":{"display":"flex","flexDirection":"column","rowGap":"8px","marginBottom":"20px","width":"100%"},"css":".gb-element-rm-specs{display:flex;flex-direction:column;gap:8px;margin-bottom:20px;width:100%}","className":"gb-el gb-el-rm-specs"} -->
<div class="gb-element-rm-specs gb-el gb-el-rm-specs">
<!-- wp:generateblocks/text {"uniqueId":"rm-sp0","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-rm-sp0{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-rm-sp0"} -->
<p class="gb-text gb-text-rm-sp0 gb-t-rm-sp0">From {{post_meta key:_pw_rate_from}} __PW_PROPERTY_CURRENCY__</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rm-sp1","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-rm-sp1{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-rm-sp1"} -->
<p class="gb-text gb-text-rm-sp1 gb-t-rm-sp1">Sleeps {{post_meta key:_pw_max_occupancy}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rm-sp2","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-rm-sp2{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-rm-sp2"} -->
<p class="gb-text gb-text-rm-sp2 gb-t-rm-sp2">{{post_terms taxonomy:pw_bed_type}}</p>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"rm-cta","tagName":"a","styles":{"display":"inline-block","marginTop":"auto","paddingTop":"12px","paddingRight":"22px","paddingBottom":"12px","paddingLeft":"22px","fontSize":"15px","fontWeight":"600","textDecoration":"none","color":"#ffffff","backgroundColor":"#1a1a1a","borderTopLeftRadius":"6px","borderTopRightRadius":"6px","borderBottomLeftRadius":"6px","borderBottomRightRadius":"6px"},"css":".gb-text-rm-cta{background-color:#1a1a1a;border-radius:6px;color:#fff;display:inline-block;font-size:15px;font-weight:600;margin-top:auto;padding:12px 22px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-rm-cta pw-hotel-cta"} -->
<a class="gb-text gb-text-rm-cta gb-t-rm-cta pw-hotel-cta" href="{{post_permalink}}">View room</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</article>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"rm-nr","tagName":"p","styles":{"marginBottom":"0px","fontSize":"16px"},"css":".gb-text-rm-nr{font-size:16px;margin-bottom:0}","className":"gb-t-rm-nr"} -->
<p class="gb-text gb-text-rm-nr gb-t-rm-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_ROOM_TYPE,
		'pw_restaurant' => <<<'PW_ST_RESTAURANT'
<!-- wp:generateblocks/query {"uniqueId":"rstq","tagName":"div","query":{"post_type":["pw_restaurant"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/element {"uniqueId":"rst-starter-root","tagName":"div","styles":{"width":"100%"},"css":".gb-element-rst-starter-root{width:100%}","className":"pw-hotel-starter-root gb-el gb-el-rst-starter-root"} -->
<div class="gb-element-rst-starter-root gb-el gb-el-rst-starter-root pw-hotel-starter-root">
<!-- wp:generateblocks/element {"uniqueId":"rst-grid","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"1fr","columnGap":"24px","rowGap":"24px","width":"100%"},"css":".gb-element-rst-grid{display:grid;grid-template-columns:1fr;column-gap:24px;row-gap:24px;width:100%}@media(min-width:640px){.gb-element-rst-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(min-width:1000px){.gb-element-rst-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}","className":"pw-hotel-card-grid gb-el gb-el-rst-grid"} -->
<div class="gb-element-rst-grid gb-el gb-el-rst-grid pw-hotel-card-grid">
<!-- wp:generateblocks/looper {"uniqueId":"rst-loop","tagName":"div","className":"gb-loop-rst-loop"} -->
<div class="gb-looper-rst-loop gb-loop-rst-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"rst-item","tagName":"div","className":"gb-li-rst-item"} -->
<div class="gb-loop-item gb-loop-item-rst-item gb-li-rst-item">
<!-- wp:generateblocks/element {"uniqueId":"rst-card","tagName":"article","styles":{"overflow":"hidden","borderTopLeftRadius":"12px","borderTopRightRadius":"12px","borderBottomLeftRadius":"12px","borderBottomRightRadius":"12px","borderTopWidth":"1px","borderRightWidth":"1px","borderBottomWidth":"1px","borderLeftWidth":"1px","borderTopStyle":"solid","borderRightStyle":"solid","borderBottomStyle":"solid","borderLeftStyle":"solid","borderTopColor":"#e8e8e8","borderRightColor":"#e8e8e8","borderBottomColor":"#e8e8e8","borderLeftColor":"#e8e8e8","boxShadow":"0 8px 24px rgba(0,0,0,0.06)","backgroundColor":"#ffffff","height":"100%","display":"flex","flexDirection":"column"},"css":".gb-element-rst-card{background-color:#fff;border:1px solid #e8e8e8;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);display:flex;flex-direction:column;height:100%;overflow:hidden}","className":"pw-hotel-card gb-el gb-el-rst-card"} -->
<article class="gb-element-rst-card gb-el gb-el-rst-card pw-hotel-card">
<!-- wp:generateblocks/element {"uniqueId":"rst-card-media","tagName":"div","styles":{"overflow":"hidden","width":"100%","maxHeight":"220px"},"css":".gb-element-rst-card-media{max-height:220px;overflow:hidden;width:100%}","className":"gb-el gb-el-rst-card-media"} -->
<div class="gb-element-rst-card-media gb-el gb-el-rst-card-media">
<!-- wp:generateblocks/image {"uniqueId":"rst-fi","styles":{"display":"block","width":"100%","maxWidth":"100%","minHeight":"200px","marginBottom":"0px","objectFit":"cover"},"css":".gb-image-rst-fi{display:block;margin-bottom:0;max-width:100%;min-height:200px;object-fit:cover;width:100%}","dynamicImage":"featured-image","className":"gb-img-rst-fi"} -->
<img class="gb-image gb-image-rst-fi gb-img-rst-fi" />
<!-- /wp:generateblocks/image -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"rst-card-body","tagName":"div","styles":{"display":"flex","flexDirection":"column","flexGrow":"1","paddingTop":"24px","paddingRight":"24px","paddingBottom":"24px","paddingLeft":"24px"},"css":".gb-element-rst-card-body{display:flex;flex-direction:column;flex-grow:1;padding:24px}","className":"gb-el gb-el-rst-card-body"} -->
<div class="gb-element-rst-card-body gb-el gb-el-rst-card-body">
<!-- wp:generateblocks/text {"uniqueId":"rs_t","tagName":"a","styles":{"marginBottom":"12px","fontSize":"22px","fontWeight":"600","display":"inline-block","textDecoration":"none","lineHeight":"1.25"},"css":".gb-text-rs_t{display:inline-block;font-size:22px;font-weight:600;line-height:1.25;margin-bottom:12px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-rs_t"} -->
<a class="gb-text gb-text-rs_t gb-t-rs_t" href="{{post_permalink}}">{{post_title}}</a>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rs_e","tagName":"p","styles":{"marginBottom":"16px","fontSize":"16px","lineHeight":"1.55","color":"#4a4a4a"},"css":".gb-text-rs_e{color:#4a4a4a;font-size:16px;line-height:1.55;margin-bottom:16px}","className":"gb-t-rs_e"} -->
<p class="gb-text gb-text-rs_e gb-t-rs_e">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"rst-specs","tagName":"div","styles":{"display":"flex","flexDirection":"column","rowGap":"8px","marginBottom":"20px","width":"100%"},"css":".gb-element-rst-specs{display:flex;flex-direction:column;gap:8px;margin-bottom:20px;width:100%}","className":"gb-el gb-el-rst-specs"} -->
<div class="gb-element-rst-specs gb-el gb-el-rst-specs">
<!-- wp:generateblocks/text {"uniqueId":"rst-sp0","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-rst-sp0{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-rst-sp0"} -->
<p class="gb-text gb-text-rst-sp0 gb-t-rst-sp0">{{post_meta key:_pw_cuisine_type}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rst-sp1","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-rst-sp1{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-rst-sp1"} -->
<p class="gb-text gb-text-rst-sp1 gb-t-rst-sp1">{{post_meta key:_pw_location}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"rst-sp2","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-rst-sp2{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-rst-sp2"} -->
<p class="gb-text gb-text-rst-sp2 gb-t-rst-sp2">{{post_terms taxonomy:pw_meal_period}} · {{post_meta key:_pw_seating_capacity}} seats</p>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"rst-cta","tagName":"a","styles":{"display":"inline-block","marginTop":"auto","paddingTop":"12px","paddingRight":"22px","paddingBottom":"12px","paddingLeft":"22px","fontSize":"15px","fontWeight":"600","textDecoration":"none","color":"#ffffff","backgroundColor":"#1a1a1a","borderTopLeftRadius":"6px","borderTopRightRadius":"6px","borderBottomLeftRadius":"6px","borderBottomRightRadius":"6px"},"css":".gb-text-rst-cta{background-color:#1a1a1a;border-radius:6px;color:#fff;display:inline-block;font-size:15px;font-weight:600;margin-top:auto;padding:12px 22px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-rst-cta pw-hotel-cta"} -->
<a class="gb-text gb-text-rst-cta gb-t-rst-cta pw-hotel-cta" href="{{post_permalink}}">View restaurant</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</article>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"rst-nr","tagName":"p","styles":{"marginBottom":"0px","fontSize":"16px"},"css":".gb-text-rst-nr{font-size:16px;margin-bottom:0}","className":"gb-t-rst-nr"} -->
<p class="gb-text gb-text-rst-nr gb-t-rst-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_RESTAURANT,
		'pw_spa' => <<<'PW_ST_SPA'
<!-- wp:generateblocks/query {"uniqueId":"spaq","tagName":"div","query":{"post_type":["pw_spa"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/element {"uniqueId":"spa-starter-root","tagName":"div","styles":{"width":"100%"},"css":".gb-element-spa-starter-root{width:100%}","className":"pw-hotel-starter-root gb-el gb-el-spa-starter-root"} -->
<div class="gb-element-spa-starter-root gb-el gb-el-spa-starter-root pw-hotel-starter-root">
<!-- wp:generateblocks/element {"uniqueId":"spa-grid","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"1fr","columnGap":"24px","rowGap":"24px","width":"100%"},"css":".gb-element-spa-grid{display:grid;grid-template-columns:1fr;column-gap:24px;row-gap:24px;width:100%}@media(min-width:640px){.gb-element-spa-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(min-width:1000px){.gb-element-spa-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}","className":"pw-hotel-card-grid gb-el gb-el-spa-grid"} -->
<div class="gb-element-spa-grid gb-el gb-el-spa-grid pw-hotel-card-grid">
<!-- wp:generateblocks/looper {"uniqueId":"spa-loop","tagName":"div","className":"gb-loop-spa-loop"} -->
<div class="gb-looper-spa-loop gb-loop-spa-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"spa-item","tagName":"div","className":"gb-li-spa-item"} -->
<div class="gb-loop-item gb-loop-item-spa-item gb-li-spa-item">
<!-- wp:generateblocks/element {"uniqueId":"spa-card","tagName":"article","styles":{"overflow":"hidden","borderTopLeftRadius":"12px","borderTopRightRadius":"12px","borderBottomLeftRadius":"12px","borderBottomRightRadius":"12px","borderTopWidth":"1px","borderRightWidth":"1px","borderBottomWidth":"1px","borderLeftWidth":"1px","borderTopStyle":"solid","borderRightStyle":"solid","borderBottomStyle":"solid","borderLeftStyle":"solid","borderTopColor":"#e8e8e8","borderRightColor":"#e8e8e8","borderBottomColor":"#e8e8e8","borderLeftColor":"#e8e8e8","boxShadow":"0 8px 24px rgba(0,0,0,0.06)","backgroundColor":"#ffffff","height":"100%","display":"flex","flexDirection":"column"},"css":".gb-element-spa-card{background-color:#fff;border:1px solid #e8e8e8;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);display:flex;flex-direction:column;height:100%;overflow:hidden}","className":"pw-hotel-card gb-el gb-el-spa-card"} -->
<article class="gb-element-spa-card gb-el gb-el-spa-card pw-hotel-card">
<!-- wp:generateblocks/element {"uniqueId":"spa-card-media","tagName":"div","styles":{"overflow":"hidden","width":"100%","maxHeight":"220px"},"css":".gb-element-spa-card-media{max-height:220px;overflow:hidden;width:100%}","className":"gb-el gb-el-spa-card-media"} -->
<div class="gb-element-spa-card-media gb-el gb-el-spa-card-media">
<!-- wp:generateblocks/image {"uniqueId":"spa-fi","styles":{"display":"block","width":"100%","maxWidth":"100%","minHeight":"200px","marginBottom":"0px","objectFit":"cover"},"css":".gb-image-spa-fi{display:block;margin-bottom:0;max-width:100%;min-height:200px;object-fit:cover;width:100%}","dynamicImage":"featured-image","className":"gb-img-spa-fi"} -->
<img class="gb-image gb-image-spa-fi gb-img-spa-fi" />
<!-- /wp:generateblocks/image -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"spa-card-body","tagName":"div","styles":{"display":"flex","flexDirection":"column","flexGrow":"1","paddingTop":"24px","paddingRight":"24px","paddingBottom":"24px","paddingLeft":"24px"},"css":".gb-element-spa-card-body{display:flex;flex-direction:column;flex-grow:1;padding:24px}","className":"gb-el gb-el-spa-card-body"} -->
<div class="gb-element-spa-card-body gb-el gb-el-spa-card-body">
<!-- wp:generateblocks/text {"uniqueId":"sp_t","tagName":"a","styles":{"marginBottom":"12px","fontSize":"22px","fontWeight":"600","display":"inline-block","textDecoration":"none","lineHeight":"1.25"},"css":".gb-text-sp_t{display:inline-block;font-size:22px;font-weight:600;line-height:1.25;margin-bottom:12px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-sp_t"} -->
<a class="gb-text gb-text-sp_t gb-t-sp_t" href="{{post_permalink}}">{{post_title}}</a>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"sp_e","tagName":"p","styles":{"marginBottom":"16px","fontSize":"16px","lineHeight":"1.55","color":"#4a4a4a"},"css":".gb-text-sp_e{color:#4a4a4a;font-size:16px;line-height:1.55;margin-bottom:16px}","className":"gb-t-sp_e"} -->
<p class="gb-text gb-text-sp_e gb-t-sp_e">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"spa-specs","tagName":"div","styles":{"display":"flex","flexDirection":"column","rowGap":"8px","marginBottom":"20px","width":"100%"},"css":".gb-element-spa-specs{display:flex;flex-direction:column;gap:8px;margin-bottom:20px;width:100%}","className":"gb-el gb-el-spa-specs"} -->
<div class="gb-element-spa-specs gb-el gb-el-spa-specs">
<!-- wp:generateblocks/text {"uniqueId":"spa-sp0","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-spa-sp0{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-spa-sp0"} -->
<p class="gb-text gb-text-spa-sp0 gb-t-spa-sp0">{{post_terms taxonomy:pw_treatment_type}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"spa-sp1","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-spa-sp1{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-spa-sp1"} -->
<p class="gb-text gb-text-spa-sp1 gb-t-spa-sp1">From age {{post_meta key:_pw_min_age}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"spa-sp2","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-spa-sp2{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-spa-sp2"} -->
<p class="gb-text gb-text-spa-sp2 gb-t-spa-sp2">{{post_meta key:_pw_number_of_treatment_rooms}} treatment rooms</p>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"spa-cta","tagName":"a","styles":{"display":"inline-block","marginTop":"auto","paddingTop":"12px","paddingRight":"22px","paddingBottom":"12px","paddingLeft":"22px","fontSize":"15px","fontWeight":"600","textDecoration":"none","color":"#ffffff","backgroundColor":"#1a1a1a","borderTopLeftRadius":"6px","borderTopRightRadius":"6px","borderBottomLeftRadius":"6px","borderBottomRightRadius":"6px"},"css":".gb-text-spa-cta{background-color:#1a1a1a;border-radius:6px;color:#fff;display:inline-block;font-size:15px;font-weight:600;margin-top:auto;padding:12px 22px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-spa-cta pw-hotel-cta"} -->
<a class="gb-text gb-text-spa-cta gb-t-spa-cta pw-hotel-cta" href="{{post_permalink}}">View spa</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</article>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"spa-nr","tagName":"p","styles":{"marginBottom":"0px","fontSize":"16px"},"css":".gb-text-spa-nr{font-size:16px;margin-bottom:0}","className":"gb-t-spa-nr"} -->
<p class="gb-text gb-text-spa-nr gb-t-spa-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_SPA,
		'pw_meeting_room' => <<<'PW_ST_MEETING_ROOM'
<!-- wp:generateblocks/query {"uniqueId":"mtq","tagName":"div","query":{"post_type":["pw_meeting_room"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/element {"uniqueId":"mt-starter-root","tagName":"div","styles":{"width":"100%"},"css":".gb-element-mt-starter-root{width:100%}","className":"pw-hotel-starter-root gb-el gb-el-mt-starter-root"} -->
<div class="gb-element-mt-starter-root gb-el gb-el-mt-starter-root pw-hotel-starter-root">
<!-- wp:generateblocks/element {"uniqueId":"mt-grid","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"1fr","columnGap":"24px","rowGap":"24px","width":"100%"},"css":".gb-element-mt-grid{display:grid;grid-template-columns:1fr;column-gap:24px;row-gap:24px;width:100%}@media(min-width:640px){.gb-element-mt-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(min-width:1000px){.gb-element-mt-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}","className":"pw-hotel-card-grid gb-el gb-el-mt-grid"} -->
<div class="gb-element-mt-grid gb-el gb-el-mt-grid pw-hotel-card-grid">
<!-- wp:generateblocks/looper {"uniqueId":"mt-loop","tagName":"div","className":"gb-loop-mt-loop"} -->
<div class="gb-looper-mt-loop gb-loop-mt-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"mt-item","tagName":"div","className":"gb-li-mt-item"} -->
<div class="gb-loop-item gb-loop-item-mt-item gb-li-mt-item">
<!-- wp:generateblocks/element {"uniqueId":"mt-card","tagName":"article","styles":{"overflow":"hidden","borderTopLeftRadius":"12px","borderTopRightRadius":"12px","borderBottomLeftRadius":"12px","borderBottomRightRadius":"12px","borderTopWidth":"1px","borderRightWidth":"1px","borderBottomWidth":"1px","borderLeftWidth":"1px","borderTopStyle":"solid","borderRightStyle":"solid","borderBottomStyle":"solid","borderLeftStyle":"solid","borderTopColor":"#e8e8e8","borderRightColor":"#e8e8e8","borderBottomColor":"#e8e8e8","borderLeftColor":"#e8e8e8","boxShadow":"0 8px 24px rgba(0,0,0,0.06)","backgroundColor":"#ffffff","height":"100%","display":"flex","flexDirection":"column"},"css":".gb-element-mt-card{background-color:#fff;border:1px solid #e8e8e8;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);display:flex;flex-direction:column;height:100%;overflow:hidden}","className":"pw-hotel-card gb-el gb-el-mt-card"} -->
<article class="gb-element-mt-card gb-el gb-el-mt-card pw-hotel-card">
<!-- wp:generateblocks/element {"uniqueId":"mt-card-media","tagName":"div","styles":{"overflow":"hidden","width":"100%","maxHeight":"220px"},"css":".gb-element-mt-card-media{max-height:220px;overflow:hidden;width:100%}","className":"gb-el gb-el-mt-card-media"} -->
<div class="gb-element-mt-card-media gb-el gb-el-mt-card-media">
<!-- wp:generateblocks/image {"uniqueId":"mt-fi","styles":{"display":"block","width":"100%","maxWidth":"100%","minHeight":"200px","marginBottom":"0px","objectFit":"cover"},"css":".gb-image-mt-fi{display:block;margin-bottom:0;max-width:100%;min-height:200px;object-fit:cover;width:100%}","dynamicImage":"featured-image","className":"gb-img-mt-fi"} -->
<img class="gb-image gb-image-mt-fi gb-img-mt-fi" />
<!-- /wp:generateblocks/image -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"mt-card-body","tagName":"div","styles":{"display":"flex","flexDirection":"column","flexGrow":"1","paddingTop":"24px","paddingRight":"24px","paddingBottom":"24px","paddingLeft":"24px"},"css":".gb-element-mt-card-body{display:flex;flex-direction:column;flex-grow:1;padding:24px}","className":"gb-el gb-el-mt-card-body"} -->
<div class="gb-element-mt-card-body gb-el gb-el-mt-card-body">
<!-- wp:generateblocks/text {"uniqueId":"mt_t","tagName":"a","styles":{"marginBottom":"12px","fontSize":"22px","fontWeight":"600","display":"inline-block","textDecoration":"none","lineHeight":"1.25"},"css":".gb-text-mt_t{display:inline-block;font-size:22px;font-weight:600;line-height:1.25;margin-bottom:12px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-mt_t"} -->
<a class="gb-text gb-text-mt_t gb-t-mt_t" href="{{post_permalink}}">{{post_title}}</a>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"mt_e","tagName":"p","styles":{"marginBottom":"16px","fontSize":"16px","lineHeight":"1.55","color":"#4a4a4a"},"css":".gb-text-mt_e{color:#4a4a4a;font-size:16px;line-height:1.55;margin-bottom:16px}","className":"gb-t-mt_e"} -->
<p class="gb-text gb-text-mt_e gb-t-mt_e">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"mt-specs","tagName":"div","styles":{"display":"flex","flexDirection":"column","rowGap":"8px","marginBottom":"20px","width":"100%"},"css":".gb-element-mt-specs{display:flex;flex-direction:column;gap:8px;margin-bottom:20px;width:100%}","className":"gb-el gb-el-mt-specs"} -->
<div class="gb-element-mt-specs gb-el gb-el-mt-specs">
<!-- wp:generateblocks/text {"uniqueId":"mt-sp0","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-mt-sp0{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-mt-sp0"} -->
<p class="gb-text gb-text-mt-sp0 gb-t-mt-sp0">Theatre {{post_meta key:_pw_capacity_theatre}} guests</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"mt-sp1","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-mt-sp1{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-mt-sp1"} -->
<p class="gb-text gb-text-mt-sp1 gb-t-mt-sp1">{{post_meta key:_pw_area_sqm}} m² · {{post_terms taxonomy:pw_av_equipment}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"mt-sp2","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-mt-sp2{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-mt-sp2"} -->
<p class="gb-text gb-text-mt-sp2 gb-t-mt-sp2">Natural light: {{post_meta key:_pw_natural_light}}</p>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"mt-cta","tagName":"a","styles":{"display":"inline-block","marginTop":"auto","paddingTop":"12px","paddingRight":"22px","paddingBottom":"12px","paddingLeft":"22px","fontSize":"15px","fontWeight":"600","textDecoration":"none","color":"#ffffff","backgroundColor":"#1a1a1a","borderTopLeftRadius":"6px","borderTopRightRadius":"6px","borderBottomLeftRadius":"6px","borderBottomRightRadius":"6px"},"css":".gb-text-mt-cta{background-color:#1a1a1a;border-radius:6px;color:#fff;display:inline-block;font-size:15px;font-weight:600;margin-top:auto;padding:12px 22px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-mt-cta pw-hotel-cta"} -->
<a class="gb-text gb-text-mt-cta gb-t-mt-cta pw-hotel-cta" href="{{post_permalink}}">View space</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</article>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"mt-nr","tagName":"p","styles":{"marginBottom":"0px","fontSize":"16px"},"css":".gb-text-mt-nr{font-size:16px;margin-bottom:0}","className":"gb-t-mt-nr"} -->
<p class="gb-text gb-text-mt-nr gb-t-mt-nr">No meeting spaces.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_MEETING_ROOM,
		'pw_experience' => <<<'PW_ST_EXPERIENCE'
<!-- wp:generateblocks/query {"uniqueId":"exq","tagName":"div","query":{"post_type":["pw_experience"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/element {"uniqueId":"ex-starter-root","tagName":"div","styles":{"width":"100%"},"css":".gb-element-ex-starter-root{width:100%}","className":"pw-hotel-starter-root gb-el gb-el-ex-starter-root"} -->
<div class="gb-element-ex-starter-root gb-el gb-el-ex-starter-root pw-hotel-starter-root">
<!-- wp:generateblocks/element {"uniqueId":"ex-grid","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"1fr","columnGap":"24px","rowGap":"24px","width":"100%"},"css":".gb-element-ex-grid{display:grid;grid-template-columns:1fr;column-gap:24px;row-gap:24px;width:100%}@media(min-width:640px){.gb-element-ex-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(min-width:1000px){.gb-element-ex-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}","className":"pw-hotel-card-grid gb-el gb-el-ex-grid"} -->
<div class="gb-element-ex-grid gb-el gb-el-ex-grid pw-hotel-card-grid">
<!-- wp:generateblocks/looper {"uniqueId":"ex-loop","tagName":"div","className":"gb-loop-ex-loop"} -->
<div class="gb-looper-ex-loop gb-loop-ex-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"ex-item","tagName":"div","className":"gb-li-ex-item"} -->
<div class="gb-loop-item gb-loop-item-ex-item gb-li-ex-item">
<!-- wp:generateblocks/element {"uniqueId":"ex-card","tagName":"article","styles":{"overflow":"hidden","borderTopLeftRadius":"12px","borderTopRightRadius":"12px","borderBottomLeftRadius":"12px","borderBottomRightRadius":"12px","borderTopWidth":"1px","borderRightWidth":"1px","borderBottomWidth":"1px","borderLeftWidth":"1px","borderTopStyle":"solid","borderRightStyle":"solid","borderBottomStyle":"solid","borderLeftStyle":"solid","borderTopColor":"#e8e8e8","borderRightColor":"#e8e8e8","borderBottomColor":"#e8e8e8","borderLeftColor":"#e8e8e8","boxShadow":"0 8px 24px rgba(0,0,0,0.06)","backgroundColor":"#ffffff","height":"100%","display":"flex","flexDirection":"column"},"css":".gb-element-ex-card{background-color:#fff;border:1px solid #e8e8e8;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);display:flex;flex-direction:column;height:100%;overflow:hidden}","className":"pw-hotel-card gb-el gb-el-ex-card"} -->
<article class="gb-element-ex-card gb-el gb-el-ex-card pw-hotel-card">
<!-- wp:generateblocks/element {"uniqueId":"ex-card-media","tagName":"div","styles":{"overflow":"hidden","width":"100%","maxHeight":"220px"},"css":".gb-element-ex-card-media{max-height:220px;overflow:hidden;width:100%}","className":"gb-el gb-el-ex-card-media"} -->
<div class="gb-element-ex-card-media gb-el gb-el-ex-card-media">
<!-- wp:generateblocks/image {"uniqueId":"ex-fi","styles":{"display":"block","width":"100%","maxWidth":"100%","minHeight":"200px","marginBottom":"0px","objectFit":"cover"},"css":".gb-image-ex-fi{display:block;margin-bottom:0;max-width:100%;min-height:200px;object-fit:cover;width:100%}","dynamicImage":"featured-image","className":"gb-img-ex-fi"} -->
<img class="gb-image gb-image-ex-fi gb-img-ex-fi" />
<!-- /wp:generateblocks/image -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"ex-card-body","tagName":"div","styles":{"display":"flex","flexDirection":"column","flexGrow":"1","paddingTop":"24px","paddingRight":"24px","paddingBottom":"24px","paddingLeft":"24px"},"css":".gb-element-ex-card-body{display:flex;flex-direction:column;flex-grow:1;padding:24px}","className":"gb-el gb-el-ex-card-body"} -->
<div class="gb-element-ex-card-body gb-el gb-el-ex-card-body">
<!-- wp:generateblocks/text {"uniqueId":"ex_t","tagName":"a","styles":{"marginBottom":"12px","fontSize":"22px","fontWeight":"600","display":"inline-block","textDecoration":"none","lineHeight":"1.25"},"css":".gb-text-ex_t{display:inline-block;font-size:22px;font-weight:600;line-height:1.25;margin-bottom:12px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-ex_t"} -->
<a class="gb-text gb-text-ex_t gb-t-ex_t" href="{{post_permalink}}">{{post_title}}</a>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ex_ex","tagName":"p","styles":{"marginBottom":"16px","fontSize":"16px","lineHeight":"1.55","color":"#4a4a4a"},"css":".gb-text-ex_ex{color:#4a4a4a;font-size:16px;line-height:1.55;margin-bottom:16px}","className":"gb-t-ex_ex"} -->
<p class="gb-text gb-text-ex_ex gb-t-ex_ex">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"ex-specs","tagName":"div","styles":{"display":"flex","flexDirection":"column","rowGap":"8px","marginBottom":"20px","width":"100%"},"css":".gb-element-ex-specs{display:flex;flex-direction:column;gap:8px;margin-bottom:20px;width:100%}","className":"gb-el gb-el-ex-specs"} -->
<div class="gb-element-ex-specs gb-el gb-el-ex-specs">
<!-- wp:generateblocks/text {"uniqueId":"ex-sp0","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-ex-sp0{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-ex-sp0"} -->
<p class="gb-text gb-text-ex-sp0 gb-t-ex-sp0">{{post_terms taxonomy:pw_experience_category}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ex-sp1","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-ex-sp1{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-ex-sp1"} -->
<p class="gb-text gb-text-ex-sp1 gb-t-ex-sp1">{{post_meta key:_pw_duration_hours}} hours</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ex-sp2","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-ex-sp2{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-ex-sp2"} -->
<p class="gb-text gb-text-ex-sp2 gb-t-ex-sp2">From {{post_meta key:_pw_price_from}} __PW_PROPERTY_CURRENCY__</p>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"ex-cta","tagName":"a","styles":{"display":"inline-block","marginTop":"auto","paddingTop":"12px","paddingRight":"22px","paddingBottom":"12px","paddingLeft":"22px","fontSize":"15px","fontWeight":"600","textDecoration":"none","color":"#ffffff","backgroundColor":"#1a1a1a","borderTopLeftRadius":"6px","borderTopRightRadius":"6px","borderBottomLeftRadius":"6px","borderBottomRightRadius":"6px"},"css":".gb-text-ex-cta{background-color:#1a1a1a;border-radius:6px;color:#fff;display:inline-block;font-size:15px;font-weight:600;margin-top:auto;padding:12px 22px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-ex-cta pw-hotel-cta"} -->
<a class="gb-text gb-text-ex-cta gb-t-ex-cta pw-hotel-cta" href="{{post_permalink}}">View experience</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</article>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"ex-nr","tagName":"p","styles":{"marginBottom":"0px","fontSize":"16px"},"css":".gb-text-ex-nr{font-size:16px;margin-bottom:0}","className":"gb-t-ex-nr"} -->
<p class="gb-text gb-text-ex-nr gb-t-ex-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_EXPERIENCE,
		'pw_nearby' => <<<'PW_ST_NEARBY'
<!-- wp:generateblocks/query {"uniqueId":"nbq","tagName":"div","query":{"post_type":["pw_nearby"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/element {"uniqueId":"nb-starter-root","tagName":"div","styles":{"width":"100%"},"css":".gb-element-nb-starter-root{width:100%}","className":"pw-hotel-starter-root gb-el gb-el-nb-starter-root"} -->
<div class="gb-element-nb-starter-root gb-el gb-el-nb-starter-root pw-hotel-starter-root">
<!-- wp:generateblocks/element {"uniqueId":"nb-grid","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"1fr","columnGap":"24px","rowGap":"24px","width":"100%"},"css":".gb-element-nb-grid{display:grid;grid-template-columns:1fr;column-gap:24px;row-gap:24px;width:100%}@media(min-width:640px){.gb-element-nb-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(min-width:1000px){.gb-element-nb-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}","className":"pw-hotel-card-grid gb-el gb-el-nb-grid"} -->
<div class="gb-element-nb-grid gb-el gb-el-nb-grid pw-hotel-card-grid">
<!-- wp:generateblocks/looper {"uniqueId":"nb-loop","tagName":"div","className":"gb-loop-nb-loop"} -->
<div class="gb-looper-nb-loop gb-loop-nb-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"nb-item","tagName":"div","className":"gb-li-nb-item"} -->
<div class="gb-loop-item gb-loop-item-nb-item gb-li-nb-item">
<!-- wp:generateblocks/element {"uniqueId":"nb-card","tagName":"article","styles":{"overflow":"hidden","borderTopLeftRadius":"12px","borderTopRightRadius":"12px","borderBottomLeftRadius":"12px","borderBottomRightRadius":"12px","borderTopWidth":"1px","borderRightWidth":"1px","borderBottomWidth":"1px","borderLeftWidth":"1px","borderTopStyle":"solid","borderRightStyle":"solid","borderBottomStyle":"solid","borderLeftStyle":"solid","borderTopColor":"#e8e8e8","borderRightColor":"#e8e8e8","borderBottomColor":"#e8e8e8","borderLeftColor":"#e8e8e8","boxShadow":"0 8px 24px rgba(0,0,0,0.06)","backgroundColor":"#ffffff","height":"100%","display":"flex","flexDirection":"column"},"css":".gb-element-nb-card{background-color:#fff;border:1px solid #e8e8e8;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);display:flex;flex-direction:column;height:100%;overflow:hidden}","className":"pw-hotel-card gb-el gb-el-nb-card"} -->
<article class="gb-element-nb-card gb-el gb-el-nb-card pw-hotel-card">
<!-- wp:generateblocks/element {"uniqueId":"nb-card-media","tagName":"div","styles":{"overflow":"hidden","width":"100%","maxHeight":"220px"},"css":".gb-element-nb-card-media{max-height:220px;overflow:hidden;width:100%}","className":"gb-el gb-el-nb-card-media"} -->
<div class="gb-element-nb-card-media gb-el gb-el-nb-card-media">
<!-- wp:generateblocks/image {"uniqueId":"nb-fi","styles":{"display":"block","width":"100%","maxWidth":"100%","minHeight":"200px","marginBottom":"0px","objectFit":"cover"},"css":".gb-image-nb-fi{display:block;margin-bottom:0;max-width:100%;min-height:200px;object-fit:cover;width:100%}","dynamicImage":"featured-image","className":"gb-img-nb-fi"} -->
<img class="gb-image gb-image-nb-fi gb-img-nb-fi" />
<!-- /wp:generateblocks/image -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"nb-card-body","tagName":"div","styles":{"display":"flex","flexDirection":"column","flexGrow":"1","paddingTop":"24px","paddingRight":"24px","paddingBottom":"24px","paddingLeft":"24px"},"css":".gb-element-nb-card-body{display:flex;flex-direction:column;flex-grow:1;padding:24px}","className":"gb-el gb-el-nb-card-body"} -->
<div class="gb-element-nb-card-body gb-el gb-el-nb-card-body">
<!-- wp:generateblocks/text {"uniqueId":"nb_t","tagName":"a","styles":{"marginBottom":"12px","fontSize":"22px","fontWeight":"600","display":"inline-block","textDecoration":"none","lineHeight":"1.25"},"css":".gb-text-nb_t{display:inline-block;font-size:22px;font-weight:600;line-height:1.25;margin-bottom:12px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-nb_t"} -->
<a class="gb-text gb-text-nb_t gb-t-nb_t" href="{{post_permalink}}">{{post_title}}</a>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"nb_e","tagName":"p","styles":{"marginBottom":"16px","fontSize":"16px","lineHeight":"1.55","color":"#4a4a4a"},"css":".gb-text-nb_e{color:#4a4a4a;font-size:16px;line-height:1.55;margin-bottom:16px}","className":"gb-t-nb_e"} -->
<p class="gb-text gb-text-nb_e gb-t-nb_e">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"nb-specs","tagName":"div","styles":{"display":"flex","flexDirection":"column","rowGap":"8px","marginBottom":"20px","width":"100%"},"css":".gb-element-nb-specs{display:flex;flex-direction:column;gap:8px;margin-bottom:20px;width:100%}","className":"gb-el gb-el-nb-specs"} -->
<div class="gb-element-nb-specs gb-el gb-el-nb-specs">
<!-- wp:generateblocks/text {"uniqueId":"nb-sp0","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-nb-sp0{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-nb-sp0"} -->
<p class="gb-text gb-text-nb-sp0 gb-t-nb-sp0">{{post_terms taxonomy:pw_nearby_type}} · {{post_terms taxonomy:pw_transport_mode}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"nb-sp1","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-nb-sp1{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-nb-sp1"} -->
<p class="gb-text gb-text-nb-sp1 gb-t-nb-sp1">{{post_meta key:_pw_distance_km}} km away</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"nb-sp2","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-nb-sp2{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-nb-sp2"} -->
<p class="gb-text gb-text-nb-sp2 gb-t-nb-sp2">{{post_meta key:_pw_travel_time_min}} min</p>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"nb-cta","tagName":"a","styles":{"display":"inline-block","marginTop":"auto","paddingTop":"12px","paddingRight":"22px","paddingBottom":"12px","paddingLeft":"22px","fontSize":"15px","fontWeight":"600","textDecoration":"none","color":"#ffffff","backgroundColor":"#1a1a1a","borderTopLeftRadius":"6px","borderTopRightRadius":"6px","borderBottomLeftRadius":"6px","borderBottomRightRadius":"6px"},"css":".gb-text-nb-cta{background-color:#1a1a1a;border-radius:6px;color:#fff;display:inline-block;font-size:15px;font-weight:600;margin-top:auto;padding:12px 22px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-nb-cta pw-hotel-cta"} -->
<a class="gb-text gb-text-nb-cta gb-t-nb-cta pw-hotel-cta" href="{{post_permalink}}">View place</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</article>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"nb-nr","tagName":"p","styles":{"marginBottom":"0px","fontSize":"16px"},"css":".gb-text-nb-nr{font-size:16px;margin-bottom:0}","className":"gb-t-nb-nr"} -->
<p class="gb-text gb-text-nb-nr gb-t-nb-nr">No nearby places.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_NEARBY,
		'pw_event' => <<<'PW_ST_EVENT'
<!-- wp:generateblocks/query {"uniqueId":"evq","tagName":"div","query":{"post_type":["pw_event"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/element {"uniqueId":"ev-starter-root","tagName":"div","styles":{"width":"100%"},"css":".gb-element-ev-starter-root{width:100%}","className":"pw-hotel-starter-root gb-el gb-el-ev-starter-root"} -->
<div class="gb-element-ev-starter-root gb-el gb-el-ev-starter-root pw-hotel-starter-root">
<!-- wp:generateblocks/element {"uniqueId":"ev-grid","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"1fr","columnGap":"24px","rowGap":"24px","width":"100%"},"css":".gb-element-ev-grid{display:grid;grid-template-columns:1fr;column-gap:24px;row-gap:24px;width:100%}@media(min-width:640px){.gb-element-ev-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(min-width:1000px){.gb-element-ev-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}","className":"pw-hotel-card-grid gb-el gb-el-ev-grid"} -->
<div class="gb-element-ev-grid gb-el gb-el-ev-grid pw-hotel-card-grid">
<!-- wp:generateblocks/looper {"uniqueId":"ev-loop","tagName":"div","className":"gb-loop-ev-loop"} -->
<div class="gb-looper-ev-loop gb-loop-ev-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"ev-item","tagName":"div","className":"gb-li-ev-item"} -->
<div class="gb-loop-item gb-loop-item-ev-item gb-li-ev-item">
<!-- wp:generateblocks/element {"uniqueId":"ev-card","tagName":"article","styles":{"overflow":"hidden","borderTopLeftRadius":"12px","borderTopRightRadius":"12px","borderBottomLeftRadius":"12px","borderBottomRightRadius":"12px","borderTopWidth":"1px","borderRightWidth":"1px","borderBottomWidth":"1px","borderLeftWidth":"1px","borderTopStyle":"solid","borderRightStyle":"solid","borderBottomStyle":"solid","borderLeftStyle":"solid","borderTopColor":"#e8e8e8","borderRightColor":"#e8e8e8","borderBottomColor":"#e8e8e8","borderLeftColor":"#e8e8e8","boxShadow":"0 8px 24px rgba(0,0,0,0.06)","backgroundColor":"#ffffff","height":"100%","display":"flex","flexDirection":"column"},"css":".gb-element-ev-card{background-color:#fff;border:1px solid #e8e8e8;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);display:flex;flex-direction:column;height:100%;overflow:hidden}","className":"pw-hotel-card gb-el gb-el-ev-card"} -->
<article class="gb-element-ev-card gb-el gb-el-ev-card pw-hotel-card">
<!-- wp:generateblocks/element {"uniqueId":"ev-card-media","tagName":"div","styles":{"overflow":"hidden","width":"100%","maxHeight":"220px"},"css":".gb-element-ev-card-media{max-height:220px;overflow:hidden;width:100%}","className":"gb-el gb-el-ev-card-media"} -->
<div class="gb-element-ev-card-media gb-el gb-el-ev-card-media">
<!-- wp:generateblocks/image {"uniqueId":"ev-fi","styles":{"display":"block","width":"100%","maxWidth":"100%","minHeight":"200px","marginBottom":"0px","objectFit":"cover"},"css":".gb-image-ev-fi{display:block;margin-bottom:0;max-width:100%;min-height:200px;object-fit:cover;width:100%}","dynamicImage":"featured-image","className":"gb-img-ev-fi"} -->
<img class="gb-image gb-image-ev-fi gb-img-ev-fi" />
<!-- /wp:generateblocks/image -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"ev-card-body","tagName":"div","styles":{"display":"flex","flexDirection":"column","flexGrow":"1","paddingTop":"24px","paddingRight":"24px","paddingBottom":"24px","paddingLeft":"24px"},"css":".gb-element-ev-card-body{display:flex;flex-direction:column;flex-grow:1;padding:24px}","className":"gb-el gb-el-ev-card-body"} -->
<div class="gb-element-ev-card-body gb-el gb-el-ev-card-body">
<!-- wp:generateblocks/text {"uniqueId":"ev_t","tagName":"a","styles":{"marginBottom":"12px","fontSize":"22px","fontWeight":"600","display":"inline-block","textDecoration":"none","lineHeight":"1.25"},"css":".gb-text-ev_t{display:inline-block;font-size:22px;font-weight:600;line-height:1.25;margin-bottom:12px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-ev_t"} -->
<a class="gb-text gb-text-ev_t gb-t-ev_t" href="{{post_permalink}}">{{post_title}}</a>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ev_e","tagName":"p","styles":{"marginBottom":"16px","fontSize":"16px","lineHeight":"1.55","color":"#4a4a4a"},"css":".gb-text-ev_e{color:#4a4a4a;font-size:16px;line-height:1.55;margin-bottom:16px}","className":"gb-t-ev_e"} -->
<p class="gb-text gb-text-ev_e gb-t-ev_e">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"ev-specs","tagName":"div","styles":{"display":"flex","flexDirection":"column","rowGap":"8px","marginBottom":"20px","width":"100%"},"css":".gb-element-ev-specs{display:flex;flex-direction:column;gap:8px;margin-bottom:20px;width:100%}","className":"gb-el gb-el-ev-specs"} -->
<div class="gb-element-ev-specs gb-el gb-el-ev-specs">
<!-- wp:generateblocks/text {"uniqueId":"ev-sp0","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-ev-sp0{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-ev-sp0"} -->
<p class="gb-text gb-text-ev-sp0 gb-t-ev-sp0">Starts {{post_meta key:_pw_start_datetime_iso8601}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ev-sp1","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-ev-sp1{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-ev-sp1"} -->
<p class="gb-text gb-text-ev-sp1 gb-t-ev-sp1">Ends {{post_meta key:_pw_end_datetime_iso8601}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"ev-sp2","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-ev-sp2{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-ev-sp2"} -->
<p class="gb-text gb-text-ev-sp2 gb-t-ev-sp2">{{post_meta key:_pw_capacity}} guests · from {{post_meta key:_pw_price_from}} __PW_PROPERTY_CURRENCY__</p>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"ev-cta","tagName":"a","styles":{"display":"inline-block","marginTop":"auto","paddingTop":"12px","paddingRight":"22px","paddingBottom":"12px","paddingLeft":"22px","fontSize":"15px","fontWeight":"600","textDecoration":"none","color":"#ffffff","backgroundColor":"#1a1a1a","borderTopLeftRadius":"6px","borderTopRightRadius":"6px","borderBottomLeftRadius":"6px","borderBottomRightRadius":"6px"},"css":".gb-text-ev-cta{background-color:#1a1a1a;border-radius:6px;color:#fff;display:inline-block;font-size:15px;font-weight:600;margin-top:auto;padding:12px 22px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-ev-cta pw-hotel-cta"} -->
<a class="gb-text gb-text-ev-cta gb-t-ev-cta pw-hotel-cta" href="{{post_permalink}}">View event</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</article>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"ev-nr","tagName":"p","styles":{"marginBottom":"0px","fontSize":"16px"},"css":".gb-text-ev-nr{font-size:16px;margin-bottom:0}","className":"gb-t-ev-nr"} -->
<p class="gb-text gb-text-ev-nr gb-t-ev-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_EVENT,
		'pw_offer' => <<<'PW_ST_OFFER'
<!-- wp:generateblocks/query {"uniqueId":"ofq","tagName":"div","query":{"post_type":["pw_offer"],"posts_per_page":10,"orderby":"title","order":"asc","_pwNote":"pagination controlled by WordPress Reading settings (Blog pages show at most)"},"className":"pw-gb-scope-property"} -->
<div class="pw-gb-scope-property">
<!-- wp:generateblocks/element {"uniqueId":"of-starter-root","tagName":"div","styles":{"width":"100%"},"css":".gb-element-of-starter-root{width:100%}","className":"pw-hotel-starter-root gb-el gb-el-of-starter-root"} -->
<div class="gb-element-of-starter-root gb-el gb-el-of-starter-root pw-hotel-starter-root">
<!-- wp:generateblocks/element {"uniqueId":"of-grid","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"1fr","columnGap":"24px","rowGap":"24px","width":"100%"},"css":".gb-element-of-grid{display:grid;grid-template-columns:1fr;column-gap:24px;row-gap:24px;width:100%}@media(min-width:640px){.gb-element-of-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(min-width:1000px){.gb-element-of-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}","className":"pw-hotel-card-grid gb-el gb-el-of-grid"} -->
<div class="gb-element-of-grid gb-el gb-el-of-grid pw-hotel-card-grid">
<!-- wp:generateblocks/looper {"uniqueId":"of-loop","tagName":"div","className":"gb-loop-of-loop"} -->
<div class="gb-looper-of-loop gb-loop-of-loop">
<!-- wp:generateblocks/loop-item {"uniqueId":"of-item","tagName":"div","className":"gb-li-of-item"} -->
<div class="gb-loop-item gb-loop-item-of-item gb-li-of-item">
<!-- wp:generateblocks/element {"uniqueId":"of-card","tagName":"article","styles":{"overflow":"hidden","borderTopLeftRadius":"12px","borderTopRightRadius":"12px","borderBottomLeftRadius":"12px","borderBottomRightRadius":"12px","borderTopWidth":"1px","borderRightWidth":"1px","borderBottomWidth":"1px","borderLeftWidth":"1px","borderTopStyle":"solid","borderRightStyle":"solid","borderBottomStyle":"solid","borderLeftStyle":"solid","borderTopColor":"#e8e8e8","borderRightColor":"#e8e8e8","borderBottomColor":"#e8e8e8","borderLeftColor":"#e8e8e8","boxShadow":"0 8px 24px rgba(0,0,0,0.06)","backgroundColor":"#ffffff","height":"100%","display":"flex","flexDirection":"column"},"css":".gb-element-of-card{background-color:#fff;border:1px solid #e8e8e8;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.06);display:flex;flex-direction:column;height:100%;overflow:hidden}","className":"pw-hotel-card gb-el gb-el-of-card"} -->
<article class="gb-element-of-card gb-el gb-el-of-card pw-hotel-card">
<!-- wp:generateblocks/element {"uniqueId":"of-card-media","tagName":"div","styles":{"overflow":"hidden","width":"100%","maxHeight":"220px"},"css":".gb-element-of-card-media{max-height:220px;overflow:hidden;width:100%}","className":"gb-el gb-el-of-card-media"} -->
<div class="gb-element-of-card-media gb-el gb-el-of-card-media">
<!-- wp:generateblocks/image {"uniqueId":"of-fi","styles":{"display":"block","width":"100%","maxWidth":"100%","minHeight":"200px","marginBottom":"0px","objectFit":"cover"},"css":".gb-image-of-fi{display:block;margin-bottom:0;max-width:100%;min-height:200px;object-fit:cover;width:100%}","dynamicImage":"featured-image","className":"gb-img-of-fi"} -->
<img class="gb-image gb-image-of-fi gb-img-of-fi" />
<!-- /wp:generateblocks/image -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"uniqueId":"of-card-body","tagName":"div","styles":{"display":"flex","flexDirection":"column","flexGrow":"1","paddingTop":"24px","paddingRight":"24px","paddingBottom":"24px","paddingLeft":"24px"},"css":".gb-element-of-card-body{display:flex;flex-direction:column;flex-grow:1;padding:24px}","className":"gb-el gb-el-of-card-body"} -->
<div class="gb-element-of-card-body gb-el gb-el-of-card-body">
<!-- wp:generateblocks/text {"uniqueId":"of_t","tagName":"a","styles":{"marginBottom":"12px","fontSize":"22px","fontWeight":"600","display":"inline-block","textDecoration":"none","lineHeight":"1.25"},"css":".gb-text-of_t{display:inline-block;font-size:22px;font-weight:600;line-height:1.25;margin-bottom:12px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-of_t"} -->
<a class="gb-text gb-text-of_t gb-t-of_t" href="{{post_permalink}}">{{post_title}}</a>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"of_e","tagName":"p","styles":{"marginBottom":"16px","fontSize":"16px","lineHeight":"1.55","color":"#4a4a4a"},"css":".gb-text-of_e{color:#4a4a4a;font-size:16px;line-height:1.55;margin-bottom:16px}","className":"gb-t-of_e"} -->
<p class="gb-text gb-text-of_e gb-t-of_e">{{post_excerpt}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/element {"uniqueId":"of-specs","tagName":"div","styles":{"display":"flex","flexDirection":"column","rowGap":"8px","marginBottom":"20px","width":"100%"},"css":".gb-element-of-specs{display:flex;flex-direction:column;gap:8px;margin-bottom:20px;width:100%}","className":"gb-el gb-el-of-specs"} -->
<div class="gb-element-of-specs gb-el gb-el-of-specs">
<!-- wp:generateblocks/text {"uniqueId":"of-sp0","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-of-sp0{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-of-sp0"} -->
<p class="gb-text gb-text-of-sp0 gb-t-of-sp0">{{post_meta key:_pw_offer_type}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"of-sp1","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-of-sp1{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-of-sp1"} -->
<p class="gb-text gb-text-of-sp1 gb-t-of-sp1">{{post_meta key:_pw_valid_from}} – {{post_meta key:_pw_valid_to}}</p>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"uniqueId":"of-sp2","tagName":"p","styles":{"marginBottom":"0px","fontSize":"15px","fontWeight":"600"},"css":".gb-text-of-sp2{font-size:15px;font-weight:600;margin-bottom:0}","className":"gb-t-of-sp2"} -->
<p class="gb-text gb-text-of-sp2 gb-t-of-sp2">{{post_meta key:_pw_discount_value}} ({{post_meta key:_pw_discount_type}}) · min {{post_meta key:_pw_minimum_stay_nights}} nights</p>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"uniqueId":"of-cta","tagName":"a","styles":{"display":"inline-block","marginTop":"auto","paddingTop":"12px","paddingRight":"22px","paddingBottom":"12px","paddingLeft":"22px","fontSize":"15px","fontWeight":"600","textDecoration":"none","color":"#ffffff","backgroundColor":"#1a1a1a","borderTopLeftRadius":"6px","borderTopRightRadius":"6px","borderBottomLeftRadius":"6px","borderBottomRightRadius":"6px"},"css":".gb-text-of-cta{background-color:#1a1a1a;border-radius:6px;color:#fff;display:inline-block;font-size:15px;font-weight:600;margin-top:auto;padding:12px 22px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"{{post_permalink}}"}],"className":"gb-t-of-cta pw-hotel-cta"} -->
<a class="gb-text gb-text-of-cta gb-t-of-cta pw-hotel-cta" href="{{post_permalink}}">View offer</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</article>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/loop-item -->
</div>
<!-- /wp:generateblocks/looper -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/query-pagination /-->
<!-- wp:generateblocks/query-no-results -->
<!-- wp:generateblocks/text {"uniqueId":"of-nr","tagName":"p","styles":{"marginBottom":"0px","fontSize":"16px"},"css":".gb-text-of-nr{font-size:16px;margin-bottom:0}","className":"gb-t-of-nr"} -->
<p class="gb-text gb-text-of-nr gb-t-of-nr">None found.</p>
<!-- /wp:generateblocks/text -->
<!-- /wp:generateblocks/query-no-results -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/query -->
PW_ST_OFFER,
		default => '',
	};
	if ( $body === '' ) {
		return '';
	}

	return $body;
}

/* ────────────────────────────────────────────────────────────────────────
 * Singular markup helpers (private — used by _pw_get_singular_starter_markup)
 * ──────────────────────────────────────────────────────────────────────── */

function _pw_gb_row( string $uid, string $label, string $value ): string {
	return '<!-- wp:generateblocks/element {"uniqueId":"' . $uid . '","tagName":"div","styles":{"display":"grid","gridTemplateColumns":"minmax(0, min(40%, 14rem)) 1fr","columnGap":"16px","alignItems":"start","paddingTop":"12px","paddingBottom":"12px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#ececec","width":"100%"},"css":".gb-element-' . $uid . '{align-items:start;border-bottom:1px solid #ececec;column-gap:16px;display:grid;grid-template-columns:minmax(0,min(40%,14rem)) 1fr;padding:12px 0;width:100%}@media(max-width:640px){.gb-element-' . $uid . '{grid-template-columns:1fr}}","className":"gb-el gb-el-' . $uid . '"} -->' . "\n"
		. '<div class="gb-element-' . $uid . ' gb-el gb-el-' . $uid . '">'
		. '<!-- wp:generateblocks/text {"uniqueId":"' . $uid . 'l","tagName":"div","styles":{"marginBottom":"0px","fontWeight":"600","fontSize":"15px","color":"#5c5c5c"},"css":".gb-text-' . $uid . 'l{color:#5c5c5c;margin-bottom:0;font-size:15px;font-weight:600}","className":"gb-t-' . $uid . 'l"} -->' . "\n"
		. '<div class="gb-text gb-text-' . $uid . 'l gb-t-' . $uid . 'l">' . $label . '</div>' . "\n"
		. '<!-- /wp:generateblocks/text -->' . "\n"
		. '<!-- wp:generateblocks/text {"uniqueId":"' . $uid . 'v","tagName":"div","styles":{"marginBottom":"0px","fontSize":"16px","lineHeight":"1.55"},"css":".gb-text-' . $uid . 'v{line-height:1.55;margin-bottom:0;font-size:16px}","className":"gb-t-' . $uid . 'v"} -->' . "\n"
		. '<div class="gb-text gb-text-' . $uid . 'v gb-t-' . $uid . 'v">' . $value . '</div>' . "\n"
		. '<!-- /wp:generateblocks/text -->' . "\n"
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/element -->';
}

function _pw_gb_h( string $uid, string $text, string $tag = 'h2' ): string {
	$fs = $tag === 'h1' ? '36px' : '26px';
	$fw = $tag === 'h1' ? '700' : '600';
	$mb = $tag === 'h1' ? '16px' : '16px';
	return '<!-- wp:generateblocks/text {"uniqueId":"' . $uid . '","tagName":"' . $tag . '","styles":{"marginBottom":"' . $mb . '","fontSize":"' . $fs . '","fontWeight":"' . $fw . '","lineHeight":"1.2"},"css":".gb-text-' . $uid . '{line-height:1.2;margin-bottom:' . $mb . ';font-size:' . $fs . ';font-weight:' . $fw . '}","className":"gb-t-' . $uid . '"} -->' . "\n"
		. '<' . $tag . ' class="gb-text gb-text-' . $uid . ' gb-t-' . $uid . '">' . $text . '</' . $tag . '>' . "\n"
		. '<!-- /wp:generateblocks/text -->';
}

function _pw_gb_p( string $uid, string $text ): string {
	return '<!-- wp:generateblocks/text {"uniqueId":"' . $uid . '","tagName":"p","styles":{"marginBottom":"16px","fontSize":"17px","lineHeight":"1.6"},"css":".gb-text-' . $uid . '{line-height:1.6;margin-bottom:16px;font-size:17px}","className":"gb-t-' . $uid . '"} -->' . "\n"
		. '<p class="gb-text gb-text-' . $uid . ' gb-t-' . $uid . '">' . $text . '</p>' . "\n"
		. '<!-- /wp:generateblocks/text -->';
}

function _pw_gb_link( string $uid, string $text, string $href ): string {
	return '<!-- wp:generateblocks/text {"uniqueId":"' . $uid . '","tagName":"a","styles":{"marginBottom":"8px","display":"inline-block","marginRight":"20px","fontSize":"16px","textDecoration":"underline"},"css":".gb-text-' . $uid . '{display:inline-block;font-size:16px;margin-bottom:8px;margin-right:20px;text-decoration:underline}","htmlAttributes":[{"key":"href","value":"' . $href . '"}],"className":"gb-t-' . $uid . '"} -->' . "\n"
		. '<a class="gb-text gb-text-' . $uid . ' gb-t-' . $uid . '" href="' . $href . '">' . $text . '</a>' . "\n"
		. '<!-- /wp:generateblocks/text -->';
}

function _pw_gb_img( string $uid ): string {
	return '<!-- wp:generateblocks/image {"uniqueId":"' . $uid . '","styles":{"display":"block","height":"auto","maxWidth":"100%","marginBottom":"32px"},"css":".gb-image-' . $uid . '{display:block;height:auto;max-width:100%;margin-bottom:32px}","dynamicImage":"featured-image","className":"gb-img-' . $uid . '"} -->' . "\n"
		. '<img class="gb-image gb-image-' . $uid . ' gb-img-' . $uid . '" />' . "\n"
		. '<!-- /wp:generateblocks/image -->';
}

function _pw_gb_hero_img( string $uid ): string {
	return '<!-- wp:generateblocks/image {"uniqueId":"' . $uid . '","styles":{"display":"block","width":"100%","maxWidth":"100%","minHeight":"280px","marginBottom":"0px","objectFit":"cover"},"css":".gb-image-' . $uid . '{display:block;margin-bottom:0;max-width:100%;min-height:280px;object-fit:cover;width:100%}","dynamicImage":"featured-image","className":"gb-img-' . $uid . ' pw-hotel-hero-img"} -->' . "\n"
		. '<img class="gb-image gb-image-' . $uid . ' gb-img-' . $uid . ' pw-hotel-hero-img" />' . "\n"
		. '<!-- /wp:generateblocks/image -->';
}

function _pw_gb_cta_button( string $uid, string $text, string $href ): string {
	return '<!-- wp:generateblocks/text {"uniqueId":"' . $uid . '","tagName":"a","styles":{"display":"inline-block","marginTop":"8px","marginBottom":"8px","paddingTop":"12px","paddingRight":"24px","paddingBottom":"12px","paddingLeft":"24px","fontSize":"16px","fontWeight":"600","textDecoration":"none","color":"#ffffff","backgroundColor":"#1a1a1a","borderTopLeftRadius":"6px","borderTopRightRadius":"6px","borderBottomLeftRadius":"6px","borderBottomRightRadius":"6px"},"css":".gb-text-' . $uid . '{background-color:#1a1a1a;border-radius:6px;color:#fff;display:inline-block;font-size:16px;font-weight:600;margin-bottom:8px;margin-top:8px;padding:12px 24px;text-decoration:none}","htmlAttributes":[{"key":"href","value":"' . $href . '"}],"className":"gb-t-' . $uid . ' pw-hotel-cta"} -->' . "\n"
		. '<a class="gb-text gb-text-' . $uid . ' gb-t-' . $uid . ' pw-hotel-cta" href="' . $href . '">' . $text . '</a>' . "\n"
		. '<!-- /wp:generateblocks/text -->';
}

function _pw_gb_needs_shortcode_placeholder( string $slug ): string {
	return '<!-- wp:html -->' . "\n"
		. '<!-- ' . $slug . ': needs shortcode -->' . "\n"
		. '<!-- /wp:html -->';
}

function _pw_gb_starter_root_wrap_div( string $uid, string $inner ): string {
	$inner = trim( $inner );
	return '<!-- wp:generateblocks/element {"uniqueId":"' . $uid . '","tagName":"div","styles":{"width":"100%"},"css":".gb-element-' . $uid . '{width:100%}","className":"pw-hotel-starter-root gb-el gb-el-' . $uid . '"} -->' . "\n"
		. '<div class="gb-element-' . $uid . ' gb-el gb-el-' . $uid . ' pw-hotel-starter-root">' . "\n"
		. $inner . "\n"
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/element -->';
}

function _pw_gb_section( string $uid, string $inner ): string {
	return '<!-- wp:generateblocks/element {"uniqueId":"' . $uid . '","tagName":"section","styles":{"marginTop":"48px"},"css":".gb-element-' . $uid . '{margin-top:48px}","className":"gb-el gb-el-' . $uid . ' pw-hotel-section"} -->' . "\n"
		. '<section class="gb-element-' . $uid . ' gb-el gb-el-' . $uid . ' pw-hotel-section">' . "\n"
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

	return _pw_gb_starter_root_wrap_div(
		'prop-starter-root',
		$identity . "\n" . $address . "\n" . $benefits . "\n" . $links . "\n" . $contacts
	);
}

/* ── Room singular ─────────────────────────────────────────────────────── */

function _pw_markup_room_singular(): string {
	$hero = _pw_gb_hero_img( 'rms-hero' ) . "\n"
		. '<!-- wp:generateblocks/element {"uniqueId":"rms-hero-copy","tagName":"div","styles":{"paddingTop":"32px","paddingBottom":"24px"},"css":".gb-element-rms-hero-copy{padding:32px 0 24px}","className":"pw-hotel-hero gb-el gb-el-rms-hero-copy"} -->' . "\n"
		. '<div class="gb-element-rms-hero-copy gb-el gb-el-rms-hero-copy pw-hotel-hero">' . "\n"
		. _pw_gb_h( 'rms-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'rms-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_cta_button( 'rms-bkurl', 'Book this room', '{{post_meta key:_pw_booking_url}}' ) . "\n"
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/element -->';

	$facts = _pw_gb_section(
		'rms-facts',
		_pw_gb_h( 'rms-fh', 'Room details', 'h2' ) . "\n"
		. _pw_gb_row( 'rms-r0', 'From', '{{post_meta key:_pw_rate_from}} __PW_PROPERTY_CURRENCY__' ) . "\n"
		. _pw_gb_row( 'rms-r1', 'To', '{{post_meta key:_pw_rate_to}} __PW_PROPERTY_CURRENCY__' ) . "\n"
		. _pw_gb_row( 'rms-r2', 'Sleeps', '{{post_meta key:_pw_max_occupancy}}' ) . "\n"
		. _pw_gb_row( 'rms-r3', 'Max adults', '{{post_meta key:_pw_max_adults}}' ) . "\n"
		. _pw_gb_row( 'rms-r4', 'Max children', '{{post_meta key:_pw_max_children}}' ) . "\n"
		. _pw_gb_row( 'rms-r5', 'Size (m²)', '{{post_meta key:_pw_size_sqm}}' ) . "\n"
		. _pw_gb_row( 'rms-r6', 'Size (ft²)', '{{post_meta key:_pw_size_sqft}}' ) . "\n"
		. _pw_gb_row( 'rms-r7', 'Extra beds', '{{post_meta key:_pw_max_extra_beds}}' ) . "\n"
		. _pw_gb_row( 'rms-r8', 'Bed type', '{{post_terms taxonomy:pw_bed_type}}' ) . "\n"
		. _pw_gb_row( 'rms-r9', 'View', '{{post_terms taxonomy:pw_view_type}}' )
	);

	$rates = _pw_gb_section(
		'rms-rates',
		_pw_gb_h( 'rms-rh', 'Rate plans', 'h2' ) . "\n"
		. _pw_gb_needs_shortcode_placeholder( 'pw_rates' )
	);

	$gallery = _pw_gb_section(
		'rms-gal',
		_pw_gb_h( 'rms-gh', 'Gallery', 'h2' ) . "\n"
		. _pw_gb_needs_shortcode_placeholder( 'pw_gallery' )
	);

	$contacts = _pw_gb_section(
		'rms-ct',
		_pw_gb_h( 'rms-ch', 'Property contacts', 'h2' ) . "\n"
		. '<!-- wp:generateblocks/query {"uniqueId":"rms-ctq","tagName":"div","query":{"post_type":["pw_contact"],"posts_per_page":20,"orderby":"title","order":"asc"},"className":"pw-gb-scope-property pw-gb-contact-filter-property"} -->' . "\n"
		. '<div class="pw-gb-scope-property pw-gb-contact-filter-property">'
		. '<!-- wp:generateblocks/looper {"uniqueId":"rms-ctlp","tagName":"div","className":"gb-loop-rms-ctlp"} -->' . "\n"
		. '<div class="gb-looper-rms-ctlp gb-loop-rms-ctlp">'
		. '<!-- wp:generateblocks/loop-item {"uniqueId":"rms-ctli","tagName":"div","styles":{"paddingTop":"16px","paddingBottom":"16px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#ececec"},"css":".gb-loop-item-rms-ctli{border-bottom:1px solid #ececec;padding:16px 0}","className":"gb-li-rms-ctli"} -->' . "\n"
		. '<div class="gb-loop-item gb-loop-item-rms-ctli gb-li-rms-ctli">'
		. _pw_gb_row( 'rms-c0', 'Label', '{{post_meta key:_pw_label}}' ) . "\n"
		. _pw_gb_row( 'rms-c1', 'Phone', '{{post_meta key:_pw_phone}}' ) . "\n"
		. _pw_gb_row( 'rms-c2', 'Email', '{{post_meta key:_pw_email}}' )
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/loop-item -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/looper -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/query -->'
	);

	$content = _pw_gb_section(
		'rms-story',
		_pw_gb_h( 'rms-sh', 'About this room', 'h2' ) . "\n"
		. _pw_gb_p( 'rms-ct', '{{post_content}}' )
	);

	return _pw_gb_starter_root_wrap_div(
		'rms-starter-root',
		$hero . "\n"
		. $facts . "\n"
		. $rates . "\n"
		. $content . "\n"
		. $gallery . "\n"
		. $contacts . "\n"
		. _pw_gb_link( 'rms-bk', "\xE2\x86\x90 Back to rooms", '{{pw_current_section_listing_url}}' )
	);
}

/* ── Restaurant singular ───────────────────────────────────────────────── */

function _pw_markup_restaurant_singular(): string {
	$hero = _pw_gb_hero_img( 'rsts-hero' ) . "\n"
		. '<!-- wp:generateblocks/element {"uniqueId":"rsts-hero-copy","tagName":"div","styles":{"paddingTop":"32px","paddingBottom":"24px"},"css":".gb-element-rsts-hero-copy{padding:32px 0 24px}","className":"pw-hotel-hero gb-el gb-el-rsts-hero-copy"} -->' . "\n"
		. '<div class="gb-element-rsts-hero-copy gb-el gb-el-rsts-hero-copy pw-hotel-hero">' . "\n"
		. _pw_gb_h( 'rsts-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'rsts-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_cta_button( 'rsts-res', 'Reserve a table', '{{post_meta key:_pw_reservation_url}}' ) . "\n"
		. _pw_gb_link( 'rsts-mu', 'View menu', '{{post_meta key:_pw_menu_url}}' ) . "\n"
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/element -->';

	$facts = _pw_gb_section(
		'rsts-facts',
		_pw_gb_h( 'rsts-fh', 'At a glance', 'h2' ) . "\n"
		. _pw_gb_row( 'rsts-r0', 'Cuisine', '{{post_meta key:_pw_cuisine_type}}' ) . "\n"
		. _pw_gb_row( 'rsts-r1', 'Location', '{{post_meta key:_pw_location}}' ) . "\n"
		. _pw_gb_row( 'rsts-r2', 'Seats', '{{post_meta key:_pw_seating_capacity}}' ) . "\n"
		. _pw_gb_row( 'rsts-r3', 'Meal periods', '{{post_terms taxonomy:pw_meal_period}}' )
	);

	$hours = _pw_gb_section(
		'rsts-hrs',
		_pw_gb_h( 'rsts-hh', 'Hours', 'h2' ) . "\n"
		. _pw_gb_needs_shortcode_placeholder( 'pw_operating_hours' )
	);

	$gallery = _pw_gb_section(
		'rsts-gal',
		_pw_gb_h( 'rsts-gh', 'Gallery', 'h2' ) . "\n"
		. _pw_gb_needs_shortcode_placeholder( 'pw_gallery' )
	);

	$content = _pw_gb_section(
		'rsts-story',
		_pw_gb_h( 'rsts-sh', 'About', 'h2' ) . "\n"
		. _pw_gb_p( 'rsts-ct', '{{post_content}}' )
	);

	$contacts = '<!-- wp:generateblocks/query {"uniqueId":"rsts-ctq","tagName":"div","query":{"post_type":["pw_contact"],"posts_per_page":10,"orderby":"title","order":"asc"},"className":"pw-gb-scope-property pw-gb-contact-filter-outlet"} -->' . "\n"
		. '<div class="pw-gb-scope-property pw-gb-contact-filter-outlet">'
		. '<!-- wp:generateblocks/looper {"uniqueId":"rsts-ctlp","tagName":"div","className":"gb-loop-rsts-ctlp"} -->' . "\n"
		. '<div class="gb-looper-rsts-ctlp gb-loop-rsts-ctlp">'
		. '<!-- wp:generateblocks/loop-item {"uniqueId":"rsts-ctli","tagName":"div","styles":{"paddingTop":"16px","paddingBottom":"16px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#ececec"},"css":".gb-loop-item-rsts-ctli{border-bottom:1px solid #ececec;padding:16px 0}","className":"gb-li-rsts-ctli"} -->' . "\n"
		. '<div class="gb-loop-item gb-loop-item-rsts-ctli gb-li-rsts-ctli">'
		. _pw_gb_row( 'rsts-c0', 'Label', '{{post_meta key:_pw_label}}' ) . "\n"
		. _pw_gb_row( 'rsts-c1', 'Phone', '{{post_meta key:_pw_phone}}' ) . "\n"
		. _pw_gb_row( 'rsts-c2', 'Email', '{{post_meta key:_pw_email}}' )
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/loop-item -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/looper -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/query -->';

	return _pw_gb_starter_root_wrap_div(
		'rsts-starter-root',
		$hero . "\n"
		. $facts . "\n"
		. $hours . "\n"
		. $content . "\n"
		. $gallery . "\n"
		. _pw_gb_section( 'rsts-ct', _pw_gb_h( 'rsts-ch', 'Contacts', 'h2' ) . "\n" . $contacts ) . "\n"
		. _pw_gb_link( 'rsts-bk', "\xE2\x86\x90 Back to restaurants", '{{pw_current_section_listing_url}}' )
	);
}

/* ── Spa singular ──────────────────────────────────────────────────────── */

function _pw_markup_spa_singular(): string {
	$hero = _pw_gb_hero_img( 'spas-hero' ) . "\n"
		. '<!-- wp:generateblocks/element {"uniqueId":"spas-hero-copy","tagName":"div","styles":{"paddingTop":"32px","paddingBottom":"24px"},"css":".gb-element-spas-hero-copy{padding:32px 0 24px}","className":"pw-hotel-hero gb-el gb-el-spas-hero-copy"} -->' . "\n"
		. '<div class="gb-element-spas-hero-copy gb-el gb-el-spas-hero-copy pw-hotel-hero">' . "\n"
		. _pw_gb_h( 'spas-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'spas-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_cta_button( 'spas-bkurl', 'Book spa', '{{post_meta key:_pw_booking_url}}' ) . "\n"
		. _pw_gb_link( 'spas-mu', 'Spa menu', '{{post_meta key:_pw_menu_url}}' ) . "\n"
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/element -->';

	$facts = _pw_gb_section(
		'spas-facts',
		_pw_gb_h( 'spas-fh', 'At a glance', 'h2' ) . "\n"
		. _pw_gb_row( 'spas-r0', 'Minimum age', '{{post_meta key:_pw_min_age}}' ) . "\n"
		. _pw_gb_row( 'spas-r1', 'Treatment rooms', '{{post_meta key:_pw_number_of_treatment_rooms}}' ) . "\n"
		. _pw_gb_row( 'spas-r2', 'Treatments', '{{post_terms taxonomy:pw_treatment_type}}' )
	);

	$hours = _pw_gb_section(
		'spas-hrs',
		_pw_gb_h( 'spas-hh', 'Hours', 'h2' ) . "\n"
		. _pw_gb_needs_shortcode_placeholder( 'pw_operating_hours' )
	);

	$gallery = _pw_gb_section(
		'spas-gal',
		_pw_gb_h( 'spas-gh', 'Gallery', 'h2' ) . "\n"
		. _pw_gb_needs_shortcode_placeholder( 'pw_gallery' )
	);

	$content = _pw_gb_section(
		'spas-story',
		_pw_gb_h( 'spas-sh', 'About', 'h2' ) . "\n"
		. _pw_gb_p( 'spas-ct', '{{post_content}}' )
	);

	$contacts = '<!-- wp:generateblocks/query {"uniqueId":"spas-ctq","tagName":"div","query":{"post_type":["pw_contact"],"posts_per_page":10,"orderby":"title","order":"asc"},"className":"pw-gb-scope-property pw-gb-contact-filter-outlet"} -->' . "\n"
		. '<div class="pw-gb-scope-property pw-gb-contact-filter-outlet">'
		. '<!-- wp:generateblocks/looper {"uniqueId":"spas-ctlp","tagName":"div","className":"gb-loop-spas-ctlp"} -->' . "\n"
		. '<div class="gb-looper-spas-ctlp gb-loop-spas-ctlp">'
		. '<!-- wp:generateblocks/loop-item {"uniqueId":"spas-ctli","tagName":"div","styles":{"paddingTop":"16px","paddingBottom":"16px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#ececec"},"css":".gb-loop-item-spas-ctli{border-bottom:1px solid #ececec;padding:16px 0}","className":"gb-li-spas-ctli"} -->' . "\n"
		. '<div class="gb-loop-item gb-loop-item-spas-ctli gb-li-spas-ctli">'
		. _pw_gb_row( 'spas-c0', 'Label', '{{post_meta key:_pw_label}}' ) . "\n"
		. _pw_gb_row( 'spas-c1', 'Phone', '{{post_meta key:_pw_phone}}' ) . "\n"
		. _pw_gb_row( 'spas-c2', 'Email', '{{post_meta key:_pw_email}}' )
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/loop-item -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/looper -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/query -->';

	return _pw_gb_starter_root_wrap_div(
		'spas-starter-root',
		$hero . "\n"
		. $facts . "\n"
		. $hours . "\n"
		. $content . "\n"
		. $gallery . "\n"
		. _pw_gb_section( 'spas-ct', _pw_gb_h( 'spas-ch', 'Contacts', 'h2' ) . "\n" . $contacts ) . "\n"
		. _pw_gb_link( 'spas-bk', "\xE2\x86\x90 Back to spas", '{{pw_current_section_listing_url}}' )
	);
}

/* ── Meeting room singular ─────────────────────────────────────────────── */

function _pw_markup_meeting_singular(): string {
	$hero = _pw_gb_hero_img( 'mts-hero' ) . "\n"
		. '<!-- wp:generateblocks/element {"uniqueId":"mts-hero-copy","tagName":"div","styles":{"paddingTop":"32px","paddingBottom":"24px"},"css":".gb-element-mts-hero-copy{padding:32px 0 24px}","className":"pw-hotel-hero gb-el gb-el-mts-hero-copy"} -->' . "\n"
		. '<div class="gb-element-mts-hero-copy gb-el gb-el-mts-hero-copy pw-hotel-hero">' . "\n"
		. _pw_gb_h( 'mts-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'mts-ex', '{{post_excerpt}}' ) . "\n"
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/element -->';

	$facts = _pw_gb_section(
		'mts-facts',
		_pw_gb_h( 'mts-fh', 'Capacity and size', 'h2' ) . "\n"
		. _pw_gb_row( 'mts-r0', 'Theatre', '{{post_meta key:_pw_capacity_theatre}}' ) . "\n"
		. _pw_gb_row( 'mts-r1', 'Classroom', '{{post_meta key:_pw_capacity_classroom}}' ) . "\n"
		. _pw_gb_row( 'mts-r2', 'Boardroom', '{{post_meta key:_pw_capacity_boardroom}}' ) . "\n"
		. _pw_gb_row( 'mts-r3', 'U-shape', '{{post_meta key:_pw_capacity_ushape}}' ) . "\n"
		. _pw_gb_row( 'mts-r4', 'Area (m²)', '{{post_meta key:_pw_area_sqm}}' ) . "\n"
		. _pw_gb_row( 'mts-r5', 'Area (ft²)', '{{post_meta key:_pw_area_sqft}}' ) . "\n"
		. _pw_gb_row( 'mts-r6', 'Pre-function (m²)', '{{post_meta key:_pw_prefunction_area_sqm}}' ) . "\n"
		. _pw_gb_row( 'mts-r7', 'Pre-function (ft²)', '{{post_meta key:_pw_prefunction_area_sqft}}' ) . "\n"
		. _pw_gb_row( 'mts-r8', 'Natural light', '{{post_meta key:_pw_natural_light}}' ) . "\n"
		. _pw_gb_row( 'mts-r9', 'AV', '{{post_terms taxonomy:pw_av_equipment}}' )
	);

	$floor = _pw_gb_section(
		'mts-plan',
		_pw_gb_h( 'mts-ph', 'Floor plan', 'h2' ) . "\n"
		. _pw_gb_needs_shortcode_placeholder( 'pw_floor_plan' )
	);

	$gallery = _pw_gb_section(
		'mts-gal',
		_pw_gb_h( 'mts-gh', 'Gallery', 'h2' ) . "\n"
		. _pw_gb_needs_shortcode_placeholder( 'pw_gallery' )
	);

	$content = _pw_gb_section(
		'mts-story',
		_pw_gb_h( 'mts-sh', 'About this space', 'h2' ) . "\n"
		. _pw_gb_p( 'mts-ct', '{{post_content}}' )
	);

	$contacts = '<!-- wp:generateblocks/query {"uniqueId":"mts-ctq","tagName":"div","query":{"post_type":["pw_contact"],"posts_per_page":10,"orderby":"title","order":"asc"},"className":"pw-gb-scope-property pw-gb-contact-filter-outlet"} -->' . "\n"
		. '<div class="pw-gb-scope-property pw-gb-contact-filter-outlet">'
		. '<!-- wp:generateblocks/looper {"uniqueId":"mts-ctlp","tagName":"div","className":"gb-loop-mts-ctlp"} -->' . "\n"
		. '<div class="gb-looper-mts-ctlp gb-loop-mts-ctlp">'
		. '<!-- wp:generateblocks/loop-item {"uniqueId":"mts-ctli","tagName":"div","styles":{"paddingTop":"16px","paddingBottom":"16px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#ececec"},"css":".gb-loop-item-mts-ctli{border-bottom:1px solid #ececec;padding:16px 0}","className":"gb-li-mts-ctli"} -->' . "\n"
		. '<div class="gb-loop-item gb-loop-item-mts-ctli gb-li-mts-ctli">'
		. _pw_gb_row( 'mts-c0', 'Label', '{{post_meta key:_pw_label}}' ) . "\n"
		. _pw_gb_row( 'mts-c1', 'Phone', '{{post_meta key:_pw_phone}}' ) . "\n"
		. _pw_gb_row( 'mts-c2', 'Email', '{{post_meta key:_pw_email}}' )
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/loop-item -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/looper -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/query -->';

	return _pw_gb_starter_root_wrap_div(
		'mts-starter-root',
		$hero . "\n"
		. $facts . "\n"
		. $floor . "\n"
		. $gallery . "\n"
		. $content . "\n"
		. _pw_gb_section( 'mts-enq', _pw_gb_h( 'mts-eh', 'Enquiries', 'h2' ) . "\n" . $contacts ) . "\n"
		. _pw_gb_link( 'mts-bk', "\xE2\x86\x90 Back to meetings", '{{pw_current_section_listing_url}}' )
	);
}

/* ── Experience singular ───────────────────────────────────────────────── */

function _pw_markup_experience_singular(): string {
	$hero = _pw_gb_hero_img( 'exs-hero' ) . "\n"
		. '<!-- wp:generateblocks/element {"uniqueId":"exs-hero-copy","tagName":"div","styles":{"paddingTop":"32px","paddingBottom":"24px"},"css":".gb-element-exs-hero-copy{padding:32px 0 24px}","className":"pw-hotel-hero gb-el gb-el-exs-hero-copy"} -->' . "\n"
		. '<div class="gb-element-exs-hero-copy gb-el gb-el-exs-hero-copy pw-hotel-hero">' . "\n"
		. _pw_gb_h( 'exs-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'exs-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_p( 'exs-dsc', '{{post_meta key:_pw_description}}' ) . "\n"
		. _pw_gb_cta_button( 'exs-bk', 'Book this experience', '{{post_meta key:_pw_booking_url}}' ) . "\n"
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/element -->';

	$facts = _pw_gb_section(
		'exs-facts',
		_pw_gb_h( 'exs-fh', 'Details', 'h2' ) . "\n"
		. _pw_gb_row( 'exs-r0', 'Duration (hours)', '{{post_meta key:_pw_duration_hours}}' ) . "\n"
		. _pw_gb_row( 'exs-r1', 'From', '{{post_meta key:_pw_price_from}} __PW_PROPERTY_CURRENCY__' ) . "\n"
		. _pw_gb_row( 'exs-r2', 'Complimentary', '{{post_meta key:_pw_is_complimentary}}' ) . "\n"
		. _pw_gb_row( 'exs-r3', 'Category', '{{post_terms taxonomy:pw_experience_category}}' )
	);

	$gallery = _pw_gb_section(
		'exs-gal',
		_pw_gb_h( 'exs-gh', 'Gallery', 'h2' ) . "\n"
		. _pw_gb_needs_shortcode_placeholder( 'pw_gallery' )
	);

	$content = _pw_gb_section(
		'exs-story',
		_pw_gb_h( 'exs-sh', 'More information', 'h2' ) . "\n"
		. _pw_gb_p( 'exs-ct', '{{post_content}}' )
	);

	$contacts = '<!-- wp:generateblocks/query {"uniqueId":"exs-ctq","tagName":"div","query":{"post_type":["pw_contact"],"posts_per_page":10,"orderby":"title","order":"asc"},"className":"pw-gb-scope-property pw-gb-contact-filter-outlet"} -->' . "\n"
		. '<div class="pw-gb-scope-property pw-gb-contact-filter-outlet">'
		. '<!-- wp:generateblocks/looper {"uniqueId":"exs-ctlp","tagName":"div","className":"gb-loop-exs-ctlp"} -->' . "\n"
		. '<div class="gb-looper-exs-ctlp gb-loop-exs-ctlp">'
		. '<!-- wp:generateblocks/loop-item {"uniqueId":"exs-ctli","tagName":"div","styles":{"paddingTop":"16px","paddingBottom":"16px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#ececec"},"css":".gb-loop-item-exs-ctli{border-bottom:1px solid #ececec;padding:16px 0}","className":"gb-li-exs-ctli"} -->' . "\n"
		. '<div class="gb-loop-item gb-loop-item-exs-ctli gb-li-exs-ctli">'
		. _pw_gb_row( 'exs-c0', 'Label', '{{post_meta key:_pw_label}}' ) . "\n"
		. _pw_gb_row( 'exs-c1', 'Phone', '{{post_meta key:_pw_phone}}' ) . "\n"
		. _pw_gb_row( 'exs-c2', 'Email', '{{post_meta key:_pw_email}}' )
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/loop-item -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/looper -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/query -->';

	return _pw_gb_starter_root_wrap_div(
		'exs-starter-root',
		$hero . "\n"
		. $facts . "\n"
		. $gallery . "\n"
		. $content . "\n"
		. _pw_gb_section( 'exs-ct', _pw_gb_h( 'exs-ch', 'Contacts', 'h2' ) . "\n" . $contacts ) . "\n"
		. _pw_gb_link( 'exs-bk', "\xE2\x86\x90 Back to experiences", '{{pw_current_section_listing_url}}' )
	);
}

/* ── Event singular ────────────────────────────────────────────────────── */

function _pw_markup_event_singular(): string {
	$hero = _pw_gb_hero_img( 'evs-hero' ) . "\n"
		. '<!-- wp:generateblocks/element {"uniqueId":"evs-hero-copy","tagName":"div","styles":{"paddingTop":"32px","paddingBottom":"24px"},"css":".gb-element-evs-hero-copy{padding:32px 0 24px}","className":"pw-hotel-hero gb-el gb-el-evs-hero-copy"} -->' . "\n"
		. '<div class="gb-element-evs-hero-copy gb-el gb-el-evs-hero-copy pw-hotel-hero">' . "\n"
		. _pw_gb_h( 'evs-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'evs-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_p( 'evs-dsc', '{{post_meta key:_pw_description}}' ) . "\n"
		. _pw_gb_cta_button( 'evs-bkurl', 'Book or register', '{{post_meta key:_pw_booking_url}}' ) . "\n"
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/element -->';

	$facts = _pw_gb_section(
		'evs-facts',
		_pw_gb_h( 'evs-fh', 'Event details', 'h2' ) . "\n"
		. _pw_gb_row( 'evs-r0', 'Starts', '{{post_meta key:_pw_start_datetime_iso8601}}' ) . "\n"
		. _pw_gb_row( 'evs-r1', 'Ends', '{{post_meta key:_pw_end_datetime_iso8601}}' ) . "\n"
		. _pw_gb_row( 'evs-r2', 'Capacity', '{{post_meta key:_pw_capacity}}' ) . "\n"
		. _pw_gb_row( 'evs-r3', 'From', '{{post_meta key:_pw_price_from}} __PW_PROPERTY_CURRENCY__' ) . "\n"
		. _pw_gb_row( 'evs-r4', 'Status', '{{post_meta key:_pw_event_status}}' ) . "\n"
		. _pw_gb_row( 'evs-r5', 'Attendance', '{{post_meta key:_pw_event_attendance_mode}}' ) . "\n"
		. _pw_gb_row( 'evs-r6', 'Type', '{{post_terms taxonomy:pw_event_type}}' ) . "\n"
		. _pw_gb_row( 'evs-r7', 'Organiser', '{{post_terms taxonomy:pw_event_organiser}}' )
	);

	$gallery = _pw_gb_section(
		'evs-gal',
		_pw_gb_h( 'evs-gh', 'Gallery', 'h2' ) . "\n"
		. _pw_gb_needs_shortcode_placeholder( 'pw_gallery' )
	);

	$content = _pw_gb_section(
		'evs-story',
		_pw_gb_h( 'evs-sh', 'More information', 'h2' ) . "\n"
		. _pw_gb_p( 'evs-ct', '{{post_content}}' )
	);

	$contacts = '<!-- wp:generateblocks/query {"uniqueId":"evs-ctq","tagName":"div","query":{"post_type":["pw_contact"],"posts_per_page":10,"orderby":"title","order":"asc"},"className":"pw-gb-scope-property pw-gb-contact-filter-property"} -->' . "\n"
		. '<div class="pw-gb-scope-property pw-gb-contact-filter-property">'
		. '<!-- wp:generateblocks/looper {"uniqueId":"evs-ctlp","tagName":"div","className":"gb-loop-evs-ctlp"} -->' . "\n"
		. '<div class="gb-looper-evs-ctlp gb-loop-evs-ctlp">'
		. '<!-- wp:generateblocks/loop-item {"uniqueId":"evs-ctli","tagName":"div","styles":{"paddingTop":"16px","paddingBottom":"16px","borderBottomWidth":"1px","borderBottomStyle":"solid","borderBottomColor":"#ececec"},"css":".gb-loop-item-evs-ctli{border-bottom:1px solid #ececec;padding:16px 0}","className":"gb-li-evs-ctli"} -->' . "\n"
		. '<div class="gb-loop-item gb-loop-item-evs-ctli gb-li-evs-ctli">'
		. _pw_gb_row( 'evs-c0', 'Label', '{{post_meta key:_pw_label}}' ) . "\n"
		. _pw_gb_row( 'evs-c1', 'Phone', '{{post_meta key:_pw_phone}}' ) . "\n"
		. _pw_gb_row( 'evs-c2', 'Email', '{{post_meta key:_pw_email}}' )
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/loop-item -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/looper -->'
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/query -->';

	return _pw_gb_starter_root_wrap_div(
		'evs-starter-root',
		$hero . "\n"
		. $facts . "\n"
		. $gallery . "\n"
		. $content . "\n"
		. _pw_gb_section( 'evs-ct', _pw_gb_h( 'evs-ch', 'Contacts', 'h2' ) . "\n" . $contacts ) . "\n"
		. _pw_gb_link( 'evs-bk', "\xE2\x86\x90 Back to events", '{{pw_current_section_listing_url}}' )
	);
}

/* ── Offer singular ────────────────────────────────────────────────────── */

function _pw_markup_offer_singular(): string {
	$hero = _pw_gb_hero_img( 'ofs-hero' ) . "\n"
		. '<!-- wp:generateblocks/element {"uniqueId":"ofs-hero-copy","tagName":"div","styles":{"paddingTop":"32px","paddingBottom":"24px"},"css":".gb-element-ofs-hero-copy{padding:32px 0 24px}","className":"pw-hotel-hero gb-el gb-el-ofs-hero-copy"} -->' . "\n"
		. '<div class="gb-element-ofs-hero-copy gb-el gb-el-ofs-hero-copy pw-hotel-hero">' . "\n"
		. _pw_gb_h( 'ofs-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'ofs-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_cta_button( 'ofs-bkurl', 'Book this offer', '{{post_meta key:_pw_booking_url}}' ) . "\n"
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/element -->';

	$facts = _pw_gb_section(
		'ofs-facts',
		_pw_gb_h( 'ofs-fh', 'Offer details', 'h2' ) . "\n"
		. _pw_gb_row( 'ofs-r0', 'Type', '{{post_meta key:_pw_offer_type}}' ) . "\n"
		. _pw_gb_row( 'ofs-r1', 'Valid from', '{{post_meta key:_pw_valid_from}}' ) . "\n"
		. _pw_gb_row( 'ofs-r2', 'Valid to', '{{post_meta key:_pw_valid_to}}' ) . "\n"
		. _pw_gb_row( 'ofs-r3', 'Discount type', '{{post_meta key:_pw_discount_type}}' ) . "\n"
		. _pw_gb_row( 'ofs-r4', 'Discount value', '{{post_meta key:_pw_discount_value}}' ) . "\n"
		. _pw_gb_row( 'ofs-r5', 'Minimum stay (nights)', '{{post_meta key:_pw_minimum_stay_nights}}' ) . "\n"
		. _pw_gb_row( 'ofs-r6', 'Featured', '{{post_meta key:_pw_is_featured}}' )
	);

	$content = _pw_gb_section(
		'ofs-story',
		_pw_gb_h( 'ofs-sh', 'Terms and details', 'h2' ) . "\n"
		. _pw_gb_p( 'ofs-ct', '{{post_content}}' )
	);

	return _pw_gb_starter_root_wrap_div(
		'ofs-starter-root',
		$hero . "\n"
		. $facts . "\n"
		. $content . "\n"
		. _pw_gb_link( 'ofs-bk', "\xE2\x86\x90 Back to offers", '{{pw_current_section_listing_url}}' )
	);
}

/* ── Nearby place singular ─────────────────────────────────────────────── */

function _pw_markup_nearby_singular(): string {
	$hero = _pw_gb_hero_img( 'pls-hero' ) . "\n"
		. '<!-- wp:generateblocks/element {"uniqueId":"pls-hero-copy","tagName":"div","styles":{"paddingTop":"32px","paddingBottom":"24px"},"css":".gb-element-pls-hero-copy{padding:32px 0 24px}","className":"pw-hotel-hero gb-el gb-el-pls-hero-copy"} -->' . "\n"
		. '<div class="gb-element-pls-hero-copy gb-el gb-el-pls-hero-copy pw-hotel-hero">' . "\n"
		. _pw_gb_h( 'pls-h1', '{{post_title}}', 'h1' ) . "\n"
		. _pw_gb_p( 'pls-ex', '{{post_excerpt}}' ) . "\n"
		. _pw_gb_cta_button( 'pls-dir', 'Get directions', '{{post_meta key:_pw_place_url}}' ) . "\n"
		. '</div>' . "\n"
		. '<!-- /wp:generateblocks/element -->';

	$facts = _pw_gb_section(
		'pls-facts',
		_pw_gb_h( 'pls-fh', 'Getting there', 'h2' ) . "\n"
		. _pw_gb_row( 'pls-r0', 'Distance (km)', '{{post_meta key:_pw_distance_km}}' ) . "\n"
		. _pw_gb_row( 'pls-r1', 'Travel time (min)', '{{post_meta key:_pw_travel_time_min}}' ) . "\n"
		. _pw_gb_row( 'pls-r2', 'Place type', '{{post_terms taxonomy:pw_nearby_type}}' ) . "\n"
		. _pw_gb_row( 'pls-r3', 'How to get there', '{{post_terms taxonomy:pw_transport_mode}}' ) . "\n"
		. _pw_gb_row( 'pls-r4', 'Latitude', '{{post_meta key:_pw_lat}}' ) . "\n"
		. _pw_gb_row( 'pls-r5', 'Longitude', '{{post_meta key:_pw_lng}}' )
	);

	$content = _pw_gb_section(
		'pls-story',
		_pw_gb_h( 'pls-sh', 'About this place', 'h2' ) . "\n"
		. _pw_gb_p( 'pls-ct', '{{post_content}}' )
	);

	return _pw_gb_starter_root_wrap_div(
		'pls-starter-root',
		$hero . "\n"
		. $facts . "\n"
		. $content . "\n"
		. _pw_gb_link( 'pls-bk', "\xE2\x86\x90 Back to places nearby", '{{pw_current_section_listing_url}}' )
	);
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

	$results = pw_run_page_installer_all_scopes();
	set_transient( 'pw_installer_manual_results', pw_summarize_installer_results( $results ), 120 );

	// Admin redirect — pw_redirect_with_qs() not required.
	wp_safe_redirect(
		add_query_arg(
			'pw_installer_ran',
			'1',
			pw_admin_data_url()
		)
	);
	exit;
}

add_action( 'admin_post_pw_run_page_installer', 'pw_handle_admin_post_pw_run_page_installer' );
