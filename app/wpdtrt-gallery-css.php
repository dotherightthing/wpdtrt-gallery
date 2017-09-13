<?php
/**
 * CSS imports
 *
 * This file contains PHP.
 *
 * @since       0.1.0
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

if ( !function_exists( 'wpdtrt_gallery_css_backend' ) ) {

  /**
   * Attach CSS for Settings > DTRT Gallery
   *
   * @since       0.1.0
   */
  function wpdtrt_gallery_css_backend() {

    $backend = 'wpdtrt_gallery_backend';

    wp_enqueue_style( $backend,
      WPDTRT_GALLERY_URL . 'css/wpdtrt-gallery-admin.css',
      array(),
      WPDTRT_GALLERY_VERSION
      //'all'
    );
  }

  add_action( 'admin_head', 'wpdtrt_gallery_css_backend' );

}

if ( !function_exists( 'wpdtrt_gallery_css_frontend' ) ) {

  /**
   * Attach CSS for front-end widgets and shortcodes
   *
   * @since       0.1.0
   */
  function wpdtrt_gallery_css_frontend() {

    $media = 'all';

    wp_register_style( 'paver',
      WPDTRT_GALLERY_URL . 'vendor/bower_components/paver/dist/css/paver.min.css',
      array(),
      '1.3.3',
      $media
    );

    wp_enqueue_style( 'wpdtrt_gallery_frontend',
      WPDTRT_GALLERY_URL . 'css/wpdtrt-gallery.css',
      array(
        // load these registered dependencies first:
        'paver'
      ),
      WPDTRT_GALLERY_VERSION,
      $media
    );

  }

  add_action( 'wp_enqueue_scripts', 'wpdtrt_gallery_css_frontend' );

}

?>
