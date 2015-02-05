<?php

/**
 * Bootstrap the plugin unit testing environment.
 *
 * @package EDD_SL_Free\Tests
 * @since 1.0.0
 */

if( ! getenv( 'WP_TESTS_DIR' ) ) {
	exit( 'WP_TESTS_DIR is not set.' . PHP_EOL );
}

$test_root = getenv( 'WP_TESTS_DIR' );

/** WordPress PHPUnit test suite functions. */
require( $test_root . '/includes/functions.php' );

/**
 * Load the plugin during the tests.
 *
 * @since 1.0.0
 */
function edd_sl_free_tests_load_plugin() {

	require( WP_PLUGIN_DIR . '/easy-digital-downloads/easy-digital-downloads.php' );
	require( WP_PLUGIN_DIR . '/edd-software-licensing/edd-software-licenses.php' );
	require( dirname( __FILE__ ) . '/../../../src/edd-sl-free.php' );
}
tests_add_filter( 'muplugins_loaded', 'edd_sl_free_tests_load_plugin' );

/** WordPress PHPUnit tests bootstrap. */
require( $test_root . '/includes/bootstrap.php' );

// EOF
