<?php
/**
 * Add Location field to attachment media modal
 *
 * This file contains PHP.
 *
 * @since       0.3.0
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

/**
 * Add Location field to media uploader, for gallery searches
 *
 * @param array  $form_fields Fields to include in attachment form.
 * @param object $post Attachment record in database.
 * @return $form_fields, modified form fields
 */
function wpdtrt_gallery_attachment_field_location( $form_fields, $post ) {

	$form_fields['wpdtrt-gallery-location'] = array(
		'label' => 'Location',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_location', true ),
		'helps' => 'For media library searches',
	);

	return $form_fields;
}

/**
 * Save value of Location field in media uploader, for gallery-viewer
 *
 * @param array $post The post data for database.
 * @param array $attachment Attachment fields from $_POST form.
 * @return $post array, modified post data
 */
function wpdtrt_gallery_attachment_field_location_save( $post, $attachment ) {
	if ( isset( $attachment['wpdtrt-gallery-location'] ) ) {
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_location', $attachment['wpdtrt-gallery-location'] );
	}

	return $post;
}
