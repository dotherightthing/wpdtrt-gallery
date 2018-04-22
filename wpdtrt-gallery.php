<?php
/**
 * Plugin Name:  DTRT Gallery
 * Plugin URI:   https://github.com/dotherightthing/wpdtrt-gallery
 * Description:  Gallery viewer which supports images, panoramas, maps, SoundCloud and Vimeo.
 * Version:      1.7.2
 * Author:       Dan Smith
 * Author URI:   https://profiles.wordpress.org/dotherightthingnz
 * License:      GPLv2 or later
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  wpdtrt-gallery
 * Domain Path:  /languages
 */

require_once plugin_dir_path( __FILE__ ) . "vendor/autoload.php";

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
  * Determine the correct path to the autoloader
  * @see https://github.com/dotherightthing/wpdtrt-plugin/issues/51
  */
if( ! defined( 'WPDTRT_PLUGIN_CHILD' ) ) {
  define( 'WPDTRT_PLUGIN_CHILD', true );
}

if( ! defined( 'WPDTRT_GALLERY_VERSION' ) ) {
/**
 * Plugin version.
 *
 * WP provides get_plugin_data(), but it only works within WP Admin,
 * so we define a constant instead.
 *
 * @example $plugin_data = get_plugin_data( __FILE__ ); $plugin_version = $plugin_data['Version'];
 * @link https://wordpress.stackexchange.com/questions/18268/i-want-to-get-a-plugin-version-number-dynamically
 *
 * @version   0.0.1
 * @since     0.7.0
 */
  define( 'WPDTRT_GALLERY_VERSION', '1.7.2' );
}

if( ! defined( 'WPDTRT_GALLERY_PATH' ) ) {
/**
 * Plugin directory filesystem path.
 *
 * @param string $file
 * @return The filesystem directory path (with trailing slash)
 *
 * @link https://developer.wordpress.org/reference/functions/plugin_dir_path/
 * @link https://developer.wordpress.org/plugins/the-basics/best-practices/#prefix-everything
 *
 * @version   0.0.1
 * @since     0.7.0
 */
  define( 'WPDTRT_GALLERY_PATH', plugin_dir_path( __FILE__ ) );
}

