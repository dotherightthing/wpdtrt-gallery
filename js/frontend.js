/**
 * File: js/frontend.js
 *
 * Front-end scripting for public pages.
 *
 * Note:
 * - PHP variables are provided in wpdtrt_gallery_config.
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */

/* global jQuery, Waypoint */
/* eslint-disable func-names, camelcase */

/**
 * Object: wpdtrt_gallery_ui
 */
const wpdtrt_gallery_ui = {
  thumbnailData: [
    'id',
    'initial',
    'latitude',
    'longitude',
    'panorama',
    'rwgps-pageid',
    'soundcloud-pageid',
    'soundcloud-trackid',
    'src-desktop-expanded',
    'vimeo-pageid'
  ],

  /**
   * Method: galleryViewerCopyThumbnailDataToLink
   *
   * Copy data- attributes from the thumbnail image,
   * to the surrounding link,
   * to support CSS icons.
   *
   * Parameters:
   *   (object) $ - jQuery
   *   (object) $galleryItemLink - jQuery gallery thumbnail link
   *
   * See:
   * - <https://github.com/dotherightthing/wpdtrt-gallery/issues/51>
   */
  galleryViewerCopyThumbnailDataToLink: ( $, $galleryItemLink ) => {
    const $galleryItemImage = $galleryItemLink.find( 'img' );

    // copy the data attributes
    $.each( wpdtrt_gallery_ui.thumbnailData, ( key, value ) => {
      $galleryItemLink
        .attr( `data-${value}`, $galleryItemImage.attr( `data-${value}` ) );
    } );
  },

  /**
   * Method: galleryViewerLazyInit
   *
   * Lazyload a gallery viewer when it is scrolled into view
   * to defer automatic loading of initial enlargements.
   *
   * Parameters:
   *   (object) $ - jQuery
   *
   * Requires:
   *   includes/attachment.php
   *
   * See:
   * - <https://varvy.com/pagespeed/defer-images.html>
   *
   * Since:
   *   3.0.0 - Added
   */
  galleryViewerLazyInit: ( $ ) => {
    const $sections = $( 'section' );

    $sections.each( ( i, item ) => {
      let $section = $( item );

      let inview = new Waypoint.Inview( {
        element: $section[ 0 ],
        enter: () => {
          wpdtrt_gallery_ui.galleryViewerInit( $, $section );
          inview.destroy();
        }
      } );
    } );
  },

  /**
   * Method: galleryViewerA11y
   *
   * Add accessibility attributes to the gallery viewer.
   *
   * Parameters:
   *   (object) $ - jQuery
   *   (object) $galleryItemLink - jQuery gallery thumbnail link
   *   (string) viewerId - viewer ID
   *
   * Requires:
   *   includes/attachment.php
   *
   * Since:
   *   3.0.0 - Added
   */
  galleryViewerA11y: ( $, $galleryItemLink, viewerId ) => {
    $galleryItemLink
      .attr( 'aria-controls', viewerId );
  },

  /**
   * Method: galleryViewerReset
   *
   * Clean up gallery viewer attributes
   * to prevent cross contamination between image types.
   *
   * Parameters:
   *   (object) $ - jQuery
   *   (object) $viewer - jQuery gallery viewer
   *
   * Since:
   *   1.3.0 - Added
   */
  galleryViewerReset: ( $, $viewer ) => {
    const $expandButton = $viewer.find( '.gallery-viewer--expand' );

    // remove forced expand used by panorama & iframe viewers
    $viewer.removeAttr( 'data-expanded-contenttype' );
    $expandButton.show();

    // reset the viewer state:
    // to the forced state reqd by the content type, or
    // to the last state chosen by the user - if that works..., or
    // to the default state of false
    $viewer.removeAttr( 'data-expanded' );
  },

  /**
   * Method: stringToBoolean
   *
   * Convert expanded state from a data-attribute string to a boolean.
   *
   * Parameters:
   *   (str) str - String
   *
   * Returns:
   *   (boolean) - viewerIsExpanded
   *
   * Since:
   *   1.3.0 - Added
   */
  stringToBoolean: function ( str ) {
    let bool = false;

    if ( str === 'true' ) {
      bool = true;
    } else if ( str === 'false' ) {
      bool = false;
    }

    return bool;
  },

  /**
   * Method: galleryViewerToggleExpanded
   *
   * Expand or collapse the gallery viewer.
   *
   * Parameters:
   *   (object) $ - jQuery
   *   (object) $expandButton - jQuery gallery expand button
   *   (boolean) triggered - Programmatically triggered
   *
   * Returns:
   *   (boolean) - viewerIsExpanded
   *
   * Requires:
   *   includes/attachment.php ?
   *
   * Since:
   *   3.0.0 - Added
   */
  galleryViewerToggleExpanded: function ( $, $expandButton, triggered ) {
    const $expandButtonText = $expandButton.find( '.says' );
    const viewerId = $expandButton.attr( 'aria-controls' );
    const $viewer = $( `#${viewerId}` );
    const $viewerImg = $viewer.find( 'img' );
    const $viewerIframe = $viewer.find( 'iframe' );

    // read data- attributes from
    const rwgpsPageId = $viewer.data( 'rwgps-pageid' );

    // the actual state
    const viewerIsExpanded = $viewer.attr( 'data-expanded' );

    // post-toggle states to reinstate
    let userExpandedSaved = $viewer.attr( 'data-expanded-user' );

    // this is problematic
    // because when the thumbnail is clicked
    // this attribute is still hanging around if used previously
    // because of the sequence in which these Update functions run
    // and clicking the thumbnail counts as a trigger for some reason
    // there needs to be a cleanup or destroy or unload function
    // which strips out all the fancy stuff
    // except info we want to retain such as the user state
    // which could always be left there
    // and if a type has some other data, that could trump it
    const dataExpandedContentType = $viewer.attr( 'data-expanded-contenttype' );

    // ------------------------------
    // update state
    // ------------------------------

    if ( triggered && dataExpandedContentType ) {
      // A - forced content state
      // data-expanded-contenttype will only be present if the content type requires it
      // and the button will be hidden so the user won't have to fight it
      // set viewer state to state required by content type
      $viewer
        .attr( 'data-expanded', dataExpandedContentType );

      // this attribute is removed by the Reset function
    } else if ( triggered && userExpandedSaved ) {
      // B - last user state, only if triggered so we don't fight the user
      // user state may be present when moving from e.g. an image to an iframe and back
      // NOTE: clicking on a thumbnail also fires the trigger.....
      // set viewer state to last/saved toggle state
      $viewer
        .attr( 'data-expanded', userExpandedSaved );

      // don't discard attribute after use
      // so we can reinstate this when switching between types
      // $viewer.removeAttr( 'data-expanded-user' );
    } else if ( triggered ) {
      // C - clicking on a thumbnail before the button has been clicked yet
      // no preference saved - so use the default
      $viewer
        .attr( 'data-expanded', false );
    } else {
      // D - toggle - clicking on the expand button to manually and explicitly toggle the state
      // sets the value of data-expanded-user if it is missing
      // save the inverse of the current state
      // as that is the state that we are changing TO
      // this will result in B being used from now on

      $viewer
        .attr( 'data-expanded-user', !this.stringToBoolean( viewerIsExpanded ) );

      // for clarity
      userExpandedSaved = !this.stringToBoolean( viewerIsExpanded );

      // use the saved user value
      $viewer
        .attr( 'data-expanded', userExpandedSaved );
    }

    // ------------------------------
    // update src image & button text
    // ------------------------------

    // if the viewer is now expanded
    if ( $viewer.attr( 'data-expanded' ) === 'true' ) {
      if ( !$viewerImg.data( 'panorama' ) ) {
        $viewerImg
          .attr( 'src', $viewerImg.data( 'src-desktop-expanded' ) );
      }

      $expandButton
        .attr( 'aria-expanded', true );

      // update the hidden button text
      $expandButtonText.text( 'Show cropped image' );
    } else if ( $viewer.attr( 'data-expanded' ) === 'false' ) {
      // if the viewer is now collapsed

      if ( !$viewerImg.data( 'panorama' ) ) {
        $viewerImg
          .attr( 'src', $viewerImg.data( 'src-desktop' ) );
      }

      $expandButton
        .attr( 'aria-expanded', false );

      // update the hidden button text
      $expandButtonText.text( 'Show full image' );

      if ( !triggered ) {
        // scroll to the top of the viewer
        $viewer.scrollView( 100, 150 );
      }
    }

    // update iframe size
    if ( rwgpsPageId ) {
      $viewerIframe
        .attr( 'height', $viewerImg.height() );
    }

    // focus the viewer
    if ( !triggered ) {
      $viewer
        .attr( 'tabindex', '-1' )
        .focus();
    }

    return ( !viewerIsExpanded );
  },

  /**
   * Method: galleryViewerCaptionUpdate
   *
   * Update the gallery caption to match the selected image.
   *
   * Parameters:
   *   (object) $ - jQuery
   *   (object) $viewer - jQuery gallery viewer
   *   (object) $galleryItemLink - jQuery gallery thumbnail link
   *
   * Returns:
   *   (string) galleryItemCaption
   *
   * Requires:
   *   includes/attachment.php
   *
   * Since:
   *   3.0.0 - Added
   */
  galleryViewerCaptionUpdate: function ( $, $viewer, $galleryItemLink ) {
    const $viewerCaption = $viewer.find( '.gallery-viewer--caption' );
    const galleryItemCaption = $.trim( $galleryItemLink.parent().next( 'figcaption' ).text() );

    // set the text of the large image caption
    $viewerCaption
      .html( `${galleryItemCaption} <span class='says'>(enlargement)</span>` );

    return galleryItemCaption;
  },

  /**
   * Method: galleryViewerIframeUpdate
   *
   * Update the gallery iframe to display a video, audio file, or interactive map.
   *
   * Parameters:
   *   (object) $ - jQuery
   *   (object) $viewer - jQuery gallery viewer
   *   (object) $galleryItemLink - jQuery gallery thumbnail link
   *
   * Requires:
   *  includes/attachment.php
   *
   * Since:
   *   3.0.0 - Added
   */
  galleryViewerIframeUpdate: function ( $, $viewer, $galleryItemLink ) {
    const $viewerIframe = $viewer.find( 'iframe' );
    const $viewerImg = $viewer.find( 'img' );
    // const $galleryItem = $galleryItemLink.parent().parent();
    const vimeoPageId = $galleryItemLink.find( 'img' ).data( 'vimeo-pageid' );
    const soundcloudPageId = $galleryItemLink.find( 'img' ).data( 'soundcloud-pageid' );
    const soundcloudTrackId = $galleryItemLink.find( 'img' ).data( 'soundcloud-trackid' );
    const rwgpsPageId = $galleryItemLink.find( 'img' ).data( 'rwgps-pageid' );
    // const isDefault = $galleryItemLink.find( 'img' ).data( 'initial' );

    const $expandButton = $viewer.find( '.gallery-viewer--expand' );

    let embedHeightTimer;

    // Disabled as working but inconsistent
    // TODO update this to only apply on page load - i.e. if triggered (#31)
    const autoplay = false; // true;

    // if first thumbnail in the gallery or
    // not first thumbnail but elected to display first
    // if ( $galleryItem.is( ':first-child' ) || isDefault ) {
    //  autoplay = false;
    // }

    // set the src of the video iframe and unhide it
    if ( vimeoPageId ) {
      // expand viewer
      $viewer
        .attr( 'data-expanded-contenttype', true );

      $expandButton.trigger( 'click' ).hide();

      $viewer
        .attr( 'data-vimeo-pageid', vimeoPageId );

      // adapted from https://appleple.github.io/modal-video/
      $viewerIframe
        .attr( {
          src: `//player.vimeo.com/video/${vimeoPageId}?api=false&autopause=${!autoplay}&autoplay=${autoplay}&byline=false&loop=false&portrait=false&title=false&xhtml=false`,
          allowfullscreen: 'true',
          title: 'Vimeo player',
          'aria-hidden': 'false'
        } )
        .css( 'height', 368 );

      $viewerImg
        .attr( 'aria-hidden', 'true' );
    } else if ( soundcloudPageId && soundcloudTrackId ) {
      // expand viewer
      $viewer
        .attr( 'data-expanded-contenttype', true );

      $expandButton.trigger( 'click' ).hide();

      $viewer
        .attr( {
          'data-soundcloud-pageid': soundcloudPageId, // https://soundcloud.com/dontbelievethehypenz/saxophonic-snack-manzhouli
          'data-soundcloud-trackid': soundcloudTrackId // 291457131
        } );

      $viewerIframe
        .attr( {
          src: `//w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/${soundcloudTrackId}?auto_play=${autoplay}&hide_related=true&show_comments=false&show_user=false&show_reposts=false&visual=true`,
          title: 'SoundCloud player',
          'aria-hidden': 'false'
        } )
        .css( 'height', 368 );

      $viewerImg
        .attr( 'aria-hidden', 'true' );
    } else if ( rwgpsPageId ) {
      // expand viewer
      $viewer
        .attr( 'data-expanded-contenttype', true );

      $expandButton.trigger( 'click' ).hide();

      $viewer
        .attr( 'data-rwgps-pageid', rwgpsPageId ); // https://ridewithgps.com/routes/18494494

      $viewerIframe
        .attr( {
          src: `//rwgps-embeds.com/routes/${rwgpsPageId}/embed`,
          title: 'Ride With GPS map viewer',
          'aria-hidden': 'false'
        } )
        .css( 'height', 500 );

      embedHeightTimer = setTimeout( () => {
        $viewerIframe
          .css( 'height', 500 );
      }, 500 );

      $viewerImg
        .attr( 'aria-hidden', 'true' );
    } else {
      // reset viewer type
      $viewer
        .removeAttr( 'data-vimeo-pageid data-rwgps-pageid data-soundcloud-pageid data-soundcloud-trackid' );

      $viewerIframe
        .removeAttr( 'src allowfullscreen' )
        .attr( {
          title: 'Gallery media viewer',
          'aria-hidden': 'true'
        } );

      $viewerImg
        .attr( 'aria-hidden', 'false' );

      clearTimeout( embedHeightTimer );
    }
  },

  /**
   * Method: galleryViewerPanoramaUpdate
   *
   * Update the gallery panorama.
   *
   * Parameters:
   *   (object) $ - jQuery
   *   (object) $viewer - jQuery gallery viewer
   *   (object) $galleryItemLink - jQuery gallery thumbnail link
   *
   * Uses:
   *   <https://stackoverflow.com/a/17308232/6850747>
   *
   * Since:
   *   3.0.0 - Added
   *
   * TODO:
   * - Add startPosition parameter in media.php (panoramaPositionX)
   * - Error - #57
   */
  galleryViewerPanoramaUpdate: function ( $, $viewer, $galleryItemLink ) {
    const panorama = $galleryItemLink.find( 'img' ).data( 'panorama' );
    const $expandButton = $viewer.find( '.gallery-viewer--expand' );
    const $scrollLiner = $viewer.find( '.img-wrapper' );
    const $gal = $scrollLiner;
    let galleryScrollTimer;
    let galleryScrollSetup;

    $gal.off( '.galleryScroll' );
    clearInterval( galleryScrollTimer );
    clearTimeout( galleryScrollSetup );

    if ( panorama ) {
      // expand viewer
      $viewer
        .attr( 'data-expanded-contenttype', true );

      $expandButton.trigger( 'click' ).hide();

      // this data attribute toggles the overflow-x scrollbar
      // which provides the correct $el[ 0 ].scrollWidth value
      $viewer
        .attr( 'data-panorama', panorama );

      // timeout ensures that the related CSS has taken effect
      galleryScrollSetup = setTimeout( () => {
        const galW = $gal.outerWidth( true ); // setTimeout reqd for this value
        const galSW = $gal[ 0 ].scrollWidth;
        const wDiff = ( galSW / galW ) - 1; // widths difference ratioÅ
        const mPadd = 75; // Mousemove padding
        const damp = 20; // Mousemove response softness
        let mX = 0; // Real mouse position
        let mX2 = 0; // Modified mouse position
        let posX = 0;
        const mmAA = galW - ( mPadd * 2 ); // The mousemove available area
        const mmAAr = ( galW / mmAA ); // get available mousemove difference ratio
        let tabindex = null;

        $gal
          .on( 'mousemove.galleryScroll', function ( event ) {
            mX = event.pageX - $( this ).offset().left;
            mX2 = Math.min( Math.max( 0, mX - mPadd ), mmAA ) * mmAAr;
          } )
          .on( 'mouseenter.galleryScroll', () => {
            galleryScrollTimer = setInterval( () => {
              posX += ( mX2 - posX ) / damp; // zeno's paradox equation 'catching delay'
              $gal.scrollLeft( posX * wDiff );
            }, 10 );

            tabindex = $viewer.attr( 'data-tabindex' );

            if ( tabindex ) {
              $viewer
                .attr( 'tabindex', tabindex )
                .removeAttr( 'data-tabindex' );
            }
          } )
          .on( 'mouseleave.galleryScroll', () => {
            clearInterval( galleryScrollTimer );
          } )
          .on( 'mousedown.galleryScroll', () => {
            // Use the scroll bar without fighting the cursor-based panning
            clearInterval( galleryScrollTimer );

            // Prevent viewer container from stealing the focus
            tabindex = $viewer.attr( 'tabindex' );

            if ( tabindex ) {
              $viewer
                .attr( 'data-tabindex', tabindex )
                .removeAttr( 'tabindex' );
            }
          } )
          .on( 'mouseup.galleryScroll', () => {
            // Reactivate the cursor-based panning
            $gal.trigger( 'mouseenter.galleryScroll' );
          } );
      }, 100 );
    } else {
      // reset viewer type
      $gal.off( '.galleryScroll' );
      clearInterval( galleryScrollTimer );
      clearTimeout( galleryScrollSetup );
      $viewer.removeAttr( 'data-panorama' );

      // reset viewer state
      $viewer.removeAttr( 'data-expanded-contenttype' );
    }
  },

  /**
   * Method: galleryViewerImageUpdate
   *
   * Update the gallery image.
   *
   * Parameters:
   *   (object) $ - jQuery
   *   (object) $viewer - jQuery gallery viewer
   *   (object) $galleryItemLink - jQuery gallery thumbnail link
   *
   * Requires:
   *   includes/attachment.php
   *
   * Since:
   *   3.0.0 - Added
   */
  galleryViewerImageUpdate: function ( $, $viewer, $galleryItemLink ) {
    const $expandButton = $viewer.find( '.gallery-viewer--expand' );
    const $galleryItemImage = $galleryItemLink.find( 'img' );
    const galleryItemImageAlt = $galleryItemImage.attr( 'alt' );
    const galleryItemImageFull = $galleryItemLink.attr( 'href' );

    // the generated enlargement
    const viewerId = $viewer.attr( 'id' );
    // const $viewerWrapper = $viewer.find( '.stack--wrapper' );

    // the other gallery items
    const $galleryItemLinks = $( `[aria-controls='${viewerId}']` );

    // unview existing thumbnail and reinstate into tab order
    $galleryItemLinks
      .removeAttr( 'data-viewing tabindex' );

    // view the selected thumbnail and remove from tab order
    $galleryItemLink
      .attr( {
        'data-viewing': true,
        tabindex: '-1'
      } );

    // set the source of the large image which is uncropped
    // after galleryViewerPanoramaUpdate
    if ( !$viewer.find( 'img' ).length ) {
      $viewer.find( '.img-wrapper' ).append( '<img/>' );
    }

    const $viewerImg = $viewer.find( 'img' );

    $viewerImg
      .attr( {
        src: galleryItemImageFull,
        alt: galleryItemImageAlt
      } );

    // store the collapsed state so when can revert it after expanding->collapsing the viewer
    $viewerImg
      .attr( 'data-src-desktop', $viewerImg.attr( 'src' ) );

    // remove old stored data
    $viewerImg.removeData();

    // copy the data attributes
    // note: not just the dataset, as data- attributes are used for DOM filtering
    $.each( this.thumbnailData, ( key, value ) => {
      $viewerImg
        .removeAttr( `data-${value}` )
        .attr( `data-${value}`, $galleryItemImage.attr( `data-${value}` ) );
    } );

    // setup viewer
    $expandButton.trigger( 'click' );

    // focus the viewer, if the user requested this
    /*
    if ( wpdtrt_gallery_ui.galleryViewerSetup ) {
      $viewer
        .attr( 'tabindex', '-1' )
        .focus();
    }
    */
  },

  /**
   * Method: galleryViewerTrack
   *
   * Only track clicks when these
   * were initiated by the user,
   * not by the setup script.
   * The trackingClass is used to define
   * the click target in GTM.
   *
   * Parameters:
   *   (object) $ - jQuery
   *   (object) $element - jQuery element
   *
   * Since:
   *   1.8.7 - DTRT Gallery - Added
   */
  galleryViewerTrack: function ( $, $element ) {
    const trackingClass = 'gallery-viewer--track';

    $element
      .on( 'mouseenter focus', function () {
        $( this ).addClass( trackingClass );
      } )
      .on( 'mouseleave blur', function () {
        $( this ).removeClass( trackingClass );
      } );
  },

  /**
   * Method: galleryViewerInit
   *
   * Initialise a gallery viewer.
   *
   * Parameters:
   *   (object) $ - jQuery
   *   (object) $section - jQuery page section
   *
   * Requires:
   *   includes/attachment.php
   *
   * Since:
   *   3.0.0 - Added
   */
  galleryViewerInit: function ( $, $section ) {
    const $sectionGallery = $section.find( '.gallery' );
    const sectionId = $section.attr( 'id' );
    let viewerId = `${sectionId}-viewer`;
    const $stackLinkViewer = $section.find( '.stack_link_viewer' );
    const $heading = $stackLinkViewer.find( '.gallery-viewer--heading' );
    const $stackWrapper = $stackLinkViewer.find( '.stack--wrapper' );
    const $sectionGalleryThumbnails = $sectionGallery.find( 'img' );
    const $sectionGalleryItemLinks = $sectionGallery.find( 'a' );

    if ( $stackLinkViewer.attr( 'data-attachment' ) ) {
      return;
    }

    if ( !$sectionGallery.length ) {
      $stackLinkViewer
        .removeAttr( 'id data-expanded' );

      $stackWrapper
        .remove();

      return;
    }

    $stackWrapper
      .attr( 'data-loading', true );

    $heading
      .after( '<h3 class=\'says\'>Gallery</h3>' );

    $stackLinkViewer
      .attr( {
        id: viewerId,
        'data-has-gallery': true
      } );

    $stackLinkViewer.find( '.gallery-viewer--header' )
      .append( `<button id='${sectionId}-viewer-expand' class='gallery-viewer--expand' aria-expanded='false' aria-controls='${viewerId}'><span class='says'>Show full image</span></button>` );

    const $expandButton = $( `#${sectionId}-viewer-expand` );

    wpdtrt_gallery_ui.galleryViewerTrack( $, $expandButton );

    // $expandButton.attr( 'tabindex', 0);

    $expandButton.click( ( event ) => {
      const triggered = !event.originalEvent;

      // prevent the click bubbling up to the viewer, creating an infinite loop
      event.stopPropagation();

      wpdtrt_gallery_ui
        .galleryViewerToggleExpanded( $, $expandButton, triggered );
    } );

    $sectionGalleryItemLinks.each( ( i, item ) => {
      wpdtrt_gallery_ui.galleryViewerA11y( $, $( item ), viewerId );
      wpdtrt_gallery_ui.galleryViewerCopyThumbnailDataToLink( $, $( item ) );
      wpdtrt_gallery_ui.galleryViewerTrack( $, $( item ) );
    } );

    $sectionGalleryItemLinks.click( ( event ) => {
      // don't load the WordPress media item page
      event.preventDefault();

      const $galleryItemLink = $( event.target );
      viewerId = $galleryItemLink.attr( 'aria-controls' );
      const $viewer = $( `#${viewerId}` );

      // only update the viewer if a different thumbnail was selected
      if ( $galleryItemLink.data( 'viewing' ) ) {
        return;
      }

      wpdtrt_gallery_ui
        .galleryViewerReset( $, $viewer );

      wpdtrt_gallery_ui
        .galleryViewerImageUpdate( $, $viewer, $galleryItemLink );

      wpdtrt_gallery_ui
        .galleryViewerPanoramaUpdate( $, $viewer, $galleryItemLink );

      wpdtrt_gallery_ui
        .galleryViewerIframeUpdate( $, $viewer, $galleryItemLink );

      wpdtrt_gallery_ui
        .galleryViewerCaptionUpdate( $, $viewer, $galleryItemLink );
    } );

    const $galleryThumbnailInitial = $sectionGalleryThumbnails.filter( '[data-initial]' );

    if ( $galleryThumbnailInitial.length ) {
      // this is the item assigned the 'initial' class in the media library 'Gallery Link Additional CSS Classes' field
      // if there is more than one, select the first
      $galleryThumbnailInitial.parents( 'a' ).eq( 0 )
        .click();
    } else {
      // automatically select the first gallery item to load its large image
      $sectionGalleryItemLinks.eq( 0 )
        .click();
    }

    $stackWrapper
      .removeAttr( 'data-loading' );
  }
};

// http://stackoverflow.com/a/28771425
document.addEventListener( 'touchstart', () => {
  // nada, this is just a hack to make :focus state render on touch
}, false );

// https://api.jquery.com/jQuery.noConflict/
/* eslint-disable wrap-iife */
( function ( $ ) {
  $( window ).on( 'load', () => {
    // defer load of .initial enlargements, to reduce initial load time for PageSpeed
    wpdtrt_gallery_ui.galleryViewerLazyInit( $ );
  } );
} )( jQuery );
/* eslint-enable wrap-iife */

jQuery( document ).ready( () => {
  // const config = wpdtrt_gallery_config;
} );
