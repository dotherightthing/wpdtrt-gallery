<?php
/**
 * Add Panorama radio button to attachment media modal
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
 * Add "Panorama" option to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function wpdtrt_gallery_attachment_field_panorama( $form_fields, $post ) {

  // Set up options
  $options = array( '1' => 'Yes', '0' => 'No' );

  // Get currently select value
  $select = get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_panorama', true );

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
 * @todo calculate this automatically, or make it a theme option to do so
 */

function wpdtrt_gallery_attachment_field_panorama_save( $post, $attachment ) {
  if( isset( $attachment['dtrt-panorama'] ) )
    update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_panorama', $attachment['dtrt-panorama'] );

  return $post;
}

add_filter( 'attachment_fields_to_save', 'wpdtrt_gallery_attachment_field_panorama_save', 10, 2 );

?>