<?php
/**
 * Plugin uninstaller
 *
 * This magic file provides an alternative to register_uninstall_hook
 * and is automatically run when the users deletes the plugin.
 *
 * Note:
 * - Deactivation Hook:
 * - Flush Cache/Temp: Yes
 * - Flush Permalinks: Yes
 * - Remove Options from {$wpdb->prefix}_options: No
 * - Remove Tables from wpdb: No
 * - Uninstall Hook:
 * - Flush Cache/Temp: No
 * - Flush Permalinks: No
 * - Remove Options from {$wpdb->prefix}_options: Yes
 * - Remove Tables from wpdb: Yes
 *
 * See:
 * - <https://developer.wordpress.org/plugins/the-basics/best-practices/#file-organization>
 * - <https://developer.wordpress.org/plugins/the-basics/uninstall-methods/#method-2-uninstall-php>
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */

// if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

delete_option( 'wpdtrt_gallery' );

// for site options in Multisite.
delete_site_option( 'wpdtrt_gallery' );
