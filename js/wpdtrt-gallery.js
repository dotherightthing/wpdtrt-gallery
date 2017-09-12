/**
 * Scripts for the public front-end
 *
 * This file contains JavaScript.
 *    PHP variables are provided in wpdtrt_soundcloud_pages_config.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-gallery
 * @since       0.1.0
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/views
 */

jQuery(document).ready(function($){

	$('.wpdtrt-gallery-badge').hover(function() {
		$(this).find('.wpdtrt-gallery-badge-info').stop(true, true).fadeIn(200);
	}, function() {
		$(this).find('.wpdtrt-gallery-badge-info').stop(true, true).fadeOut(200);
	});

  $.post( wpdtrt_gallery_config.ajax_url, {
    action: 'wpdtrt_gallery_data_refresh'
  }, function( response ) {
    //console.log( 'Ajax complete' );
  });

});
