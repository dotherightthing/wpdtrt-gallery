<?php
/**
 * Add Ride With GPS field to attachment media modal
 *
 * This file contains PHP.
 *
 * @since       0.3.0
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

/**
 * Add Ride With GPS field to media uploader, for gallery-viewer
 *
 * @param array  $form_fields Fields to include in attachment form.
 * @param object $post Attachment record in database.
 * @return $form_fields Modified form fields
 */
function wpdtrt_gallery_attachment_field_rwgps_pageid( $form_fields, $post ) {

	$form_fields['wpdtrt-gallery-rwgps-pageid'] = array(
		'label' => '<abbr title="Ride With GPS">RWGPS</abbr> ID',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_rwgps_pageid', true ),
		'helps' => 'ridewithgps.com/routes/ID',
	);

	return $form_fields;
}

/**
 * Save value of Ride With GPS field in media uploader, for gallery-viewer
 *
 * @param array $post The post data for database.
 * @param array $attachment Attachment fields from $_POST form.
 * @return array $post Modified post data
 */
function wpdtrt_gallery_attachment_field_rwgps_pageid_save( $post, $attachment ) {

	if ( isset( $attachment['wpdtrt-gallery-rwgps-pageid'] ) ) {
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_rwgps_pageid', $attachment['wpdtrt-gallery-rwgps-pageid'] );
	}

	return $post;
}
