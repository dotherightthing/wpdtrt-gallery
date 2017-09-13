<?php
/**
 * Add Source Image link to attachment media modal
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
 * Add read-only Source Image field to media uploader, for gallery-viewer
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function wpdtrt_gallery_attachment_field_source_image( $form_fields, $post ) {
  $form_fields['wpdtrt-gallery-source-image'] = array(
    'label' => 'Image',
    'input' => 'html',
    'html' => '<a href="' . wp_get_attachment_image_src($post->ID, 'full')[0] . '" target="_blank" title="Source image (opens in a new tab/window). " style="display:block; font-size: 13px; margin-top: .45em;">View original</a>',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'wpdtrt_gallery_attachment_field_source_image', 10, 2 );

?>