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

if ( !function_exists( 'wpdtrt_gallery_frontend_js' ) ) {

  /**
   * Attach JS for front-end widgets and shortcodes
   *    Generate a configuration object which the JavaScript can access.
   *    When an Ajax command is submitted, pass it to our function via the Admin Ajax page.
   *
   * @since       0.1.0
   * @see         https://codex.wordpress.org/AJAX_in_Plugins
   * @see         https://codex.wordpress.org/Function_Reference/wp_localize_script
   */
  function wpdtrt_gallery_frontend_js() {

    wp_enqueue_script( 'wpdtrt_gallery_frontend_js',
      WPDTRT_GALLERY_URL . 'js/wpdtrt-gallery.js',
      array('jquery'),
      WPDTRT_GALLERY_VERSION,
      true
    );

    wp_localize_script( 'wpdtrt_gallery_frontend_js',
      'wpdtrt_gallery_config',
      array(
        'ajax_url' => admin_url( 'admin-ajax.php' ) // wpdtrt_gallery_config.ajax_url
      )
    );

  }

  add_action( 'wp_enqueue_scripts', 'wpdtrt_gallery_frontend_js' );

}

?>
