<?php
/**
 * Sample data pack entry (loaded from extracted ZIP). Defines PW_SAMPLE_PACK_DIR then loads installers.
 *
 * @package Portico_Webworks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PW_SAMPLE_PACK_DIR' ) ) {
	define( 'PW_SAMPLE_PACK_DIR', __DIR__ );
}

require_once PW_SAMPLE_PACK_DIR . '/sample-data-multi-install.php';
