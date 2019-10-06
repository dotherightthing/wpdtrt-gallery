<?php
/**
 * File: src/legacy/attachment-field-heading.php
 *
 * Add Heading above attachment fields
 *
 * See:
 * - <http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/>
 *
 * Since:
 *   0.3.0 - Added
 */

/**
 * Function: wpdtrt_gallery_attachment_field_heading
 *
 * Add read-only Heading 'field' to media uploader, for gallery-viewer.
 *
 * Parameters:
 *   $form_fields - Fields to include in attachment form
 *   $post - Attachment record in database
 *
 * Returns:
 *   $form_fields - Modified form fields
 *
 * See:
 * - <https://code.tutsplus.com/articles/creating-custom-fields-for-attachments-in-wordpress--net-13076>
 */
function wpdtrt_gallery_attachment_field_heading( array $form_fields, object $post ) : array {

	$form_fields['wpdtrt-gallery-heading'] = array(
		'label' => '<h2>WPDTRT Gallery</h2>',
		'input' => 'html',
		'html'  => '<span></span>',
	);

	return $form_fields;
}
