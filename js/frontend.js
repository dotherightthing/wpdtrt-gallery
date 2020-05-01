/**
 * @file js/frontend.js
 * @summary Front-end scripting for public pages.
 * @description PHP variables are provided in wpdtrt_gallery_config.
 * @requires DTRT WordPress Plugin Boilerplate Generator 0.8.13
 */

/* global jQuery */
/* eslint-disable func-names, camelcase */

/**
 * jQuery object
 *
 * @external jQuery
 * @see {@link http://api.jquery.com/jQuery/}
 */

/**
 * @namespace wpdtrtGalleryUi
 */
const wpdtrtGalleryUi = {
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
     * @function galleryViewerCopyThumbnailDataToLink
     * @summary Copy data- attributes from the thumbnail image, to the surrounding link, to support CSS icons.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $galleryItemLink - jQuery gallery thumbnail link
     * {@link https://github.com/dotherightthing/wpdtrt-gallery/issues/51}
     */
    galleryViewerCopyThumbnailDataToLink: ($, $galleryItemLink) => {
        const $galleryItemImage = $galleryItemLink.find('img');

        // copy the data attributes
        $.each(wpdtrtGalleryUi.thumbnailData, (key, value) => {
            $galleryItemLink
                .attr(`data-${value}`, $galleryItemImage.attr(`data-${value}`));
        });
    },

    /**
     * @function galleryViewerLazyInit
     * @summary Lazyload a gallery viewer when it is scrolled into view
     * to defer automatic loading of initial enlargements.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @requires includes/attachment.php
     * {@link https://varvy.com/pagespeed/defer-images.html}
     * @since 3.0.0
     */
    galleryViewerLazyInit: ($) => {
        const $sections = $('.wpdtrt-gallery__section');
        let $section;

        /**
         * Respond to an observed intersection.
         *
         * @param {object} changes - Observed changes
         * @param {object} observer - Intersection Observer
         */
        function onChange(changes, observer) {
            changes.forEach(change => {
                // ratio of the element which is visible
                if (change.intersectionRatio > 0.1) {
                    let $inView = $(change.target);

                    wpdtrtGalleryUi.galleryViewerInit($, $inView);
                    observer.unobserve(change.target);
                }
            });
        }

        if ('IntersectionObserver' in window) {
            let observer = new IntersectionObserver(onChange, {
                root: null, // relative to document viewport
                rootMargin: '0px', // margin around root, unitless values not allowed
                threshold: 0.1 // visible amount of item shown in relation to root
            });

            $sections.each((i, item) => {
                // add element to the set being watched by the IntersectionObserver
                observer.observe($(item).get(0));
            });
        } else {
            $sections.each(() => {
                wpdtrtGalleryUi.galleryViewerInit($, $section);
            });
        }
    },

    /**
     * @function galleryViewerA11y
     * @summary Add accessibility attributes to the gallery viewer.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $galleryItemLink - jQuery gallery thumbnail link
     * @param {string} viewerId - viewer ID
     * @requires includes/attachment.php
     * @since 3.0.0
     */
    galleryViewerA11y: ($, $galleryItemLink, viewerId) => {
        $galleryItemLink
            .attr('aria-controls', viewerId);
    },

    /**
     * @function galleryViewerReset
     * @summary Clean up gallery viewer attributes to prevent cross contamination between image types.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $viewer - jQuery gallery viewer
     * @since 1.3.0
     */
    galleryViewerReset: ($, $viewer) => {
        const $expandButton = $viewer.find('.wpdtrt-gallery-viewer__expand');

        // remove forced expand used by panorama & iframe viewers
        $viewer
            .removeAttr('data-always-expanded');

        $expandButton
            .removeAttr('aria-hidden');

        // reset the viewer state:
        // to the forced state reqd by the content type, or
        // to the last state chosen by the user - if that works..., or
        // to the default state of false
        $viewer
            .removeAttr('data-expanded');
    },

    /**
     * @function stringToBoolean
     * @summary Convert expanded state from a data-attribute string to a boolean.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {string} str - String
     *
     * @returns {boolean} viewerIsExpanded
     * @since 1.3.0
     */
    stringToBoolean: function (str) {
        let bool = false;

        if (str === 'true') {
            bool = true;
        } else if (str === 'false') {
            bool = false;
        }

        return bool;
    },

    /**
     * @function galleryViewerToggleExpanded
     * @summary Expand or collapse the gallery viewer.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $expandButton - jQuery gallery expand button
     * @param {boolean} triggered - Programmatically triggered
     * @returns {boolean} viewerIsExpanded
     * @requires includes/attachment.php ?
     * @since 3.0.0
     */
    galleryViewerToggleExpanded: function ($, $expandButton, triggered) {
        const $expandButtonText = $expandButton.find('.says');
        const viewerId = $expandButton.attr('aria-controls');
        const $viewer = $(`#${viewerId}`);
        const $viewerImg = $viewer.find('img');
        const $viewerIframe = $viewer.find('iframe');

        // read data- attributes from
        const rwgpsPageId = $viewer.data('rwgps-pageid');

        // the actual state
        const viewerIsExpanded = $viewer.attr('data-expanded');

        // post-toggle states to reinstate
        let userExpandedSaved = $viewer.attr('data-expanded-user');

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
        const isEmbed = $viewer.attr('data-always-expanded');

        // ------------------------------
        // update state
        // ------------------------------

        if (triggered && isEmbed) {
            // A - forced content state
            // data-always-expanded will only be present if the content type requires it
            // and the button will be hidden so the user won't have to fight it
            // set viewer state to state required by content type
            $viewer
                .attr('data-expanded', isEmbed);

            // this attribute is removed by the Reset function
        } else if (triggered && userExpandedSaved) {
            // B - last user state, only if triggered so we don't fight the user
            // user state may be present when moving from e.g. an image to an iframe and back
            // NOTE: clicking on a thumbnail also fires the trigger.....
            // set viewer state to last/saved toggle state
            $viewer
                .attr('data-expanded', userExpandedSaved);

            // don't discard attribute after use
            // so we can reinstate this when switching between types
            // $viewer.removeAttr('data-expanded-user');
        } else if (triggered) {
            // C - clicking on a thumbnail before the button has been clicked yet
            // no preference saved - so use the default
            $viewer
                .attr('data-expanded', false);
        } else {
            // D - toggle - clicking on the expand button to manually and explicitly toggle the state
            // sets the value of data-expanded-user if it is missing
            // save the inverse of the current state
            // as that is the state that we are changing TO
            // this will result in B being used from now on

            $viewer
                .attr('data-expanded-user', !this.stringToBoolean(viewerIsExpanded));

            // for clarity
            userExpandedSaved = !this.stringToBoolean(viewerIsExpanded);

            // use the saved user value
            $viewer
                .attr('data-expanded', userExpandedSaved);
        }

        // ------------------------------
        // update src image & button text
        // ------------------------------

        // if the viewer is now expanded
        if ($viewer.attr('data-expanded') === 'true') {
            if (!$viewerImg.data('panorama')) {
                $viewerImg
                    .attr('src', $viewerImg.data('src-desktop-expanded'));
            }

            $expandButton
                .attr('aria-expanded', true);

            // update the hidden button text
            $expandButtonText
                .text('Show cropped image');
        } else if ($viewer.attr('data-expanded') === 'false') {
            // if the viewer is now collapsed

            if (!$viewerImg.data('panorama')) {
                $viewerImg
                    .attr('src', $viewerImg.data('src-desktop'));
            }

            $expandButton
                .attr('aria-expanded', false);

            // update the hidden button text
            $expandButtonText.text('Show uncropped image');

            if (!triggered) {
            // scroll to the top of the viewer
                $viewer
                    .scrollView(100, 150);
            }
        }

        // update iframe size
        if (rwgpsPageId) {
            $viewerIframe
                .attr('height', $viewerImg.height());
        }

        // focus the viewer
        if (!triggered) {
            $viewer
                .attr('tabindex', '-1')
                .focus();
        }

        return (!viewerIsExpanded);
    },

    /**
     * @function galleryViewerCaptionUpdate
     * @summary Update the gallery caption to match the selected image.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $viewer - jQuery gallery viewer
     * @param {external:jQuery} $galleryItemLink - jQuery gallery thumbnail link
     * @returns {string} galleryItemCaption
     * @requires includes/attachment.php
     * @since 3.0.0
     */
    galleryViewerCaptionUpdate: function ($, $viewer, $galleryItemLink) {
        const $viewerCaption = $viewer.find('.wpdtrt-gallery-viewer__caption');
        const galleryItemCaption = $.trim($galleryItemLink.parent().next('figcaption').text());

        // set the text of the large image caption
        $viewerCaption
            .html(`${galleryItemCaption} <span class='says'>(enlargement)</span>`);

        return galleryItemCaption;
    },

    /**
     * @function galleryViewerIframeUpdate
     * @summary Update the gallery iframe to display a video, audio file, or interactive map.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $viewer - jQuery gallery viewer
     * @param {external:jQuery} $galleryItemLink - jQuery gallery thumbnail link
     * @requires includes/attachment.php
     * @since 3.0.0
     */
    galleryViewerIframeUpdate: function ($, $viewer, $galleryItemLink) {
        const $viewerIframe = $viewer.find('iframe');
        const $viewerImg = $viewer.find('img');
        // const $galleryItem = $galleryItemLink.parent().parent();
        const vimeoPageId = $galleryItemLink.find('img').data('vimeo-pageid');
        const soundcloudPageId = $galleryItemLink.find('img').data('soundcloud-pageid');
        const soundcloudTrackId = $galleryItemLink.find('img').data('soundcloud-trackid');
        const rwgpsPageId = $galleryItemLink.find('img').data('rwgps-pageid');
        // const isDefault = $galleryItemLink.find('img').data('initial');

        const $expandButton = $viewer.find('.wpdtrt-gallery-viewer__expand');

        let embedHeightTimer;

        // Disabled as working but inconsistent
        // TODO update this to only apply on page load - i.e. if triggered (#31)
        const autoplay = false; // true;

        // if first thumbnail in the gallery or
        // not first thumbnail but elected to display first
        // if ($galleryItem.is(':first-child') || isDefault) {
        //  autoplay = false;
        // }

        // set the src of the video iframe and unhide it
        if (vimeoPageId) {
            // expand viewer
            $viewer
                .attr('data-always-expanded', true);

            $expandButton.trigger('click')
                .attr('aria-hidden', 'true');

            $viewer
                .attr('data-vimeo-pageid', vimeoPageId);

            // adapted from https://appleple.github.io/modal-video/
            $viewerIframe
                .attr({
                    src: `//player.vimeo.com/video/${vimeoPageId}?api=false&autopause=${!autoplay}&autoplay=${autoplay}&byline=false&loop=false&portrait=false&title=false&xhtml=false`,
                    allowfullscreen: 'true',
                    title: 'Vimeo player',
                    'aria-hidden': 'false'
                })
                .css('height', 368);

            $viewerImg
                .attr('aria-hidden', 'true');
        } else if (soundcloudPageId && soundcloudTrackId) {
            // expand viewer
            $viewer
                .attr('data-always-expanded', true);

            $expandButton.trigger('click')
                .attr('aria-hidden', 'true');

            $viewer
                .attr({
                    'data-soundcloud-pageid': soundcloudPageId, // https://soundcloud.com/dontbelievethehypenz/saxophonic-snack-manzhouli
                    'data-soundcloud-trackid': soundcloudTrackId // 291457131
                });

            $viewerIframe
                .attr({
                    src: `//w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/${soundcloudTrackId}?auto_play=${autoplay}&hide_related=true&show_comments=false&show_user=false&show_reposts=false&visual=true`,
                    title: 'SoundCloud player',
                    'aria-hidden': 'false'
                })
                .css('height', 368);

            $viewerImg
                .attr('aria-hidden', 'true');
        } else if (rwgpsPageId) {
            // expand viewer
            $viewer
                .attr('data-always-expanded', true);

            $expandButton.trigger('click')
                .attr('aria-hidden', 'true');

            $viewer
                .attr('data-rwgps-pageid', rwgpsPageId); // https://ridewithgps.com/routes/18494494

            $viewerIframe
                .attr({
                    src: `//rwgps-embeds.com/routes/${rwgpsPageId}/embed`,
                    title: 'Ride With GPS map viewer',
                    'aria-hidden': 'false'
                })
                .css('height', 500);

            embedHeightTimer = setTimeout(() => {
                $viewerIframe
                    .css('height', 500);
            }, 500);

            $viewerImg
                .attr('aria-hidden', 'true');
        } else {
            // reset viewer type
            $viewer
                .removeAttr('data-vimeo-pageid data-rwgps-pageid data-soundcloud-pageid data-soundcloud-trackid');

            $viewerIframe
                .removeAttr('src allowfullscreen')
                .attr({
                    title: 'Gallery media viewer',
                    'aria-hidden': 'true'
                });

            $viewerImg
                .attr('aria-hidden', 'false');

            clearTimeout(embedHeightTimer);
        }
    },

    /**
     * @function galleryViewerPanoramaUpdate
     * @summary Update the gallery panorama.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $viewer - jQuery gallery viewer
     * @param {external:jQuery} $galleryItemLink - jQuery gallery thumbnail link
     * {@link https://stackoverflow.com/a/17308232/6850747}
     * @since 3.0.0
     * @todo Add startPosition parameter in media.php (panoramaPositionX)
     * @todo Error - #57
     */
    galleryViewerPanoramaUpdate: function ($, $viewer, $galleryItemLink) {
        const panorama = $galleryItemLink.find('img').data('panorama');
        const $expandButton = $viewer.find('.wpdtrt-gallery-viewer__expand');
        const $scrollLiner = $viewer.find('.wpdtrt-gallery-viewer__img-wrapper');
        const $gal = $scrollLiner;
        let galleryScrollTimer;
        let galleryScrollSetup;

        $gal.off('.galleryScroll');
        clearInterval(galleryScrollTimer);
        clearTimeout(galleryScrollSetup);

        if (panorama) {
            // expand viewer
            $viewer
                .attr('data-always-expanded', true);

            $expandButton.trigger('click')
                .attr('aria-hidden', 'true');

            // this data attribute toggles the overflow-x scrollbar
            // which provides the correct $el[ 0 ].scrollWidth value
            $viewer
                .attr('data-panorama', panorama);

            // TODO move into separate file, add a data- attribute
            // timeout ensures that the related CSS has taken effect
            galleryScrollSetup = setTimeout(() => {
            // if (!$gal.length) {
            //  return;
            // }

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
                    .on('mousemove.galleryScroll', function (event) {
                        mX = event.pageX - $(this).offset().left;
                        mX2 = Math.min(Math.max(0, mX - mPadd), mmAA) * mmAAr;
                    })
                    .on('mouseenter.galleryScroll', () => {
                        galleryScrollTimer = setInterval(() => {
                            posX += (mX2 - posX) / damp; // zeno's paradox equation 'catching delay'
                            $gal.scrollLeft(posX * wDiff);
                        }, 10);

                        tabindex = $viewer.attr('data-tabindex');

                        if (tabindex) {
                            $viewer
                                .attr('tabindex', tabindex)
                                .removeAttr('data-tabindex');
                        }
                    })
                    .on('mouseleave.galleryScroll', () => {
                        clearInterval(galleryScrollTimer);
                    })
                    .on('mousedown.galleryScroll', () => {
                        // Use the scroll bar without fighting the cursor-based panning
                        clearInterval(galleryScrollTimer);

                        // Prevent viewer container from stealing the focus
                        tabindex = $viewer.attr('tabindex');

                        if (tabindex) {
                            $viewer
                                .attr('data-tabindex', tabindex)
                                .removeAttr('tabindex');
                        }
                    })
                    .on('mouseup.galleryScroll', () => {
                        // Reactivate the cursor-based panning
                        $gal
                            .trigger('mouseenter.galleryScroll');
                    });
            }, 100);
        } else {
            // reset viewer type
            $gal
                .off('.galleryScroll');
            clearInterval(galleryScrollTimer);
            clearTimeout(galleryScrollSetup);
            $viewer
                .removeAttr('data-panorama');

            // reset viewer state
            $viewer
                .removeAttr('data-always-expanded');
        }
    },

    /**
     * @function galleryViewerImageUpdate
     * @summary Update the gallery image.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $viewer - jQuery gallery viewer
     * @param {external:jQuery} $galleryItemLink - jQuery gallery thumbnail link
     * @requires includes/attachment.php
     * @since 3.0.0
     */
    galleryViewerImageUpdate: function ($, $viewer, $galleryItemLink) {
        const $expandButton = $viewer.find('.wpdtrt-gallery-viewer__expand');
        const $galleryItemImage = $galleryItemLink.find('img');
        const galleryItemImageAlt = $galleryItemImage.attr('alt');
        const galleryItemImageFull = $galleryItemLink.attr('href');

        // the generated enlargement
        const viewerId = $viewer.attr('id');
        // const $viewerWrapper = $viewer.find('.wpdtrt-gallery-viewer__liner');

        // the other gallery items
        const $galleryItemLinks = $(`[aria-controls='${viewerId}']`);

        // unview existing thumbnail and reinstate into tab order
        $galleryItemLinks
            .attr('aria-expanded', false);

        // view the selected thumbnail
        $galleryItemLink
            .attr({
                'aria-expanded': true
            });

        // set the source of the large image which is uncropped
        // after galleryViewerPanoramaUpdate
        if (!$viewer.find('img').length) {
            $viewer.find('.wpdtrt-gallery-viewer__img-wrapper').append('<img/>');
        }

        const $viewerImg = $viewer.find('img');

        $viewerImg
            .attr({
                src: galleryItemImageFull,
                alt: galleryItemImageAlt
            });

        // store the collapsed state so when can revert it after expanding->collapsing the viewer
        $viewerImg
            .attr('data-src-desktop', $viewerImg.attr('src'));

        // remove old stored data
        $viewerImg.removeData();

        // copy the data attributes
        // note: not just the dataset, as data- attributes are used for DOM filtering
        $.each(this.thumbnailData, (key, value) => {
            $viewerImg
                .removeAttr(`data-${value}`)
                .attr(`data-${value}`, $galleryItemImage.attr(`data-${value}`));
        });

        // setup viewer
        $expandButton.trigger('click');

        // focus the viewer, if the user requested this
        /*
        if (wpdtrtGalleryUi.galleryViewerSetup) {
        $viewer
        .attr('tabindex', '-1')
        .focus();
        }
        */
    },

    /**
     * @function galleryViewerTrack
     * @summary Only track clicks when these were initiated by the user, not by the setup script.
     * @description The trackingClass is used to define the click target in GTM.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $element - jQuery element
     * @since 1.8.7
     */
    galleryViewerTrack: function ($, $element) {
        const trackingClass = 'wpdtrt-gallery-viewer--track';

        $element
            .on('mouseenter focus', function () {
                $(this).addClass(trackingClass);
            })
            .on('mouseleave blur', function () {
                $(this).removeClass(trackingClass);
            });
    },

    /**
     * @function galleryViewerInit
     * @summary Initialise a gallery viewer.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $section - .wpdtrt-gallery__section
     * @requires includes/attachment.php
     * @since 3.0.0
     */
    galleryViewerInit: function ($, $section) {
        const $sectionGallery = $section.find('.gallery');
        const sectionId = $section.attr('id');
        let viewerId = `${sectionId}-viewer`;
        const $stackLinkViewer = $section.find('.wpdtrt-gallery-viewer');
        const $stackWrapper2 = $stackLinkViewer.find('.wpdtrt-gallery-viewer__wrapper');
        const $stackWrapper = $stackLinkViewer.find('.wpdtrt-gallery-viewer__liner');
        const $sectionGalleryThumbnails = $sectionGallery.find('img');
        const $sectionGalleryItemLinks = $sectionGallery.find('a');

        // ?
        // if ($stackLinkViewer.attr('data-attachment')) {
        //     return;
        // }

        if (!$sectionGallery.length) {
            $stackLinkViewer
                .removeAttr('id data-expanded');

            $stackWrapper2
                .remove();

            return;
        }

        $stackWrapper
            .attr('data-loading', true);

        $stackLinkViewer
            .attr({
                id: viewerId,
                'data-enabled': true
            });

        $stackLinkViewer.find('.wpdtrt-gallery-viewer__header')
            .append(`<button id='${sectionId}-viewer-expand' class='wpdtrt-gallery-viewer__expand' aria-expanded='false' aria-controls='${viewerId}'><span class='says'>Show uncropped image</span></button>`);

        const $expandButton = $(`#${sectionId}-viewer-expand`);

        wpdtrtGalleryUi.galleryViewerTrack($, $expandButton);

        // $expandButton.attr('tabindex', 0);

        $expandButton.click((event) => {
            const triggered = !event.originalEvent;

            // prevent the click bubbling up to the viewer, creating an infinite loop
            event
                .stopPropagation();

            wpdtrtGalleryUi
                .galleryViewerToggleExpanded($, $expandButton, triggered);
        });

        $sectionGalleryItemLinks.each((i, item) => {
            wpdtrtGalleryUi.galleryViewerA11y($, $(item), viewerId);
            wpdtrtGalleryUi.galleryViewerCopyThumbnailDataToLink($, $(item));
            wpdtrtGalleryUi.galleryViewerTrack($, $(item));
        });

        $sectionGalleryItemLinks.click((event) => {
            // don't load the WordPress media item page
            event
                .preventDefault();

            const $galleryItemLink = $(event.target);
            viewerId = $galleryItemLink.attr('aria-controls');
            const $viewer = $(`#${viewerId}`);

            // only update the viewer if a different thumbnail was selected
            if ($galleryItemLink.attr('aria-expanded') === 'true') {
                return;
            }

            wpdtrtGalleryUi
                .galleryViewerReset($, $viewer);

            wpdtrtGalleryUi
                .galleryViewerImageUpdate($, $viewer, $galleryItemLink);

            wpdtrtGalleryUi
                .galleryViewerPanoramaUpdate($, $viewer, $galleryItemLink);

            wpdtrtGalleryUi
                .galleryViewerIframeUpdate($, $viewer, $galleryItemLink);

            wpdtrtGalleryUi
                .galleryViewerCaptionUpdate($, $viewer, $galleryItemLink);
        });

        const $galleryThumbnailInitial = $sectionGalleryThumbnails.filter('[data-initial]');

        if ($galleryThumbnailInitial.length) {
            // this is the item assigned the 'initial' class in the media library 'Gallery Link Additional CSS Classes' field
            // if there is more than one, select the first
            $galleryThumbnailInitial.parents('a').eq(0)
                .click();
        } else {
            // automatically select the first gallery item to load its large image
            $sectionGalleryItemLinks.eq(0)
                .click();
        }

        $stackWrapper
            .removeAttr('data-loading');
    }
};

// http://stackoverflow.com/a/28771425
document.addEventListener('touchstart', () => {
    // nada, this is just a hack to make :focus state render on touch
}, false);

// https://api.jquery.com/jQuery.noConflict/
/* eslint-disable wrap-iife */
(function ($) {
    $(window).on('load', () => {
        // defer initialisation of viewers, to reduce initial load time
        wpdtrtGalleryUi.galleryViewerLazyInit($);
    });
})(jQuery);
/* eslint-enable wrap-iife */

jQuery(document).ready(() => {
    // const config = wpdtrt_gallery_config;
});
