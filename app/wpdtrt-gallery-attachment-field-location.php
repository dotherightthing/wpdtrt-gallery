<?php
/**
 * Add Location field to attachment media modal
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
 * Add Location field to media uploader, for gallery searches
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function wpdtrt_gallery_attachment_field_location( $form_fields, $post ) {
  $form_fields['wpdtrt-gallery-location'] = array(
    'label' => 'Location',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_location', true ),
    //'helps' => '50% __%',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'wpdtrt_gallery_attachment_field_location', 10, 2 );

/**
 * Save value of Location field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function wpdtrt_gallery_attachment_field_location_save( $post, $attachment ) {
  if ( isset( $attachment['wpdtrt-gallery-location'] ) ) {
    update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_location', $attachment['wpdtrt-gallery-location'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'wpdtrt_gallery_attachment_field_location_save', 10, 2 );

?>