<?php
/**
 * Add Soundcloud fields to attachment media modal
 *
 * This file contains PHP.
 *
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 * @todo Use ACF field groups
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

function dtrt_attachment_field_soundcloud_pageid( $form_fields, $post ) {
  $form_fields['dtrt-soundcloud-pageid'] = array(
    'label' => '<abbr title="SoundCloud">SC</abbr> Page ID',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'dtrt_soundcloud_pageid', true ),
    'helps' => '//soundcloud.com/dontbelievethehypenz/_________',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_soundcloud_pageid', 10, 2 );

/**
 * Save value of SoundCloud field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function dtrt_attachment_field_soundcloud_pageid_save( $post, $attachment ) {
  if ( isset( $attachment['dtrt-soundcloud-pageid'] ) ) {
    update_post_meta( $post['ID'], 'dtrt_soundcloud_pageid', $attachment['dtrt-soundcloud-pageid'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'dtrt_attachment_field_soundcloud_pageid_save', 10, 2 );

/**
 * Add SoundCloud field to media uploader, for gallery-viewer
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */

function dtrt_attachment_field_soundcloud_trackid( $form_fields, $post ) {
  $form_fields['dtrt-soundcloud-trackid'] = array(
    'label' => '<abbr title="SoundCloud">SC</abbr> Track ID',
    'input' => 'text',
    'value' => get_post_meta( $post->ID, 'dtrt_soundcloud_trackid', true ),
    'helps' => '//api.soundcloud.com/tracks/_________',
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'dtrt_attachment_field_soundcloud_trackid', 10, 2 );

/**
 * Save value of SoundCloud field in media uploader, for gallery-viewer
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function dtrt_attachment_field_soundcloud_trackid_save( $post, $attachment ) {
  if ( isset( $attachment['dtrt-soundcloud-trackid'] ) ) {
    update_post_meta( $post['ID'], 'dtrt_soundcloud_trackid', $attachment['dtrt-soundcloud-trackid'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'dtrt_attachment_field_soundcloud_trackid_save', 10, 2 );

?>