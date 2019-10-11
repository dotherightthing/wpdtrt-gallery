<?php
/**
 * File: src/legacy/attachment-field-ridewithgps.php
 *
 * Add Ride With GPS field to attachment media modal
 *
 * See:
 * - <http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/>
 *
 * Since:
 *   0.3.0 - Added
 */

/**
 * Function: wpdtrt_gallery_attachment_field_rwgps_pageid
 *
 * Add Ride With GPS field to media uploader, for gallery-viewer.
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
function wpdtrt_gallery_attachment_field_rwgps_pageid( array $form_fields, object $post ) : array {

	$form_fields['wpdtrt-gallery-rwgps-pageid'] = array(
		'label' => '<abbr title="Ride With GPS">RWGPS</abbr> ID',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_rwgps_pageid', true ),
		'helps' => 'ridewithgps.com/routes/ID',
	);

	return $form_fields;
}

/**
 * Function: wpdtrt_gallery_attachment_field_rwgps_pageid_save
 *
 * Save value of Ride With GPS field in media uploader, for gallery-viewer
 *
 * Parameters:
 *   $post - The post data for database
 *   $attachment - Attachment fields from $_POST form
 *
 * Returns:
 *   $post - Modified post data
 */
function wpdtrt_gallery_attachment_field_rwgps_pageid_save( array $post, array $attachment ) : array {

	if ( isset( $attachment['wpdtrt-gallery-rwgps-pageid'] ) ) {
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_rwgps_pageid', $attachment['wpdtrt-gallery-rwgps-pageid'] );
	}

	return $post;
}
