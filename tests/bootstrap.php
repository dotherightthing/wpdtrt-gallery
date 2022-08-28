<?php
/**
 * File: tests/bootstrap.php
 *
 * PHPUnit bootstrap file.
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

// TODO sys_get_temp_dir() returns default of '/tmp' on Github Actions CI.
if ( ! $_tests_dir ) {
	// if the default value is returned.
	if ( sys_get_temp_dir() === '/tmp' ) {
		throw new Exception( 'sys_get_temp_dir returned /tmp. RUNNER_TEMP = ' . getenv( 'RUNNER_TEMP' ) );
	}

	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/tmp/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	throw new Exception( "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// The PHPUnit Polyfills library is a requirement for running the WP test suite.
require_once dirname( dirname( __FILE__ ) ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

/**
 * Manually load the plugin being tested, and any dependencies.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/wpdtrt-gallery.php'; // Access static methods of plugin class.
	$composer_json                    = dirname( dirname( __FILE__ ) ) . '/composer.json';
	$composer_dependencies            = WPDTRT_Gallery_Plugin::get_wp_composer_dependencies( $composer_json );
	$composer_dependencies_to_require = WPDTRT_Gallery_Plugin::get_wp_composer_dependencies_wpunit( $composer_dependencies );
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
