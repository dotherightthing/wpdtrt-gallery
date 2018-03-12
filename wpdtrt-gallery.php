<?php
/*
Plugin Name:  DTRT Gallery
Plugin URI:   https://github.com/dotherightthing/wpdtrt-gallery
Description:  Gallery viewer which supports images, panoramas, maps, SoundCloud and Vimeo
Version:      1.3.2
Author:       Dan Smith
Author URI:   https://profiles.wordpress.org/dotherightthingnz
License:      GPLv2 or later
License URI:  http://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wpdtrt-gallery
Domain Path:  /languages
*/

/**
 * Constants
 * WordPress makes use of the following constants when determining the path to the content and plugin directories.
 * These should not be used directly by plugins or themes, but are listed here for completeness.
 * WP_CONTENT_DIR  // no trailing slash, full paths only
 * WP_CONTENT_URL  // full url
 * WP_PLUGIN_DIR  // full path, no trailing slash
 * WP_PLUGIN_URL  // full url, no trailing slash
 *
 * WordPress provides several functions for easily determining where a given file or directory lives.
 * Always use these functions in your plugins instead of hard-coding references to the wp-content directory
 * or using the WordPress internal constants.
 * plugins_url()
 * plugin_dir_url()
 * plugin_dir_path()
 * plugin_basename()
 *
 * @link https://codex.wordpress.org/Determining_Plugin_and_Content_Directories#Constants
 * @link https://codex.wordpress.org/Determining_Plugin_and_Content_Directories#Plugins
 */

/**
 * Plugin version
 * WP provides get_plugin_data(), but it only works within WP Admin,
 * so we define a constant instead.
 * @example $plugin_data = get_plugin_data( __FILE__ ); $plugin_version = $plugin_data['Version'];
 * @link https://wordpress.stackexchange.com/questions/18268/i-want-to-get-a-plugin-version-number-dynamically
 */
if( ! defined( 'WPDTRT_GALLERY_VERSION' ) ) {
  define( 'WPDTRT_GALLERY_VERSION', '1.3.2' );
}

/**
 * plugin_dir_path
 * @param string $file
 * @return The filesystem directory path (with trailing slash)
 * @link https://developer.wordpress.org/reference/functions/plugin_dir_path/
 * @link https://developer.wordpress.org/plugins/the-basics/best-practices/#prefix-everything
 */
if( ! defined( 'WPDTRT_GALLERY_PATH' ) ) {
  define( 'WPDTRT_GALLERY_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * The version information is only available within WP Admin
 * @param string $file
 * @return The URL (with trailing slash)
 * @link https://codex.wordpress.org/Function_Reference/plugin_dir_url
 * @link https://developer.wordpress.org/plugins/the-basics/best-practices/#prefix-everything
 */
if( ! defined( 'WPDTRT_GALLERY_URL' ) ) {
  define( 'WPDTRT_GALLERY_URL', plugin_dir_url( __FILE__ ) );
}


/**
 * Store all of our plugin options in an array
 * So that we only use have to consume one row in the WP Options table
 * WordPress automatically serializes this (into a string)
 * because MySQL does not support arrays as a data type
 * @example update_option('wpdtrt_gallery', $wpdtrt_gallery_options);
 * @example $wpdtrt_gallery_options = get_option('wpdtrt_gallery');
 */
  $wpdtrt_gallery_options = array();

/**
 * Include plugin logic
 */

  //require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-api.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-css.php');
  //require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-html.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-js.php');
  //require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-options.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-shortcode.php');
  //require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-widget.php');

  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-attachment-field-heading.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-attachment-field-source.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-attachment-field-default.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-attachment-field-panorama.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-attachment-field-location.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-attachment-field-position-y.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-attachment-field-ridewithgps.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-attachment-field-soundcloud.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-attachment-field-vimeo.php');

  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-image-sizes.php');
  require_once(WPDTRT_GALLERY_PATH . 'app/wpdtrt-gallery-thumbnail.php');

  require_once(WPDTRT_GALLERY_PATH . 'config/tgm-plugin-activation.php');

?>
