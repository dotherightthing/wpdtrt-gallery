<?php
/**
 * Plugin uninstaller
 *
 * This file contains PHP.
 * This magic file provides an alternative to register_uninstall_hook
 * and is automatically run when the users deletes the plugin.
 *
 * Deactivation Hook:
 * Flush Cache/Temp: Yes
 * Flush Permalinks: Yes
 * Remove Options from {$wpdb->prefix}_options: No
 * Remove Tables from wpdb: No
 *
 * Uninstall Hook:
 * Flush Cache/Temp: No
 * Flush Permalinks: No
 * Remove Options from {$wpdb->prefix}_options: Yes
 * Remove Tables from wpdb: Yes
 *
 * @link https://developer.wordpress.org/plugins/the-basics/best-practices/#file-organization
 * @link https://developer.wordpress.org/plugins/the-basics/uninstall-methods/#method-2-uninstall-php
 *
 * @package     WPDTRT_Gallery
 * @version     0.0.1
 * @since       0.7.0
 */

// if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

delete_option( 'wpdtrt_gallery' );

// for site options in Multisite.
delete_site_option( 'wpdtrt_gallery' );
