<?php
/**
 * Image sizes
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

// Portrait/Landscape image

$mobile_width = 400; // allows for bigger phones
$mobile_height = 9999; // auto
$mobile_hardcrop = false;

add_image_size(
  'wpdtrt-gallery-mobile',
  $mobile_width,
  $mobile_height,
  $mobile_hardcrop
);

$desktop_width = 972;
$desktop_height = 9999; // auto
$desktop_hardcrop = false;

add_image_size(
  'wpdtrt-gallery-desktop',
  $desktop_width,
  $desktop_height,
  $desktop_hardcrop
);

// Landscape Panorama image

$panorama_width = 9999; // auto
$panorama_height = 368; // both desktop and mobile
$panorama_hardcrop = false;

add_image_size(
  'wpdtrt-gallery-panorama',
  $panorama_width,
  $panorama_height,
  $panorama_hardcrop
);

?>