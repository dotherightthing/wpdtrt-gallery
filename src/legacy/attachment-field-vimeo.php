<?php
/**
 * Add Vimeo field to attachment media modal
 *
 * This file contains PHP.
 *
 * @since       0.3.0
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

/**
 * Add Vimeo field to media uploader, for gallery-viewer
 *
 * @param array  $form_fields Fields to include in attachment form.
 * @param object $post Attachment record in database.
 * @return $form_fields Modified form fields
 */
function wpdtrt_gallery_attachment_field_vimeo_pageid( $form_fields, $post ) {

	$form_fields['wpdtrt-gallery-vimeo-pageid'] = array(
		'label' => 'Vimeo ID',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_vimeo_pageid', true ),
		'helps' => 'vimeo.com/ID',
	);

	return $form_fields;
}

/**
 * Save value of Vimeo field in media uploader, for gallery-viewer
 *
 * @param array $post The post data for database.
 * @param array $attachment Attachment fields from $_POST form.
 * @return $post array Modified post data
 */
function wpdtrt_gallery_attachment_field_vimeo_pageid_save( $post, $attachment ) {

	if ( isset( $attachment['wpdtrt-gallery-vimeo-pageid'] ) ) {
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_vimeo_pageid', $attachment['wpdtrt-gallery-vimeo-pageid'] );
	}

	return $post;
}
