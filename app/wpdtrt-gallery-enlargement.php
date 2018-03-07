<?php
/**
 * Enlargement images
 *
 * This file contains PHP.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-gallery
 * @since       1.2.0
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

/**
 * Scale width proportional to max height in UI.
 *  Note: Scaling is always relative to the shorter axis.
 *
 * @see https://stackoverflow.com/a/18159895/6850747 (used)
 * @see https://wordpress.stackexchange.com/questions/212768/add-image-size-where-largest-possible-proportional-size-is-generated (not used)
 * @see https://www.smashingmagazine.com/2016/09/responsive-images-in-wordpress-with-art-direction/
 */

$wpdtrt_enlargement_width_portrait = 972; // desktop - could be much smaller for mobile
$wpdtrt_enlargement_height_portrait = 9999; // auto
$wpdtrt_enlargement_hardcrop_portrait = false;

add_image_size(
  'wpdtrt-gallery-enlargement-portrait',
  $wpdtrt_enlargement_width_portrait,
  $wpdtrt_enlargement_height_portrait,
  $wpdtrt_enlargement_hardcrop_portrait
);

$wpdtrt_enlargement_width_panorama = 9999; // auto
$wpdtrt_enlargement_height_panorama = 368; // both desktop and mobile - and/or could @2x
$wpdtrt_enlargement_hardcrop_panorama = false;

add_image_size(
  'wpdtrt-gallery-enlargement-panorama',
  $wpdtrt_enlargement_width_panorama,
  $wpdtrt_enlargement_height_panorama,
  $wpdtrt_enlargement_hardcrop_panorama
);

?>