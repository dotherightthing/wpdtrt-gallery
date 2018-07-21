<?php
/**
 * Add Position-Y field to attachment media modal
 *
 * This file contains PHP.
 *
 * @since       0.3.0
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

/**
 * Add Position-Y field to media uploader, for gallery-viewer
 *
 * @param array  $form_fields Fields to include in attachment form.
 * @param object $post Attachment record in database.
 * @return $form_fields, modified form fields
 */
function wpdtrt_gallery_attachment_field_position_y( $form_fields, $post ) {

	$form_fields['wpdtrt-gallery-position-y'] = array(
		'label' => 'Position Y',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_position_y', true ),
		'helps' => '0% __%',
	);

	return $form_fields;
}

/**
 * Save value of Position-Y field in media uploader, for gallery-viewer
 *
 * @param array $post The post data for database.
 * @param array $attachment Attachment fields from $_POST form.
 * @return $post array Modified post data
 */
function wpdtrt_gallery_attachment_field_position_y_save( $post, $attachment ) {

	if ( isset( $attachment['wpdtrt-gallery-position-y'] ) ) {
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_position_y', $attachment['wpdtrt-gallery-position-y'] );
	}

	return $post;
}
