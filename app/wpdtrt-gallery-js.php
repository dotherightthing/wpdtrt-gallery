<?php
/**
 * JS imports
 *
 * This file contains PHP.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-gallery
 * @see         https://codex.wordpress.org/AJAX_in_Plugins
 * @since       0.1.0
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

if ( !function_exists( 'wpdtrt_gallery_js' ) ) {

  /**
   * Attach JS for front-end widgets and shortcodes
   *    Generate a configuration object which the JavaScript can access.
   *    When an Ajax command is submitted, pass it to our function via the Admin Ajax page.
   *
   * @since       0.1.0
   * @see         https://codex.wordpress.org/AJAX_in_Plugins
   * @see         https://codex.wordpress.org/Function_Reference/wp_localize_script
   */
  function wpdtrt_gallery_js() {

    $attach_to_footer = true;

    /**
     * Registering scripts is technically not necessary, but highly recommended nonetheless.
     *
     * Scripts that have been pre-registered using wp_register_script()
     * do not need to be manually enqueued using wp_enqueue_script()
     * if they are listed as a dependency of another script that is enqueued.
     * WordPress will automatically include the registered script
     * before it includes the enqueued script that lists the registered script’s handle as a dependency.
     *
     * @see https://developer.wordpress.org/reference/functions/wp_register_script/#more-information
     */

    // panoramas
    wp_register_script( 'jquery_ba_throttle_debounce',
      WPDTRT_GALLERY_URL . 'vendor/bower_components/jquery-throttle-debounce/jquery.ba-throttle-debounce.min.js',
      array(
        'jquery'
      ),
      '1.1',
      $attach_to_footer
    );

    // panoramas
    wp_register_script( 'jquery_paver',
      WPDTRT_GALLERY_URL . 'vendor/bower_components/paver/dist/js/jquery.paver.min.js',
      array(
        'jquery_ba_throttle_debounce',
      ),
      '1.3.3',
      $attach_to_footer
    );

    // thumbnail query params
    wp_register_script( 'uri',
      WPDTRT_GALLERY_URL . 'vendor/bower_components/urijs/src/URI.min.js',
      array(),
      '1.18.12',
      $attach_to_footer
    );

    // inview lazy loading
    wp_register_script( 'jquery_waypoints',
      WPDTRT_GALLERY_URL . 'vendor/bower_components/waypoints/lib/jquery.waypoints.min.js',
      array(
        'jquery',
      ),
      '4.0.0',
      $attach_to_footer
    );

    // inview lazy loading
    wp_register_script( 'waypoints_inview',
      WPDTRT_GALLERY_URL . 'vendor/bower_components/waypoints/lib/shortcuts/inview.min.js',
      array(
        'jquery_waypoints'
      ),
      '4.0.0',
      $attach_to_footer
    );

    // init
    wp_enqueue_script( 'wpdtrt_gallery',
      WPDTRT_GALLERY_URL . 'js/wpdtrt-gallery.js',
      array(
        // registered dependencies:
        'jquery',
        'jquery_paver',
        'uri',
        'waypoints_inview',
      ),
      WPDTRT_GALLERY_VERSION,
      $attach_to_footer
    );

    /*
    wp_localize_script( 'wpdtrt_gallery_js',
      'wpdtrt_gallery_config',
      array(
        'ajax_url' => admin_url( 'admin-ajax.php' ) // wpdtrt_gallery_config.ajax_url
      )
    );
    */

  }

  add_action( 'wp_enqueue_scripts', 'wpdtrt_gallery_js' );

}

?>
