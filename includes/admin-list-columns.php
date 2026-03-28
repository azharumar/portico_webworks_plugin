<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pw_admin_list_column_post_types() {
	return [
		'pw_property',
	];
}

function pw_admin_post_types_with_property_column() {
	return array_diff( pw_admin_list_column_post_types(), [ 'pw_property' ] );
}

function pw_admin_insert_columns_after_key( array $columns, array $insert, $after_key ) {
	$out = [];
	foreach ( $columns as $key => $label ) {
		$out[ $key ] = $label;
		if ( $after_key === $key ) {
			foreach ( $insert as $col_key => $col_label ) {
				$out[ $col_key ] = $col_label;
			}
		}
	}
	return $out;
}

function pw_admin_render_property_cell( $post_id ) {
	$pid = (int) get_post_meta( $post_id, '_pw_property_id', true );
	if ( $pid <= 0 ) {
		echo '—';
		return;
	}
	$p = get_post( $pid );
	if ( ! $p || 'pw_property' !== $p->post_type ) {
		echo '—';
		return;
	}
	$link = get_edit_post_link( $pid );
	if ( $link ) {
		echo '<a href="' . esc_url( $link ) . '">' . esc_html( get_the_title( $pid ) ) . '</a>';
	} else {
		echo esc_html( get_the_title( $pid ) );
	}
}

function pw_admin_format_datetime_list_text( $raw ) {
	$raw = is_string( $raw ) ? trim( $raw ) : '';
	if ( $raw === '' ) {
		return '—';
	}
	$t = strtotime( $raw );
	if ( ! $t ) {
		return $raw;
	}
	return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $t );
}

function pw_admin_posts_columns( $columns, $post_type ) {
	if ( 'pw_property' === $post_type ) {
		return pw_admin_insert_columns_after_key(
			$columns,
			[
				'pw_city'    => __( 'City', 'portico-webworks' ),
				'pw_state'   => __( 'State', 'portico-webworks' ),
				'pw_country' => __( 'Country', 'portico-webworks' ),
			],
			'title'
		);
	}

	$extra = [];

	if ( in_array( $post_type, pw_admin_post_types_with_property_column(), true ) ) {
		$extra['pw_property'] = __( 'Property', 'portico-webworks' );
	}

	return pw_admin_insert_columns_after_key( $columns, $extra, 'title' );
}

function pw_admin_render_post_list_column( $column, $post_id ) {
	switch ( $column ) {
		case 'pw_city':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_city', true ) );
			break;
		case 'pw_state':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_state', true ) );
			break;
		case 'pw_country':
			echo esc_html( (string) get_post_meta( $post_id, '_pw_country', true ) );
			break;
		case 'pw_property':
			pw_admin_render_property_cell( $post_id );
			break;
	}
}

function pw_admin_taxonomies_with_extra_columns() {
	return [
		'pw_property_type',
	];
}

function pw_admin_taxonomy_columns( $columns, $taxonomy ) {
	$insert = [ 'pw_term_id' => __( 'ID', 'portico-webworks' ) ];
	return pw_admin_insert_columns_after_key( $columns, $insert, 'name' );
}

function pw_admin_taxonomy_custom_column() {
	$a = func_get_args();
	if ( count( $a ) < 2 ) {
		return;
	}
	if ( count( $a ) >= 3 ) {
		if ( $a[0] === '' ) {
			$column  = $a[1];
			$term_id = (int) $a[2];
		} else {
			$column  = $a[0];
			$term_id = (int) $a[2];
		}
	} else {
		$column  = $a[0];
		$term_id = (int) $a[1];
	}
	if ( 'pw_term_id' === $column ) {
		echo $term_id > 0 ? esc_html( (string) $term_id ) : '—';
	}
}

function pw_register_admin_list_columns() {
	foreach ( pw_admin_list_column_post_types() as $pt ) {
		add_filter(
			"manage_{$pt}_posts_columns",
			static function ( $columns ) use ( $pt ) {
				return pw_admin_posts_columns( $columns, $pt );
			},
			20
		);
		add_action(
			"manage_{$pt}_posts_custom_column",
			static function ( $column, $post_id ) {
				pw_admin_render_post_list_column( $column, $post_id );
			},
			10,
			2
		);
	}

	foreach ( pw_admin_post_types_with_property_column() as $pt ) {
		add_filter(
			"manage_edit-{$pt}_sortable_columns",
			static function ( $sortable ) {
				$sortable['pw_property'] = 'pw_property';
				return $sortable;
			}
		);
	}

	foreach ( pw_admin_taxonomies_with_extra_columns() as $tax ) {
		add_filter(
			"manage_edit-{$tax}_columns",
			static function ( $columns ) use ( $tax ) {
				return pw_admin_taxonomy_columns( $columns, $tax );
			},
			20
		);
		add_action(
			"manage_{$tax}_custom_column",
			'pw_admin_taxonomy_custom_column',
			10,
			3
		);
	}
}

add_action( 'admin_init', 'pw_register_admin_list_columns' );

function pw_admin_sort_posts_by_property( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'pw_property' !== $query->get( 'orderby' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit' !== $screen->base || ! $screen->post_type ) {
		return;
	}
	if ( ! in_array( $screen->post_type, pw_admin_post_types_with_property_column(), true ) ) {
		return;
	}
	$query->set( 'meta_key', '_pw_property_id' );
	$query->set( 'orderby', 'meta_value_num' );
}

add_action( 'pre_get_posts', 'pw_admin_sort_posts_by_property' );
