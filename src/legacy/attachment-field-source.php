<?php
/**
 * File: src/legacy/attachment-field-source.php
 *
 * Add Source Image link to attachment media modal
 *
 * See:
 * - <http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/>
 *
 * Since:
 *   0.3.0 - Added
 */

/**
 * Function: wpdtrt_gallery_attachment_field_source_image
 *
 * Add read-only Source Image field to media uploader, for gallery-viewer.
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
function wpdtrt_gallery_attachment_field_source_image( array $form_fields, object $post ) : array {
	$form_fields['wpdtrt-gallery-source-image'] = array(
		'label' => 'Source',
		'input' => 'html',
		'html'  => '<a href="' . wp_get_attachment_image_src( $post->ID, 'full' )[0] . '" target="_blank" title="Source image (opens in a new tab/window). ">View original</a>',
	);

	return $form_fields;
}
