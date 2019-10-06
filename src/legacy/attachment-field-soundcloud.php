<?php
/**
 * File: src/legacy/attachment-field-soundcloud.php
 *
 * Add Soundcloud fields to attachment media modal
 *
 * See:
 * - <http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/>
 *
 * Since:
 *   0.3.0 - Added
 */

/**
 * Function: wpdtrt_gallery_attachment_field_soundcloud_pageid
 *
 * Add SoundCloud field to media uploader, for gallery-viewer.
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
function wpdtrt_gallery_attachment_field_soundcloud_pageid( array $form_fields, object $post ) : array {

	$form_fields['wpdtrt-gallery-soundcloud-pageid'] = array(
		'label' => '<abbr title="SoundCloud">SC</abbr> Page ID',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_soundcloud_pageid', true ),
		'helps' => 'soundcloud.com/USER/ID',
	);

	return $form_fields;
}

/**
 * Save value of SoundCloud field in media uploader, for gallery-viewer
 *
 * Parameters:
 *   $post - The post data for database
 *   $attachment - Attachment fields from $_POST form
 *
 * Returns:
 *   $post - Modified post data
 */
function wpdtrt_gallery_attachment_field_soundcloud_pageid_save( object $post, array $attachment ) : object {

	if ( isset( $attachment['wpdtrt-gallery-soundcloud-pageid'] ) ) {
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_soundcloud_pageid', $attachment['wpdtrt-gallery-soundcloud-pageid'] );
	}

	return $post;
}

/**
 * Add SoundCloud field to media uploader, for gallery-viewer
 *
 * Parameters:
 *   $form_fields - Fields to include in attachment form
 *   $post - Attachment record in database
 *
 * Returns:
 *   $form_fields - Modified form fields
 */
function wpdtrt_gallery_attachment_field_soundcloud_trackid( array $form_fields, object $post ) : array {

	$form_fields['wpdtrt-gallery-soundcloud-trackid'] = array(
		'label' => '<abbr title="SoundCloud">SC</abbr> Track ID',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_soundcloud_trackid', true ),
		'helps' => 'api.soundcloud.com/tracks/ID',
	);

	return $form_fields;
}

/**
 * Save value of SoundCloud field in media uploader, for gallery-viewer
 *
 * Parameters:
 *   $post - The post data for database
 *   $attachment - Attachment fields from $_POST form
 *
 * Returns:
 *   $post - Modified post data
 */
function wpdtrt_gallery_attachment_field_soundcloud_trackid_save( object $post, array $attachment ) : object {

	if ( isset( $attachment['wpdtrt-gallery-soundcloud-trackid'] ) ) {
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_soundcloud_trackid', $attachment['wpdtrt-gallery-soundcloud-trackid'] );
	}

	return $post;
}
