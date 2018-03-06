<?php
/**
 * Template partial for the public front-end
 * This is run with do_shortcode run on content
 *
 * This file contains PHP, and HTML.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-gallery
 * @since       0.1.0
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/templates
 */
?>

<?php
  // output widget customisations (not output with shortcode)
  echo $before_widget;
  echo $before_title . $title . $after_title;
?>

<div class="stack stack_link_viewer gallery-viewer h2-viewer" id="[]-viewer" data-has-image="false" data-expanded="false">
  <div class="gallery-viewer--header">
    <?php
      // <h2>Heading text</h2>
      echo $content;
    ?>
  </div>
  <div class="stack--wrapper" style="">
    <figure class="stack--liner">
      <div class="img-wrapper">
        <img src="" alt="">
      </div>
      <iframe width="100%" height="100%" src="" frameborder="0" allowfullscreen="true" scrolling="no" aria-hidden="true"></iframe>
      <figcaption class="gallery-viewer--footer">
        <div class="gallery-viewer--caption"></div>
      </figcaption>
    </figure>
  </div>
</div>

<?php
  // output widget customisations (not output with shortcode)
  echo $after_widget;
?>
