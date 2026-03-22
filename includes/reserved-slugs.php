<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build reserved slug list from a settings snapshot (for validation before save).
 *
 * @param array<string, mixed> $settings Same shape as pw_get_merged_pw_settings() output.
 * @return list<string>
 */
function pw_build_reserved_slugs_from_settings( array $settings ) {
	$slugs = [];
	$bases = isset( $settings['pw_section_bases'] ) && is_array( $settings['pw_section_bases'] )
		? $settings['pw_section_bases']
		: pw_default_section_bases();
	foreach ( pw_default_section_bases() as $cpt => $def ) {
		$pair = isset( $bases[ $cpt ] ) && is_array( $bases[ $cpt ] ) ? $bases[ $cpt ] : $def;
		$p = sanitize_title( (string) ( $pair['plural'] ?? '' ) );
		$s = sanitize_title( (string) ( $pair['singular'] ?? '' ) );
		if ( $p !== '' ) {
			$slugs[] = $p;
		}
		if ( $s !== '' ) {
			$slugs[] = $s;
		}
	}
	$slugs = array_values( array_unique( array_filter( $slugs ) ) );
	sort( $slugs );
	return $slugs;
}

/**
 * @return list<string>
 */
function pw_get_reserved_slugs() {
	return pw_build_reserved_slugs_from_settings( pw_get_merged_pw_settings() );
}

function pw_is_reserved_slug( $slug ) {
	$slug = sanitize_title( (string) $slug );
	if ( $slug === '' ) {
		return false;
	}
	return in_array( $slug, pw_get_reserved_slugs(), true );
}

/**
 * @return true if OK, false if conflicts (transient set with message HTML).
 */
function pw_validate_new_settings_reserved_conflicts( array $proposed_merged ) {
	$reserved = pw_build_reserved_slugs_from_settings( $proposed_merged );
	if ( $reserved === [] ) {
		return true;
	}
	$conflicts = [];
	$types     = [ 'pw_property', 'page' ];
	foreach ( $types as $pt ) {
		$q = new WP_Query(
			[
				'post_type'              => $pt,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			]
		);
		foreach ( $q->posts as $pid ) {
			$pid = (int) $pid;
			$name = get_post_field( 'post_name', $pid );
			if ( ! is_string( $name ) || $name === '' ) {
				continue;
			}
			if ( in_array( $name, $reserved, true ) ) {
				$edit = get_edit_post_link( $pid, 'raw' );
				$conflicts[] = [
					'title' => get_the_title( $pid ),
					'url'   => $edit ? $edit : '',
				];
			}
		}
		wp_reset_postdata();
	}
	if ( $conflicts === [] ) {
		return true;
	}
	$lines = [];
	foreach ( $conflicts as $c ) {
		$t = esc_html( $c['title'] );
		if ( $c['url'] !== '' ) {
			$t = '<a href="' . esc_url( $c['url'] ) . '">' . $t . '</a>';
		}
		$lines[] = $t;
	}
	set_transient(
		'pw_settings_reserved_conflict',
		wp_kses_post( '<p>' . implode( '</p><p>', $lines ) . '</p>' ),
		120
	);
	return false;
}

add_filter(
	'wp_unique_post_slug',
	static function ( $slug, $post_id, $post_status, $post_type, $post_parent, $original_slug ) {
		if ( ! in_array( $post_type, [ 'pw_property', 'page' ], true ) ) {
			return $slug;
		}
		if ( ! pw_is_reserved_slug( $slug ) ) {
			return $slug;
		}
		$base = sanitize_title( (string) $original_slug );
		if ( $base === '' ) {
			$base = $slug;
		}
		$new = $base . '-2';
		$n   = 2;
		while ( true ) {
			$dup = get_posts(
				[
					'post_type'      => $post_type,
					'post_status'    => 'any',
					'name'           => $new,
					'post__not_in'   => $post_id ? [ (int) $post_id ] : [],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				]
			);
			if ( ! pw_is_reserved_slug( $new ) && empty( $dup ) ) {
				break;
			}
			++$n;
			$new = $base . '-' . $n;
		}
		set_transient(
			'pw_reserved_slug_notice_' . get_current_user_id(),
			sprintf(
				/* translators: 1: original slug, 2: new slug */
				__( 'The slug "%1$s" is reserved by Portico Webworks URL settings. The slug was changed to "%2$s".', 'portico-webworks' ),
				esc_html( $slug ),
				esc_html( $new )
			),
			60
		);
		return $new;
	},
	10,
	6
);

add_action(
	'save_post',
	static function ( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}
		if ( ! $post instanceof WP_Post ) {
			return;
		}
		if ( ! in_array( $post->post_type, [ 'pw_property', 'page' ], true ) ) {
			return;
		}
		if ( $post->post_status !== 'publish' ) {
			return;
		}
		$name = $post->post_name;
		if ( ! is_string( $name ) || $name === '' ) {
			return;
		}
		if ( pw_is_reserved_slug( $name ) ) {
			set_transient(
				'pw_reserved_slug_advisory_' . get_current_user_id(),
				sprintf(
					/* translators: %s: post slug */
					__( 'The slug "%s" conflicts with a Portico Webworks reserved URL base. Consider changing it to avoid routing issues.', 'portico-webworks' ),
					esc_html( $name )
				),
				60
			);
		}
	},
	1,
	3
);

add_action(
	'admin_notices',
	static function () {
		if ( current_user_can( 'edit_posts' ) ) {
			$uid = get_current_user_id();
			$k   = 'pw_reserved_slug_notice_' . $uid;
			$msg = get_transient( $k );
			if ( $msg ) {
				delete_transient( $k );
				echo '<div class="notice notice-warning is-dismissible"><p>' . wp_kses_post( $msg ) . '</p></div>';
			}
			$k2 = 'pw_reserved_slug_advisory_' . $uid;
			$m2 = get_transient( $k2 );
			if ( $m2 ) {
				delete_transient( $k2 );
				echo '<div class="notice notice-info is-dismissible"><p>' . wp_kses_post( $m2 ) . '</p></div>';
			}
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$html = get_transient( 'pw_settings_reserved_conflict' );
		if ( ! $html ) {
			return;
		}
		delete_transient( 'pw_settings_reserved_conflict' );
		echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Settings not saved:', 'portico-webworks' ) . '</strong> ';
		echo esc_html__( 'These published posts use slugs that would become reserved under the new URL bases:', 'portico-webworks' );
		echo '</p>' . $html . '</div>';
	}
);
