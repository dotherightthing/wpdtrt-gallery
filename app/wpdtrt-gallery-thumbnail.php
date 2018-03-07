<?php
/**
 * Thumbnail modification
 *
 * This file contains PHP.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-gallery
 * @since       0.1.0
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

/**
* Add attributes to gallery thumbnail links
* These are transformed to data attributes by the UI JS
*
* @param $html
* @param $id
* @param $size
* @param $permalink
* @return string
* @todo Update to query all fields in an ACF fieldgroup
* @todo Create a gallery plugin for this
*/
function wpdtrt_thumbnail_queryparams($html, $id, $size, $permalink) {

  if ( false !== $permalink ) {
    return $html;
  }

  $link_options = array();

  // Vimeo

  $vimeo_pageid = get_post_meta( $id, 'wpdtrt_gallery_attachment_vimeo_pageid', true ); // used for embed

  if ( $vimeo_pageid ) {
    $link_options['vimeo_pageid'] = $vimeo_pageid;
  }

  // SoundCloud

  $soundcloud_pageid = get_post_meta( $id, 'wpdtrt_gallery_attachment_soundcloud_pageid', true ); // used for SEO
  $soundcloud_trackid = get_post_meta( $id, 'wpdtrt_gallery_attachment_soundcloud_trackid', true ); // used for embed, see also http://stackoverflow.com/a/28182284

  if ( $soundcloud_pageid && $soundcloud_trackid ) {
    $link_options['soundcloud_pageid'] = urlencode( $soundcloud_pageid );
    $link_options['soundcloud_trackid'] = $soundcloud_trackid;
  }

  // Ride With GPS

  $rwgps_pageid = get_post_meta( $id, 'wpdtrt_gallery_attachment_rwgps_pageid', true );

  if ( $rwgps_pageid ) {
    $link_options['rwgps_pageid'] = urlencode( $rwgps_pageid );
  }

  // Position Y

  $position_y = get_post_meta( $id, 'wpdtrt_gallery_attachment_position_y', true );
  $position_y_default = "50";

  if ( $position_y !== '' ) {
    $link_options['position_y'] = $position_y;
  }
  else {
    $link_options['position_y'] = $position_y_default;
  }

  // Select onload

  $default = get_post_meta( $id, 'wpdtrt_gallery_attachment_default', true );

  if ( $default == '1' ) {
    $link_options['default'] = $default;
  }

  // Panorama

  $panorama = get_post_meta( $id, 'wpdtrt_gallery_attachment_panorama', true ); // used for JS dragging

  if ( $panorama == '1' ) {
    $link_options['panorama'] = $panorama;
  }

  // Geolocation
  // Could this be replaced by simply looking up the custom field?

  if ( function_exists( 'wpdtrt_exif_get_attachment_metadata' ) ) {
    $attachment_metadata = wpdtrt_exif_get_attachment_metadata( $id );
    $attachment_metadata_gps = wpdtrt_exif_get_attachment_metadata_gps( $attachment_metadata, 'number' );

    $link_options['latitude'] = $attachment_metadata_gps['latitude'];
    $link_options['longitude'] = $attachment_metadata_gps['longitude'];
  }

  /**
   * Filter the gallery thumbnail links to point to custom image sizes, rather than the 'full' image size.
   * @see http://johnciacia.com/2012/12/31/filter-wordpress-gallery-image-link/
   * list() is used to assign a list of variables in one operation.
   */

  // see wpdtrt-gallery-enlargement.php
  $image_size_mobile = 'wpdtrt-gallery-mobile';
  $image_size_desktop = 'wpdtrt-gallery-desktop';
  $image_size_panorama = 'wpdtrt-gallery-panorama';

  $image_size_small = $image_size_mobile;
  $image_size_large = $panorama ? $image_size_panorama : $image_size_desktop;

  // assign a list of variables by taking values from the image_src array
  list( $link, , ) = wp_get_attachment_image_src( $id, $image_size_large );

  // store the other enlargement sizes in data attributes
  $link_options['src_mobile'] = wp_get_attachment_image_src( $id, $image_size_small )[0];

  // Encode options
  // http://stackoverflow.com/a/39370906

  $query = http_build_query($link_options, '', '&amp;');
  $link .= '?' . $query;

  // Update gallery link
  return preg_replace( "/href='([^']+)'/", "href='$link'", $html );
}

add_filter( 'wp_get_attachment_link', 'wpdtrt_thumbnail_queryparams', 1, 4 );

?>