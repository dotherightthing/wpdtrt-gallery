<?php
/**
 * Add Ride With GPS field to attachment media modal
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
 * Add Ride With GPS field to media uploader, for gallery-viewer
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function wpdtrt_gallery_attachment_field_rwgps_pageid( $form_fields, $post ) {
  $form_fields['wpdtrt-gallery-rwgps-pageid'] = array(
    'label' => '<abbr title="Ride With GPS">RWGPS</abbr> ID',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_rwgps_pageid', true ),
    'helps' => 'ridewithgps.com/routes/ID',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'wpdtrt_gallery_attachment_field_rwgps_pageid', 10, 2 );

/**
 * Save value of Ride With GPS field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function wpdtrt_gallery_attachment_field_rwgps_pageid_save( $post, $attachment ) {
  if ( isset( $attachment['dtrt-rwgps-pageid'] ) ) {
    update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_rwgps_pageid', $attachment['dtrt-rwgps-pageid'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'wpdtrt_gallery_attachment_field_rwgps_pageid_save', 10, 2 );

?>