<?php
/**
 * Add EXIF fields (Time and GPS) to attachment media modal
 *
 * This file contains PHP.
 *
 * @since       0.3.0
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

/**
 * Convert Degrees Minutes Seconds String To Number
 * @param string $string The String
 * @return number $number The number
 */
function wpdtrt_gallery_dms_to_number( $string ) {

  $number = 0;

  $pos = strpos($string, '/');

  if ($pos !== false) {
    $temp = explode('/', $string);
    $number = $temp[0] / $temp[1];
  }

  return $number;
}

/**
 * Convert Degrees Minutes Seconds to Decimal Degrees
 * @param string $reference_direction (n/s/e/w)
 * @param string $degrees Degrees
 * @param string $minutes Minutes
 * @param string $seconds Seconds
 * @return string $decimal The decimal value
 */
function wpdtrt_gallery_gps_dms_to_decimal( $reference_direction, $degrees, $minutes, $seconds ) {

  // http://stackoverflow.com/a/32611358
  // http://stackoverflow.com/a/19420991
  // https://www.mail-archive.com/pkg-perl-maintainers@lists.launchpad.net/msg02335.html

  $degrees = wpdtrt_gallery_dms_to_number( $degrees );
  $minutes = wpdtrt_gallery_dms_to_number( $minutes );
  $seconds = wpdtrt_gallery_dms_to_number( $seconds );

  $decimal = ( $degrees + ( $minutes / 60 ) + ( $seconds / 3600 ) );

  //If the latitude is South, or the longitude is West, make it negative.
  if ( ( $reference_direction === 'S' ) || ( $reference_direction === 'W' ) ) {
    $decimal *= -1;
  }

  return $decimal;
}

/**
 * Add GPS field to media uploader, for GPS dependent functions (map, weather)
 * Writing the EXIF back to the image is a hassle, so we can query the GPS in the future, instead.
 *
 * Note: except for 'title', unwanted fields cannot be removed from the attachment modal
 * @see https://codex.wordpress.org/Function_Reference/remove_post_type_support
 * @see https://core.trac.wordpress.org/ticket/23932
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 * @see https://codex.wordpress.org/Function_Reference/get_attached_file
 * @see attachment.php
 * @see wp-admin/includes/media.php
 * @see wp-includes/media-template.php
 */
function wpdtrt_gallery_attachment_field_exif( $form_fields, $post ) {

  $file = get_attached_file( $post->ID );
  $exif = @exif_read_data( $file );

  // wp_get_attachment_link has been overwritten to pass settings to JS, so it only ever points to the 'large' version
  //wpdtrt_log( 'TEST 1: ' . wp_get_attachment_image($post->ID, 'full') );
  //wpdtrt_log( 'TEST 2: ' . wp_get_attachment_link($post->ID, 'full') );

  if ( !empty( $exif['DateTimeOriginal'] ) ) {
    $form_fields['wpdtrt-gallery-time'] = array(
      'label' => 'Time',
      'input' => 'html',
      'html' => '<input type="text" readonly="readonly" value="' . $exif['DateTimeOriginal'] . '" />',
    );
  }

  $meta = array();

  if ( !empty( $exif['GPSLatitude'] ) && isset( $exif['GPSLatitudeRef'] ) ) {
    $meta['latitude'] = wpdtrt_gallery_gps_dms_to_decimal( $exif['GPSLatitudeRef'], $exif["GPSLatitude"][0], $exif["GPSLatitude"][1], $exif["GPSLatitude"][2] );
  }

  if ( !empty( $exif['GPSLongitude'] ) && isset( $exif['GPSLongitudeRef'] ) ) {
    $meta['longitude'] = wpdtrt_gallery_gps_dms_to_decimal( $exif['GPSLongitudeRef'], $exif["GPSLongitude"][0], $exif["GPSLongitude"][1], $exif["GPSLongitude"][2] );
  }

  // if the values can be pulled from the image
  if ( isset( $meta['latitude'], $meta['longitude'] ) ) {
    // then display these values to content admins
    $value = ( $meta['latitude'] . ',' . $meta['longitude'] ); // working
  }
  // else try to pull these values from the user field
  else {
    $value = get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_exif_gps', true ); // working
  }

  $gmap = '';

  if ( $value !== '' ) {
    $gmap .= 'https://maps.googleapis.com/maps/api/staticmap?';
    $gmap .= 'maptype=satellite';
    $gmap .= '&center=' . $value;
    $gmap .= '&zoom=8';
    $gmap .= '&size=150x150';
    $gmap .= '&markers=color:0xff0000|' . $value;
    $gmap .= '&key=AIzaSyAyMI7z2mnFYdONaVV78weOmB0U2LThZMo';
  }

  $form_fields['wpdtrt-gallery-gps'] = array(
    'label' => 'GPS',
    'input' => 'text',
    'value' => $value,
    'helps' => '<img src="' . $gmap . '" alt="' . $value . '" title="' . $value . '" width="150" height="150" />',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'wpdtrt_gallery_attachment_field_exif', 10, 2 );

/**
 * Save value of Location field in media uploader, for GPS dependent functions (map, weather)
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function wpdtrt_gallery_attachment_field_exif_save( $post, $attachment ) {

  if ( isset( $attachment['dtrt-gps'] ) ) {
    update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_exif_gps', $attachment['dtrt-gps'] ); // working
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'wpdtrt_gallery_attachment_field_exif_save', 10, 2 );


?>