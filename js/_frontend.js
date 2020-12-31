/**
 * @file js/frontend.js
 * @summary Front-end scripting for public pages.
 * @description PHP variables are provided in wpdtrt_gallery_config.
 * @requires DTRT WordPress Plugin Boilerplate Generator 0.8.13
 */

/* globals jQuery, TabbedCarousel */
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
    animations: false,
    imgData: [
        'id',
        'initial',
        'latitude',
        'longitude',
        'panorama',
        'rwgps-pageid',
        'soundcloud-pageid',
        'soundcloud-trackid',
        'src-desktop',
        'src-desktop-expanded',
        'src-panorama',
        'vimeo-pageid'
    ],

    /**
     * @function galleryViewerAnimations
     * @summary Allow animations to be managed via browser preferences.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @see https://github.com/dotherightthing/accessibility-research
     */
    galleryViewerAnimations: function () {
        const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

        if (!mediaQuery || mediaQuery.matches) {
            wpdtrtGalleryUi.animations = false;
        } else {
            wpdtrtGalleryUi.animations = true;
        }

        mediaQuery.addEventListener('change', () => {
            if (mediaQuery.matches) {
                wpdtrtGalleryUi.animations = false;
            } else {
                wpdtrtGalleryUi.animations = true;
            }
        });
    },

    /**
     * @function galleryViewerCopyImgDataToTabpanel
     * @summary Copy data- attributes from the image, to the parent tabpanel, to support plugin JS.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $tabpanel - jQuery gallery tabpanel
     * {@link https://github.com/dotherightthing/wpdtrt-gallery/issues/51}
     */
    galleryViewerCopyImgDataToTabpanel: ($, $tabpanel) => {
        const $tabpanelImg = $tabpanel.find('img');

        // copy the data attributes
        $.each(wpdtrtGalleryUi.imgData, (key, value) => {
            $tabpanel
                .attr(`data-${value}`, $tabpanelImg.attr(`data-${value}`));
        });
    },

    /**
     * @function galleryViewerCopyImgDataToTab
     * @summary Copy data- attributes from the thumbnail image, to the parent tab, to support CSS icons.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $tab - jQuery gallery tab
     * {@link https://github.com/dotherightthing/wpdtrt-gallery/issues/51}
     */
    galleryViewerCopyImgDataToTab: ($, $tab) => {
        const $tabImage = $tab.find('img');

        // copy the data attributes
        $.each(wpdtrtGalleryUi.imgData, (key, value) => {
            $tab
                .attr(`data-${value}`, $tabImage.attr(`data-${value}`));
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
            // NOTE: this isn't loading images anymore as they are hardcoded in the viewerTabPanels
            $sections.each((i, item) => {
                wpdtrtGalleryUi.galleryViewerInit($, $(item));
            });
        }
    },

    /**
     * @function reset
     * @summary Clean up gallery viewer attributes to prevent cross contamination between image types.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $component - jQuery component
     * @since 1.3.0
     */
    reset: ($, $component) => {
        const $expandButton = $component.find('.wpdtrt-gallery-viewer__expand');

        // remove forced expand used by panorama & iframe viewers
        $component
            .removeAttr('data-lock-expanded');

        if ($component.attr('data-expanded-user') === 'false') {
            $component
                .attr('data-expanded', false);
        }

        $expandButton
            .prop('disabled', false);
    },

    /**
     * @function stringToBoolean
     * @summary Convert expanded state from a data-attribute string to a boolean.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {string} str - String
     *
     * @returns {boolean} componentIsExpanded
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
     * @function toggleExpanded
     * @summary Expand or collapse the viewer.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $component - jQuery component
     * @param {boolean} triggered - Programmatically triggered
     * @returns {boolean} componentIsExpanded
     * @requires includes/attachment.php ?
     * @since 3.0.0
     */
    toggleExpanded: function ($, $component, triggered) {
        const $expandButton = $component.find('.wpdtrt-gallery-viewer__expand');
        const $expandButtonText = $expandButton.find('.says');
        const $tabpanel = $component.find('[role="tabpanel"]:not([hidden])');
        const $tabpanelImg = $tabpanel.find('img');
        const $tabpanelIframe = $tabpanel.find('iframe');
        const $viewer = $component.find('.wpdtrt-gallery-viewer');

        // read data- attributes
        const rwgpsPageId = $tabpanel.data('rwgps-pageid');

        // the actual state
        const componentIsExpanded = $component.attr('data-expanded');

        // post-toggle states to reinstate
        let userExpandedSaved = $component.attr('data-expanded-user');

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
        const isLocked = $component.attr('data-lock-expanded');

        // ------------------------------
        // update state
        // ------------------------------

        if (triggered && isLocked) {
            // A - forced content state
            // data-lock-expanded will only be present if the content type requires it
            // and the button will be hidden so the user won't have to fight it
            // set viewer state to state required by content type
            $component
                .attr('data-expanded', isLocked);

            // this attribute is removed by the Reset function
        } else if (triggered && userExpandedSaved) {
            // B - last user state, only if triggered so we don't fight the user
            // user state may be present when moving from e.g. an image to an iframe and back
            // NOTE: clicking on a thumbnail also fires the trigger.....
            // set viewer state to last/saved toggle state
            $component
                .attr('data-expanded', userExpandedSaved);

            // don't discard attribute after use
            // so we can reinstate this when switching between types
            // $viewer.removeAttr('data-expanded-user');
        } else if (triggered) {
            // C - clicking on a thumbnail before the button has been clicked yet
            // no preference saved - so use the default
            $component
                .attr('data-expanded', false);
        } else {
            // D - toggle - clicking on the expand button to manually and explicitly toggle the state
            // sets the value of data-expanded-user if it is missing
            // save the inverse of the current state
            // as that is the state that we are changing TO
            // this will result in B being used from now on

            $component
                .attr('data-expanded-user', !this.stringToBoolean(componentIsExpanded));

            // for clarity
            userExpandedSaved = !this.stringToBoolean(componentIsExpanded);

            // use the saved user value
            $component
                .attr('data-expanded', userExpandedSaved);
        }

        // ------------------------------
        // update src image & button text
        // ------------------------------

        // if the viewer is now expanded
        if ($component.attr('data-expanded') === 'true') {
            if (!$tabpanel.data('panorama')) {
                $tabpanelImg
                    .attr('src', $tabpanel.data('src-desktop-expanded'));
            }

            $expandButton
                .attr('aria-expanded', true);

            // update the hidden button text
            $expandButtonText
                .text('Collapse');

            if (!triggered) {
                wpdtrtGalleryUi.scrollToElement($, $viewer, 100, 150);
            }
        } else if ($component.attr('data-expanded') === 'false') {
            // if the viewer is now collapsed

            if ($tabpanel.data('panorama')) {
                $tabpanelImg
                    .attr('src', $tabpanel.data('src-panorama'));
            } else {
                $tabpanelImg
                    .attr('src', $tabpanel.data('src-desktop'));
            }

            $expandButton
                .attr('aria-expanded', false);

            // update the hidden button text
            $expandButtonText.text('Expand');

            if (!triggered) {
                wpdtrtGalleryUi.scrollToElement($, $viewer, 100, 150);
            }
        }

        if (isLocked) {
            $expandButton
                .prop('disabled', true);

            $tabpanel
                .attr('tabindex', '0');
        } else {
            $expandButton
                .prop('disabled', false);

            $tabpanel
                .removeAttr('tabindex');
        }

        // update iframe size
        if (rwgpsPageId) {
            $tabpanelIframe
                .attr('height', $tabpanelImg.height());
        }

        return (!componentIsExpanded);
    },

    /**
     * @function updateIframe
     * @summary Update the gallery iframe to display a video, audio file, or interactive map.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $tabpanel - jQuery gallery viewer
     * @requires includes/attachment.php
     * @since 3.0.0
     */
    updateIframe: function ($, $tabpanel) {
        const $component = $tabpanel.parents('.wpdtrt-gallery').eq(0);
        const $expandButton = $component.find('.wpdtrt-gallery-viewer__expand');

        const $tabpanelIframe = $tabpanel.find('iframe');
        const $tabpanelImg = $tabpanel.find('img');

        // const isDefault = $tabpanel.data('initial');
        const soundcloudPageId = $tabpanel.data('soundcloud-pageid');
        const soundcloudTrackId = $tabpanel.data('soundcloud-trackid');
        const rwgpsPageId = $tabpanel.data('rwgps-pageid');
        const vimeoPageId = $tabpanel.data('vimeo-pageid');

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
            $component
                .attr('data-lock-expanded', true);

            $expandButton.trigger('click')
                .prop('disabled', true);

            $tabpanel
                .attr('tabindex', '0');

            // adapted from https://appleple.github.io/modal-video/
            $tabpanelIframe
                .attr({
                    src: `//player.vimeo.com/video/${vimeoPageId}?api=false&autopause=${!autoplay}&autoplay=${autoplay}&byline=false&loop=false&portrait=false&title=false&xhtml=false`,
                    allowfullscreen: 'true',
                    title: 'Vimeo player'
                })
                .removeAttr('aria-hidden')
                .css('height', 368);

            $tabpanelImg
                .attr('aria-hidden', 'true');
        } else if (soundcloudPageId && soundcloudTrackId) {
            // expand viewer
            $component
                .attr('data-lock-expanded', true);

            $expandButton.trigger('click')
                .prop('disabled', true);

            $tabpanel
                .attr('tabindex', '0');

            $tabpanelIframe
                .attr({
                    src: `//w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/${soundcloudTrackId}?auto_play=${autoplay}&hide_related=true&show_comments=false&show_user=false&show_reposts=false&visual=true`,
                    title: 'SoundCloud player'
                })
                .removeAttr('aria-hidden')
                .css('height', 368);

            $tabpanelImg
                .attr('aria-hidden', 'true');
        } else if (rwgpsPageId) {
            // expand viewer
            $component
                .attr('data-lock-expanded', true);

            $expandButton.trigger('click')
                .prop('disabled', true);

            $tabpanel
                .attr('tabindex', '0');

            $tabpanelIframe
                .attr({
                    src: `//rwgps-embeds.com/routes/${rwgpsPageId}/embed`,
                    title: 'Ride With GPS map viewer'
                })
                .removeAttr('aria-hidden')
                .css('height', 500);

            embedHeightTimer = setTimeout(() => {
                $tabpanelIframe
                    .css('height', 500);
            }, 500);

            $tabpanelImg
                .attr('aria-hidden', 'true');
        } else {
            // reset viewer type

            $tabpanelIframe
                .removeAttr('src allowfullscreen')
                .attr({
                    title: 'Gallery media viewer',
                    'aria-hidden': 'true'
                });

            $tabpanelImg
                .removeAttr('aria-hidden');

            clearTimeout(embedHeightTimer);
        }
    },

    /**
     * @function updatePanorama
     * @summary Update the gallery panorama.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $tabpanel - jQuery gallery viewer
     * {@link https://stackoverflow.com/a/17308232/6850747}
     * @since 3.0.0
     * @todo Add startPosition parameter in media.php (panoramaPositionX)
     * @todo Error - #57
     */
    updatePanorama: function ($, $tabpanel) {
        const panorama = $tabpanel.data('panorama');
        const $component = $tabpanel.parents('.wpdtrt-gallery').eq(0);
        const $scrollLiner = $tabpanel.find('.wpdtrt-gallery-viewer__img-wrapper');
        const $expandButton = $component.find('.wpdtrt-gallery-viewer__expand');
        const $gal = $scrollLiner;
        let galleryScrollTimer;
        let galleryScrollSetup;

        $gal.off('.galleryScroll');
        clearInterval(galleryScrollTimer);
        clearTimeout(galleryScrollSetup);

        if (panorama) {
            // expand component (which wraps header)
            $component
                .attr('data-lock-expanded', true);

            $expandButton.trigger('click')
                .prop('disabled', true);

            // 'data-panorama toggles the overflow-x scrollbar
            // which provides the correct $el[ 0 ].scrollWidth value

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

                        // TODO - test
                        tabindex = $tabpanel.attr('data-tabindex');

                        if (tabindex) {
                            $tabpanel
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
                        tabindex = $tabpanel.attr('tabindex');

                        if (tabindex) {
                            $tabpanel
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

            // reset viewer state
            $component
                .removeAttr('data-lock-expanded');
        }
    },

    /**
     * @function imageUpdate
     * @summary Update the gallery image.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $tabpanel - jQuery gallery viewer
     * @requires includes/attachment.php
     * @since 3.0.0
     */
    imageUpdate: function ($, $tabpanel) {
        const $component = $tabpanel.parents('.wpdtrt-gallery');
        // const $tabImage = $tab.find('img');
        const $viewer = $component.find('.wpdtrt-gallery-viewer');
        const $expandButton = $viewer.find('.wpdtrt-gallery-viewer__expand');

        // set the source of the large image which is uncropped
        // after updatePanorama
        if (!$tabpanel.find('img').length) {
            $tabpanel.find('.wpdtrt-gallery-viewer__img-wrapper').append('<img/>');
        }

        // setup viewer
        $expandButton.trigger('click');
    },

    /**
     * @function scrollToElement
     * @summary Scroll to top of element.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $target - Target
     * @param {number} offset - Offset
     * @param {number} duration - Duration
     * @see https://web-design-weekly.com/snippets/scroll-to-position-with-jquery/
     */
    scrollToElement: function ($, $target, offset, duration) {
        if (wpdtrtGalleryUi.animations) {
            $target.each(function () {
                $('html, body').animate({
                    scrollTop: $(this).offset().top - offset
                }, duration);
            });
        }
    },

    /**
     * @function track
     * @summary Only track clicks when these were initiated by the user, not by the setup script.
     * @description The trackingClass is used to define the click target in GTM.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $element - jQuery element
     * @since 1.8.7
     */
    track: function ($, $element) {
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
        const $component = $section.find('.wpdtrt-gallery');
        // const $componentHeader = $component.find('.wpdtrt-gallery__header');

        const $tablist = $component.find('[role="tablist"]');
        const $tabs = $tablist.find('[role="tab"]');

        const $viewer = $component.find('.wpdtrt-gallery-viewer');
        const $viewerLiner = $viewer.find('.wpdtrt-gallery-viewer__liner');
        const $viewerTabPanels = $viewer.find('[role="tabpanel"]');
        const $viewerWrapper = $viewer.find('.wpdtrt-gallery-viewer__wrapper');

        // ?
        // if ($viewer.attr('data-attachment')) {
        //     return;
        // }

        if (!$tablist.length) {
            $component
                .removeAttr('id data-expanded');

            $viewerWrapper
                .remove();

            return;
        }

        $viewerLiner
            .attr('data-loading', true);

        $component
            .attr({
                'data-enabled': true
            });

        $viewerTabPanels.each((i, item) => {
            $(item)
                .prepend('<div class="wpdtrt-gallery-viewer__expand-wrapper"><button class="wpdtrt-gallery-viewer__expand" aria-expanded="false"><span class="says">Expand</span></button></div>')
                .removeAttr('tabindex');
        });

        const $expandButton = $('.wpdtrt-gallery-viewer__expand');

        wpdtrtGalleryUi.track($, $expandButton);

        $expandButton.click((event) => {
            const triggered = !event.originalEvent;

            // prevent the click bubbling up to the viewer, creating an infinite loop
            event
                .stopPropagation();

            const $currentComponent = $(event.target).parents('.wpdtrt-gallery');

            wpdtrtGalleryUi
                .toggleExpanded($, $currentComponent, triggered);
        });

        $tabs.each((i, item) => {
            wpdtrtGalleryUi.galleryViewerCopyImgDataToTab($, $(item));
            wpdtrtGalleryUi.track($, $(item));
        });

        $viewerTabPanels.each((i, item) => {
            wpdtrtGalleryUi.galleryViewerCopyImgDataToTabpanel($, $(item));
        });

        // TODO a regular or pubsub callback in tabbed carousel would be cleaner here
        $tabs.click((event) => {
            let $tab = $(event.target);

            if ($tab.is('span')) {
                $tab = $tab.parent();
            }

            const tabpanelId = $tab.attr('aria-controls'); // this is now the tabpanel
            const $tabpanel = $(`#${tabpanelId}`);

            // only update the viewer if a different tab was selected
            // if ($tab.attr('aria-selected') === 'true') {
            //     return;
            // }

            wpdtrtGalleryUi
                .reset($, $component);

            wpdtrtGalleryUi
                .imageUpdate($, $tabpanel, $tab);

            wpdtrtGalleryUi
                .updatePanorama($, $tabpanel);

            wpdtrtGalleryUi
                .updateIframe($, $tabpanel, $tab);
        });

        $viewerLiner
            .removeAttr('data-loading');

        wpdtrtGalleryUi.galleryViewerAnimations();
    }
};

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
    const config = wpdtrt_gallery_config; // eslint-disable-line
    console.log('wpdtrtGalleryUi.init'); // eslint-disable-line no-console

    document.querySelectorAll('.wpdtrt-gallery__section').forEach((tabbedCarousel) => {
        const tabbedCarouselInstance = new TabbedCarousel({
            initialSelection: '1', // tabbedCarousel.getAttribute('data-initial-selection'),
            instanceElement: tabbedCarousel,
            selectionFollowsFocus: false
        });

        tabbedCarouselInstance.init();
    });
});
