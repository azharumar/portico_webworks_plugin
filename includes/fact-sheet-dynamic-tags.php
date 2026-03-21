<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pw_fact_sheet_gb_tag_output( $html, $options, $instance ) {
	if ( class_exists( 'GenerateBlocks_Dynamic_Tag_Callbacks' ) ) {
		return GenerateBlocks_Dynamic_Tag_Callbacks::output( $html, $options, $instance );
	}
	return $html;
}

function pw_fact_sheet_register_generateblocks_dynamic_tags() {
	if ( ! class_exists( 'GenerateBlocks_Register_Dynamic_Tag' ) ) {
		return;
	}

	$defs = array(
		array(
			'title' => 'Portico — Fact sheet error',
			'tag'   => 'pw_fact_error',
		),
		array(
			'title' => 'Portico — Fact sheet title',
			'tag'   => 'pw_fact_title',
		),
		array(
			'title' => 'Portico — Fact sheet lead',
			'tag'   => 'pw_fact_lead',
		),
		array(
			'title' => 'Portico — Fact sheet overview (excerpt & description)',
			'tag'   => 'pw_fact_header',
		),
		array(
			'title' => 'Portico — Fact sheet property details',
			'tag'   => 'pw_fact_property',
		),
		array(
			'title' => 'Portico — Fact sheet linked content',
			'tag'   => 'pw_fact_linked',
		),
	);

	foreach ( $defs as $def ) {
		try {
			new GenerateBlocks_Register_Dynamic_Tag(
				array(
					'title'    => $def['title'],
					'tag'      => $def['tag'],
					'type'     => 'portico',
					'supports' => array( 'source' ),
					'return'   => static function ( $options, $block, $instance ) use ( $def ) {
						if ( is_admin() || wp_is_json_request() ) {
							return pw_fact_sheet_gb_tag_output( '{{' . $def['tag'] . '}}', $options, $instance );
						}
						return pw_fact_sheet_gb_tag_output( pw_fact_sheet_render_tag( $def['tag'] ), $options, $instance );
					},
				)
			);
		} catch ( Throwable $e ) {
			continue;
		}
	}
}

add_action( 'init', 'pw_fact_sheet_register_generateblocks_dynamic_tags', 25 );