if( ! defined( 'WPDTRT_GALLERY_URL' ) ) {
/**
 * Plugin directory URL path.
 *
 * @param string $file
 * @return The URL (with trailing slash)
 *
 * @link https://codex.wordpress.org/Function_Reference/plugin_dir_url
 * @link https://developer.wordpress.org/plugins/the-basics/best-practices/#prefix-everything
 *
 * @version   0.0.1
 * @since     0.7.0
 */
  define( 'WPDTRT_GALLERY_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Include plugin logic
 *
 * @version   0.0.1
 * @since     0.7.0
 */

  // base class
  // redundant, but includes the composer-generated autoload file if not already included
  require_once(WPDTRT_GALLERY_PATH . 'vendor/dotherightthing/wpdtrt-plugin/index.php');

  // classes without composer.json files are loaded via Bower
  //require_once(WPDTRT_GALLERY_PATH . 'vendor/name/file.php');

  // sub classes
  require_once(WPDTRT_GALLERY_PATH . 'src/class-wpdtrt-gallery-plugin.php');

  // legacy helpers
  require_once(WPDTRT_GALLERY_PATH . 'src/legacy/attachment-field-heading.php');
  require_once(WPDTRT_GALLERY_PATH . 'src/legacy/attachment-field-source.php');
  require_once(WPDTRT_GALLERY_PATH . 'src/legacy/attachment-field-default.php');
  require_once(WPDTRT_GALLERY_PATH . 'src/legacy/attachment-field-panorama.php');
  require_once(WPDTRT_GALLERY_PATH . 'src/legacy/attachment-field-location.php');
  require_once(WPDTRT_GALLERY_PATH . 'src/legacy/attachment-field-position-y.php');
  require_once(WPDTRT_GALLERY_PATH . 'src/legacy/attachment-field-ridewithgps.php');
  require_once(WPDTRT_GALLERY_PATH . 'src/legacy/attachment-field-soundcloud.php');
  require_once(WPDTRT_GALLERY_PATH . 'src/legacy/attachment-field-vimeo.php');

  // log & trace helpers
  $debug = new DoTheRightThing\WPDebug\Debug;

  /**
   * Plugin initialisaton
   *
   * We call init before widget_init so that the plugin object properties are available to it.
   * If widget_init is not working when called via init with priority 1, try changing the priority of init to 0.
   * init: Typically used by plugins to initialize. The current user is already authenticated by this time.
   * └─ widgets_init: Used to register sidebars. Fired at 'init' priority 1 (and so before 'init' actions with priority ≥ 1!)
   *
   * @see https://wp-mix.com/wordpress-widget_init-not-working/
   * @see https://codex.wordpress.org/Plugin_API/Action_Reference
   * @todo Add a constructor function to WPDTRT_Gallery_Plugin, to explain the options array
   */
  function wpdtrt_gallery_init() {
    // pass object reference between classes via global
    // because the object does not exist until the WordPress init action has fired
    global $wpdtrt_gallery_plugin;

    /**
     * Admin settings
     * For array syntax, please view the field documentation:
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-checkbox.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-number.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-password.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-select.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-text.php
     */
    $plugin_options = array();

    /**
     * All options available to Widgets and Shortcodes
     * For array syntax, please view the field documentation:
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-checkbox.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-number.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-password.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-select.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-text.php
     */
    $instance_options = array();

    $wpdtrt_gallery_plugin = new WPDTRT_Gallery_Plugin(
      array(
        'url' => WPDTRT_GALLERY_URL,
        'prefix' => 'wpdtrt_gallery',
        'slug' => 'wpdtrt-gallery',
        'menu_title' => __('Gallery', 'wpdtrt-gallery'),
        'developer_prefix' => '',
        'path' => WPDTRT_GALLERY_PATH,
        'messages' => array(
          'loading' => __('Loading latest data...', 'wpdtrt-gallery'),
          'success' => __('settings successfully updated', 'wpdtrt-gallery'),
          'insufficient_permissions' => __('Sorry, you do not have sufficient permissions to access this page.', 'wpdtrt-gallery'),
          'options_form_title' => __('General Settings', 'wpdtrt-gallery'),
          'options_form_description' => __('Please enter your preferences.', 'wpdtrt-gallery'),
          'no_options_form_description' => __('There aren\'t currently any options.', 'wpdtrt-gallery'),
          'options_form_submit' => __('Save Changes', 'wpdtrt-gallery'),
          'noscript_warning' => __('Please enable JavaScript', 'wpdtrt-gallery'),
        ),
        'plugin_options' => $plugin_options,
        'instance_options' => $instance_options,
        'version' => WPDTRT_GALLERY_VERSION,
        'plugin_dependencies' => array(
          // Dependency: Gallery viewer is initialised as sections are scrolled into view
          array(
            'name'          => 'DTRT Content Sections',
            'slug'          => 'wpdtrt-contentsections',
            'source'        => 'https://github.com/dotherightthing/wpdtrt-contentsections/releases/download/0.0.1/release.zip',
            'version'       => '0.0.1',
            'external_url'  => 'https://github.com/dotherightthing/wpdtrt-contentsections',
            'required'      => true,
          ),
        ),
        'demo_shortcode_params' => null
      )
    );
  }

  add_action( 'init', 'wpdtrt_gallery_init', 0 );

  /**
   * Register Shortcode
   *
   * @todo Add centigrade as a shortcode option (#1)
   * @todo Add units as a shortcode option (#2)
   */
  function wpdtrt_gallery_shortcode_heading_init() {

    global $wpdtrt_gallery_plugin;

    $wpdtrt_gallery_shortcode_heading = new DoTheRightThing\WPPlugin\Shortcode(
      array(
        'name' => 'wpdtrt_gallery_shortcode_heading',
        'plugin' => $wpdtrt_gallery_plugin,
        'template' => 'heading',
        'selected_instance_options' => array()
      )
    );
  }

  add_action( 'init', 'wpdtrt_gallery_shortcode_heading_init', 100 );

  /**
   * Register functions to be run when the plugin is activated.
   *
   * @see https://codex.wordpress.org/Function_Reference/register_activation_hook
   *
   * @version   0.0.1
   * @since     0.7.0
   */
  function wpdtrt_gallery_activate() {
    //wpdtrt_gallery_rewrite_rules();
    flush_rewrite_rules();
  }

  register_activation_hook(__FILE__, 'wpdtrt_gallery_activate');

  /**
   * Register functions to be run when the plugin is deactivated.
   *
   * (WordPress 2.0+)
   *
   * @see https://codex.wordpress.org/Function_Reference/register_deactivation_hook
   *
   * @version   0.0.1
   * @since     0.7.0
   */
  function wpdtrt_gallery_deactivate() {
    flush_rewrite_rules();
  }

  register_deactivation_hook(__FILE__, 'wpdtrt_gallery_deactivate');

?>
