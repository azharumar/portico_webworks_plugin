<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PW_NAV_MENU_PRIMARY', 'pw_primary' );
define( 'PW_NAV_MENU_UTILITY', 'pw_utility' );
define( 'PW_NAV_MENU_FOOTER', 'pw_footer' );

add_action( 'after_setup_theme', 'pw_register_nav_menus', 20 );

function pw_register_nav_menus(): void {
	register_nav_menus(
		[
			PW_NAV_MENU_PRIMARY  => __( 'Portico primary (header)', 'portico-webworks' ),
			PW_NAV_MENU_UTILITY => __( 'Portico utility (header bar)', 'portico-webworks' ),
			PW_NAV_MENU_FOOTER  => __( 'Portico footer', 'portico-webworks' ),
		]
	);
}

add_shortcode( 'pw_primary_nav', 'pw_shortcode_primary_nav' );
add_shortcode( 'pw_utility_nav', 'pw_shortcode_utility_nav' );
add_shortcode( 'pw_footer_nav', 'pw_shortcode_footer_nav' );
add_shortcode( 'pw_site_branding', 'pw_shortcode_site_branding' );
add_shortcode( 'pw_book_now_button', 'pw_shortcode_book_now_button' );
add_shortcode( 'pw_portico_breadcrumbs', 'pw_shortcode_portico_breadcrumbs' );

function pw_shortcode_primary_nav(): string {
	ob_start();
	wp_nav_menu(
		[
			'theme_location'  => PW_NAV_MENU_PRIMARY,
			'container'       => 'nav',
			'container_class' => 'pw-primary-nav',
			'container_aria_label' => __( 'Primary', 'portico-webworks' ),
			'menu_class'      => 'pw-primary-nav__list',
			'fallback_cb'     => false,
			'depth'           => 3,
		]
	);

	return (string) ob_get_clean();
}

function pw_shortcode_utility_nav(): string {
	ob_start();
	wp_nav_menu(
		[
			'theme_location'  => PW_NAV_MENU_UTILITY,
			'container'       => 'nav',
			'container_class' => 'pw-utility-nav',
			'container_aria_label' => __( 'Utility', 'portico-webworks' ),
			'menu_class'      => 'pw-utility-nav__list',
			'fallback_cb'     => false,
			'depth'           => 1,
		]
	);

	return (string) ob_get_clean();
}

function pw_shortcode_footer_nav(): string {
	ob_start();
	wp_nav_menu(
		[
			'theme_location'       => PW_NAV_MENU_FOOTER,
			'container'            => 'nav',
			'container_class'      => 'pw-footer-nav',
			'container_aria_label' => __( 'Footer', 'portico-webworks' ),
			'menu_class'           => 'pw-footer-nav__list',
			'fallback_cb'          => false,
			'depth'                => 2,
		]
	);

	return (string) ob_get_clean();
}

function pw_shortcode_site_branding(): string {
	$home = esc_url( untrailingslashit( home_url( '/' ) ) );
	if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
		ob_start();
		echo '<a class="pw-site-branding" href="' . esc_url( $home ) . '" rel="home">';
		the_custom_logo();
		echo '</a>';

		return (string) ob_get_clean();
	}

	return sprintf(
		'<a class="pw-site-branding pw-site-branding--text" href="%1$s" rel="home"><span class="pw-site-branding__title">%2$s</span></a>',
		esc_url( $home ),
		esc_html( get_bloginfo( 'name', 'display' ) )
	);
}

function pw_shortcode_book_now_button(): string {
	$url = pw_get_setting( 'pw_book_now_url', '' );
	$url = is_string( $url ) && $url !== '' ? $url : '#';
	$url = esc_url( $url );

	return sprintf(
		'<a class="pw-book-now-button btn-primary" href="%1$s">%2$s</a>',
		$url,
		esc_html__( 'Book now', 'portico-webworks' )
	);
}

function pw_shortcode_portico_breadcrumbs(): string {
	return pw_get_breadcrumbs_html();
}

