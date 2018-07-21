<?php
/**
 * Add Heading above attachment fields
 *
 * This file contains PHP.
 *
 * @since       1.1.0
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

/**
 * Add read-only Heading 'field' to media uploader, for gallery-viewer
 *
 * @param array  $form_fields Fields to include in attachment form.
 * @param object $post Attachment record in database.
 * @return $form_fields, modified form fields
 */
function wpdtrt_gallery_attachment_field_heading( $form_fields, $post ) {

	$form_fields['wpdtrt-gallery-heading'] = array(
		'label' => '<h2>WPDTRT Gallery</h2>',
		'input' => 'html',
		'html' => '<span></span>',
	);

	return $form_fields;
}
