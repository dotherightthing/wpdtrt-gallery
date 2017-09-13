<?php
/**
 * Add Select Onload radio button to attachment media modal
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

?>