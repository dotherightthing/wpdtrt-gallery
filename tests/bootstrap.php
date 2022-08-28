<?php
/**
 * File: tests/bootstrap.php
 *
 * PHPUnit bootstrap file.
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */

$_tests_dir   = getenv( 'WP_TESTS_DIR' );

// https://docs.github.com/en/actions/learn-github-actions/environment-variables#default-environment-variables.
$_ci          = getenv( 'CI' );
$_ci_temp_dir = getenv( 'RUNNER_TEMP' );

if ( ! $_tests_dir ) {
	if ( isset( $ci, $_ci_temp_dir ) ) {
		$_env_temp_dir = $_ci_temp_dir;
	} else {
		$_env_temp_dir = sys_get_temp_dir();
	}

	$_tests_dir = rtrim( $_env_temp_dir, '/\\' ) . '/tmp/wordpress-tests-lib';
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
