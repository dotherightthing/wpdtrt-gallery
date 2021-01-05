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

    /**
     * @function setupAnimations
     * @summary Allow animations to be managed via browser preferences.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @see https://github.com/dotherightthing/accessibility-research
     */
    setupAnimations: function () {
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
     * @function galleryLazyInit
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
    galleryLazyInit: ($) => {
        const $sections = $('.wpdtrt-gallery');

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

                    wpdtrtGalleryUi.galleryInit($, $inView.find('.wpdtrt-gallery'));
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
            // NOTE: this isn't loading images anymore as they are hardcoded in the tabPanels
            $sections.each((i, item) => {
                wpdtrtGalleryUi.galleryInit($, $(item).find('.wpdtrt-gallery'));
            });
        }
    },

    /**
     * @function reset
     * @summary Prevents cross contamination between media types when changing the active tabpanel.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $gallery - Gallery
     * @since 1.3.0
     */
    reset: ($gallery) => {
        const $expandButton = $gallery.find('.wpdtrt-gallery-viewer__expand');

        // remove forced expand used by panorama & iframe viewers
        $gallery
            .attr('data-expanded-locked', false);

        if ($gallery.attr('data-expanded-user') === 'false') {
            $gallery
                .attr('data-expanded', false);
        }

        $expandButton
            .prop('disabled', false);
    },

    /**
     * @function triggerToggleExpanded
     * @summary Trigger the function which sets up the gallery state.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $tabpanel - The active tabpanel
     * @since 1.3.0
     */
    triggerToggleExpanded: ($tabpanel) => {
        const $expandButton = $tabpanel.find('.wpdtrt-gallery-viewer__expand');

        // setup viewer
        $expandButton.trigger('click');
    },

    /**
     * @function toggleExpanded
     * @summary Expand or collapse the viewer.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $gallery - Gallery
     * @param {boolean} triggered - Programmatically triggered
     * @returns {boolean} isExpanded
     * @requires includes/attachment.php ?
     * @since 3.0.0
     * @todo pubsub or observer to fix expand button after keyboard-activated tabpanel change
     */
    toggleExpanded: function ($, $gallery, triggered) {
        const $expandButton = $gallery.find('.wpdtrt-gallery-viewer__expand');
        const $expandButtonText = $expandButton.find('.says');
        const $tabpanel = $gallery.find('[role="tabpanel"]:not([hidden])');
        const $tabpanelImg = $tabpanel.find('img');

        // the actual state
        // toggled to false by the reset function unless isUserExpanded
        const isExpanded = $gallery.attr('data-expanded') === 'true';

        // post-toggle states to reinstate
        let isUserExpanded = $gallery.attr('data-expanded-user') === 'true';

        // locked state for use with iframe embeds
        // removed by the reset function
        const isLockedExpanded = $gallery.attr('data-expanded-locked') === 'true';

        // ------------------------------
        // update state
        // ------------------------------

        if (triggered && isLockedExpanded) {
            // A - forced content state - set viewer state to state required by content type
            // data-expanded-locked will only be present if the content type requires it
            // and the button will be disabled so the user won't have to fight it
            $gallery
                .attr('data-expanded', isLockedExpanded);
        } else if (triggered && isUserExpanded) {
            // B - set viewer state to last/saved toggle state
            // only if triggered so we don't fight the user
            // user state may be present when moving from e.g. an image to an iframe and back
            $gallery
                .attr('data-expanded', isUserExpanded);
        } else if (!triggered) {
            // C - toggle - clicking on the expand button to manually and explicitly toggle the state
            // sets the value of data-expanded-user if it is missing
            // save the inverse of the current state
            // as that is the state that we are changing TO
            // this will result in B being used from now on

            $gallery
                .attr('data-expanded-user', !isExpanded);

            $gallery
                .attr('data-expanded', !isExpanded);
        }

        // ------------------------------
        // update src image & button text
        // ------------------------------

        // if the viewer is now expanded
        if ($gallery.attr('data-expanded') === 'true') {
            if ($tabpanelImg.length && !$tabpanel.data('panorama')) {
                $tabpanelImg
                    .attr('src', $tabpanel.data('src-desktop-expanded'));
            }

            $expandButton
                .attr('aria-expanded', true);

            // update the hidden button text
            $expandButtonText
                .text('Collapse');

            if (!triggered) {
                wpdtrtGalleryUi.scrollToElement($, $gallery, 100, 150);
            }
        } else if ($gallery.attr('data-expanded') === 'false') {
            // if the viewer is now collapsed

            if ($tabpanelImg.length && !$tabpanel.data('panorama')) {
                $tabpanelImg
                    .attr('src', $tabpanel.data('src-desktop'));
            }

            $expandButton
                .attr('aria-expanded', false);

            // update the hidden button text
            $expandButtonText
                .text('Expand');

            if (!triggered) {
                wpdtrtGalleryUi.scrollToElement($, $gallery, 100, 150);
            }
        }

        if (isLockedExpanded) {
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

        return (!isExpanded);
    },

    /**
     * @function autoExpandForComplexMedia
     * @summary Expand the gallery if non-image media.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $tabpanel - jQuery gallery viewer
     * @returns {boolean} autoExpanded
     * @requires includes/attachment.php
     * @since 3.0.0
     */
    autoExpandForComplexMedia: function ($tabpanel) {
        const autoExpanded = false;
        const $gallery = $tabpanel.parents('.wpdtrt-gallery').eq(0);
        const $expandButton = $gallery.find('.wpdtrt-gallery-viewer__expand');

        // const isDefault = $tabpanel.data('initial');
        const panorama = $tabpanel.data('panorama');
        const rwgps = $tabpanel.data('rwgps-pageid');
        const soundcloud = $tabpanel.data('soundcloud-pageid') && $tabpanel.data('soundcloud-trackid');
        const vimeo = $tabpanel.data('vimeo-pageid');

        if (panorama || rwgps || soundcloud || vimeo) {
            // set flag to expand viewer on triggerToggleExpanded
            $gallery
                .attr('data-expanded-locked', true);

            $expandButton.trigger('click')
                .prop('disabled', true);

            $tabpanel
                .attr('tabindex', '0');

            autoExpanded = true;
        }

        return autoExpanded;
    },

    /**
     * @function setupPanoramaScrolling
     * @summary Setup the gallery panorama.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $tabpanel - jQuery gallery viewer
     * @param {external:jQuery} $ - jQuery
     * {@link https://stackoverflow.com/a/17308232/6850747}
     * @since 3.0.0
     * @todo Add startPosition parameter in media.php (panoramaPositionX)
     * @todo Error - #57
     */
    setupPanoramaScrolling: function ($tabpanel, $) {
        const panorama = $tabpanel.data('panorama');
        const $galleryImgWrapper = $tabpanel.find('.wpdtrt-gallery-viewer__img-wrapper');
        let galleryScrollTimer;

        if (panorama) {
            // 'data-panorama toggles the overflow-x scrollbar
            // which provides the correct $el[ 0 ].scrollWidth value

            // TODO move into separate file, add a data- attribute
            // timeout ensures that the related CSS has taken effect
            setTimeout(() => {
                const uiWidth = $galleryImgWrapper.outerWidth(true); // setTimeout reqd for this value
                const actualWidth = $galleryImgWrapper[0].scrollWidth;
                const wDiff = (actualWidth / uiWidth); // widths difference ratio
                const mPadd = 75; // Mousemove padding
                const damp = 10; // Mousemove response softness
                let mX = 0; // Real mouse position
                let mX2 = 0; // Modified mouse position
                let posX = 0;
                const mmAA = uiWidth - (mPadd * 2); // The mousemove available area
                const mmAAr = (uiWidth / mmAA); // get available mousemove difference ratio
                let tabindex = null;

                $galleryImgWrapper
                    .on('mousemove.galleryScroll', function (event) {
                        mX = event.pageX - $(this).offset().left;
                        mX2 = Math.min(Math.max(0, mX - mPadd), mmAA) * mmAAr;
                    })
                    .on('mouseenter.galleryScroll', () => {
                        galleryScrollTimer = setInterval(() => {
                            posX += (mX2 - posX) / damp; // zeno's paradox equation 'catching delay'
                            $galleryImgWrapper.scrollLeft(posX * wDiff);
                        }, 10);

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
                        $galleryImgWrapper
                            .trigger('mouseenter.galleryScroll');
                    });
            }, 100);
        }
    },

    /**
     * @function setupImageLoading
     * @summary Update the gallery image.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $tabpanel - jQuery gallery viewer
     * @requires includes/attachment.php
     * @todo Reimplement lazy loading
     * @since 3.0.0
     */
    setupImageLoading: function ($tabpanel) {
        // const $gallery = $tabpanel.parents('.wpdtrt-gallery');
        // const $expandButton = $gallery.find('.wpdtrt-gallery-viewer__expand');
        // const $img = $tabpanel.find('img');

        // lazy loading
        // if ($img) {
        //     if ($img.attr('src') === '') {
        //         const desktopSrc = $tabpanel.data('src-desktop');
        //         $img.attr('src', desktopSrc);
        //     }
        // }

        // setup viewer
        // $expandButton.trigger('click');
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
     * @function trackOnHover
     * @summary Only track clicks when these were initiated by the user, not by the setup script.
     * @description The trackingClass is used to define the click target in GTM.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $element - jQuery element
     * @since 1.8.7
     */
    trackOnHover: function ($, $element) {
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
     * @function tabpanelInit
     * @summary Re-initialise a gallery when the active tabpanel changes.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $gallery - .wpdtrt-gallery
     * @param {external:jQuery} $tabpanel - selected tabpanel
     * @requires includes/attachment.php
     * @since 3.0.0
     */
    tabpanelInit: function ($gallery, $tabpanel) {
        wpdtrtGalleryUi
            .reset($gallery);

        wpdtrtGalleryUi
            .autoExpandForComplexMedia($tabpanel);

        wpdtrtGalleryUi
            .triggerToggleExpanded($tabpanel);
    },

    /**
     * @function galleryInit
     * @summary Initialise a gallery on page load.
     * @memberof wpdtrtGalleryUi
     * @protected
     *
     * @param {external:jQuery} $ - jQuery
     * @param {external:jQuery} $gallery - .wpdtrt-gallery
     * @requires includes/attachment.php
     * @since 3.0.0
     */
    galleryInit: function ($, $gallery) {
        const $tablist = $gallery.find('[role="tablist"]');
        const $tabs = $tablist.find('[role="tab"]');
        const $viewer = $gallery.find('.wpdtrt-gallery-viewer');
        const $viewerLiner = $viewer.find('.wpdtrt-gallery-viewer__liner');
        const $tabPanels = $viewer.find('[role="tabpanel"]');

        $viewerLiner
            .attr('data-loading', true);

        $tabs.each((i, item) => {
            wpdtrtGalleryUi.trackOnHover($, $(item)); // TODO change to panel onTabSelect
        });

        $tabPanels.each((i, item) => {
            const $tabpanel = $(item);

            $tabpanel
                .prepend('<div class="wpdtrt-gallery-viewer__expand-wrapper"><button class="wpdtrt-gallery-viewer__expand" aria-expanded="false"><span class="says">Expand</span></button></div>')
                .removeAttr('tabindex');

            wpdtrtGalleryUi.setupImageLoading($tabpanel);
            wpdtrtGalleryUi.setupPanoramaScrolling($tabpanel, $);
        });

        const $expandButton = $('.wpdtrt-gallery-viewer__expand');

        wpdtrtGalleryUi.trackOnHover($, $expandButton);

        $expandButton.click((event) => {
            const triggered = !event.originalEvent;

            // prevent the click bubbling up to the viewer, creating an infinite loop
            event
                .stopPropagation();

            const $currentGallery = $(event.target).parents('.wpdtrt-gallery').eq(0);

            wpdtrtGalleryUi.toggleExpanded($, $currentGallery, triggered);
        });

        $viewerLiner
            .removeAttr('data-loading');

        wpdtrtGalleryUi.setupAnimations();
    }
};

// https://api.jquery.com/jQuery.noConflict/
/* eslint-disable wrap-iife */
// (function ($) {
//     $(window).on('load', () => {
//         // defer initialisation of viewers, to reduce initial load time
//         wpdtrtGalleryUi.galleryLazyInit($);
//     });
// })(jQuery);
/* eslint-enable wrap-iife */

jQuery(document).ready(($) => {
    const config = wpdtrt_gallery_config; // eslint-disable-line

    // replacement for disabled window.onload
    $('.wpdtrt-gallery').each((i, item) => {
        const $gallery = $(item);
        wpdtrtGalleryUi.galleryInit($, $gallery);
    });

    console.log('wpdtrtGalleryUi.galleryInit'); // eslint-disable-line no-console

    document.querySelectorAll('.wpdtrt-gallery__section').forEach((tabbedCarousel) => {
        const tabbedCarouselInstance = new TabbedCarousel({
            initialSelection: '1', // tabbedCarousel.getAttribute('data-initial-selection'),
            instanceElement: tabbedCarousel,
            onTabSelect: (selectedTabPanel) => {
                const $tabpanel = $(selectedTabPanel);
                const $gallery = $tabpanel.parents('.wpdtrt-gallery').eq(0);

                wpdtrtGalleryUi.tabpanelInit($gallery, $tabpanel);
            },
            selectionFollowsFocus: false
        });

        tabbedCarouselInstance.init();
    });
});
