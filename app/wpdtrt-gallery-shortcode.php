<?php
/**
 * Generate a shortcode, to embed the widget inside a content area.
 *
 * This file contains PHP.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-gallery
 * @link        https://generatewp.com/shortcodes/
 * @since       0.1.0
 *
 * @example     [wpdtrt_gallery]H2 heading text[/wpdtrt_gallery]
 * @example     do_shortcode( '[wpdtrt_gallery]H2 heading text[/wpdtrt_gallery]' );
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

add_filter( 'the_content', 'wpdtrt_gallery_shortcode_inject' );

/**
 * Automatically inject plugin shortcodes into the content
 *
 * @return $content
 */
function wpdtrt_gallery_shortcode_inject($content) {

  //if ( ! function_exists('wpdtrt_content_sections') ) {
  //  return;
  //}

  $content = preg_replace("/(<h2>.+<\/h2>)/", "[wpdtrt-gallery-h2]$1[/wpdtrt-gallery-h2]", $content);

  $content = preg_replace("/<div id='gallery'>/", "<h3>Gallery</h3>$1", $content);

  return $content;
}

if ( !function_exists( 'wpdtrt_gallery_shortcode' ) ) {

  /**
   * @param       array $atts
   *    Optional shortcode attributes specified by the user
   * @param       string $content
   *    Content within the enclosing shortcode tags
   *
   * @since       0.1.0
   * @uses        ../../../../wp-includes/shortcodes.php
   * @see         https://codex.wordpress.org/Function_Reference/add_shortcode
   * @see         https://codex.wordpress.org/Shortcode_API#Enclosing_vs_self-closing_shortcodes
   * @see         http://php.net/manual/en/function.ob-start.php
   * @see         http://php.net/manual/en/function.ob-get-clean.php
   */
  function wpdtrt_gallery_shortcode( $atts, $content = null ) {

    // post object to get info about the post in which the shortcode appears
    global $post;

    // predeclare variables
    $before_widget = null;
    $before_title = null;
    $title = null;
    $after_title = null;
    $after_widget = null;
    $option1 = null;
    $shortcode = 'wpdtrt_gallery_shortcode';

    /**
     * Combine user attributes with known attributes and fill in defaults when needed.
     * @see https://developer.wordpress.org/reference/functions/shortcode_atts/
     */
    $atts = shortcode_atts(
      array(
        'option1' => 'foo'
      ),
      $atts,
      $shortcode
    );

    // only overwrite predeclared variables
    extract( $atts, EXTR_IF_EXISTS );

    $wpdtrt_gallery_options = get_option('wpdtrt_gallery');
    $wpdtrt_gallery_data = $wpdtrt_gallery_options['wpdtrt_gallery_data'];

    /**
     * ob_start — Turn on output buffering
     * This stores the HTML template in the buffer
     * so that it can be output into the content
     * rather than at the top of the page.
     */
    ob_start();

    // template outputs $content enclosed within shortcode tags
    require(WPDTRT_GALLERY_PATH . 'templates/wpdtrt-gallery-front-end.php');

    /**
     * ob_get_clean — Get current buffer contents and delete current output buffer
     */
    $content = ob_get_clean();

    return $content;
  }

  /**
   * @param string $tag
   *    Shortcode tag to be searched in post content.
   * @param callable $func
   *    Hook to run when shortcode is found.
   */
  add_shortcode( 'wpdtrt-gallery', 'wpdtrt_gallery_shortcode' );

}

?>
