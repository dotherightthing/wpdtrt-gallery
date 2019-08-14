/**
 * @file DTRT Gallery frontend.js
 * @summary
 * 		Front-end scripting for public pages
 *   	PHP variables are provided in `wpdtrt_gallery_config`.
 * @version 	0.0.1
 * @since       0.7.0
 */

/* eslint-env browser */
/* globals jQuery, Waypoint, wpdtrt_gallery_config */
/* eslint-disable no-unused-vars */
/* eslint-disable max-len */

/**
 * @namespace wpdtrt_gallery_ui
 */
const wpdtrt_gallery_ui = {
  thumbnail_data: [
  	"id",
  	"initial",
  	"latitude",
  	"longitude",
  	"panorama",
  	"rwgps-pageid",
  	"soundcloud-pageid",
  	"soundcloud-trackid",
  	"src-desktop-expanded",
  	"vimeo-pageid"
  ],

  /**
   * Copy data- attributes from the thumbnail image,
   * to the surrounding link,
   * to support CSS icons.
	 * @param {object} $ - jQuery
	 * @param {object} $gallery_item_link - jQuery gallery thumbnail link
   * @see https://github.com/dotherightthing/wpdtrt-gallery/issues/51
   */
  gallery_viewer_copy_thumbnail_data_to_link: ($, $gallery_item_link) => {
	  const $gallery_item_image = $gallery_item_link.find("img");

	  // copy the data attributes
	  $.each(wpdtrt_gallery_ui.thumbnail_data, (key, value) => {
	  	$gallery_item_link.attr(`data-${value}`, $gallery_item_image.attr(`data-${value}`));
	  });
  },

	/**
	 * Lazyload a gallery viewer when it is scrolled into view
	 * to defer automatic loading of initial enlargements
	 * @param {object} $ - jQuery
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 * @see https://varvy.com/pagespeed/defer-images.html
	 */
  gallery_viewer_lazyinit: ($) => {
	  const $sections = $("section");

	  $sections.each( (i, item) => {
	    let $section = $(item);

	    let inview = new Waypoint.Inview({
	      element: $section[0],
	      enter: () => {
	        wpdtrt_gallery_ui.gallery_viewer_init($, $section);
	        inview.destroy();
	      }
	    });
	  });
	},

	/**
	 * Add accessibility attributes to the gallery viewer
	 *
	 * @param {object} $ - jQuery
	 * @param {object} $gallery_item_link - jQuery gallery thumbnail link
	 * @param {string} viewer_id
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 */
	gallery_viewer_a11y: ($, $gallery_item_link, viewer_id) => {
	  $gallery_item_link
	    .attr( "aria-controls", viewer_id );
	},

	/**
	 * Clean up gallery viewer attributes
	 * 	to prevent cross contamination between image types
	 * @param {object} $ - jQuery
	 * @param {object} $viewer - jQuery gallery viewer
	 * @since 1.3.0
	 */
	gallery_viewer_reset: ($, $viewer) => {
		const $expand_button = $viewer.find(".gallery-viewer--expand");

		// remove forced expand used by panorama & iframe viewers
		$viewer.removeAttr("data-expanded-contenttype");
		$expand_button.show();

		// reset the viewer state:
		// to the forced state reqd by the content type, or
		// to the last state chosen by the user - if that works..., or
		// to the default state of false
		$viewer.removeAttr("data-expanded");
	},

	/**
	 * Convert expanded state from a data-attribute string to a boolean
	 * @param {str} str - String
	 * @return {boolean} viewer_is_expanded
	 * @since 1.3.0
	 */
	string_to_boolean: function(str) {
		let bool = false;

		if ( str === "true" ) {
			bool = true;
		}
		else if ( str === "false" ) {
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
		const $expand_button_text =   $expand_button.find(".says");
		const viewer_id =             $expand_button.attr("aria-controls");
		const $viewer =               $(`#${viewer_id}`);
		const $viewer_img =           $viewer.find("img");
		const $viewer_iframe =        $viewer.find("iframe");

		// read data- attributes from
		const vimeo_pageid =          $viewer.data("vimeo-pageid");
		const soundcloud_pageid =     $viewer.data("soundcloud-pageid");
		const soundcloud_trackid =    $viewer.data("soundcloud-trackid");
		const rwgps_pageid =          $viewer.data("rwgps-pageid");

		// the actual state
		const viewer_is_expanded =    $viewer.attr("data-expanded");

		// post-toggle states to reinstate
		let user_expanded_saved = $viewer.attr("data-expanded-user");

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
		const data_expanded_contenttype = $viewer.attr("data-expanded-contenttype");

		// ------------------------------
		// update state
		// ------------------------------

		// A - forced content state
		// data-expanded-contenttype will only be present if the content type requires it
		// and the button will be hidden so the user won't have to fight it
		if ( triggered && data_expanded_contenttype ) {

			// set viewer state to state required by content type
			$viewer.attr("data-expanded", data_expanded_contenttype );

			// this attribute is removed by the _reset function
		}

		// B - last user state, only if triggered so we don't fight the user
		// user state may be present when moving from e.g. an image to an iframe and back
		// NOTE: clicking on a thumbnail also fires the trigger.....
		else if ( triggered && user_expanded_saved ) {

			// set viewer state to last/saved toggle state
			$viewer.attr("data-expanded", user_expanded_saved);

			// don't discard attribute after use
			// so we can reinstate this when switching between types
			// $viewer.removeAttr("data-expanded-user");
		}

		// C - clicking on a thumbnail before the button has been clicked yet
		else if ( triggered ) {
			// no preference saved - so use the default
			$viewer.attr("data-expanded", false );
		}

		// D - toggle - clicking on the expand button to manually and explicitly toggle the state
		// sets the value of data-expanded-user if it is missing
		else {
			// save the inverse of the current state
			// as that is the state that we are changing TO
			// this will result in B being used from now on

			$viewer.attr("data-expanded-user", ! this.string_to_boolean( viewer_is_expanded ) );

			// for clarity
			user_expanded_saved = ! this.string_to_boolean( viewer_is_expanded );

			// use the saved user value
			$viewer.attr("data-expanded", user_expanded_saved );
		}

		// ------------------------------
		// update src image & button text
		// ------------------------------

		// if the viewer is now expanded
		if ( $viewer.attr("data-expanded") === "true" ) {

			if (! $viewer_img.data("panorama")) {
				$viewer_img.attr("src", $viewer_img.data("src-desktop-expanded"));
			}

			$expand_button.attr("aria-expanded", true);

			// update the hidden button text
			$expand_button_text.text("Show cropped image");
		}

		// if the viewer is now collapsed
		else if ( $viewer.attr("data-expanded") === "false" ) {

			if (! $viewer_img.data("panorama")) {
				$viewer_img.attr("src", $viewer_img.data("src-desktop"));
			}

			$expand_button.attr("aria-expanded", false);

			// update the hidden button text
			$expand_button_text.text("Show full image");

			if ( ! triggered ) {
				// scroll to the top of the viewer
				$viewer.scrollView(100, 150);
			}
		}

		// update iframe size
		if ( rwgps_pageid ) {
			$viewer_iframe.attr("height", $viewer_img.height() );
		}

		// focus the viewer
		if ( ! triggered ) {
			$viewer
				.attr("tabindex", "-1")
				.focus();
		}

		return ( ! viewer_is_expanded );
	},

	/**
	 * Update the gallery caption to match the selected image
	 * @param {object} $ - jQuery
	 * @param {object} $viewer - jQuery gallery viewer
	 * @param {object} $gallery_item_link - jQuery gallery thumbnail link
	 * @return {string} gallery_item_caption
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 */
	gallery_viewer_caption_update: function($, $viewer, $gallery_item_link) {
	  const $viewer_caption =       $viewer.find(".gallery-viewer--caption");
	  const gallery_item_caption =  $.trim( $gallery_item_link.parent().next("figcaption").text() );

	  // set the text of the large image caption
	  $viewer_caption
	    .html( `${gallery_item_caption} <span class="says">(enlargement)</span>` );

	  return gallery_item_caption;
	},

	/**
	 * Update the gallery iframe to display a video, audio file, or interactive map
	 * @param {object} $ - jQuery
	 * @param {object} $viewer - jQuery gallery viewer
	 * @param {object} $gallery_item_link - jQuery gallery thumbnail link
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 */
	gallery_viewer_iframe_update: function($, $viewer, $gallery_item_link) {
	  const $viewer_iframe = $viewer.find("iframe");
	  const $viewer_img = $viewer.find("img");
	  const $gallery_item = $gallery_item_link.parent().parent();
	  const vimeo_pageid = $gallery_item_link.find("img").data("vimeo-pageid");
	  const soundcloud_pageid = $gallery_item_link.find("img").data("soundcloud-pageid");
	  const soundcloud_trackid =  $gallery_item_link.find("img").data("soundcloud-trackid");
	  const rwgps_pageid = $gallery_item_link.find("img").data("rwgps-pageid");
	  const is_default = $gallery_item_link.find("img").data("initial");

	  const $expand_button = $viewer.find(".gallery-viewer--expand");

    let embedHeightTimer = undefined;

	  // Disabled as working but inconsistent
	  // TODO update this to only apply on page load - i.e. if triggered (#31)
	  const autoplay = false; // true;

	  // if first thumbnail in the gallery or
	  // not first thumbnail but elected to display first
	  //if ( $gallery_item.is(":first-child") || is_default ) {
	  //	autoplay = false;
	  //}

	  // set the src of the video iframe and unhide it
	  if ( vimeo_pageid ) {

		// expand viewer
		$viewer.attr("data-expanded-contenttype", true);
		$expand_button.trigger("click").hide();

	    $viewer
	      .attr("data-vimeo-pageid", vimeo_pageid);

	    // adapted from https://appleple.github.io/modal-video/
	    $viewer_iframe
	      .attr("src", `//player.vimeo.com/video/${vimeo_pageid}?api=false&autopause=${!autoplay}&autoplay=${autoplay}&byline=false&loop=false&portrait=false&title=false&xhtml=false`)
	      .attr("height", "368px")
	      .attr("aria-hidden", "false");

	    $viewer_img
	      .attr("aria-hidden", "true");
	  }
	  else if ( soundcloud_pageid && soundcloud_trackid ) {

		// expand viewer
		$viewer.attr("data-expanded-contenttype", true);
		$expand_button.trigger("click").hide();

	    $viewer
	      .attr("data-soundcloud-pageid", soundcloud_pageid) // https://soundcloud.com/dontbelievethehypenz/saxophonic-snack-manzhouli
	      .attr("data-soundcloud-trackid", soundcloud_trackid); // 291457131

	    $viewer_iframe
	      .attr("src", `//w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/${soundcloud_trackid}?auto_play=${autoplay}&hide_related=true&show_comments=false&show_user=false&show_reposts=false&visual=true`)
	      .attr("height", "368px")
	      .attr("aria-hidden", "false");

	    $viewer_img
	      .attr("aria-hidden", "true");
	  }
	  else if ( rwgps_pageid ) {

  		// expand viewer
  		$viewer.attr("data-expanded-contenttype", true);
  		$expand_button.trigger("click").hide();

	    $viewer
	      .attr("data-rwgps-pageid", rwgps_pageid); // https://ridewithgps.com/routes/18494494

	    $viewer_iframe
	      .attr("src", `//rwgps-embeds.com/routes/${rwgps_pageid}/embed`)
	      .attr("height", "500px" )
	      .attr("aria-hidden", "false");

	    embedHeightTimer = setTimeout( () => {
	      $viewer_iframe
	        .attr("height", "500px" );
	    }, 500);

	    $viewer_img
	      .attr("aria-hidden", "true");
	  }

	  else {
	  	// reset viewer type
	    $viewer
	      .removeAttr("data-vimeo-pageid")
	      .removeAttr("data-rwgps-pageid")
	      .removeAttr("data-soundcloud-pageid")
	      .removeAttr("data-soundcloud-trackid");

	    $viewer_iframe
	      .removeAttr("src")
	      .attr("height", "auto")
	      .attr("aria-hidden", "true");

	    $viewer_img
	      .attr("aria-hidden", "false");

	      clearTimeout(embedHeightTimer);
	  }
	},

	/**
	 * Update the gallery panorama
	 * @param {object} $ - jQuery
	 * @param {object} $viewer - jQuery gallery viewer
	 * @param {object} $gallery_item_link - jQuery gallery thumbnail link
	 * @uses https://stackoverflow.com/a/17308232/6850747
	 * @since 3.0.0
	 * @todo Add startPosition parameter in media.php (panorama_position_x)
	 */
	gallery_viewer_panorama_update: function($, $viewer, $gallery_item_link) {
		const panorama =         $gallery_item_link.find("img").data("panorama");
		const $expand_button =   $viewer.find(".gallery-viewer--expand");
		const $scroll_liner =    $viewer.find(".img-wrapper");
		const $gal = 			 $scroll_liner;
    	let galleryScrollTimer = undefined;
		let galleryScrollSetup = undefined;

		$gal.off(".galleryScroll");
		clearInterval(galleryScrollTimer);
		clearTimeout(galleryScrollSetup);

		if ( panorama ) {
			// expand viewer
			$viewer.attr("data-expanded-contenttype", true);
			$expand_button.trigger("click").hide();

			// this data attribute toggles the overflow-x scrollbar
			// which provides the correct $el[0].scrollWidth value
			$viewer.attr("data-panorama", panorama);

			// timeout ensures that the related CSS has taken effect
			galleryScrollSetup = setTimeout( () => {

				const galW = $gal.outerWidth(true); // setTimeout reqd for this value
				const galSW = $gal[0].scrollWidth;
				const wDiff = (galSW / galW) - 1; // widths difference ratioÅ
				const mPadd = 75; // Mousemove padding
				const damp = 20; // Mousemove response softness
				let mX = 0; // Real mouse position
				let mX2 = 0; // Modified mouse position
				let posX = 0;
				const mmAA = galW - (mPadd * 2); // The mousemove available area
				const mmAAr = (galW / mmAA); // get available mousemove difference ratio
				let tabindex = null;

				$gal
					.on("mousemove.galleryScroll", function(event) {
						mX = event.pageX - $(this).offset().left;
						mX2 = Math.min(Math.max(0, mX - mPadd), mmAA) * mmAAr;
					})
					.on("mouseenter.galleryScroll", () => {
						galleryScrollTimer = setInterval(() => {
							posX += (mX2 - posX) / damp; // zeno's paradox equation "catching delay"
							$gal.scrollLeft(posX * wDiff);
						}, 10);

						tabindex = $viewer.attr("data-tabindex");

						if ( tabindex ) {
							$viewer
								.attr("tabindex", tabindex)
								.removeAttr("data-tabindex");
						}

					})
					.on("mouseleave.galleryScroll", () => {
						clearInterval(galleryScrollTimer);
					})
					.on("mousedown.galleryScroll", () => {
						// Use the scroll bar without fighting the cursor-based panning
						clearInterval(galleryScrollTimer);

						// Prevent viewer container from stealing the focus
						tabindex = $viewer.attr("tabindex");

						if ( tabindex ) {
							$viewer
								.attr("data-tabindex", tabindex)
								.removeAttr("tabindex");
						}
					})
					.on("mouseup.galleryScroll", () => {
						// Reactivate the cursor-based panning
						$gal.trigger("mouseenter.galleryScroll");
					});

			}, 100 );
		}
		else {
			// reset viewer type
			$gal.off(".galleryScroll");
			clearInterval(galleryScrollTimer);
			clearTimeout(galleryScrollSetup);
			$viewer.removeAttr("data-panorama");

			// reset viewer state
			$viewer.removeAttr("data-expanded-contenttype");
		}
	},

	/**
	 * Update the gallery image
	 * @param {object} $ - jQuery
	 * @param {object} $viewer - jQuery gallery viewer
	 * @param {object} $gallery_item_link - jQuery gallery thumbnail link
	 * @requires includes/attachment.php
	 * @since 3.0.0
	 */
	gallery_viewer_image_update: function($, $viewer, $gallery_item_link) {
	  const $expand_button = 		      $viewer.find(".gallery-viewer--expand");
	  const $gallery_item_image =     $gallery_item_link.find("img");
	  const gallery_item_image_alt =  $gallery_item_image.attr("alt");
	  const gallery_item_image_full = $gallery_item_link.attr("href");

	  // the generated enlargement
	  const viewer_id =               $viewer.attr("id");
	  const $viewer_wrapper =         $viewer.find(".stack--wrapper");

	  // the other gallery items
	  const $gallery_item_links = 	 $(`[aria-controls="${viewer_id}"]`);

	  // unview existing thumbnail and reinstate into tab order
	  $gallery_item_links
	    .removeAttr("data-viewing")
	    .removeAttr("tabindex");

	  // view the selected thumbnail and remove from tab order
	  $gallery_item_link
	    .attr("data-viewing", true)
	    .attr("tabindex", "-1");

	  // set the source of the large image which is uncropped
	  // after gallery_viewer_panorama_update
	  const $viewer_img = $viewer.find("img");

	  $viewer_img
	    .attr("src", gallery_item_image_full)
	    .attr("alt", gallery_item_image_alt);

	  // store the collapsed state so when can revert it after expanding->collapsing the viewer
		$viewer_img.attr("data-src-desktop", $viewer_img.attr("src"));

		// remove old stored data
	  $viewer_img.removeData();

	  // copy the data attributes
	  // note: not just the dataset, as data- attributes are used for DOM filtering
	  $.each(this.thumbnail_data, (key, value) => {
	  	$viewer_img.removeAttr(`data-${value}`);
	  	$viewer_img.attr(`data-${value}`, $gallery_item_image.attr(`data-${value}`));
	  });

		// setup viewer
		$expand_button.trigger("click");

	  // focus the viewer, if the user requested this
	  /*
	  if ( wpdtrt_gallery_ui.gallery_viewer_setup ) {
	    $viewer
	      .attr("tabindex", "-1")
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
    const $section_gallery = $section.find(".gallery");
    const section_id = $section.attr("id");
    let viewer_id = `${section_id}-viewer`;
    const $stack_link_viewer = $section.find(".stack_link_viewer");
    const $heading = $stack_link_viewer.find(".gallery-viewer--heading");
    const $stack_wrapper = $stack_link_viewer.find(".stack--wrapper");
    const $section_gallery_thumbnails =  $section_gallery.find("img");
    const $section_gallery_item_links =  $section_gallery.find("a");

    if ( $stack_link_viewer.attr("data-attachment") ) {
      return;
    }

    if ( ! $section_gallery.length ) {
      $stack_link_viewer
        .removeAttr("id")
        .removeAttr("data-expanded");

      $stack_wrapper
        .remove();

      return;
    }

    $stack_wrapper
      .attr("data-loading", true);

    $heading
      .after("<h3 class=\"says\">Gallery</h3>");

    $stack_link_viewer
      .attr("id", viewer_id)
      .attr("data-has-gallery", true);

    $stack_link_viewer.find(".gallery-viewer--header")
      .append(`<button id="${section_id}-viewer-expand" class="gallery-viewer--expand" aria-expanded="false" aria-controls="${viewer_id}"><span class="says">Show full image</span></button>`);

    const $expand_button = $(`#${section_id}-viewer-expand`);

    //$expand_button.attr("tabindex", 0);

    $expand_button.click( (event) => {

      const triggered = event.originalEvent ? false : true;

      // prevent the click bubbling up to the viewer, creating an infinite loop
      event.stopPropagation();

      wpdtrt_gallery_ui.gallery_viewer_toggle_expanded($, $expand_button, triggered);
    });

    $section_gallery_item_links.each( (i, item) => {
      wpdtrt_gallery_ui.gallery_viewer_a11y( $, $(item), viewer_id );
			wpdtrt_gallery_ui.gallery_viewer_copy_thumbnail_data_to_link( $, $(item) );
    });

    $section_gallery_item_links.click( (event) => {

      // don't load the WordPress media item page
      event.preventDefault();

      const $gallery_item_link = $(event.target);
      viewer_id = $gallery_item_link.attr("aria-controls");
      const $viewer = $(`#${viewer_id}`);

      // only update the viewer if a different thumbnail was selected
      if ( $gallery_item_link.data("viewing") ) {
        return;
      }

      wpdtrt_gallery_ui.gallery_viewer_reset($, $viewer);

      wpdtrt_gallery_ui.gallery_viewer_image_update($, $viewer, $gallery_item_link);

      wpdtrt_gallery_ui.gallery_viewer_panorama_update($, $viewer, $gallery_item_link);

      wpdtrt_gallery_ui.gallery_viewer_iframe_update($, $viewer, $gallery_item_link);

      wpdtrt_gallery_ui.gallery_viewer_caption_update($, $viewer, $gallery_item_link);

    });

    const $gallery_thumbnail_initial = $section_gallery_thumbnails.filter("[data-initial]");

    if ( $gallery_thumbnail_initial.length ) {
      // this is the item assigned the "initial" class in the media library "Gallery Link Additional CSS Classes" field
      // if there is more than one, select the first
      $gallery_thumbnail_initial.parents("a").eq(0)
        .click();
    }
    else {
      // automatically select the first gallery item to load its large image
      $section_gallery_item_links.eq(0)
        .click();
    }

    $stack_wrapper
      .removeAttr("data-loading");
	}
};

jQuery(document).ready( ($) => {
	const config = wpdtrt_gallery_config;
	console.log("wpdtrt_gallery_ui.init");
});

(function($) {
  $(window).on("load", () => {
    // defer load of .initial enlargements, to reduce initial load time for PageSpeed
    wpdtrt_gallery_ui.gallery_viewer_lazyinit($);
  });
})(jQuery);