function pw_get_breadcrumbs_html(): string {
	$items = [];

	$items[] = [
		'url'   => untrailingslashit( home_url( '/' ) ),
		'label' => __( 'Home', 'portico-webworks' ),
		'current' => false,
	];

	$pid = (int) pw_get_current_property_id();
	if ( $pid > 0 ) {
		$purl  = pw_get_property_url( $pid );
		$ptitle = get_the_title( $pid );
		if ( $purl !== '' && is_string( $ptitle ) && $ptitle !== '' ) {
			$items[] = [
				'url'     => $purl,
				'label'   => $ptitle,
				'current' => false,
			];
		}
	}

	$cpt    = sanitize_key( (string) get_query_var( 'pw_section_cpt', '' ) );
	$outlet = (string) get_query_var( 'pw_outlet_slug', '' );

	if ( $cpt !== '' && in_array( $cpt, pw_url_section_cpts(), true ) && $pid > 0 ) {
		$list_url = pw_get_section_listing_url( $pid, $cpt );
		$slabel   = pw_get_section_breadcrumb_label( $cpt );
		$is_singular = $outlet !== '';
		if ( $list_url !== '' && $slabel !== '' ) {
			$items[] = [
				'url'     => $is_singular ? $list_url : '',
				'label'   => $slabel,
				'current' => ! $is_singular,
			];
		}
		if ( $is_singular ) {
			$oid = (int) get_queried_object_id();
			$t   = $oid > 0 ? get_the_title( $oid ) : '';
			if ( is_string( $t ) && $t !== '' ) {
				$items[] = [
					'url'     => '',
					'label'   => $t,
					'current' => true,
				];
			}
		}
	} elseif ( is_singular( 'pw_property' ) ) {
		$items[] = [
			'url'     => '',
			'label'   => get_the_title(),
			'current' => true,
		];
	}

	if ( count( $items ) <= 1 ) {
		return '';
	}

	$parts = [];
	foreach ( $items as $i ) {
		$label = isset( $i['label'] ) ? (string) $i['label'] : '';
		if ( $label === '' ) {
			continue;
		}
		if ( ! empty( $i['current'] ) || empty( $i['url'] ) ) {
			$parts[] = '<span class="pw-breadcrumbs__current" aria-current="page">' . esc_html( $label ) . '</span>';
		} else {
			$parts[] = '<a class="pw-breadcrumbs__link" href="' . esc_url( (string) $i['url'] ) . '">' . esc_html( $label ) . '</a>';
		}
	}

	if ( count( $parts ) <= 1 ) {
		return '';
	}

	return '<nav class="pw-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'portico-webworks' ) . '"><div class="pw-breadcrumbs__inner">' . implode( ' <span class="pw-breadcrumbs__sep">/</span> ', $parts ) . '</div></nav>';
}

function pw_get_section_breadcrumb_label( string $cpt ): string {
	$map = [
		'pw_room_type'    => __( 'Rooms', 'portico-webworks' ),
		'pw_restaurant'   => __( 'Dining', 'portico-webworks' ),
		'pw_spa'          => __( 'Spa', 'portico-webworks' ),
		'pw_meeting_room' => __( 'Meetings', 'portico-webworks' ),
		'pw_experience'   => __( 'Experiences', 'portico-webworks' ),
		'pw_event'        => __( 'Events', 'portico-webworks' ),
		'pw_offer'        => __( 'Offers', 'portico-webworks' ),
		'pw_nearby'       => __( 'Places', 'portico-webworks' ),
	];

	return $map[ $cpt ] ?? '';
}

/**
 * Property ID used for Portico primary nav section URLs (default property, or first published by title in multi).
 */
function pw_resolve_portico_nav_seed_property_id(): int {
	$pid = (int) pw_get_setting( 'pw_default_property_id', 0 );
	if ( pw_get_setting( 'pw_property_mode', 'single' ) === 'multi' && $pid <= 0 ) {
		$first = get_posts(
			[
				'post_type'      => 'pw_property',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'fields'         => 'ids',
			]
		);
		$pid = isset( $first[0] ) ? (int) $first[0] : 0;
	}
	return $pid;
}

/**
 * Map menu item title => custom URL for Portico-seeded primary links.
 *
 * @return array<string, string>
 */
function pw_get_portico_primary_nav_seed_custom_url_map( int $pid ): array {
	$map = [];
	if ( pw_get_setting( 'pw_property_mode', 'single' ) === 'multi' ) {
		$map[ __( 'Hotels', 'portico-webworks' ) ] = untrailingslashit( home_url( '/' ) );
	}
	$links = [
		__( 'Experiences', 'portico-webworks' ) => 'pw_experience',
		__( 'Offers', 'portico-webworks' )       => 'pw_offer',
		__( 'Dining', 'portico-webworks' )       => 'pw_restaurant',
		__( 'Meetings', 'portico-webworks' )     => 'pw_meeting_room',
		__( 'Events', 'portico-webworks' )       => 'pw_event',
		__( 'Spa', 'portico-webworks' )          => 'pw_spa',
	];
	foreach ( $links as $title => $section_cpt ) {
		$url = pw_get_section_listing_url( $pid, $section_cpt );
		if ( $url !== '' ) {
			$map[ $title ] = $url;
		}
	}
	return $map;
}

