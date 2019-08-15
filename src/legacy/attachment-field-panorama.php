<?php
/**
 * Add Panorama radio button to attachment media modal
 *
 * This file contains PHP.
 *
 * @since       0.3.0
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/app
 */

/**
 * Add "Panorama" option to media uploader
 *
 * @param array  $form_fields Fields to include in attachment form.
 * @param object $post Attachment record in database.
 * @return $form_fields, modified form fields
 * @see https://code.tutsplus.com/articles/creating-custom-fields-for-attachments-in-wordpress--net-13076
 */
function wpdtrt_gallery_attachment_field_panorama( $form_fields, $post ) {

	// Get currently select value.
	$selection = get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_panorama', true );

	// If no selection, default to 'No'.
	if ( ! isset( $selection ) ) {
		$selection = '0';
	}

	if ( '1' === $selection ) {
		$checked = ' checked="checked"';
	} else {
		$checked = '';
	}

	$html  = '';
	$html .= '<div class="wpdtrt-gallery-panorama-option">';
	$html .= '<input type="checkbox" name="attachments[' . $post->ID . '][wpdtrt-gallery-panorama]" id="attachments-' . $post->ID . '-wpdtrt-gallery-panorama" value="1"' . $checked . ' />';
	$html .= '</div>';
	$out[] = $html;

	// Construct the form field.
	$form_fields['wpdtrt-gallery-panorama'] = array(
		'label' => 'Panorama',
		'input' => 'html',
		'html'  => join( "\n", $out ),
	);

	// Return all form fields.
	return $form_fields;
}

/**
 * Save value of "panorama" selection in media uploader
 *
 * @param array $post The post data for database.
 * @param array $attachment Attachment fields from $_POST form.
 * @return array $post Modified post data
 * @see https://stackoverflow.com/questions/4554758/how-to-read-if-a-checkbox-is-checked-in-php
 * @todo calculate this automatically, or make it a theme option to do so
 */
function wpdtrt_gallery_attachment_field_panorama_save( $post, $attachment ) {

	if ( isset( $attachment['wpdtrt-gallery-panorama'] ) ) {
		// if checked.
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_panorama', $attachment['wpdtrt-gallery-panorama'] );
	} else {
		// if not checked.
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_panorama', '0' );
	}

	return $post;
}
