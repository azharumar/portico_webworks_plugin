<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\PhpOffice\PhpSpreadsheet\Spreadsheet' ) ) {
	add_action( 'pw_admin_notices', function() {
		echo '<div class="notice notice-warning"><p><strong>Portico Webworks:</strong> PhpSpreadsheet is not installed. Run <code>composer install</code> in the plugin directory to enable Import / Export.</p></div>';
	} );
	return;
}

// ---------------------------------------------------------------------------
// Admin tab registration
// ---------------------------------------------------------------------------

add_filter( 'pw_admin_tabs', function( $tabs ) {
	$tabs['import_export'] = 'Import / Export';
	return $tabs;
} );

add_action( 'pw_render_tab_import_export', 'pw_render_import_export_tab' );

function pw_render_import_export_tab() {
	$allowed_types = [
		'pw_property',
		'pw_room_type',
		'pw_restaurant',
		'pw_spa',
		'pw_meeting_room',
		'pw_amenity',
		'pw_policy',
		'pw_faq',
		'pw_feature',
		'pw_offer',
		'pw_nearby',
		'pw_experience',
		'pw_event',
	];

	$type_labels = [
		'pw_property'     => 'Properties',
		'pw_room_type'    => 'Room Types',
		'pw_restaurant'   => 'Restaurants',
		'pw_spa'          => 'Spas',
		'pw_meeting_room' => 'Meeting Rooms',
		'pw_amenity'      => 'Amenities',
		'pw_policy'       => 'Policies',
		'pw_faq'          => 'FAQs',
		'pw_feature'      => 'Features',
		'pw_offer'        => 'Offers',
		'pw_nearby'       => 'Nearby Locations',
		'pw_experience'   => 'Experiences',
		'pw_event'        => 'Events',
	];

	if ( isset( $_GET['pw_imported'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Import completed successfully.</p></div>';
	}

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">Export</div></div>';
	echo '<div class="pw-card-body">';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="pw_export" />';
	wp_nonce_field( 'pw_export' );
	echo '<table class="form-table"><tbody><tr>';
	echo '<th scope="row"><label for="pw-export-type">Post Type</label></th>';
	echo '<td><select id="pw-export-type" name="post_type">';
	foreach ( $allowed_types as $pt ) {
		$label = $type_labels[ $pt ] ?? $pt;
		echo '<option value="' . esc_attr( $pt ) . '">' . esc_html( $label ) . '</option>';
	}
	echo '</select></td>';
	echo '</tr></tbody></table>';
	submit_button( 'Download Excel' );
	echo '</form>';
	echo '</div></div>';

	echo '<div class="pw-card">';
	echo '<div class="pw-card-head"><div class="pw-card-title">Import</div></div>';
	echo '<div class="pw-card-body">';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data">';
	echo '<input type="hidden" name="action" value="pw_import" />';
	wp_nonce_field( 'pw_import' );
	echo '<table class="form-table"><tbody>';
	echo '<tr><th scope="row"><label for="pw-import-type">Post Type</label></th>';
	echo '<td><select id="pw-import-type" name="post_type">';
	foreach ( $allowed_types as $pt ) {
		$label = $type_labels[ $pt ] ?? $pt;
		echo '<option value="' . esc_attr( $pt ) . '">' . esc_html( $label ) . '</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th scope="row"><label for="pw-import-file">Excel File (.xlsx)</label></th>';
	echo '<td><input id="pw-import-file" type="file" name="import_file" accept=".xlsx" required /></td></tr>';
	echo '</tbody></table>';
	submit_button( 'Import' );
	echo '</form>';
	echo '</div></div>';
}

// ---------------------------------------------------------------------------
// Export handler
// ---------------------------------------------------------------------------

add_action( 'admin_post_pw_export', 'pw_handle_export' );

function pw_handle_export() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
	check_admin_referer( 'pw_export' );

	$post_type = sanitize_key( $_POST['post_type'] );
	$allowed   = [
		'pw_property', 'pw_room_type', 'pw_restaurant', 'pw_spa',
		'pw_meeting_room', 'pw_amenity', 'pw_policy', 'pw_faq', 'pw_feature',
		'pw_offer', 'pw_nearby', 'pw_experience', 'pw_event',
	];

	if ( ! in_array( $post_type, $allowed, true ) ) wp_die( 'Invalid post type' );

	$autoload = plugin_dir_path( PW_PLUGIN_FILE ) . 'vendor/autoload.php';
	if ( ! file_exists( $autoload ) ) wp_die( 'PhpSpreadsheet is not installed. Run: composer require phpoffice/phpspreadsheet' );
	require_once $autoload;

	$posts = get_posts( [
		'post_type'      => $post_type,
		'post_status'    => 'any',
		'posts_per_page' => -1,
	] );

	$headers        = [ 'ID', 'Title', 'Status' ];
	$meta_keys_seen = [];

	foreach ( $posts as $post ) {
		foreach ( get_post_meta( $post->ID ) as $key => $_ ) {
			if ( ! in_array( $key, $meta_keys_seen, true ) ) {
				$meta_keys_seen[] = $key;
				$headers[]        = $key;
			}
		}
	}

	$rows = [ $headers ];

	foreach ( $posts as $post ) {
		$row = [ $post->ID, $post->post_title, $post->post_status ];
		foreach ( array_slice( $headers, 3 ) as $key ) {
			$val   = get_post_meta( $post->ID, $key, true );
			$row[] = is_array( $val ) ? wp_json_encode( $val ) : $val;
		}
		$rows[] = $row;
	}

	$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
	$sheet       = $spreadsheet->getActiveSheet();
	$sheet->fromArray( $rows );

	$sheet->getStyle( '1:1' )->getFont()->setBold( true );

	foreach ( range( 'A', $sheet->getHighestColumn() ) as $col ) {
		$sheet->getColumnDimension( $col )->setAutoSize( true );
	}

	$filename = $post_type . '-export-' . gmdate( 'Y-m-d' ) . '.xlsx';

	header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Cache-Control: max-age=0' );

	( new \PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet ) )->save( 'php://output' );
	exit;
}

// ---------------------------------------------------------------------------
// Import handler
// ---------------------------------------------------------------------------

add_action( 'admin_post_pw_import', 'pw_handle_import' );

function pw_handle_import() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
	check_admin_referer( 'pw_import' );

	$post_type = sanitize_key( $_POST['post_type'] );
	$file      = $_FILES['import_file'] ?? null;

	$allowed = [
		'pw_property', 'pw_room_type', 'pw_restaurant', 'pw_spa',
		'pw_meeting_room', 'pw_amenity', 'pw_policy', 'pw_faq', 'pw_feature',
		'pw_offer', 'pw_nearby', 'pw_experience', 'pw_event',
	];

	if ( ! in_array( $post_type, $allowed, true ) ) wp_die( 'Invalid post type' );

	if ( ! $file || $file['error'] !== UPLOAD_ERR_OK ) wp_die( 'Upload failed' );

	$autoload = plugin_dir_path( PW_PLUGIN_FILE ) . 'vendor/autoload.php';
	if ( ! file_exists( $autoload ) ) wp_die( 'PhpSpreadsheet is not installed. Run: composer require phpoffice/phpspreadsheet' );
	require_once $autoload;

	$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load( $file['tmp_name'] );
	$rows        = $spreadsheet->getActiveSheet()->toArray();
	$headers     = array_shift( $rows );

	$id_index     = array_search( 'ID',     $headers );
	$title_index  = array_search( 'Title',  $headers );
	$status_index = array_search( 'Status', $headers );

	foreach ( $rows as $row ) {
		$post_id     = ! empty( $row[ $id_index ] ) ? (int) $row[ $id_index ] : 0;
		$post_title  = sanitize_text_field( $row[ $title_index ]  ?? '' );
		$post_status = sanitize_key(        $row[ $status_index ] ?? 'draft' );

		$post_data = [
			'post_type'   => $post_type,
			'post_title'  => $post_title,
			'post_status' => $post_status,
		];

		if ( $post_id && get_post( $post_id ) ) {
			$post_data['ID'] = $post_id;
			wp_update_post( $post_data );
		} else {
			$post_id = wp_insert_post( $post_data );
		}

		foreach ( $headers as $i => $key ) {
			if ( in_array( $key, [ 'ID', 'Title', 'Status' ], true ) ) continue;
			$value   = $row[ $i ] ?? '';
			$decoded = json_decode( $value, true );
			update_post_meta( $post_id, $key, $decoded !== null ? $decoded : sanitize_text_field( $value ) );
		}
	}

	wp_redirect( add_query_arg( 'pw_imported', '1', wp_get_referer() ) );
	exit;
}
