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
	    .attr( 'data-position-y',         uri_query.position_y)
	    .attr( 'data-initial',            uri_query.default )
	    .attr( 'data-vimeo-pageid',       uri_query.vimeo_pageid )
	    .attr( 'data-soundcloud-pageid',  uri_query.soundcloud_pageid )
	    .attr( 'data-soundcloud-trackid', uri_query.soundcloud_trackid )
	    .attr( 'data-rwgps-pageid',       uri_query.rwgps_pageid )
	    .attr( 'data-latitude',           uri_query.latitude )
	    .attr( 'data-longitude',          uri_query.longitude )
	    .attr( 'data-panorama',           uri_query.panorama )
	    .attr( 'data-src-mobile',         uri_query.src_mobile );

	  /**
	   * Strip URL params as all have now been converted into data attrs
	   * exclude Google maps
	   */
	  if ( uri_domain.match(/dontbelievethehype/) ) {
	    $gallery_item.attr('href', uri.search('') );
	  }

	},

	/**
	 * Clean up gallery viewer attributes
	 * 	to prevent cross contamination between image types
	 * @param {object} $ - jQuery
	 * @param {object} $viewer - jQuery gallery viewer
	 * @since 1.3.0
	 */
	gallery_viewer_reset: function($, $viewer) {

		var $expand_button = $viewer.find('.gallery-viewer--expand');

		// remove forced expand used by panorama & iframe viewers
		$viewer.removeAttr('data-expanded-contenttype');
		$expand_button.show();

		// reset the viewer state:
		// to the forced state reqd by the content type, or
		// to the last state chosen by the user - if that works..., or
		// to the default state of false
		$viewer.removeAttr('data-expanded');
	},

	/**
	 * Convert expanded state from a data-attribute string to a boolean
	 * @param {str} str - String
	 * @return {boolean} viewer_is_expanded
	 * @since 1.3.0
	 */
	string_to_boolean: function(str) {
		var bool;

		if ( str === 'true' ) {
			bool = true;
		}
		else if ( str === 'false' ) {
			bool = false;
		}

		return bool;
	},

	/**
	 * Expand or collapse the gallery viewer
	 * @param {object} $ - jQuery
	 * @param {object} $expand_button - jQuery gallery expand button
	 * @param {boolean} triggered - Programmatically triggered
	 * @return {boolean} viewer_is_expanded
	 * @requires includes/attachment.php ?
	 * @since 3.0.0
	 */
	gallery_viewer_toggle_expanded: function($, $expand_button, triggered) {

		var $expand_button_text =   $expand_button.find('.says');
		var viewer_id =             $expand_button.attr('aria-controls');
		var $viewer =               $('#' + viewer_id);
		var $viewer_img =           $viewer.find('img');
		var $viewer_iframe =        $viewer.find('iframe');

		// read data- attributes from
		var vimeo_pageid =          $viewer.data('vimeo-pageid');
		var soundcloud_pageid =     $viewer.data('soundcloud-pageid');
		var soundcloud_trackid =    $viewer.data('soundcloud-trackid');
		var rwgps_pageid =          $viewer.data('rwgps-pageid');

		// the actual state
		var viewer_is_expanded =    $viewer.attr('data-expanded');

		// post-toggle states to reinstate
		var user_expanded_saved = $viewer.attr('data-expanded-user');

		// this is problematic
		// because when the thumbnail is clicked
		// this attribute is still hanging around if used previously
		// because of the sequence in which these _update functions run
		// and clicking the thumbnail counts as a trigger for some reason
		// there needs to be a cleanup or destroy or unload function
		// which strips out all the fancy stuff
		// except info we want to retain such as the user state
		// which could always be left there
		// and if a type has some other data, that could trump it
		var data_expanded_contenttype = $viewer.attr('data-expanded-contenttype');

		// ------------------------------
		// update state
		// ------------------------------

		// A - forced content state
		// data-expanded-contenttype will only be present if the content type requires it
		// and the button will be hidden so the user won't have to fight it
		if ( triggered && data_expanded_contenttype ) {

			// set viewer state to state required by content type
			$viewer.attr('data-expanded', data_expanded_contenttype );

			// this attribute is removed by the _reset function
		}

		// B - last user state, only if triggered so we don't fight the user
		// user state may be present when moving from e.g. an image to an iframe and back
		// NOTE: clicking on a thumbnail also fires the trigger.....
		else if ( triggered && user_expanded_saved ) {

			// set viewer state to last/saved toggle state
			$viewer.attr('data-expanded', user_expanded_saved);
			
			// don't discard attribute after use
			// so we can reinstate this when switching between types
			// $viewer.removeAttr('data-expanded-user');
		}

		// C - clicking on a thumbnail before the button has been clicked yet
		else if ( triggered ) {
			// no preference saved - so use the default
			$viewer.attr('data-expanded', false );
		}

		// D - toggle - clicking on the expand button to manually and explicitly toggle the state
		// sets the value of data-expanded-user if it is missing
		else {
			// save the inverse of the current state
			// as that is the state that we are changing TO
			// this will result in B being used from now on

			$viewer.attr('data-expanded-user', ! this.string_to_boolean( viewer_is_expanded ) );

			// for clarity
			user_expanded_saved = ! this.string_to_boolean( viewer_is_expanded );

			// use the saved user value
			$viewer.attr('data-expanded', user_expanded_saved );
		}

		// ------------------------------
		// update button text
		// ------------------------------

		// if the viewer is now expanded
		if ( $viewer.attr('data-expanded') === 'true' ) {

			$expand_button.attr('aria-expanded', true);

			// update the hidden button text
			$expand_button_text.text('Show cropped image');
		}

		// if the viewer is now collapsed
		else if ( $viewer.attr('data-expanded') === 'false' ) {

			$expand_button.attr('aria-expanded', false);

			// update the hidden button text
			$expand_button_text.text('Show full image');

			if ( ! triggered ) {
				// scroll to the top of the viewer
				$viewer.scrollView(100, 150);
			}
		}

		// update iframe size
		if ( vimeo_pageid || ( soundcloud_pageid && soundcloud_trackid ) || rwgps_pageid ) {
			$viewer_iframe.attr('height', $viewer_img.height() );
		}

		// focus the viewer
		if ( ! triggered ) {
			$viewer
				.attr('tabindex', '-1')
				.focus();
		}

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

	  var vimeo_pageid =        $gallery_item.data('vimeo-pageid');
	  var soundcloud_pageid =   $gallery_item.data('soundcloud-pageid');
	  var soundcloud_trackid =  $gallery_item.data('soundcloud-trackid');
	  var rwgps_pageid =        $gallery_item.data('rwgps-pageid');

	  var $expand_button = $viewer.find('.gallery-viewer--expand');

	  var embedHeightTimer;

	  // set the src of the video iframe and unhide it
	  if ( vimeo_pageid ) {

		// expand viewer
		$viewer.attr('data-expanded-contenttype', true);
		$expand_button.trigger('click').hide();

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

		// expand viewer
		$viewer.attr('data-expanded-contenttype', true);
		$expand_button.trigger('click').hide();

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

		// expand viewer
		$viewer.attr('data-expanded-contenttype', true);
		$expand_button.trigger('click').hide();

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
	  	// reset viewer type
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
	 * @uses https://stackoverflow.com/a/17308232/6850747
	 * @since 3.0.0
	 * @todo Add startPosition parameter in media.php (panorama_position_x)
	 */
	gallery_viewer_panorama_update: function($, $viewer, $gallery_item) {

		var panorama =        $gallery_item.data('panorama');
		var $expand_button =  $viewer.find('.gallery-viewer--expand');
		var $scroll_liner =   $viewer.find('.img-wrapper');
		var $gal = 			  $scroll_liner;
		var galleryScrollTimer;
		var galleryScrollSetup;

		$gal.off('.galleryScroll');
		clearInterval(galleryScrollTimer);
		clearTimeout(galleryScrollSetup);

		if ( panorama ) {
			// expand viewer
			$viewer.attr('data-expanded-contenttype', true);
			$expand_button.trigger('click').hide();

			// this data attribute toggles the overflow-x scrollbar
			// which provides the correct $el[0].scrollWidth value
			$viewer.attr('data-panorama', panorama);

			// timeout ensures that the related CSS has taken effect
			galleryScrollSetup = setTimeout( function() {

				var galW = $gal.outerWidth(true), // setTimeout reqd for this value
					galSW = $gal[0].scrollWidth,
					wDiff = (galSW / galW) - 1, // widths difference ratio
					mPadd = 75, // Mousemove padding
					damp = 20, // Mousemove response softness
					mX = 0, // Real mouse position
					mX2 = 0, // Modified mouse position
					posX = 0,
					mmAA = galW - (mPadd * 2), // The mousemove available area
					mmAAr = (galW / mmAA), // get available mousemove difference ratio
					tabindex = null;

				$gal
					.on('mousemove.galleryScroll', function(e) {
						mX = e.pageX - $(this).offset().left;
						mX2 = Math.min(Math.max(0, mX - mPadd), mmAA) * mmAAr;
					})
					.on('mouseenter.galleryScroll', function() {
						galleryScrollTimer = setInterval(function() {
							posX += (mX2 - posX) / damp; // zeno's paradox equation "catching delay"	
							$gal.scrollLeft(posX * wDiff);
						}, 10);

						tabindex = $viewer.attr('data-tabindex');

						if ( tabindex ) {
							$viewer
								.attr('tabindex', tabindex)
								.removeAttr('data-tabindex');
						}

					})
					.on('mouseleave.galleryScroll', function() {
						clearInterval(galleryScrollTimer);
					})
					.on('mousedown.galleryScroll', function() {
						// Use the scroll bar without fighting the cursor-based panning
						clearInterval(galleryScrollTimer);

						// Prevent viewer container from stealing the focus
						tabindex = $viewer.attr('tabindex');

						if ( tabindex ) {
							$viewer
								.attr('data-tabindex', tabindex)
								.removeAttr('tabindex');
						}
					})
					.on('mouseup.galleryScroll', function() {
						// Reactivate the cursor-based panning
						$gal.trigger('mouseenter.galleryScroll');
					});

			}, 100 );
		}
		else {
			// reset viewer type
			$gal.off('.galleryScroll');
			clearInterval(galleryScrollTimer);
			clearTimeout(galleryScrollSetup);
			$viewer.removeAttr('data-panorama');

			// reset viewer state
			$viewer.removeAttr('data-expanded-contenttype');
		}
	},

	/**
	 * Update the gallery image
	 * @param {object} $ - jQuery
	 * @param {object} $viewer - jQuery gallery viewer
	 * @param {object} $gallery_item - jQuery gallery thumbnail
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 */
	gallery_viewer_image_update: function($, $viewer, $gallery_item) {

	  var $expand_button = 			$viewer.find('.gallery-viewer--expand');
	  var $gallery_item_image =     $gallery_item.find('img');
	  var gallery_item_alt =        $gallery_item_image.attr('alt');
	  var gallery_item_img_full =   $gallery_item.attr('href');

	  // the generated enlargement
	  var viewer_id =               $viewer.attr('id');
	  var $viewer_wrapper =         $viewer.find('.stack--wrapper');

	  // the other gallery items
	  var $gallery_items = 			$('[aria-controls="' + viewer_id + '"]');

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
	      'background-position': '50% ' + $gallery_item.data('position-y') + '%'
	    });

	  // set the source of the large image which is uncropped
	  // after gallery_viewer_panorama_update
	  $viewer.find('img')
	    .attr('src', gallery_item_img_full )
	    .attr('alt', gallery_item_alt);

		// setup viewer
		$expand_button.trigger('click');

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
	      .append('<button id="' + section_id + '-viewer-expand" class="gallery-viewer--expand" aria-expanded="false" aria-controls="' + viewer_id + '"><span class="says">Show full image</span></button>');

	    var $expand_button = $('#' + section_id + '-viewer-expand');

	    //$expand_button.attr('tabindex', 0);

	    $expand_button.click( function(e) {

	      var triggered = e.originalEvent ? false : true;

	      // prevent the click bubbling up to the viewer, creating an infinite loop
	      e.stopPropagation();

	      wpdtrt_gallery_ui.gallery_viewer_toggle_expanded($, $expand_button, triggered);
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

	      wpdtrt_gallery_ui.gallery_viewer_reset($, $viewer);

	      wpdtrt_gallery_ui.gallery_viewer_image_update($, $viewer, $gallery_item);

	      wpdtrt_gallery_ui.gallery_viewer_panorama_update($, $viewer, $gallery_item);

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
