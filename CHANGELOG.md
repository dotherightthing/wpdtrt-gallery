* [9742d46] Update dependencies, incl wpdtrt-plugin-boilerplate from 1.7.6 to 1.7.7 to use Composer v1
* [6c5b91c] Lint PHP
* [e1c26bc] Remove comments as they cause PHPCS to take hours rather than seconds
* [482418d] Update wpdtrt-npm-scripts
* [a809105] Lint PHP
* [17382bd] Remove Cypress tests from release scripts (dotherightthing/wpdtrt-npm-scripts#46)
* [6f04268] Don't cache vendor directory, cache entire cache directory
* [e81c8ff] Remove step skipping
* [7027e58] Do not cache node_modules (Cypress docs)
* [90dbf26] Rename CI step
* [f963d63] Disable Cypress video feature
* [4a30f92] Fix indenting
* [264f7df] Cache dependencies
* [0b64115] Only allow retries when cypress open is used
* [8bdb127] Update tests
* [7ee1f76] Fix syntax
* [792208b] Fix syntax
* [02a048d] Downgrade composer after setting up PHP
* [cb81133] Require version 1 of composer-plugin-api
* [f6bfdbd] Lint JS
* [01a09c5] Prevent linting of icomoon SCSS files
* [1d66f53] Give Cypress access permission to download to cache directory
* [1c54c99] Give Cypress access permission to write to cache directory
* [e9f9af3] Downgrade from Composer 2 in ubuntu-latest, to Composer 1, as v2 causes issues
* [5bb31cd] Update documentation
* [e17c422] Update tests
* [d34ed44] Fix width of panorama image
* [b453880] Reinstate aria-controls on expand button, pointing at the affected img/iframe
* [5047408] Lint PHP
* [87ac491] Update tests
* [7165120] Make Cypress available via NPM scripts
* [a62dfc4] Retries are now supported by Cypress
* [014a2a6] Update Cypress
* [ea6c56a] Print styles
* [28d66f1] Add space below tab hint on mobile
* [e7d56f4] Refactor box shadows to be IE11 compatible
* [d841f68] Update accessible-components to only reset focus when the last selected tab on the page has focus
* [eb71c9e] Fix selected state of first tab when JS is disabled
* [c9ab515] Update accessible-components to fix IE11 errors
* [da24bf8] Reduce grid layout degradation in IE11
* [f42db33] Fix tab spacing
* [239abea] Remove flex layout from gallery
* [217ba19] Lint JS
* [7ff444b] Add IE9+ polyfill for forEach
* [876a510] Fix IE11 property test
* [1883cd0] Update accessible-components to prevent initialisation from moving the focus down the page
* [2d5d085] Fix border at top of soundcloud player
* [2d10a59] Fix auto-expand of soundcloud media
* [1222881] Refactor tabhint reveal and document why it's shown to mouse users
* [98c2087] Fix IE11 error
* [a016446] Optimise gallery shortcode
* [bae1236] Optimise JS
* [7f3e715] Optimise gallery shortcode
* [fed2aed] Allow use of aria-hidden on visible elements
* [7172bd3] Keep tabhint in DOM, visually hide contents
* [cea2093] Fix hover state for expand button
* [1e2d012] Change to lighter keyboard icon to match hand icon
* [a013770] Allow for infinite lines of tabhint text
* [d6fd6bb] Style gallery header
* [57e47d1] Remove tabindex when expand button is enabled
* [5e5868b] Add hand icon
* [ce57111] Rename and reorder functions, document, use jQuery 3.0 syntax for document ready
* [090e7fb] Optimise state management
* [f11c2f4] Refactor variable names
* [171b4aa] Bind click events on a per gallery basis
* [94c0f47] Fix syntax error
* [f7f5032] Scroll to section rather than gallery
* [a276ab3] Use span for icon rather than pseudo element
* [bdf53ac] Add tab pattern keyboard hint (#121)
* [c3d5b40] Add aria-label to icon
* [8de2dd3] Add keyboard icon
* [bc6afcf] Add tab pattern keyboard hint (fixes #121)
* [2bcb115] Lint JS
* [abdbc5f] Fix expanded state management (fixes #118)
* [77f7c27] Fix panorama mouse-move scrolling (fixes #123)
* [1c426b1] Update tests (incomplete)
* [ed7c62f] Update lazy loading (incomplete)
* [d8da5ef] Add borders above and below expanded media (top emulates Soundcloud player)
* [82dda20] Fix gap between expanded viewer and caption
* [1aa15e8] Manage icons in PHP rather than CSS, rename __embed to __iframe-wrapper, rename __footer to __caption-wrapper, use media icon in tabpanel background and caption
* [ebf2678] Housekeeping
* [0408c7b] Image and iframe no longer co-exist
* [019382b] Use icon attribute selectors rather than classes
* [cfcefa8] Update wpdtrt-plugin-boilerplate from 1.7.5 to 1.7.6 to fix saving of admin field values
* [61c3a19] Add demo/test pages to README
* [63ecdc3] Update tab icons
* [219b1a0] Fix missing gallery heading, link heading to tabs with aria-labelledby
* [88163ac] 'enable' gallery styling in PHP rather than JS
* [6ad8e47] Remove duplicate anchor link highlighting controller
* [c8f7b69] Improve visibility of selected tab
* [291d3b3] Remove redundant variable
* [92971c2] Fix boolean checks
* [f5ee095] Restyle tab states
* [836fd0e] Update accessible-components to fix Safari button focus issue
* [eb797d2] Prevent tab-liner from stealing tab button click event in Safari
* [883df84] Make tab icon and border separate elements
* [fea5d92] Pass wprtrt-anchorlinks anchor element to shortcode as an HTML string rather than individual shortcode attributes
* [48a48e8] Pass data-anchorlinks-id to shortcode as a generic attribute-value pair
* [8f0d2d0] Fix heading style if no gallery
* [e3321e4] Remove redundant HTML from no-gallery heading template
* [30efbd5] Fix missing wpdtrt-anchorlinks anchor list targets by retaining data-anchorlinks-id attribute when injecting gallery title
* [a34e89e] Update gallery tab styling, refactor variable names
* [ec1bda3] Update gallery icons and refactor styling
* [055f173] Update accessible-components
* [55d26f0] Fix tab selected state
* [0ebb1d7] Fix tab hover state
* [2ac66a1] Fix panorama image source
* [c8a3bc6] Add class to tab, grey out tab when disabled
* [9b82d00] Add tab and tabpanel data attributes via PHP rather than JS
* [c8b30e7] Optimise gallery JS
* [c2f965e] Update accessible-components
* [4a0bd7c] Implement tabs pattern in gallery
* [b3e8e07] Remove heading if there is a gallery, else wrap heading in shortcode
* [4dfb305] Lint PHP
* [8e240c5] Pass heading attributes to gallery shortcode when reordering HTML using filter_content_galleries
* [7906257] Use single flag to control tabs pattern output
* [b20f3b6] Lint JS
* [4b5d6af] Update accessible-components
* [1d527b5] Implement tabs pattern in gallery
* [0bd1a4d] Replace gallery output by duplicating and adapting core function
* [de61d3a] Update wpdtrt-npm-scripts, move touchstart fix to wpdtrt-dbth
* [6ab1cf0] Reveal photo gallery heading
* [bf550ef] Allow user to disable page scroll animation on expand/collapse (fixes #111)
* [b2bb645] Install Cypress to run existing tests
