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
  $geo_exif = wpdtrt_gallery_get_attachment_geodata($id, 'number');

  $link_options['latitude'] = $geo_exif[0];
  $link_options['longitude'] = $geo_exif[1];

  /**
   * Filter the gallery thumbnail links to link to the 'large' image size and not the 'full' image size.
   * [and update the link to include the options ?? ]
   * @see http://johnciacia.com/2012/12/31/filter-wordpress-gallery-image-link/
   * list() is used to assign a list of variables in one operation.
   *
   * @todo the 'full' images are massive!
   * need to resize these proportional to a max height of 350px
   * @see https://wordpress.stackexchange.com/questions/212768/add-image-size-where-largest-possible-proportional-size-is-generated
   */
  $image_size = $panorama ? 'full' : 'large';

  list( $link, , ) = wp_get_attachment_image_src( $id, $image_size );

  // Encode options
  // http://stackoverflow.com/a/39370906

  $query = http_build_query($link_options, '', '&amp;');
  $link .= '?' . $query;

  // Update gallery link
  return preg_replace( "/href='([^']+)'/", "href='$link'", $html );
}

add_filter( 'wp_get_attachment_link', 'wpdtrt_thumbnail_queryparams', 1, 4 );

?>