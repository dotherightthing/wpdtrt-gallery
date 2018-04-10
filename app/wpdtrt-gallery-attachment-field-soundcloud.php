<?php
/**
 * Add Soundcloud fields to attachment media modal
 *
 * This file contains PHP.
 *
 * @since       0.3.0
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

/**
 * Add SoundCloud field to media uploader, for gallery-viewer
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function wpdtrt_gallery_attachment_field_soundcloud_pageid( $form_fields, $post ) {
  $form_fields['wpdtrt-gallery-soundcloud-pageid'] = array(
    'label' => '<abbr title="SoundCloud">SC</abbr> Page ID',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_soundcloud_pageid', true ),
    'helps' => 'soundcloud.com/USER/ID',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'wpdtrt_gallery_attachment_field_soundcloud_pageid', 10, 2 );

/**
 * Save value of SoundCloud field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function wpdtrt_gallery_attachment_field_soundcloud_pageid_save( $post, $attachment ) {
  if ( isset( $attachment['wpdtrt-gallery-soundcloud-pageid'] ) ) {
    update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_soundcloud_pageid', $attachment['wpdtrt-gallery-soundcloud-pageid'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'wpdtrt_gallery_attachment_field_soundcloud_pageid_save', 10, 2 );

/**
 * Add SoundCloud field to media uploader, for gallery-viewer
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function wpdtrt_gallery_attachment_field_soundcloud_trackid( $form_fields, $post ) {
  $form_fields['wpdtrt-gallery-soundcloud-trackid'] = array(
    'label' => '<abbr title="SoundCloud">SC</abbr> Track ID',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_soundcloud_trackid', true ),
    'helps' => 'api.soundcloud.com/tracks/ID',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'wpdtrt_gallery_attachment_field_soundcloud_trackid', 10, 2 );

/**
 * Save value of SoundCloud field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function wpdtrt_gallery_attachment_field_soundcloud_trackid_save( $post, $attachment ) {
  if ( isset( $attachment['wpdtrt-gallery-soundcloud-trackid'] ) ) {
    update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_soundcloud_trackid', $attachment['wpdtrt-gallery-soundcloud-trackid'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'wpdtrt_gallery_attachment_field_soundcloud_trackid_save', 10, 2 );

?>