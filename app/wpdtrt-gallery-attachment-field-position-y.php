<?php
/**
 * Add Position-Y field to attachment media modal
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

?>