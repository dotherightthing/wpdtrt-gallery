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

    $frontend = 'wpdtrt_gallery';
    $attach_to_footer = true;

    wp_enqueue_script( $frontend,
      WPDTRT_GALLERY_URL . 'js/wpdtrt-gallery.js',
      array('jquery'),
      WPDTRT_GALLERY_VERSION,
      $attach_to_footer
    );

    // panoramas
    wp_enqueue_script( 'wpdtrt_gallery_paver_debounce',
      WPDTRT_GALLERY_URL . 'vendor/bower_components/jquery-throttle-debounce/jquery.ba-throttle-debounce.min.js',
      array('jquery', $frontend),
      WPDTRT_GALLERY_VERSION,
      $attach_to_footer
    );

    // panoramas
    wp_enqueue_script( 'wpdtrt_gallery_paver',
      WPDTRT_GALLERY_URL . 'vendor/bower_components/paver/dist/js/jquery.paver.min.js',
      array('jquery', $frontend),
      WPDTRT_GALLERY_VERSION,
      $attach_to_footer
    );

    // thumbnail query params
    wp_enqueue_script( 'wpdtrt_gallery_uri',
      WPDTRT_GALLERY_URL . 'vendor/bower_components/urijs/src/URI.min.js',
      array('jquery', $frontend),
      WPDTRT_GALLERY_VERSION,
      $attach_to_footer
    );

    // inview lazy loading
    wp_enqueue_script( 'wpdtrt_gallery_waypoints',
      WPDTRT_GALLERY_URL . 'vendor/bower_components/waypoints/lib/jquery.waypoints.min.js',
      array('jquery', $frontend),
      WPDTRT_GALLERY_VERSION,
      $attach_to_footer
    );

    // inview lazy loading
    wp_enqueue_script( 'wpdtrt_gallery_waypoints_inview',
      WPDTRT_GALLERY_URL . 'vendor/bower_components/waypoints/lib/shortcuts/inview.min.js',
      array('jquery', $frontend),
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
