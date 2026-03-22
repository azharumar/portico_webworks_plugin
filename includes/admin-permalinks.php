<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'pw_admin_tabs',
	static function ( $tabs ) {
		$tabs['permalinks'] = 'Permalinks';
		return $tabs;
	},
	15
);

add_action( 'admin_post_pw_save_permalinks', 'pw_handle_save_permalinks' );

function pw_handle_save_permalinks() {
	if (
		! isset( $_POST['pw_permalinks_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pw_permalinks_nonce'] ) ), 'pw_save_permalinks' ) ||
		! current_user_can( 'manage_options' )
	) {
		wp_die( 'Unauthorised' );
	}

	$existing = pw_get_merged_pw_settings();
	$allowed  = pw_permalink_base_source_allowed();

	$src = isset( $_POST['pw_permalink_base_source'] ) ? sanitize_key( wp_unslash( $_POST['pw_permalink_base_source'] ) ) : 'fixed';
	if ( ! isset( $allowed[ $src ] ) ) {
		$src = 'fixed';
	}

	$slug_src = isset( $_POST['pw_permalink_slug_source'] ) && $_POST['pw_permalink_slug_source'] === '_pw_url_slug'
		? '_pw_url_slug'
		: 'post_name';

	$fixed = pw_sanitize_property_base( wp_unslash( $_POST['pw_permalink_base_fixed'] ?? '' ) );

	$subpaths = [];
	if ( isset( $_POST['pw_subpath_segment'] ) && isset( $_POST['pw_subpath_page'] ) ) {
		$segs  = wp_unslash( $_POST['pw_subpath_segment'] );
		$pages = wp_unslash( $_POST['pw_subpath_page'] );
		if ( is_array( $segs ) && is_array( $pages ) ) {
			$n = max( count( $segs ), count( $pages ) );
			for ( $i = 0; $i < $n; $i++ ) {
				$seg = isset( $segs[ $i ] ) ? sanitize_title( (string) $segs[ $i ] ) : '';
				$pid = isset( $pages[ $i ] ) ? (int) $pages[ $i ] : 0;
				if ( $seg === '' || $pid <= 0 ) {
					continue;
				}
				if ( get_post_type( $pid ) !== 'page' || get_post_status( $pid ) !== 'publish' ) {
					continue;
				}
				$slug = get_post_field( 'post_name', $pid );
				if ( is_string( $slug ) && $slug !== '' ) {
					$subpaths[ $seg ] = $slug;
				}
			}
		}
	}

	$settings                             = $existing;
	$settings['pw_permalink_base_source'] = $src;
	$settings['pw_permalink_base_fixed']  = $fixed;
	$settings['pw_property_base']         = $fixed;
	$settings['pw_permalink_slug_source'] = $slug_src;
	$settings['pw_permalink_subpaths']    = $subpaths;

	update_option( 'pw_settings', $settings );

	$flush = (
		$src !== $existing['pw_permalink_base_source'] ||
		$fixed !== $existing['pw_permalink_base_fixed'] ||
		$slug_src !== $existing['pw_permalink_slug_source'] ||
		wp_json_encode( $subpaths ) !== wp_json_encode( $existing['pw_permalink_subpaths'] ?? [] )
	);
	if ( $flush ) {
		flush_rewrite_rules();
	}

	wp_safe_redirect(
		add_query_arg(
			'pw_permalinks_saved',
			'1',
			pw_admin_permalinks_url()
		)
	);
	exit;
}

add_action( 'pw_render_tab_permalinks', 'pw_render_permalinks_tab' );

