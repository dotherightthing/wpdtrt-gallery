/**
 * Scripts for the public front-end
 *
 * This file contains JavaScript.
 *    PHP variables are provided in wpdtrt_gallery_config.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-gallery
 * @since       0.1.0
 *
 * @package     WPDTRT_Gallery
 * @subpackage  WPDTRT_Gallery/js
 */

/* globals jQuery, Waypoint, URI */

var wpdtrt_gallery_ui = {

	/**
	 * Lazyload a gallery viewer when it is scrolled into view
	 * to defer automatic loading of initial enlargements
	 * @param {object} $ - jQuery
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 * @see https://varvy.com/pagespeed/defer-images.html
	 */
	gallery_viewer_lazyinit: function($) {

	  var $sections = $('section');

	  $sections.each( function(i, item) {
	    var $section = $(item);

	    var inview = new Waypoint.Inview({
	      element: $section[0],
	      enter: function() {
	        wpdtrt_gallery_ui.gallery_viewer_init($, $section);
	        inview.destroy();
	      }
	    });
	  });
	},

	/**
	 * Convert query params into data attributes
	 * for easy configuration of the gallery viewer
	 * @param {object} $ - jQuery
	 * @param {object} $gallery_item - jQuery gallery item
	 * @param {string} viewer_id
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 */
	gallery_viewer_data: function($, $gallery_item, viewer_id) {

	  /**
	   * Gallery settings are passed in via PHP manipulation of the thumbnail URL
	   */
	  var href = $gallery_item.attr('href');
	  var uri = new URI( href );
	  var uri_domain = uri.domain();
	  var uri_query = uri.search(true);

	  /**
	   * When a query variable is not present
	   * the data- attribute will be set to undefined
	   * which results in it being suppressed
	   */
	  $gallery_item
	    .attr( 'aria-controls',           viewer_id )
	    .attr( 'data-position_y',         uri_query.position_y)
	    .attr( 'data-initial',            uri_query.default )
	    .attr( 'data-vimeo_pageid',       uri_query.vimeo_pageid )
	    .attr( 'data-soundcloud_pageid',  uri_query.soundcloud_pageid )
	    .attr( 'data-soundcloud_trackid', uri_query.soundcloud_trackid )
	    .attr( 'data-rwgps_pageid',       uri_query.rwgps_pageid )
	    .attr( 'data-latitude',           uri_query.latitude )
	    .attr( 'data-longitude',          uri_query.longitude )
	    .attr( 'data-panorama',           uri_query.panorama );

	  /**
	   * Strip URL params as all have now been converted into data attrs
	   * exclude Google maps
	   */
	  if ( uri_domain.match(/dontbelievethehype/) ) {
	    $gallery_item.attr('href', uri.search('') );
	  }

	},

	/**
	 * Expand or collapse the gallery viewer
	 * @param {object} $ - jQuery
	 * @param {object} $expand_button - jQuery gallery expand button
	 * @return {boolean} viewer_is_expanded
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 */
	gallery_viewer_toggle_expanded: function($, $expand_button) {

	  var $expand_button_text =   $expand_button.find('.says');

	  var viewer_id =             $expand_button.attr('aria-controls');
	  var $viewer =               $('#' + viewer_id);
	  var $viewer_img =           $viewer.find('img');
	  var $viewer_iframe =        $viewer.find('iframe');

	  // read data- attributes from
	  var viewer_is_expanded =    $viewer.attr('data-expanded');
	  var vimeo_pageid =          $viewer.data('vimeo-pageid');
	  var soundcloud_pageid =     $viewer.data('soundcloud-pageid');
	  var soundcloud_trackid =    $viewer.data('soundcloud-trackid');
	  var rwgps_pageid =          $viewer.data('rwgps-pageid');

	  if ( viewer_is_expanded === 'false' ) {

	    // expand viewer and display the contract button
	    $viewer
	      .attr('data-expanded', true);

	    // update the hidden button text
	    $expand_button_text
	      .text('Show cropped image');
	  }
	  else if ( viewer_is_expanded === 'true' ) {

	    // collapse viewer
	    // scroll to the top of the viewer
	    $viewer
	      .attr('data-expanded', false)
	      .scrollView(100, 150);

	    // update the hidden button text
	    $expand_button_text
	      .text('Show full image');
	  }

	  // update iframe size
	  if ( vimeo_pageid || ( soundcloud_pageid && soundcloud_trackid ) || rwgps_pageid ) {
	    $viewer_iframe
	      .attr('height', $viewer_img.height() );
	  }

	  // focus the viewer
	  $viewer
	    .attr('tabindex', '-1')
	      .focus();

	  return ( ! viewer_is_expanded );
	},

	/**
	 * Update the gallery caption to match the selected image
	 * @param {object} $ - jQuery
	 * @param {object} $viewer - jQuery gallery viewer
	 * @param {object} $gallery_item - jQuery gallery thumbnail
	 * @return {string} gallery_item_caption
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 */
	gallery_viewer_caption_update: function($, $viewer, $gallery_item) {

	  var $viewer_caption =       $viewer.find('.gallery-viewer--caption');
	  var gallery_item_caption =  $.trim( $gallery_item.parent().next('figcaption').text() );

	  // set the text of the large image caption
	  $viewer_caption
	    .html( gallery_item_caption + ' <span class="says">(enlargement)</span>' );

	  return gallery_item_caption;
	},

	/**
	 * Update the gallery iframe to display a video, audio file, or interactive map
	 * @param {object} $ - jQuery
	 * @param {object} $viewer - jQuery gallery viewer
	 * @param {object} $gallery_item - jQuery gallery thumbnail
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 */
	gallery_viewer_iframe_update: function($, $viewer, $gallery_item) {

	  var $viewer_iframe =      $viewer.find('iframe');
	  var $viewer_img =         $viewer.find('img');

	  var vimeo_pageid =        $gallery_item.data('vimeo_pageid');
	  var soundcloud_pageid =   $gallery_item.data('soundcloud_pageid');
	  var soundcloud_trackid =  $gallery_item.data('soundcloud_trackid');
	  var rwgps_pageid =        $gallery_item.data('rwgps_pageid');

	  var embedHeightTimer;

	  // set the src of the video iframe and unhide it
	  if ( vimeo_pageid ) {
	    $viewer
	      .attr('data-vimeo-pageid', vimeo_pageid);

	    // adapted from https://appleple.github.io/modal-video/
	    $viewer_iframe
	      .attr('src', '//player.vimeo.com/video/' + vimeo_pageid + '?api=false&autopause=true&autoplay=false&byline=false&loop=false&portrait=false&title=false&xhtml=false')
	      .attr('height', $viewer_img.height() )
	      .attr('aria-hidden', 'false');

	    embedHeightTimer = setTimeout( function() {
	      $viewer_iframe
	        .attr('height', $viewer_img.height() );
	    }, 500);

	    $viewer_img
	      .attr('aria-hidden', 'true');
	  }
	  else if ( soundcloud_pageid && soundcloud_trackid ) {
	    $viewer
	      .attr('data-soundcloud-pageid', soundcloud_pageid) // https://soundcloud.com/dontbelievethehypenz/saxophonic-snack-manzhouli
	      .attr('data-soundcloud-trackid', soundcloud_trackid); // 291457131

	    $viewer_iframe
	      .attr('src', '//w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/' + soundcloud_trackid + '?auto_play=false&hide_related=true&show_comments=false&show_user=false&show_reposts=false&visual=true')
	      .attr('height', $viewer_img.height() )
	      .attr('aria-hidden', 'false');

	    embedHeightTimer = setTimeout( function() {
	      $viewer_iframe
	        .attr('height', $viewer_img.height() );
	    }, 500);

	    $viewer_img
	      .attr('aria-hidden', 'true');
	  }
	  else if ( rwgps_pageid ) {
	    $viewer
	      .attr('data-rwgps-pageid', rwgps_pageid); // https://ridewithgps.com/routes/18494494

	    $viewer_iframe
	      .attr('src', '//rwgps-embeds.com/routes/' + rwgps_pageid + '/embed')
	      .attr('height', '500px' )
	      .attr('aria-hidden', 'false');

	    embedHeightTimer = setTimeout( function() {
	      $viewer_iframe
	        .attr('height', '500px' );
	    }, 500);

	    $viewer_img
	      .attr('aria-hidden', 'true');
	  }

	  else {
	    $viewer
	      .removeAttr('data-vimeo-pageid')
	      .removeAttr('data-rwgps-pageid')
	      .removeAttr('data-soundcloud-pageid')
	      .removeAttr('data-soundcloud-trackid');

	    $viewer_iframe
	      .removeAttr('src')
	      .attr('height', 'auto')
	      .attr('aria-hidden', 'true');

	    $viewer_img
	      .attr('aria-hidden', 'false');

	      clearTimeout(embedHeightTimer);
	  }
	},

	/**
	 * Update the gallery panorama
	 * @param {object} $ - jQuery
	 * @param {object} $viewer - jQuery gallery viewer
	 * @param {object} $gallery_item - jQuery gallery thumbnail
	 * @requires includes/attachment.php
	 * @requires jquery.paver.min.js
	 * @since 3.0.0
	 * @todo Add startPosition parameter in media.php (panorama_position_x)
	 */
	gallery_viewer_panorama_update: function($, $viewer, $gallery_item) {

		var panorama =        $gallery_item.data('panorama');
		var $viewer_liner =   $viewer.find('.stack--liner');
		var $expand_button =  $viewer.find('.gallery-viewer--expand');

		// destroy current Paver instance regardless
		if ( $viewer_liner.hasClass('paver--on') ) {
			$viewer_liner.trigger('destroy.paver');
			$viewer_liner.css('min-height', '368px');
			$viewer.removeAttr('data-panorama');
		}

		$expand_button.show();

		// add or remove paver
		if ( panorama ) {

			$viewer.attr('data-expanded', 'false');

			$expand_button.hide();

			$viewer.attr('data-panorama', panorama);

			enquire.register("screen and (min-width:44.375em)", {

			    // OPTIONAL
			    // If supplied, triggered when a media query matches.
			    match: function() {

			    	console.log('match!');

					$viewer_liner.paver({
						startPosition: 0.5,
						tilt: false,
						gracefulFailure: false // suppress manual scroll hint
					});
			    },

			    // remove paver
			    // but leave the expand button hidden
			    // as we'll show the fallback overflow scrollbar
			    unmatch: function() {

					if ( $viewer_liner.hasClass('paver--on') ) {
						$viewer_liner.trigger('destroy.paver');
						$viewer_liner.css('min-height', '368px');
						//$viewer.removeAttr('data-panorama');
					}
			    }
			});

		}
		else {
			$expand_button.show();
		}
	},

	/**
	 * Update the gallery portrait image
	 * @param {object} $ - jQuery
	 * @param {object} $viewer - jQuery gallery viewer
	 * @param {object} $gallery_item - jQuery gallery thumbnail
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 */
	gallery_viewer_portrait_update: function($, $viewer, $gallery_item) {

	  var $gallery_item_image =     $gallery_item.find('img');
	  var gallery_item_alt =        $gallery_item_image.attr('alt');
	  var gallery_item_img_full =   $gallery_item.attr('href');

	  // the generated enlargement
	  var viewer_id =               $viewer.attr('id');
	  var $viewer_wrapper =         $viewer.find('.stack--wrapper');

	  // the other gallery items
	  var $gallery_items = $('[aria-controls="' + viewer_id + '"]');

	  // unview existing thumbnail and reinstate into tab order
	  $gallery_items
	    .removeAttr('data-viewing')
	    .removeAttr('tabindex');

	  // view the selected thumbnail and remove from tab order
	  $gallery_item
	    .attr('data-viewing', true)
	    .attr('tabindex', '-1');

	  // set the source of the large image at the appropriate crop
	  $viewer_wrapper
	    .css({
	      'background-image': 'url(' + gallery_item_img_full + ')',
	      'background-position': '50% ' + $gallery_item.data('position_y') + '%'
	    });

	  // set the source of the large image which is uncropped
	  // after gallery_viewer_panorama_update
	  $viewer.find('img') // not pre-cached as panorama's paver changes the markup structure
	    .attr('src', gallery_item_img_full )
	    .attr('alt', gallery_item_alt);

	  // focus the viewer, if the user requested this
	  /*
	  if ( wpdtrt_gallery_ui.gallery_viewer_setup ) {
	    $viewer
	      .attr('tabindex', '-1')
	      .focus();
	  }
	  */

	},

	/**
	 * Initialise a gallery viewer
	 * @param {object} $ - jQuery
	 * @param {object} $section - jQuery page section
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 */
	gallery_viewer_init: function($, $section) {

	    var $section_gallery =        $section.find('.gallery');
	    var section_id =              $section.attr('id');
	    var viewer_id =               section_id + '-viewer';
	    var $stack_link_viewer =      $section.find('.stack_link_viewer');
	    var $heading =                $stack_link_viewer.find('.gallery-viewer--heading');
	    var $stack_wrapper =          $stack_link_viewer.find('.stack--wrapper');
	    var $section_gallery_items =  $section_gallery.find('a');

	    if ( $stack_link_viewer.attr('data-attachment') ) {
	      return;
	    }

	    if ( ! $section_gallery.length ) {
	      $stack_link_viewer
	        .removeAttr('id')
	        .removeAttr('data-expanded');

	      $stack_wrapper
	        .remove();

	      return;
	    }

	    $stack_wrapper
	      .attr('data-loading', true);

	    $heading
	      .after('<h3 class="says">Gallery</h3>');

	    $stack_link_viewer
	      .attr('id', viewer_id)
	      .attr('data-has-image', true);

	    $stack_link_viewer.find('.gallery-viewer--header')
	      .append('<button id="' + section_id + '-viewer-expand" class="gallery-viewer--expand" aria-controls="' + viewer_id + '"><span class="says">Show full image</span></button>');

	    var $expand_button = $('#' + section_id + '-viewer-expand');

	    //$expand_button.attr('tabindex', 0);

	    $expand_button.click( function(e) {
	      // prevent the click bubbling up to the viewer, creating an infinite loop
	      e.stopPropagation();

	      wpdtrt_gallery_ui.gallery_viewer_toggle_expanded($, $expand_button);
	    });

	    $section_gallery_items.each( function(i, item) {
	      wpdtrt_gallery_ui.gallery_viewer_data( $, $(item), viewer_id );
	    });

	    $section_gallery_items.click( function(e) {

	      // don't load the WordPress media item page
	      e.preventDefault();

	      var $gallery_item = $(this);
	      var viewer_id = $gallery_item.attr('aria-controls');
	      var $viewer = $( '#' + viewer_id );

	      // only update the viewer if a different thumbnail was selected
	      if ( $gallery_item.data('viewing') ) {
	        return;
	      }

	      // todo prevent errors - set timeout ?

	      wpdtrt_gallery_ui.gallery_viewer_panorama_update($, $viewer, $gallery_item);

	      wpdtrt_gallery_ui.gallery_viewer_portrait_update($, $viewer, $gallery_item);

	      wpdtrt_gallery_ui.gallery_viewer_iframe_update($, $viewer, $gallery_item);

	      wpdtrt_gallery_ui.gallery_viewer_caption_update($, $viewer, $gallery_item);

	    });

	    var $gallery_item_initial = $section_gallery_items.filter('[data-initial]');

	    if ( $gallery_item_initial.length ) {
	      // this is the item assigned the 'initial' class in the media library 'Gallery Link Additional CSS Classes' field
	      // if there is more than one, select the first
	      $gallery_item_initial.eq(0)
	        .click();
	    }
	    else {
	      // automatically select the first gallery item to load its large image
	      $section_gallery_items.eq(0)
	        .click();
	    }

	    $stack_wrapper
	      .removeAttr('data-loading');
	}
};

(function($) {
  $(window).on('load', function() {
    // defer load of .initial enlargements, to reduce initial load time for PageSpeed
    wpdtrt_gallery_ui.gallery_viewer_lazyinit($);
  });
})(jQuery);
