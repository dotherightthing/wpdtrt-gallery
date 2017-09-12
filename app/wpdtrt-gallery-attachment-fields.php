<?php
/**
 * Add new fields to attachments (media modal)
 *
 * This file contains PHP.
 *
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 * @todo Use ACF field groups
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

/**
 * Convert Degrees Minutes Seconds String To Number
 * @param string $string The String
 * @return number $number The number
 */
function dms_to_number( $string ) {

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
function gps_dms_to_decimal( $reference_direction, $degrees, $minutes, $seconds ) {

  // http://stackoverflow.com/a/32611358
  // http://stackoverflow.com/a/19420991
  // https://www.mail-archive.com/pkg-perl-maintainers@lists.launchpad.net/msg02335.html

  $degrees = dms_to_number( $degrees );
  $minutes = dms_to_number( $minutes );
  $seconds = dms_to_number( $seconds );

  $decimal = ( $degrees + ( $minutes / 60 ) + ( $seconds / 3600 ) );

  //If the latitude is South, or the longitude is West, make it negative.
  if ( ( $reference_direction === 'S' ) || ( $reference_direction === 'W' ) ) {
    $decimal *= -1;
  }

  return $decimal;
}

/**
 * Add read-only Source Image field to media uploader, for gallery-viewer
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function dtrt_attachment_field_source_image( $form_fields, $post ) {
  $form_fields['dtrt-source-image'] = array(
    'label' => 'Image',
    'input' => 'html',
    'html' => '<a href="' . wp_get_attachment_image_src($post->ID, 'full')[0] . '" target="_blank" title="Source image (opens in a new tab/window). " style="display:block; font-size: 13px; margin-top: .45em;">View original</a>',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_source_image', 10, 2 );

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
function dtrt_attachment_field_gps( $form_fields, $post ) {

  $file = get_attached_file( $post->ID );
  $exif = @exif_read_data( $file );

  // wp_get_attachment_link has been overwritten to pass settings to JS, so it only ever points to the 'large' version
  //wpdtrt_log( 'TEST 1: ' . wp_get_attachment_image($post->ID, 'full') );
  //wpdtrt_log( 'TEST 2: ' . wp_get_attachment_link($post->ID, 'full') );

  if ( !empty( $exif['DateTimeOriginal'] ) ) {
    $form_fields['dtrt-time'] = array(
      'label' => 'Time',
      'input' => 'html',
      'html' => '<input type="text" readonly="readonly" value="' . $exif['DateTimeOriginal'] . '" />',
    );
  }

  $meta = array();

  if ( !empty( $exif['GPSLatitude'] ) && isset( $exif['GPSLatitudeRef'] ) ) {
    $meta['latitude'] = gps_dms_to_decimal( $exif['GPSLatitudeRef'], $exif["GPSLatitude"][0], $exif["GPSLatitude"][1], $exif["GPSLatitude"][2] );
  }

  if ( !empty( $exif['GPSLongitude'] ) && isset( $exif['GPSLongitudeRef'] ) ) {
    $meta['longitude'] = gps_dms_to_decimal( $exif['GPSLongitudeRef'], $exif["GPSLongitude"][0], $exif["GPSLongitude"][1], $exif["GPSLongitude"][2] );
  }

  // if the values can be pulled from the image
  if ( isset( $meta['latitude'], $meta['longitude'] ) ) {
    // then display these values to content admins
    $value = ( $meta['latitude'] . ',' . $meta['longitude'] ); // working
  }
  // else try to pull these values from the user field
  else {
    $value = get_post_meta( $post->ID, 'dtrt_gps', true ); // working
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

  $form_fields['dtrt-gps'] = array(
    'label' => 'GPS',
    'input' => 'text',
    'value' => $value,
    'helps' => '<img src="' . $gmap . '" alt="' . $value . '" title="' . $value . '" width="150" height="150" />',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_gps', 10, 2 );

/**
 * Save value of Location field in media uploader, for GPS dependent functions (map, weather)
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function dtrt_attachment_field_gps_save( $post, $attachment ) {

  if ( isset( $attachment['dtrt-gps'] ) ) {
    update_post_meta( $post['ID'], 'dtrt_gps', $attachment['dtrt-gps'] ); // working
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'dtrt_attachment_field_gps_save', 10, 2 );

/**
 * Add Location field to media uploader, for gallery searches
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function dtrt_attachment_field_location( $form_fields, $post ) {
  $form_fields['dtrt-location'] = array(
    'label' => 'Location',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'dtrt_location', true ),
    //'helps' => '50% __%',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_location', 10, 2 );

/**
 * Save value of Location field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function dtrt_attachment_field_location_save( $post, $attachment ) {
  if ( isset( $attachment['dtrt-location'] ) ) {
    update_post_meta( $post['ID'], 'dtrt_location', $attachment['dtrt-location'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'dtrt_attachment_field_location_save', 10, 2 );

/**
 * Add Position-Y field to media uploader, for gallery-viewer
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function dtrt_attachment_field_position_y( $form_fields, $post ) {
  $form_fields['dtrt-position-y'] = array(
    'label' => 'Position Y',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'dtrt_position_y', true ),
    'helps' => '0% __%',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_position_y', 10, 2 );

/**
 * Save value of Position-Y field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function dtrt_attachment_field_position_y_save( $post, $attachment ) {
  if ( isset( $attachment['dtrt-position-y'] ) ) {
    update_post_meta( $post['ID'], 'dtrt_position_y', $attachment['dtrt-position-y'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'dtrt_attachment_field_position_y_save', 10, 2 );

/**
 * Add Ride With GPS field to media uploader, for gallery-viewer
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function dtrt_attachment_field_rwgps_pageid( $form_fields, $post ) {
  $form_fields['dtrt-rwgps-pageid'] = array(
    'label' => 'Ride With GPS Route ID',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'dtrt_rwgps_pageid', true ),
    'helps' => 'https://ridewithgps.com/routes/_________',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_rwgps_pageid', 10, 2 );

/**
 * Save value of Ride With GPS field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function dtrt_attachment_field_rwgps_pageid_save( $post, $attachment ) {
  if ( isset( $attachment['dtrt-rwgps-pageid'] ) ) {
    update_post_meta( $post['ID'], 'dtrt_rwgps_pageid', $attachment['dtrt-rwgps-pageid'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'dtrt_attachment_field_rwgps_pageid_save', 10, 2 );

/**
 * Add SoundCloud field to media uploader, for gallery-viewer
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function dtrt_attachment_field_soundcloud_pageid( $form_fields, $post ) {
  $form_fields['dtrt-soundcloud-pageid'] = array(
    'label' => '<abbr title="SoundCloud">SC</abbr> Page ID',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'dtrt_soundcloud_pageid', true ),
    'helps' => '//soundcloud.com/dontbelievethehypenz/_________',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_soundcloud_pageid', 10, 2 );

/**
 * Save value of SoundCloud field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function dtrt_attachment_field_soundcloud_pageid_save( $post, $attachment ) {
  if ( isset( $attachment['dtrt-soundcloud-pageid'] ) ) {
    update_post_meta( $post['ID'], 'dtrt_soundcloud_pageid', $attachment['dtrt-soundcloud-pageid'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'dtrt_attachment_field_soundcloud_pageid_save', 10, 2 );

/**
 * Add SoundCloud field to media uploader, for gallery-viewer
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function dtrt_attachment_field_soundcloud_trackid( $form_fields, $post ) {
  $form_fields['dtrt-soundcloud-trackid'] = array(
    'label' => '<abbr title="SoundCloud">SC</abbr> Track ID',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'dtrt_soundcloud_trackid', true ),
    'helps' => '//api.soundcloud.com/tracks/_________',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_soundcloud_trackid', 10, 2 );

/**
 * Save value of SoundCloud field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function dtrt_attachment_field_soundcloud_trackid_save( $post, $attachment ) {
  if ( isset( $attachment['dtrt-soundcloud-trackid'] ) ) {
    update_post_meta( $post['ID'], 'dtrt_soundcloud_trackid', $attachment['dtrt-soundcloud-trackid'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'dtrt_attachment_field_soundcloud_trackid_save', 10, 2 );

/**
 * Add Vimeo field to media uploader, for gallery-viewer
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function dtrt_attachment_field_vimeo_pageid( $form_fields, $post ) {
  $form_fields['dtrt-vimeo-pageid'] = array(
    'label' => 'Vimeo ID',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'dtrt_vimeo_pageid', true ),
    'helps' => '//vimeo.com/_________',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_vimeo_pageid', 10, 2 );

/**
 * Save value of Vimeo field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function dtrt_attachment_field_vimeo_pageid_save( $post, $attachment ) {
  if ( isset( $attachment['dtrt-vimeo-pageid'] ) ) {
    update_post_meta( $post['ID'], 'dtrt_vimeo_pageid', $attachment['dtrt-vimeo-pageid'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'dtrt_attachment_field_vimeo_pageid_save', 10, 2 );


/**
 * Add "Select onload" option to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function dtrt_attachment_field_select_onload( $form_fields, $post ) {

  // Set up options
  $options = array( '1' => 'Yes', '0' => 'No' );

  // Get currently select value
  $select = get_post_meta( $post->ID, 'dtrt_select_onload', true );

  // If no select value, default to 'No'
  if( !isset( $select ) )
    $select = '0';

  // Display each option
  foreach ( $options as $value => $label ) {
    $checked = '';
    $css_id = 'select-onload-option-' . $value;

    if ( $select == $value ) {
      $checked = " checked='checked'";
    }

    $html = "<div class='select-onload-option'><input type='radio' name='attachments[$post->ID][dtrt-select-onload]' id='{$css_id}' value='{$value}'$checked />";

    $html .= "<label for='{$css_id}'>$label</label>";

    $html .= '</div>';

    $out[] = $html;
  }

  // Construct the form field
  $form_fields['dtrt-select-onload'] = array(
    'label' => 'Select onload',
    'input' => 'html',
    'html'  => join("\n", $out),
  );

  // Return all form fields
  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_select_onload', 10, 2 );


/**
 * Save value of "Select onload" selection in media uploader
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function dtrt_attachment_field_select_onload_save( $post, $attachment ) {
  if( isset( $attachment['dtrt-select-onload'] ) )
    update_post_meta( $post['ID'], 'dtrt_select_onload', $attachment['dtrt-select-onload'] );

  return $post;
}

add_filter( 'attachment_fields_to_save', 'dtrt_attachment_field_select_onload_save', 10, 2 );


/**
 * Add "Panorama" option to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function dtrt_attachment_field_panorama( $form_fields, $post ) {

  // Set up options
  $options = array( '1' => 'Yes', '0' => 'No' );

  // Get currently select value
  $select = get_post_meta( $post->ID, 'dtrt_panorama', true );

  // If no select value, default to 'No'
  if( !isset( $select ) )
    $select = '0';

  // Display each option
  foreach ( $options as $value => $label ) {
    $checked = '';
    $css_id = 'panorama-option-' . $value;

    if ( $select == $value ) {
      $checked = " checked='checked'";
    }

    $html = "<div class='panorama-option'><input type='radio' name='attachments[$post->ID][dtrt-panorama]' id='{$css_id}' value='{$value}'$checked />";

    $html .= "<label for='{$css_id}'>$label</label>";

    $html .= '</div>';

    $out[] = $html;
  }

  // Construct the form field
  $form_fields['dtrt-panorama'] = array(
    'label' => 'Panorama',
    'input' => 'html',
    'html'  => join("\n", $out),
  );

  // Return all form fields
  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_panorama', 10, 2 );

/**
 * Save value of "panorama" selection in media uploader
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 * @todo calculate this automatically, or make it a theme option to do so
 */

function dtrt_attachment_field_panorama_save( $post, $attachment ) {
  if( isset( $attachment['dtrt-panorama'] ) )
    update_post_meta( $post['ID'], 'dtrt_panorama', $attachment['dtrt-panorama'] );

  return $post;
}

add_filter( 'attachment_fields_to_save', 'dtrt_attachment_field_panorama_save', 10, 2 );

?>