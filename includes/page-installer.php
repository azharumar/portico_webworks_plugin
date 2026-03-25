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

	set_transient(
		'pw_installer_last_run',
		[
			'property_id'    => (int) $post->ID,
			'property_title' => get_the_title( $post ),
			'page_titles'    => [],
		],
		300
	);
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