function pw_maybe_seed_portico_nav_menus(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$locations = get_theme_mod( 'nav_menu_locations', [] );
	if ( ! is_array( $locations ) ) {
		$locations = [];
	}
	$existing_id = isset( $locations[ PW_NAV_MENU_PRIMARY ] ) ? (int) $locations[ PW_NAV_MENU_PRIMARY ] : 0;
	if ( $existing_id > 0 && is_nav_menu( $existing_id ) ) {
		return;
	}

	$menu_name = __( 'Portico Primary', 'portico-webworks' );
	$menu      = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $menu_name );
	if ( $menu_id <= 0 || is_wp_error( $menu_id ) ) {
		return;
	}

	$pid   = pw_resolve_portico_nav_seed_property_id();
	$order = 0;
	foreach ( pw_get_portico_primary_nav_seed_custom_url_map( $pid ) as $title => $url ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			[
				'menu-item-title'      => $title,
				'menu-item-url'        => esc_url_raw( $url ),
				'menu-item-status'     => 'publish',
				'menu-item-position'   => ++$order,
			]
		);
	}

	$locations[ PW_NAV_MENU_PRIMARY ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * After sample data install: refresh primary nav custom-link URLs to match current section listings.
 */
function pw_sync_portico_nav_menus_after_sample_install(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$pid = pw_resolve_portico_nav_seed_property_id();
	if ( $pid <= 0 ) {
		return;
	}

	$locations = get_theme_mod( 'nav_menu_locations', [] );
	if ( ! is_array( $locations ) ) {
		$locations = [];
	}
	$menu_id = isset( $locations[ PW_NAV_MENU_PRIMARY ] ) ? (int) $locations[ PW_NAV_MENU_PRIMARY ] : 0;
	if ( $menu_id <= 0 || ! is_nav_menu( $menu_id ) ) {
		pw_maybe_seed_portico_nav_menus();
		return;
	}

	$url_map = pw_get_portico_primary_nav_seed_custom_url_map( $pid );
	$items   = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return;
	}

	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post || $item->type !== 'custom' ) {
			continue;
		}
		$title = isset( $item->title ) ? (string) $item->title : '';
		if ( $title === '' || ! isset( $url_map[ $title ] ) ) {
			continue;
		}
		$new_url = $url_map[ $title ];
		if ( untrailingslashit( (string) $item->url ) === untrailingslashit( $new_url ) ) {
			continue;
		}
		wp_update_nav_menu_item(
			$menu_id,
			(int) $item->db_id,
			[
				'menu-item-title'    => $title,
				'menu-item-url'      => esc_url_raw( $new_url ),
				'menu-item-status'   => 'publish',
			]
		);
	}
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( is_admin() ) {
			return;
		}
		$css = '.pw-primary-nav__list,.pw-utility-nav__list{display:flex;flex-wrap:wrap;gap:1rem;list-style:none;margin:0;padding:0;align-items:center}.pw-utility-nav,.pw-utility-nav a{color:inherit}.pw-utility-nav__list{justify-content:flex-start}.pw-primary-nav{justify-content:center;width:100%}.pw-header-main__menu{display:flex;justify-content:center;width:100%}.pw-header-main__cta{display:flex;justify-content:flex-end;align-items:center}.pw-book-now-button{display:inline-flex;align-items:center;padding:10px 48px;background:var(--green-1,#025155);text-decoration:none;color:#fff;font-weight:600;text-transform:uppercase;font-size:12px}.pw-book-now-button:hover,.pw-book-now-button:focus{background:var(--green-2,#013d40);color:#fff}@media (max-width:1024px){.pw-book-now-button{padding-left:20px;padding-right:20px;font-size:12px}}.pw-breadcrumbs{font-size:13px;padding:10px 0;color:var(--text-1,#555)}.pw-site-branding{display:inline-block}.pw-site-branding img{max-height:48px;width:auto}.pw-footer-nav__list{display:flex;flex-direction:column;gap:10px;list-style:none;margin:0;padding:0}.pw-footer-nav__list a{color:inherit;text-decoration:none}.pw-footer-nav__list a:hover,.pw-footer-nav__list a:focus{text-decoration:underline}.pw-footer-ql a{color:inherit;text-decoration:none}.pw-footer-ql a:hover,.pw-footer-ql a:focus{text-decoration:underline}';
		wp_register_style( 'pw-nav-menus', false, [], defined( 'PW_VERSION' ) ? PW_VERSION : '0' );
		wp_enqueue_style( 'pw-nav-menus' );
		wp_add_inline_style( 'pw-nav-menus', $css );
	},
	20
);
