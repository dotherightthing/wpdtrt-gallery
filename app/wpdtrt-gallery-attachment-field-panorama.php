<?php
/**
 * Add Panorama radio button to attachment media modal
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
 * Add "Panorama" option to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 * @see https://code.tutsplus.com/articles/creating-custom-fields-for-attachments-in-wordpress--net-13076
 */

function wpdtrt_gallery_attachment_field_panorama( $form_fields, $post ) {

  // Get currently select value
  $selection = get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_panorama', true );

  // If no selection, default to 'No'
  if ( !isset( $selection ) ) {
    $selection = '0';
  }

  if ( $selection == '1' ) {
    $checked = ' checked="checked"';
  }
  else {
    $checked = '';
  }

  $html = '';
  $html .= '<div class="wpdtrt-gallery-panorama-option">';
  $html .= '<input type="checkbox" name="attachments[' . $post->ID . '][wpdtrt-gallery-panorama]" id="attachments-' . $post->ID . '-wpdtrt-gallery-panorama" value="1"' . $checked . ' />';
  $html .= '</div>';

  $out[] = $html;

  // Construct the form field
  $form_fields['wpdtrt-gallery-panorama'] = array(
    'label' => 'Panorama',
    'input' => 'html',
    'html'  => join("\n", $out),
  );

  // Return all form fields
  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'wpdtrt_gallery_attachment_field_panorama', 10, 2 );

/**
 * Save value of "panorama" selection in media uploader
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 * @see https://stackoverflow.com/questions/4554758/how-to-read-if-a-checkbox-is-checked-in-php
 * @todo calculate this automatically, or make it a theme option to do so
 */

function wpdtrt_gallery_attachment_field_panorama_save( $post, $attachment ) {

  // if checked
  if ( isset( $attachment['wpdtrt-gallery-panorama'] ) ) {
    update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_panorama', $attachment['wpdtrt-gallery-panorama'] );
  }
  // if not checked
  else {
    update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_panorama', '0' );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'wpdtrt_gallery_attachment_field_panorama_save', 10, 2 );

?>