function pw_render_permalinks_tab() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['pw_permalinks_saved'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Permalink settings saved.', 'portico-webworks' ) . '</p></div>';
	}

	$src      = pw_get_permalink_base_source();
	$slug_src = pw_get_permalink_slug_source();
	$fixed    = pw_get_fixed_permalink_base();
	$subpaths = pw_get_permalink_subpaths();

	$permalink_reading = admin_url( 'options-permalink.php' );
	$dyn_warning       = $src !== 'fixed';

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">' . esc_html__( 'Permalinks', 'portico-webworks' ) . '</div></div>';
	echo '<div class="pw-card-body">';

	echo '<p class="description">' . esc_html__( 'Path-based property URLs (no query strings). Site-wide permalink mode is still set in WordPress:', 'portico-webworks' ) . ' ';
	echo '<a href="' . esc_url( $permalink_reading ) . '">' . esc_html__( 'Settings → Permalinks', 'portico-webworks' ) . '</a>.</p>';

	if ( $dyn_warning ) {
		echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'Dynamic base segments use property fields (city, country, etc.). Leave those fields filled for every published property or URLs may be wrong or empty.', 'portico-webworks' ) . '</p></div>';
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="pw_save_permalinks" />';
	wp_nonce_field( 'pw_save_permalinks', 'pw_permalinks_nonce' );

	echo '<table class="form-table" role="presentation"><tbody>';

	echo '<tr><th scope="row">' . esc_html__( 'Base segment', 'portico-webworks' ) . '</th><td>';
	echo '<fieldset>';
	$opts = [
		'fixed'            => __( 'Fixed prefix (e.g. properties, hotels)', 'portico-webworks' ),
		'_pw_city'         => __( 'City (address field)', 'portico-webworks' ),
		'_pw_state'        => __( 'State / province', 'portico-webworks' ),
		'_pw_country'      => __( 'Country (full name)', 'portico-webworks' ),
		'_pw_country_code' => __( 'Country code (ISO)', 'portico-webworks' ),
		'pw_property_type' => __( 'Property type (first term)', 'portico-webworks' ),
	];
	foreach ( $opts as $val => $label ) {
		echo '<label style="display:block;margin:0.25em 0;"><input type="radio" name="pw_permalink_base_source" value="' . esc_attr( $val ) . '" ' . checked( $src, $val, false ) . ' /> ' . esc_html( $label ) . '</label>';
	}
	echo '</fieldset>';
	echo '<p class="description">' . esc_html__( 'Example: City → /bengaluru/your-hotel-slug/. Fixed → /properties/your-hotel-slug/.', 'portico-webworks' ) . '</p>';
	echo '</td></tr>';

	echo '<tr class="pw-permalink-fixed-row"><th scope="row"><label for="pw_permalink_base_fixed">' . esc_html__( 'Fixed base value', 'portico-webworks' ) . '</label></th><td>';
	echo '<input type="text" class="regular-text" id="pw_permalink_base_fixed" name="pw_permalink_base_fixed" value="' . esc_attr( $fixed ) . '" placeholder="properties" />';
	echo '<p class="description">' . esc_html__( 'Used only when “Fixed prefix” is selected. Sanitized like a URL slug.', 'portico-webworks' ) . '</p>';
	echo '</td></tr>';

	echo '<tr><th scope="row">' . esc_html__( 'Property slug source', 'portico-webworks' ) . '</th><td>';
	echo '<fieldset>';
	echo '<label><input type="radio" name="pw_permalink_slug_source" value="post_name" ' . checked( $slug_src, 'post_name', false ) . ' /> ' . esc_html__( 'Post slug (WordPress default)', 'portico-webworks' ) . '</label><br />';
	echo '<label><input type="radio" name="pw_permalink_slug_source" value="_pw_url_slug" ' . checked( $slug_src, '_pw_url_slug', false ) . ' /> ' . esc_html__( 'Custom URL slug (property profile field)', 'portico-webworks' ) . '</label>';
	echo '</fieldset>';
	echo '<p class="description">' . esc_html__( 'Custom slug falls back to post slug when empty. Must be unique per published property.', 'portico-webworks' ) . '</p>';
	echo '</td></tr>';

	echo '</tbody></table>';

	echo '<h2 class="title">' . esc_html__( 'Sub-path mappings', 'portico-webworks' ) . '</h2>';
	echo '<p class="description">' . esc_html__( 'Maps a path segment after the property slug to a published WordPress page, e.g. fact-sheet → Fact Sheet page.', 'portico-webworks' ) . '</p>';

	$pairs = [];
	foreach ( $subpaths as $sk => $pslug ) {
		$pairs[] = [ 'seg' => $sk, 'slug' => $pslug ];
	}
	if ( $pairs === [] ) {
		$pairs[] = [ 'seg' => '', 'slug' => '' ];
	}
	echo '<table class="widefat striped" style="max-width:640px;"><thead><tr>';
	echo '<th>' . esc_html__( 'Path segment', 'portico-webworks' ) . '</th>';
	echo '<th>' . esc_html__( 'Page', 'portico-webworks' ) . '</th>';
	echo '</tr></thead><tbody id="pw-subpath-rows">';
	foreach ( $pairs as $pair ) {
		$sk      = $pair['seg'];
		$pv      = $pair['slug'];
		$page_id = 0;
		if ( $pv !== '' ) {
			$pobj = get_page_by_path( $pv, OBJECT, 'page' );
			if ( $pobj instanceof WP_Post ) {
				$page_id = (int) $pobj->ID;
			}
			if ( $page_id <= 0 ) {
				$found = get_posts(
					[
						'post_type'      => 'page',
						'post_status'    => 'publish',
						'name'           => $pv,
						'posts_per_page' => 1,
						'fields'         => 'ids',
					]
				);
				$page_id = ! empty( $found ) ? (int) $found[0] : 0;
			}
		}
		echo '<tr class="pw-subpath-row">';
		echo '<td><input type="text" class="regular-text" name="pw_subpath_segment[]" value="' . esc_attr( $sk ) . '" placeholder="fact-sheet" /></td>';
		echo '<td>';
		wp_dropdown_pages(
			[
				'name'              => 'pw_subpath_page[]',
				'show_option_none'  => esc_html__( '— Select page —', 'portico-webworks' ),
				'option_none_value' => '0',
				'selected'          => $page_id,
			]
		);
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
	echo '<p><button type="button" class="button" id="pw-add-subpath-row">' . esc_html__( 'Add row', 'portico-webworks' ) . '</button></p>';

	$example = pw_permalink_example_url();
	if ( $example !== '' ) {
		echo '<h2 class="title">' . esc_html__( 'Example URL', 'portico-webworks' ) . '</h2>';
		echo '<p><code>' . esc_html( $example ) . '</code></p>';
	} else {
		echo '<p class="description">' . esc_html__( 'Publish at least one property to see a live example URL.', 'portico-webworks' ) . '</p>';
	}

	submit_button( __( 'Save Permalinks', 'portico-webworks' ) );
	echo '</form>';

	echo '</div></div>';

	pw_print_permalinks_tab_script();
}

