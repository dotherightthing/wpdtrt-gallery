<?php
/**
 * API requests
 *
 * This file contains PHP.
 *
 * @since       0.1.0
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

if ( !function_exists( 'wpdtrt_gallery_get_data' ) ) {

  /**
   * Request the data from the API
   *
   * @param       string $wpdtrt_gallery_datatype
   *    The type of data to return.
   * @return      object $wpdtrt_gallery_data
   *    The body of the JSON response
   *
   * @since       0.1.0
   * @uses        ../../../../wp-includes/http.php
   * @see         https://developer.wordpress.org/reference/functions/wp_remote_get/
   */
  function wpdtrt_gallery_get_data( $wpdtrt_gallery_datatype ) {

    $endpoint = 'http://jsonplaceholder.typicode.com/' . $wpdtrt_gallery_datatype;

    $args = array(
      'timeout' => 30 // seconds to wait for the request to complete
    );

    $response = wp_remote_get(
      $endpoint,
      $args
    );

    /**
     * Return the body, not the header
     * Note: There is an optional boolean argument, which returns an associative array if TRUE
     */
    $wpdtrt_gallery_data = json_decode( $response['body'] );

    return $wpdtrt_gallery_data;
  }

}

if ( !function_exists( 'wpdtrt_gallery_data_refresh' ) ) {

  /**
   * Refresh the data from the API
   *    The 'action' key's value, 'wpdtrt_gallery_data_refresh',
   *    matches the latter half of the action 'wp_ajax_wpdtrt_gallery_data_refresh' in our AJAX handler.
   *    This is because it is used to call the server side PHP function through admin-ajax.php.
   *    If an action is not specified, admin-ajax.php will exit, and return 0 in the process.
   *
   * @since       0.1.0
   * @see         https://codex.wordpress.org/AJAX_in_Plugins
   */
  function wpdtrt_gallery_data_refresh() {

    $wpdtrt_gallery_options = get_option('wpdtrt_gallery');
    $last_updated = $wpdtrt_gallery_options['last_updated'];

    $current_time = time();
    $update_difference = $current_time - $last_updated;
    $one_hour = (1 * 60 * 60);

    if ( $update_difference > $one_hour ) {

      $wpdtrt_gallery_datatype = $wpdtrt_gallery_options['wpdtrt_gallery_datatype'];

      $wpdtrt_gallery_options['wpdtrt_gallery_data'] = wpdtrt_gallery_get_data( $wpdtrt_gallery_datatype );

      // inspecting the database will allow us to check
      // whether the profile is being updated
      $wpdtrt_gallery_options['last_updated'] = time();

      update_option('wpdtrt_gallery', $wpdtrt_gallery_options);
    }

    /**
     * Let the Ajax know when the entire function has completed
     *
     * wp_die() vs die() vs exit()
     * Most of the time you should be using wp_die() in your Ajax callback function.
     * This provides better integration with WordPress and makes it easier to test your code.
     */
    wp_die();

  }

  add_action('wp_ajax_wpdtrt_gallery_data_refresh', 'wpdtrt_gallery_data_refresh');

}

?>
