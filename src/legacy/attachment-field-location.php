<?php
/**
 * File: src/legacy/attachment-field-location.php
 *
 * See:
 * - <http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/>
 *
 * Since:
 *   0.3.0 - Added
 */

/**
 * Function: wpdtrt_gallery_attachment_field_location
 *
 * Add Location field to media uploader, for gallery searches.
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
function wpdtrt_gallery_attachment_field_location( array $form_fields, object $post ) : array {

	$form_fields['wpdtrt-gallery-location'] = array(
		'label' => 'Location',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_location', true ),
		'helps' => 'For media library searches',
	);

	return $form_fields;
}

/**
 * Function: wpdtrt_gallery_attachment_field_location_save
 *
 * Save value of Location field in media uploader, for gallery-viewer.
 *
 * Parameters:
 *   $post - The post data for database
 *   $attachment - Attachment fields from $_POST form
 *
 * Returns:
 *   $post - Modified post data
 *
 * See:
 * - <https://stackoverflow.com/questions/4554758/how-to-read-if-a-checkbox-is-checked-in-php>
 */
function wpdtrt_gallery_attachment_field_location_save( array $post, array $attachment ) : array {
	if ( isset( $attachment['wpdtrt-gallery-location'] ) ) {
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_location', $attachment['wpdtrt-gallery-location'] );
	}

	return $post;
}