/**
 * @return string Full URL or empty.
 */
function pw_permalink_example_url() {
	$props = get_posts(
		[
			'post_type'      => 'pw_property',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'fields'         => 'ids',
		]
	);
	if ( empty( $props ) ) {
		return '';
	}
	$pid = (int) $props[0];
	$url = pw_get_property_url( $pid );
	$sub = pw_get_permalink_subpaths();
	if ( $url !== '' && ! empty( $sub ) ) {
		$first = array_key_first( $sub );
		if ( is_string( $first ) && $first !== '' ) {
			$url = trailingslashit( $url ) . $first;
		}
	}
	return $url;
}

function pw_print_permalinks_tab_script() {
	?>
	<script>
	(function(){
		var btn = document.getElementById('pw-add-subpath-row');
		var tbody = document.getElementById('pw-subpath-rows');
		if (!btn || !tbody) return;
		btn.addEventListener('click', function(){
			var last = tbody.querySelector('tr.pw-subpath-row:last-of-type');
			if (last) {
				var clone = last.cloneNode(true);
				clone.querySelectorAll('input[type="text"]').forEach(function(i){ i.value = ''; });
				var sel = clone.querySelector('select');
				if (sel) sel.selectedIndex = 0;
				tbody.appendChild(clone);
				return;
			}
		});
		var radios = document.querySelectorAll('input[name="pw_permalink_base_source"]');
		var fixedRow = document.querySelector('.pw-permalink-fixed-row');
		function toggleFixedRow(){
			var v = document.querySelector('input[name="pw_permalink_base_source"]:checked');
			if (fixedRow) fixedRow.style.display = (v && v.value === 'fixed') ? '' : 'none';
		}
		radios.forEach(function(r){ r.addEventListener('change', toggleFixedRow); });
		toggleFixedRow();
	})();
	</script>
	<?php
}
