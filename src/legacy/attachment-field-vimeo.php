<?php
/**
 * File: src/legacy/attachment-field-vimeo.php
 *
 * Add Vimeo field to attachment media modal
 *
 * See:
 * - <http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/>
 *
 * Since:
 *   0.3.0 - Added
 */

/**
 * Function: wpdtrt_gallery_attachment_field_vimeo_pageid
 *
 * Add Vimeo field to media uploader, for gallery-viewer.
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
function wpdtrt_gallery_attachment_field_vimeo_pageid( array $form_fields, object $post ) : array {

	$form_fields['wpdtrt-gallery-vimeo-pageid'] = array(
		'label' => 'Vimeo ID',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_vimeo_pageid', true ),
		'helps' => 'vimeo.com/ID',
	);

	return $form_fields;
}

/**
 * Save value of Vimeo field in media uploader, for gallery-viewer.
 *
 * Parameters:
 *   $post - The post data for database
 *   $attachment - Attachment fields from $_POST form
 *
 * Returns:
 *   $post - Modified post data
 */
function wpdtrt_gallery_attachment_field_vimeo_pageid_save( array $post, array $attachment ) : array {

	if ( isset( $attachment['wpdtrt-gallery-vimeo-pageid'] ) ) {
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_vimeo_pageid', $attachment['wpdtrt-gallery-vimeo-pageid'] );
	}

	return $post;
}
