<?php
/**
 * File: src/legacy/attachment-field-default.php
 *
 * Add Default (default selection) radio button to attachment media modal
 *
 * See:
 * - <http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/>
 *
 * Since:
 *   0.3.0 - Added
 */

/**
 * Function: wpdtrt_gallery_attachment_field_default
 *
 * Add "Select onload" option to media uploader, for gallery-viewer.
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
function wpdtrt_gallery_attachment_field_default( array $form_fields, object $post ) : array {

	// Get currently select value.
	$selection = get_post_meta( $post->ID, 'wpdtrt_gallery_attachment_default', true );

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
	$html .= '<div class="wpdtrt-gallery-default-option">';
	$html .= '<input type="checkbox" name="attachments[' . $post->ID . '][wpdtrt-gallery-default]" id="attachments-' . $post->ID . '-wpdtrt-gallery-default" value="1"' . $checked . ' />';
	$html .= '</div>';

	$out[] = $html;

	// Construct the form field.
	$form_fields['wpdtrt-gallery-default'] = array(
		'label' => 'Selected',
		'input' => 'html',
		'html'  => join( "\n", $out ),
	);

	// Return all form fields.
	return $form_fields;
}

/**
 * Function: wpdtrt_gallery_attachment_field_default_save
 *
 * Save value of "Select onload" selection in media uploader, for gallery-viewer.
 *
 * Parameters:
 *   $post - The post data for database
 *   $attachment - Attachment fields from $_POST form
 *
 * Returns:
 *   $post - Modified post data
 *
 * See:
 * - <https://stackoverflow.com/questions/4554758/how-to-read-if-a-checkbox-is-checked-in-php>
 */
function wpdtrt_gallery_attachment_field_default_save( object $post, array $attachment ) : object {

	// if checked.
	if ( isset( $attachment['wpdtrt-gallery-default'] ) ) {
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_default', $attachment['wpdtrt-gallery-default'] );
	} else {
		// if not checked.
		update_post_meta( $post['ID'], 'wpdtrt_gallery_attachment_default', '0' );
	}

	return $post;
}
